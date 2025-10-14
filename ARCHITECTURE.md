# System Architecture - SPMB STM

> Technical deep dive into the system architecture, database design, and implementation details.

---

## 📋 Table of Contents

- [System Overview](#system-overview)
- [Database Schema](#database-schema)
- [Application Layers](#application-layers)
- [Key Design Patterns](#key-design-patterns)
- [Data Flow](#data-flow)
- [Security](#security)
- [Performance Considerations](#performance-considerations)

---

## 🏗️ System Overview

### **Application Type**
Web-based Student Registration System (PPDB) with separate public and admin interfaces.

### **Architecture Style**
- **Pattern:** MVC (Model-View-Controller)
- **Admin:** Component-based (Filament)
- **Public:** Traditional blade templates with Livewire components

### **Deployment Model**
- **Type:** Monolithic web application
- **Server:** PHP-FPM + Nginx/Apache
- **Database:** Single MySQL instance
- **Storage:** Local filesystem (expandable to S3)

---

## 🗄️ Database Schema

### **ERD Overview**

```
┌─────────────┐
│   waves     │
└──────┬──────┘
       │ 1:N
       │
┌──────▼──────────┐       ┌─────────────┐
│   applicants    │───N:1─│   forms     │
└──────┬──────────┘       └──────┬──────┘
       │ 1:N                     │ 1:N
       │                         │
       │                  ┌──────▼────────────┐
       │                  │  form_versions    │
       │                  └──────┬────────────┘
       │                         │ 1:N
       │                         │
       │                  ┌──────▼────────────┐
       │                  │   form_steps      │
       │                  └──────┬────────────┘
       │                         │ 1:N
       │                         │
       │                  ┌──────▼────────────┐
       │                  │   form_fields     │
       │                  └───────────────────┘
       │
       │ 1:N             1:N
┌──────▼──────────┐  ┌──────▼──────────┐
│   submissions   │  │    payments     │
└──────┬──────────┘  └─────────────────┘
       │ 1:N
       │
┌──────▼────────────────┐
│ submission_answers    │
│ submission_files      │
└───────────────────────┘
```

---

### **Core Tables**

#### **waves**
Registration periods with quotas and fees.

```sql
CREATE TABLE waves (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    wave_name VARCHAR(50) NOT NULL,
    wave_code VARCHAR(30) UNIQUE NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    quota_limit INT NULL,
    registration_fee_amount DECIMAL(12,2) NOT NULL,
    is_active BOOLEAN NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Business Rules:**
- Only 1 active wave at a time
- Active wave displayed on public form
- End datetime must be after start datetime

---

#### **applicants**
Main entity for registered students.

```sql
CREATE TABLE applicants (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    registration_number VARCHAR(30) UNIQUE NOT NULL,
    applicant_full_name VARCHAR(150) NOT NULL,
    applicant_nisn VARCHAR(20) NOT NULL,
    applicant_phone_number VARCHAR(30) NOT NULL,
    applicant_email_address VARCHAR(150) NOT NULL,
    chosen_major_name VARCHAR(50) NOT NULL,
    wave_id BIGINT NOT NULL,
    payment_status VARCHAR(20) NULL, -- ⚠️ DEPRECATED (computed)
    registered_datetime DATETIME NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (wave_id) REFERENCES waves(id) ON DELETE CASCADE
);
```

**Important Notes:**
- `payment_status` is **COMPUTED** from `payments.payment_status_name`
- Use `$applicant->payment_status` accessor (not direct column)
- See: `docs/payment/PAYMENT_STATUS_REFACTOR_SUMMARY.md`

---

#### **forms** & **form_versions**
Dynamic form definition with versioning.

```sql
CREATE TABLE forms (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    form_name VARCHAR(100) NOT NULL,
    form_code VARCHAR(50) UNIQUE NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE form_versions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    form_id BIGINT NOT NULL,
    version_number INT NOT NULL,
    is_active BOOLEAN NOT NULL,
    published_datetime TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE
);
```

**Versioning Logic:**
- Each form can have multiple versions
- Only 1 version active per form at a time
- Old submissions reference specific version
- Allows form evolution without breaking old data

---

#### **form_fields**
Dynamic field definitions.

```sql
CREATE TABLE form_fields (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    form_version_id BIGINT NOT NULL,
    form_step_id BIGINT NOT NULL,
    field_key VARCHAR(100) NOT NULL,
    field_label VARCHAR(255) NOT NULL,
    field_type VARCHAR(30) NOT NULL, -- text, select, file, etc.
    field_options_json JSON NULL,
    is_required BOOLEAN NOT NULL,
    is_filterable BOOLEAN NOT NULL,
    is_exportable BOOLEAN NOT NULL,
    is_archived BOOLEAN NOT NULL,
    is_system_field BOOLEAN DEFAULT FALSE, -- ⭐ Protection flag
    field_placeholder_text VARCHAR(255) NULL,
    field_help_text VARCHAR(255) NULL,
    field_order_number INT NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (form_version_id) REFERENCES form_versions(id) ON DELETE CASCADE,
    FOREIGN KEY (form_step_id) REFERENCES form_steps(id) ON DELETE CASCADE
);
```

**System Fields Protection:**
- `is_system_field = true` for critical fields (nama_lengkap, nisn, email, etc.)
- Protected fields:
  - ❌ Cannot change `field_key`
  - ❌ Cannot change `field_type`
  - ❌ Cannot be archived
  - ❌ Cannot be deleted
  - ✅ Can change `field_label` (for customization)
- Protection implemented at model level (silent revert)
- See: `docs/SYSTEM_FIELDS_PROTECTION_GUIDE.md`

---

#### **submissions**
Submitted form data.

```sql
CREATE TABLE submissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    applicant_id BIGINT NOT NULL,
    form_id BIGINT NOT NULL,
    form_version_id BIGINT NOT NULL,
    answers_json JSON NOT NULL, -- All answers in JSON format
    submitted_datetime DATETIME NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE,
    FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE,
    FOREIGN KEY (form_version_id) REFERENCES form_versions(id) ON DELETE CASCADE
);
```

**Data Storage:**
- `answers_json`: Key-value pairs `{field_key: value}`
- Example: `{"nama_lengkap": "Budi", "email": "budi@email.com"}`
- Flexible schema (no need to alter table for new fields)

---

#### **payments**
Payment transactions via Midtrans.

```sql
CREATE TABLE payments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    applicant_id BIGINT NOT NULL,
    payment_order_id VARCHAR(50) UNIQUE NOT NULL,
    payment_amount DECIMAL(12,2) NOT NULL,
    payment_status_name VARCHAR(30) NOT NULL, -- ⭐ Source of truth
    payment_method_name VARCHAR(50) NULL,
    midtrans_transaction_id VARCHAR(100) NULL,
    midtrans_snap_token TEXT NULL,
    paid_datetime DATETIME NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE
);
```

**Single Source of Truth:**
```php
// ✅ CORRECT: Payment status is THE source
$payment->payment_status_name // PaymentStatus ENUM

// ✅ Applicant status is COMPUTED
$applicant->payment_status // Computed from latestPayment

// ❌ WRONG: Don't update applicant.payment_status manually!
```

---

## 🏛️ Application Layers

### **1. Presentation Layer**

**Public Interface:**
- `resources/views/registration/` - Blade templates
- `app/Livewire/` - Dynamic components
- Tailwind CSS styling
- Alpine.js for interactions

**Admin Interface:**
- Filament 3.x resources
- `app/Filament/Resources/`
- Component-based UI
- Auto-generated CRUD

---

### **2. Application Layer**

**Controllers:**
- `app/Http/Controllers/RegistrationController.php` - Public form
- `app/Http/Controllers/PaymentController.php` - Payment handling
- `app/Http/Controllers/FileDownloadController.php` - File serving

**Filament Resources:**
- `ApplicantResource` - Student management
- `FormResource` - Form builder
- `WaveResource` - Wave management
- `PaymentResource` - Payment tracking

---

### **3. Business Logic Layer**

**Services:**
- `app/Services/MidtransService.php` - Payment gateway integration

**Models (with business logic):**
- `Applicant.php` - Payment status accessors
- `FormField.php` - System field protection
- `Payment.php` - Status management

**Enums:**
- `PaymentStatus.php` - Payment status enum with colors & labels
- `PaymentMethod.php` - Payment method enum

---

### **4. Data Access Layer**

**Eloquent Models:**
- Relationships defined
- Accessors & mutators
- Scopes for queries
- Model events for protection

**Database Interactions:**
- Query builder for complex queries
- Eager loading to avoid N+1
- Transactions for data integrity

---

## 🎨 Key Design Patterns

### **1. Single Source of Truth (Payment Status)**

**Problem:** Dual status sync (Payment ↔ Applicant) caused inconsistencies.

**Solution:**
```php
// Payment is THE source
class Payment {
    protected $casts = [
        'payment_status_name' => PaymentStatus::class, // ENUM
    ];
}

// Applicant computes from Payment
class Applicant {
    public function getPaymentStatusAttribute(): ?string {
        return $this->latestPayment?->payment_status_name->value;
    }
    
    public function hasSuccessfulPayment(): bool {
        return $this->payment_status === 'settlement';
    }
}
```

**Benefits:**
- No manual syncing
- Always consistent
- Single update point

---

### **2. System Fields Protection**

**Problem:** Admin could accidentally change critical field_key → breaks registration.

**Solution:**
```php
class FormField extends Model {
    protected static function booted(): void {
        static::updating(function (FormField $field) {
            if ($field->is_system_field) {
                // Silently revert changes
                if ($field->isDirty('field_key')) {
                    $field->field_key = $field->getOriginal('field_key');
                }
                
                if ($field->isDirty('field_type')) {
                    $field->field_type = $field->getOriginal('field_type');
                }
                
                if ($field->isDirty('is_archived') && $field->is_archived) {
                    $field->is_archived = false;
                }
            }
        });
        
        static::deleting(function (FormField $field) {
            if ($field->is_system_field) {
                return false; // Prevent deletion
            }
        });
    }
}
```

**Benefits:**
- No exceptions thrown
- Silent protection
- Smooth UX
- UI already disables inputs

---

### **3. Dynamic Form with Versioning**

**Pattern:** Content Versioning Pattern

**Implementation:**
- Form → FormVersion → FormFields
- Submissions reference specific version
- Allows form evolution without breaking old data

**Benefits:**
- Can change form without affecting old submissions
- Historical data integrity
- Admin freedom to iterate

---

### **4. Service Layer for External APIs**

**Pattern:** Facade/Service Pattern

```php
class MidtransService {
    public function createTransaction(Applicant $applicant, Wave $wave): array
    {
        // Configure Midtrans
        // Create transaction
        // Return snap token
    }
    
    public function handleNotification(array $notification): void
    {
        // Verify signature
        // Update payment status
        // No manual applicant update needed!
    }
}
```

**Benefits:**
- Encapsulates Midtrans API
- Easy to mock in tests
- Centralized error handling

---

## 🔄 Data Flow

### **Registration Flow**

```
┌──────────┐
│  User    │
└────┬─────┘
     │ 1. Fill form
     ▼
┌──────────────────┐
│ RegistrationCtrl │
└────┬─────────────┘
     │ 2. Validate
     │ 3. DB::transaction()
     ▼
┌──────────────────┐
│ Create Applicant │ ──→ applicants table
└────┬─────────────┘
     │
     ▼
┌──────────────────┐
│ Create Submission│ ──→ submissions table
└────┬─────────────┘
     │
     ▼
┌──────────────────┐
│  Save Files      │ ──→ submission_files table + storage/
└────┬─────────────┘
     │
     ▼
┌──────────────────┐
│ Create Payment   │ ──→ payments table (status: pending)
└────┬─────────────┘
     │
     ▼
┌──────────────────┐
│ MidtransService  │ ──→ Generate Snap token
└────┬─────────────┘
     │
     ▼
┌──────────────────┐
│ Redirect to      │
│ Midtrans Snap    │
└──────────────────┘
```

---

### **Payment Notification Flow**

```
┌──────────────┐
│  Midtrans    │
└──────┬───────┘
       │ POST /payment/notification
       ▼
┌───────────────────┐
│ PaymentController │
└──────┬────────────┘
       │ 1. Verify signature
       │ 2. Get payment by order_id
       ▼
┌───────────────────┐
│ MidtransService   │
└──────┬────────────┘
       │ 3. Map status
       │    (settlement, pending, failure, etc.)
       ▼
┌───────────────────┐
│ Update Payment    │ ──→ payments.payment_status_name
└──────┬────────────┘
       │
       │ 4. Applicant.payment_status
       │    automatically computed! ✅
       ▼
┌───────────────────┐
│ Return 200 OK     │
└───────────────────┘
```

---

### **Admin View Data Flow**

```
┌──────────┐
│  Admin   │
└────┬─────┘
     │ View Applicant
     ▼
┌────────────────────┐
│ ApplicantResource  │
└────┬───────────────┘
     │ Eager load:
     │ - latestSubmission
     │ - latestPayment
     ▼
┌────────────────────┐
│ ViewApplicant page │
└────┬───────────────┘
     │
     ├─→ Show applicant info
     │
     ├─→ Show payment status (computed badge)
     │
     └─→ Show form answers (from submission.answers_json)
         ├─ Match field_key to FormField
         ├─ Display with current field_label
         └─ Render files inline (images/PDFs)
```

---

## 🔐 Security

### **Authentication**
- Admin: Filament default auth
- Public: No auth (open registration)

### **Authorization**
- Filament policies for resources
- Role-based access (extendable)

### **Input Validation**
- Server-side validation in controllers
- Form request classes
- Filament form validation

### **File Upload Security**
- MIME type validation
- File size limits
- Unique filenames
- Storage outside public root

### **Payment Security**
- Midtrans signature verification
- HTTPS required
- Order ID uniqueness
- Webhook IP validation (optional)

### **SQL Injection Protection**
- Eloquent ORM (parameterized queries)
- No raw SQL with user input

### **XSS Protection**
- Blade `{{ }}` auto-escaping
- Filament sanitizes inputs

---

## ⚡ Performance Considerations

### **Database Optimization**

**Indexes:**
```sql
-- Critical indexes
ALTER TABLE applicants ADD INDEX idx_payment_status (payment_status);
ALTER TABLE applicants ADD INDEX idx_wave_id (wave_id);
ALTER TABLE payments ADD INDEX idx_applicant_id (applicant_id);
ALTER TABLE payments ADD INDEX idx_order_id (payment_order_id);
ALTER TABLE submissions ADD INDEX idx_applicant_id (applicant_id);
```

**Eager Loading:**
```php
// ✅ GOOD: Eager load relations
Applicant::with(['latestSubmission', 'latestPayment'])->get();

// ❌ BAD: N+1 query problem
$applicants = Applicant::all();
foreach ($applicants as $applicant) {
    echo $applicant->latestPayment->status; // N+1!
}
```

---

### **Caching Strategy**

**Config caching:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Query caching (if needed):**
```php
$activeWave = Cache::remember('active_wave', 3600, function () {
    return Wave::where('is_active', true)->first();
});
```

---

### **File Storage**

**Current:** Local storage (`storage/app/public/`)

**Scalable option:**
- Move to S3 or CDN
- Update `config/filesystems.php`
- No code changes needed (Laravel abstraction)

---

### **Queue System**

**Current:** Sync (no queue)

**Recommended for production:**
```php
// config/queue.php
'default' => env('QUEUE_CONNECTION', 'redis'),
```

**Queue-able tasks:**
- Email notifications
- Export large datasets
- Image processing

---

## 📚 References

- **Design Patterns:** Gang of Four, Laravel Best Practices
- **Database:** MySQL 8.0 Documentation
- **Payment:** Midtrans API Documentation
- **Framework:** Laravel 10.x Documentation

---

**Last Updated:** 2025-01-13  
**Version:** 1.0
