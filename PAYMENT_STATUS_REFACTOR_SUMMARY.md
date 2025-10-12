# Payment Status Refactoring Summary

## 🎯 Objective
Refactor payment status system to use **Single Source of Truth** pattern. `Applicant.payment_status` is now computed from `Payment.payment_status_name` instead of being manually synced.

---

## 📊 What Changed

### Before (Dual Status - Prone to Sync Issues)
```
┌──────────────────┐        Manual Sync (error-prone)        ┌─────────────────┐
│ Payment Table    │  ────────────────────────────────────>  │ Applicant Table │
│ payment_status   │  <────────────────────────────────────  │ payment_status  │
│ (Source of Truth)│                                          │ (Duplicate)     │
└──────────────────┘                                          └─────────────────┘
```

### After (Single Source of Truth ✅)
```
┌──────────────────┐
│ Payment Table    │  ← SINGLE SOURCE OF TRUTH
│ payment_status   │
└─────────┬────────┘
          │ Automatic Accessor/Computed
          ↓
┌─────────────────┐
│ Applicant Table │
│ payment_status  │  ← Computed via accessor (always in sync)
└─────────────────┘
```

---

## 🔧 Technical Implementation

### 1. **Model Applicant** (`app/Models/Applicant.php`)
**Added:**
- ✅ `latestPayment()` HasOne relation
- ✅ `getPaymentStatusComputedAttribute()` accessor → returns PaymentStatus enum
- ✅ `getPaymentStatusAttribute()` accessor → returns string value (for compatibility)
- ✅ `getPaymentStatusBadgeAttribute()` accessor → returns badge data for UI
- ✅ Helper methods: `hasSuccessfulPayment()`, `hasPendingPayment()`, `hasFailedPayment()`

**Key Code:**
```php
// Payment status is now computed from latest payment
public function getPaymentStatusAttribute(): ?string 
{
    return $this->latestPayment?->payment_status_name?->value;
}
```

---

### 2. **MidtransService** (`app/Services/MidtransService.php`)
**Removed:** Manual update of `Applicant.payment_status`

**Before:**
```php
if ($paymentStatus->isSuccess()) {
    $payment->applicant->update(['payment_status' => 'paid']);
}
```

**After:**
```php
// No manual update needed - automatically computed from Payment
```

---

### 3. **RegistrationController** (`app/Http/Controllers/RegistrationController.php`)
**Removed:** Setting `payment_status` on applicant creation

**Before:**
```php
Applicant::create([
    'payment_status' => 'unpaid', // ← Manual set
]);
```

**After:**
```php
Applicant::create([
    // payment_status computed from Payment relation
]);
```

---

### 4. **PaymentController** (`app/Http/Controllers/PaymentController.php`)
**Changed:** Check payment using accessor

**Before:**
```php
if ($applicant->payment_status === 'paid') {
```

**After:**
```php
if ($applicant->hasSuccessfulPayment()) { // ← Using helper method
```

Also added eager loading:
```php
->with('wave', 'payments', 'latestPayment')
```

---

### 5. **ApplicantResource** (`app/Filament/Resources/ApplicantResource.php`)
**Changed:** Badge column to use relation

**Before:**
```php
BadgeColumn::make('payment_status')
    ->colors([...])
    ->formatStateUsing(fn ($state) => match($state) {...})
```

**After:**
```php
BadgeColumn::make('latestPayment.payment_status_name')
    ->formatStateUsing(fn ($state) => $state?->label() ?? 'Belum Bayar')
    ->color(fn ($state) => $state?->color() ?? 'warning')
```

**Changed:** Filter to use whereHas

**Before:**
```php
SelectFilter::make('payment_status')
    ->options(['paid' => 'Lunas', ...])
```

