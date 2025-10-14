# Developer Guide - SPMB STM

> Panduan lengkap untuk developer yang akan mengembangkan atau maintain sistem PPDB ini.

---

## 📖 Table of Contents

- [Architecture Overview](#architecture-overview)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Key Features](#key-features)
- [Development Workflow](#development-workflow)
- [Code Conventions](#code-conventions)
- [Testing](#testing)
- [Deployment](#deployment)

---

## 🏗️ Architecture Overview

Sistem PPDB (Penerimaan Peserta Didik Baru) ini menggunakan architecture Laravel modern dengan Filament Admin Panel.

### **High-Level Architecture**

```
┌─────────────────────────────────────────────────────────┐
│                    CLIENT BROWSER                       │
└──────────────────┬──────────────────────────────────────┘
                   │
          ┌────────┴────────┐
          │                 │
    ┌─────▼──────┐   ┌─────▼─────────┐
    │   Public   │   │  Admin Panel  │
    │    Form    │   │   (Filament)  │
    └─────┬──────┘   └─────┬─────────┘
          │                 │
          └────────┬────────┘
                   │
         ┌─────────▼──────────┐
         │   Laravel Backend  │
         │  ┌──────────────┐  │
         │  │ Controllers  │  │
         │  │   Models     │  │
         │  │  Services    │  │
         │  └──────────────┘  │
         └─────────┬──────────┘
                   │
         ┌─────────▼──────────┐
         │    MySQL Database  │
         └─────────┬──────────┘
                   │
         ┌─────────▼──────────┐
         │  External Services │
         │  • Midtrans API    │
         │  • File Storage    │
         └────────────────────┘
```

### **Data Flow**

**Registration Flow:**
```
User fills form → Validation → Create Applicant → Create Submission 
→ Save Files → Generate Payment → Redirect to Midtrans
```

**Admin Management Flow:**
```
Admin login → Filament Dashboard → Manage (Applicants/Forms/Waves/Payments)
→ Export Data → View Reports
```

---

## 🛠️ Tech Stack

### **Core**
- **Framework:** Laravel 10.x
- **PHP:** 8.1+
- **Database:** MySQL 8.0
- **Admin Panel:** Filament 3.x

### **Frontend**
- **CSS Framework:** Tailwind CSS 3.x
- **JS Framework:** Alpine.js (via Livewire)
- **Build Tool:** Vite

### **Key Packages**
- `filament/filament` - Admin panel
- `livewire/livewire` - Dynamic components
- `midtrans/midtrans-php` - Payment gateway
- `maatwebsite/excel` - Data export
- `intervention/image` - Image processing

---

## 📁 Project Structure

```
SPMB-STM/
├── app/
│   ├── Console/Commands/          # Artisan commands
│   ├── Enums/                     # PHP Enums (PaymentStatus, etc)
│   ├── Filament/                  # Filament admin resources
│   │   ├── Resources/
│   │   │   ├── ApplicantResource/
│   │   │   ├── FormResource/
│   │   │   ├── WaveResource/
│   │   │   └── PaymentResource/
│   │   └── Widgets/               # Dashboard widgets
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── RegistrationController.php  # Public form
│   │   │   ├── PaymentController.php       # Payment handling
│   │   │   └── FileDownloadController.php  # File serving
│   │   └── Middleware/
│   ├── Models/                    # Eloquent models
│   │   ├── Applicant.php
│   │   ├── Form.php, FormVersion.php, FormField.php
│   │   ├── Submission.php
│   │   ├── Payment.php
│   │   └── Wave.php
│   └── Services/
│       └── MidtransService.php    # Payment gateway integration
├── database/
│   ├── migrations/                # Database schema
│   └── seeders/                   # Sample data
├── resources/
│   ├── views/
│   │   ├── registration/          # Public registration views
│   │   └── filament/              # Filament custom views
│   └── css/ & js/                 # Frontend assets
├── routes/
│   ├── web.php                    # Public routes
│   └── api.php                    # API routes (if any)
├── docs/                          # 📚 Documentation
│   ├── payment/                   # Payment integration docs
│   ├── form-management/           # Form system docs
│   ├── features/                  # Feature documentation
│   ├── troubleshooting/           # Common issues & fixes
│   └── archived/                  # Historical docs
└── tests/                         # PHPUnit tests
```

---

## 🎯 Key Features

### **1. Dynamic Form Management**

**Files:** 
- `app/Models/Form.php`, `FormVersion.php`, `FormField.php`, `FormStep.php`
- `app/Filament/Resources/FormResource/`

**How it works:**
- Admin creates forms with multiple versions
- Each version has steps (wizard navigation)
- Fields are dynamic: text, select, file upload, etc.
- Fields support `field_options_json` for select/checkbox options

**System Fields Protection:**
- Critical fields (nama_lengkap, nisn, email, etc.) are marked as `is_system_field`
- Protected from deletion/key changes at model level
- See: `docs/SYSTEM_FIELDS_PROTECTION_GUIDE.md`

---

### **2. Payment Integration (Midtrans)**

**Files:**
- `app/Services/MidtransService.php`
- `app/Http/Controllers/PaymentController.php`
- `app/Enums/PaymentStatus.php`

**Payment Flow:**
1. User submits registration → Payment created (status: pending)
2. User redirected to Midtrans Snap
3. User pays → Midtrans sends notification to webhook
4. Webhook updates payment status → Applicant status computed
5. Admin sees real-time payment status

**Single Source of Truth:**
- `Payment.payment_status_name` (ENUM) is the source
- `Applicant.payment_status` is COMPUTED via accessor
- No manual syncing needed!

**Docs:** `docs/payment/`

---

### **3. File Upload System**

**Files:**
- `app/Models/SubmissionFile.php`
- `app/Http/Controllers/FileDownloadController.php`
- `app/Filament/Infolists/Components/FileViewerEntry.php`

**Features:**
- Multiple file upload (images, PDFs, documents)
- Storage in `storage/app/public/submissions/`
- Preview for images & PDFs
- Force download for other files

**Routes:**
- `/files/{id}/preview` - Inline view
- `/files/{id}/download` - Force download

**Docs:** `docs/form-management/IMAGE_UPLOAD_GUIDE.md`

---

### **4. Applicant Management**

**Files:**
- `app/Models/Applicant.php`
- `app/Filament/Resources/ApplicantResource/`

**Key Features:**
- View submitted form answers
- Display files (images/PDFs) inline
- Payment status badge (computed from Payment)
- Export to Excel
- Filter & search

**Computed Attributes:**
```php
$applicant->payment_status // Computed from latestPayment
$applicant->payment_status_badge // ['label', 'color', 'value']

// Helper methods
$applicant->hasSuccessfulPayment()
$applicant->hasPendingPayment()
$applicant->hasFailedPayment()
```

---

### **5. Wave Management**

**Files:**
- `app/Models/Wave.php`
- `app/Filament/Resources/WaveResource/`

**Features:**
- Define registration periods (start/end datetime)
- Set quota limits
- Configure registration fee
- Only 1 active wave at a time
- Active wave displayed on public form

**Docs:** `docs/features/DYNAMIC_WAVE_COMPONENT.md`

---

### **6. Export System**

**Files:**
- `app/Filament/Resources/ApplicantResource/Pages/ListApplicants.php`

**Features:**
- Export to Excel (.xlsx)
- Customizable columns via ExportTemplate
- Export only `is_exportable` fields
- Include computed payment status

**Docs:** `docs/features/EXPORT_DATA_FEATURE.md`

---

## 🔄 Development Workflow

### **Local Setup**

```bash
# 1. Clone & install
git clone <repo-url>
cd SPMB-STM
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database
# Create MySQL database: spmb_stm
php artisan migrate --seed

# 4. Storage link
php artisan storage:link

# 5. Run dev servers
php artisan serve
npm run dev
```

### **Admin Access**

Default seeded admin:
- Email: `admin@example.com`
- Password: `password`

Change in `database/seeders/UserSeeder.php`

---

### **Common Tasks**

**Add new FormField type:**
1. Add to `FormFieldsRelationManager::form()` options
2. Handle in `RegistrationController::saveStep()`
3. Handle display in `ViewApplicant::formatAnswerValueForDisplay()`

**Add new PaymentStatus:**
1. Add to `app/Enums/PaymentStatus.php`
2. Update `MidtransService::handleNotification()`
3. Update badge colors in `ApplicantResource`

**Modify public form UI:**
- Views: `resources/views/registration/`
- Controller: `app/Http/Controllers/RegistrationController.php`
- Livewire components: `app/Livewire/`

---

## 📏 Code Conventions

### **Models**

```php
// Use fillable or guarded
protected $guarded = [];

// Define casts
protected $casts = [
    'is_active' => 'boolean',
    'data_json' => 'array',
];

// Relations
public function applicant(): BelongsTo
{
    return $this->belongsTo(Applicant::class);
}

// Accessors (for computed values)
public function getPaymentStatusAttribute(): ?string
{
    return $this->latestPayment?->payment_status_name->value;
}
```

### **Controllers**

```php
// Use type hints
public function store(Request $request): RedirectResponse
{
    // Validate
    $validated = $request->validate([...]);
    
    // Business logic
    DB::transaction(function () use ($validated) {
        // ...
    });
    
    // Response
    return redirect()->route('success');
}
```

### **Filament Resources**

```php
// Use static methods
public static function form(Form $form): Form
{
    return $form->schema([...]);
}

// Use descriptive labels in Indonesian
TextInput::make('applicant_full_name')
    ->label('Nama Lengkap')
    ->required()
```

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Test specific file
php artisan test tests/Feature/RegistrationTest.php

# With coverage
php artisan test --coverage
```

**Key Test Areas:**
- Registration flow
- Payment webhook handling
- File upload
- Export functionality
- System fields protection

---

## 🚀 Deployment

### **Pre-Deployment Checklist**

- [ ] Update `.env` for production
- [ ] Set `APP_DEBUG=false`
- [ ] Configure Midtrans production keys
- [ ] Set proper `APP_URL`
- [ ] Configure mail settings
- [ ] Set up queue workers
- [ ] Configure file storage (S3 optional)

### **Deployment Steps**

```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# 3. Migrate database
php artisan migrate --force

# 4. Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize

# 5. Restart queue workers
php artisan queue:restart
```

### **Server Requirements**

- PHP 8.1+
- MySQL 8.0+
- Composer
- Node.js & npm
- Web server (Nginx/Apache)
- Supervisor (for queue workers)

---

## 📚 Additional Resources

### **Documentation**
- [Payment Integration](docs/payment/README.md)
- [Form Management](docs/form-management/README.md)
- [Features Guide](docs/features/README.md)
- [Troubleshooting](docs/troubleshooting/README.md)

### **External Docs**
- [Laravel Documentation](https://laravel.com/docs)
- [Filament Documentation](https://filamentphp.com/docs)
- [Midtrans API Docs](https://docs.midtrans.com)

---

## 🆘 Support

**Common Issues:**
1. Payment not updating → Check webhook URL & Midtrans dashboard
2. File upload fails → Check storage permissions & disk space
3. Form not showing → Check active wave & form version

**Need Help?**
- Check `docs/troubleshooting/` folder
- Review error logs: `storage/logs/laravel.log`
- Contact: [Your Email]

---

**Last Updated:** 2025-01-13  
**Version:** 1.0  
**Maintainer:** Development Team