**After:**
```php
SelectFilter::make('latestPayment.payment_status_name')
    ->options(fn () => collect(PaymentStatus::cases())
        ->mapWithKeys(fn ($status) => [$status->value => $status->label()])
    )
    ->query(function ($query, $data) {
        if (filled($data['value'])) {
            return $query->whereHas('latestPayment', function ($q) use ($data) {
                $q->where('payment_status_name', $data['value']);
            });
        }
    })
```

Added eager loading:
```php
->modifyQueryUsing(fn (Builder $query) => $query->with(['wave', 'latestSubmission', 'latestPayment']))
```

---

### 6. **ViewApplicant** (`app/Filament/Resources/ApplicantResource/Pages/ViewApplicant.php`)
**Changed:** Display badge using relation

**Before:**
```php
TextEntry::make('payment_status')
    ->color(fn ($state) => match($state) {...})
```

**After:**
```php
TextEntry::make('latestPayment.payment_status_name')
    ->formatStateUsing(fn ($state) => $state?->label() ?? 'Belum Bayar')
    ->color(fn ($state) => $state?->color() ?? 'warning')
```

---

### 7. **PpdbStatsOverview** (`app/Filament/Widgets/PpdbStatsOverview.php`)
**Changed:** Count paid using whereHas

**Before:**
```php
$paidCount = Applicant::where('payment_status', 'paid')->count();
$totalPaidAmount = Applicant::where('payment_status', 'paid')
    ->withSum('payments as total_paid_sum', 'paid_amount_total')
    ->get()->sum('total_paid_sum');
```

**After:**
```php
$paidCount = Applicant::whereHas('latestPayment', function ($query) {
    $query->where('payment_status_name', PaymentStatus::SETTLEMENT->value);
})->count();

$totalPaidAmount = Payment::where('payment_status_name', PaymentStatus::SETTLEMENT->value)
    ->sum('paid_amount_total');
```

---

### 8. **Seeders**
**ApplicantSeeder:**
- ❌ Removed: `'payment_status' => 'verified'/'paid'/'pending'`
- ✅ Now: Status computed from Payment created by PaymentSeeder

**PaymentSeeder:**
- ❌ Removed: Filter by `Applicant::whereIn('payment_status', ['paid', 'verified'])`
- ✅ Now: Create payments for all applicants with random status distribution:
  - 85% → `settlement` (paid)
  - 10% → `pending`
  - 5% → `failure`

---

### 9. **Migration** (`database/migrations/2025_10_12_*_add_payment_status_computed_comment_to_applicants_table.php`)
**Purpose:** Document the change

**Actions:**
- Add COMMENT to `applicants.payment_status` column marking it as DEPRECATED/computed
- Set all existing `payment_status` values to NULL (will be computed automatically)

```sql
ALTER TABLE applicants MODIFY COLUMN payment_status VARCHAR(20) NULL 
COMMENT 'DEPRECATED: Computed from latest Payment relation. Do not update manually.';

UPDATE applicants SET payment_status = NULL;
```

---

## ✅ Benefits

| Before | After |
|--------|-------|
| ❌ Manual sync required | ✅ Auto-computed (always sync) |
| ❌ Data duplication | ✅ Single source of truth |
| ❌ Sync errors possible | ✅ No sync errors |
| ❌ Update 2 places | ✅ Update 1 place (Payment only) |
| ❌ Complex logic | ✅ Simple accessor |

---

## 🧪 Testing Checklist

### Manual Testing
- [ ] Create new applicant → `payment_status` should be NULL
- [ ] Create payment with SETTLEMENT → Applicant shows "Pembayaran Berhasil" (green badge)
- [ ] Create payment with PENDING → Applicant shows "Menunggu Pembayaran" (yellow badge)
- [ ] Create payment with FAILURE → Applicant shows "Gagal" (red badge)
- [ ] Filter applicants by payment status → works correctly
- [ ] Stats widget shows correct paid count and total amount
- [ ] ViewApplicant page displays correct payment status badge
- [ ] Multiple payments for one applicant → shows latest payment status

### Automated Testing Commands
```bash
# Fresh migration and seed
php artisan migrate:fresh --seed

# Check Applicant payment_status values (should all be NULL)
php artisan tinker --execute="dump(App\Models\Applicant::pluck('payment_status')->unique());"

# Check computed values working
php artisan tinker --execute="
\$applicant = App\Models\Applicant::with('latestPayment')->first();
dump([
    'db_value' => \$applicant->getRawOriginal('payment_status'),
    'computed_value' => \$applicant->payment_status,
    'latest_payment_status' => \$applicant->latestPayment?->payment_status_name?->value,
]);
"

# Test badge accessor
php artisan tinker --execute="
\$applicant = App\Models\Applicant::with('latestPayment')->first();
dump(\$applicant->payment_status_badge);
"
```

---

## 🔄 Migration Steps

1. **Backup database** (important!)
   ```bash
   mysqldump -u root -p spmb_stm > backup_before_refactor.sql
   ```

2. **Run migration**
   ```bash
   php artisan migrate
   ```
   
3. **Re-seed if needed** (development only)
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Verify** everything works:
   - Check ApplicantResource list
   - Check ViewApplicant detail page
   - Check Stats widget
   - Create test payment via Midtrans
   - Check filters work

---

## 🚨 Breaking Changes

### What Might Break
1. **Direct column access** - If any code does:
   ```php
   // This will return NULL now (deprecated)
   $applicant->getRawOriginal('payment_status')
   
   // Use accessor instead (correct way)
   $applicant->payment_status // ✅ Returns computed value
   ```

2. **Manual updates** - If any code does:
   ```php
   // This won't work anymore (and shouldn't!)
   $applicant->update(['payment_status' => 'paid']); // ❌ Don't do this
   
   // Update Payment instead
   $payment->update(['payment_status_name' => PaymentStatus::SETTLEMENT]); // ✅
   ```

3. **Query builder** - Direct where clauses need adjustment:
   ```php
   // Old way (won't work)
   Applicant::where('payment_status', 'paid')->get(); // ❌
   
   // New way (correct)
   Applicant::whereHas('latestPayment', function($q) {
       $q->where('payment_status_name', PaymentStatus::SETTLEMENT->value);
   })->get(); // ✅
   ```

---

## 📝 Developer Notes

### When Adding New Code:
1. **Never update** `Applicant.payment_status` manually
2. **Always update** `Payment.payment_status_name` instead
3. **Use accessor** `$applicant->payment_status` for reading
4. **Use helper methods** for checks:
   - `$applicant->hasSuccessfulPayment()`
   - `$applicant->hasPendingPayment()`
   - `$applicant->hasFailedPayment()`

### Query Performance:
- Always **eager load** `latestPayment` when displaying status:
  ```php
  Applicant::with('latestPayment')->get()
  ```
- Use `whereHas('latestPayment')` for filtering

---

## 🎉 Summary

**Status pembayaran sekarang HANYA ada di Payment table.**  
Applicant hanya "baca" status dari Payment terbaru via accessor.

**Single Source of Truth** = Payment.payment_status_name ✅

---

**Migration Created:** `2025_10_12_172417_add_payment_status_computed_comment_to_applicants_table.php`

**Files Modified:** 9 files
1. `app/Models/Applicant.php`
2. `app/Services/MidtransService.php`
3. `app/Http/Controllers/RegistrationController.php`
4. `app/Http/Controllers/PaymentController.php`
5. `app/Filament/Resources/ApplicantResource.php`
6. `app/Filament/Resources/ApplicantResource/Pages/ViewApplicant.php`
7. `app/Filament/Widgets/PpdbStatsOverview.php`
8. `database/seeders/ApplicantSeeder.php`
9. `database/seeders/PaymentSeeder.php`
10. `app/Console/Commands/TestMidtransConnection.php`
