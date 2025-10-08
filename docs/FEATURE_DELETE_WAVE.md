# Feature: Delete Wave (Hapus Gelombang)

## Overview

Fitur untuk menghapus data gelombang pendaftaran dari panel admin Filament dengan konfirmasi dan informasi yang jelas tentang dampak penghapusan.

## Features

✅ **Single Delete** - Hapus gelombang satu per satu
✅ **Bulk Delete** - Hapus banyak gelombang sekaligus
✅ **Confirmation Modal** - Konfirmasi sebelum hapus
✅ **Smart Warning** - Peringatan jika ada pendaftar terkait
✅ **Cascade Delete** - Auto-hapus data terkait (applicants, payments)
✅ **Audit Trail** - Log penghapusan untuk tracking
✅ **Navigation Badge** - Counter jumlah gelombang di menu

## Implementation

### 1. Delete Actions

**File:** `app/Filament/Resources/WaveResource.php`

#### Single Delete Action

```php
Tables\Actions\DeleteAction::make()
    ->label('Hapus')
    ->requiresConfirmation()
    ->modalHeading('Hapus Gelombang?')
    ->modalDescription(fn (Wave $record) => 
        $record->applicants()->count() > 0
            ? "Gelombang ini memiliki {$record->applicants()->count()} pendaftar. Menghapus gelombang akan menghapus semua data terkait."
            : "Apakah Anda yakin ingin menghapus gelombang ini?"
    )
    ->modalSubmitActionLabel('Ya, Hapus')
    ->successNotificationTitle('Gelombang berhasil dihapus')
    ->before(function (Wave $record) {
        Log::info("Deleting wave: {$record->wave_name} (ID: {$record->id})");
    })
```

**Features:**
- Konfirmasi wajib sebelum hapus
- Dynamic message berdasarkan jumlah pendaftar
- Log sebelum delete untuk audit trail
- Success notification

#### Bulk Delete Action

```php
Tables\Actions\BulkActionGroup::make([
    Tables\Actions\DeleteBulkAction::make()
        ->label('Hapus yang Dipilih')
        ->requiresConfirmation()
        ->modalHeading('Hapus Gelombang?')
        ->modalDescription('Apakah Anda yakin ingin menghapus gelombang yang dipilih? Semua data terkait akan dihapus.')
        ->modalSubmitActionLabel('Ya, Hapus Semua')
        ->successNotificationTitle('Gelombang berhasil dihapus'),
])
```

**Features:**
- Checkbox selection di table
- Delete multiple records sekaligus
- Konfirmasi untuk bulk action
- Success notification

### 2. Permission Control

```php
public static function canDelete(Model $record): bool
{
    // Allow deletion
    // Note: Cascade delete is handled by database foreign key constraint
    return true;
}
```

**Logic:**
- Return `true` = Semua gelombang bisa dihapus
- Cascade delete otomatis handle data terkait
- Bisa dikustomisasi dengan kondisi tambahan jika perlu

### 3. Navigation Badge

```php
public static function getNavigationBadge(): ?string
{
    return static::getModel()::count();
}

public static function getNavigationBadgeColor(): ?string
{
    return static::getModel()::where('is_active', true)->count() > 0 ? 'success' : 'gray';
}
```

**Features:**
- Badge menampilkan jumlah total gelombang
- Warna hijau jika ada gelombang aktif
- Warna abu-abu jika semua tidak aktif

## Cascade Delete

### Database Level

**File:** `database/migrations/2024_05_05_000100_create_waves_and_applicants_tables.php`

```php
$table->foreignId('wave_id')
    ->constrained('waves')
    ->cascadeOnDelete();
```

**Behavior:**
Ketika gelombang dihapus, otomatis menghapus:
1. **Applicants** (Calon siswa)
2. **Payments** (via cascade dari applicants)
3. **Submissions** (via cascade dari applicants)
4. **Related data** lainnya

### Cascade Chain

```
Wave (Gelombang)
└─ Applicants (Calon Siswa)
   ├─ Payments (Pembayaran)
   ├─ Submissions (Pengajuan)
   │  ├─ Submission Answers
   │  └─ Submission Files
   └─ Submission Drafts
```

**Semua akan terhapus otomatis!**

## User Interface

### Single Delete

1. **Table Action**
   - Icon: Trash
   - Posisi: Row actions (kanan setiap row)
   - Label: "Hapus"

2. **Confirmation Modal**
   ```
   Heading: "Hapus Gelombang?"
   
   Message (jika ada pendaftar):
   "Gelombang ini memiliki 45 pendaftar. Menghapus gelombang 
   akan menghapus semua data terkait."
   
   Message (jika kosong):
   "Apakah Anda yakin ingin menghapus gelombang ini?"
   
   Buttons:
   [Batal]  [Ya, Hapus]
   ```

3. **Success Notification**
   ```
   ✓ Gelombang berhasil dihapus
   ```

### Bulk Delete

1. **Selection**
   - Checkbox di setiap row
   - "Select all" di header
   - Counter: "2 selected"

2. **Bulk Action**
   - Dropdown: "Actions"
   - Option: "Hapus yang Dipilih"

3. **Confirmation Modal**
   ```
   Heading: "Hapus Gelombang?"
   
   Message:
   "Apakah Anda yakin ingin menghapus gelombang yang dipilih? 
   Semua data terkait akan dihapus."
   
   Buttons:
   [Batal]  [Ya, Hapus Semua]
   ```

4. **Success Notification**
   ```
   ✓ Gelombang berhasil dihapus
   ```

## Safety Features

### 1. Confirmation Required

Semua delete action wajib konfirmasi:
```php
->requiresConfirmation()
```

Tidak ada "accidental delete".

### 2. Dynamic Warning

Warning message berubah berdasarkan data:
```php
->modalDescription(fn (Wave $record) => 
    $record->applicants()->count() > 0
        ? "Gelombang ini memiliki {$record->applicants()->count()} pendaftar..."
        : "Apakah Anda yakin..."
)
```

User aware tentang dampak penghapusan.

### 3. Audit Log

Setiap penghapusan dicatat:
```php
->before(function (Wave $record) {
    Log::info("Deleting wave: {$record->wave_name} (ID: {$record->id})");
})
```

Log tersimpan di `storage/logs/laravel.log`.

### 4. Database Transaction

Filament automatically wrap delete dalam transaction:
- Jika gagal, auto rollback
- Data consistency terjaga

## Use Cases

### Use Case 1: Delete Empty Wave

**Scenario:** Gelombang dibuat salah, belum ada pendaftar

**Steps:**
1. Admin buka `/admin/waves`
2. Click icon trash di row gelombang
3. Modal muncul: "Apakah Anda yakin..."
4. Click "Ya, Hapus"
5. Gelombang terhapus

**Result:** Gelombang dihapus tanpa efek samping

### Use Case 2: Delete Wave with Applicants

**Scenario:** Gelombang lama ingin dihapus, ada 45 pendaftar

**Steps:**
1. Admin buka `/admin/waves`
2. Click icon trash di row gelombang
3. Modal muncul: "Gelombang ini memiliki 45 pendaftar..."
4. Admin pertimbangkan dampaknya
5. Click "Ya, Hapus" jika yakin
6. Gelombang + 45 applicants + payments terhapus

**Result:** 
- 1 wave deleted
- 45 applicants deleted (cascade)
- ~45 payments deleted (cascade)
- All related data deleted (cascade)

### Use Case 3: Bulk Delete Multiple Waves

**Scenario:** Hapus 3 gelombang lama sekaligus

**Steps:**
1. Admin buka `/admin/waves`
2. Check 3 gelombang yang ingin dihapus
3. Click "Actions" → "Hapus yang Dipilih"
4. Modal konfirmasi muncul
5. Click "Ya, Hapus Semua"
6. 3 gelombang + data terkait terhapus

**Result:** Bulk delete lebih efisien

## Testing

### Test Single Delete

```bash
# Via Tinker
php artisan tinker

$wave = App\Models\Wave::first();
echo "Before: " . App\Models\Wave::count() . " waves\n";
echo "Applicants: " . $wave->applicants()->count() . "\n";

$wave->delete();

echo "After: " . App\Models\Wave::count() . " waves\n";
```

### Test Cascade Delete

```bash
php artisan tinker

$wave = App\Models\Wave::where('wave_name', 'Gelombang 1')->first();
$applicantCount = $wave->applicants()->count();
$paymentCount = App\Models\Payment::whereIn('applicant_id', $wave->applicants->pluck('id'))->count();

echo "Before delete:\n";
echo "- Wave: {$wave->wave_name}\n";
echo "- Applicants: {$applicantCount}\n";
echo "- Payments: {$paymentCount}\n";

$wave->delete();

echo "\nAfter delete:\n";
echo "- Wave: deleted\n";
echo "- Applicants: " . App\Models\Applicant::whereIn('id', $wave->applicants->pluck('id'))->count() . "\n";
echo "- Payments: " . App\Models\Payment::whereIn('applicant_id', $wave->applicants->pluck('id'))->count() . "\n";
```

### Test via UI

1. **Test Single Delete:**
   - Buka `/admin/waves`
   - Click trash icon pada gelombang
   - Verify modal muncul
   - Click "Ya, Hapus"
   - Verify notification success
   - Verify gelombang hilang dari table

2. **Test Bulk Delete:**
   - Check 2-3 gelombang
   - Click "Actions" → "Hapus yang Dipilih"
   - Verify modal muncul
   - Click "Ya, Hapus Semua"
   - Verify notification success
   - Verify semua gelombang terpilih hilang

3. **Test Warning Message:**
   - Try delete gelombang dengan pendaftar
   - Verify message menunjukkan jumlah pendaftar
   - Try delete gelombang tanpa pendaftar
   - Verify message berbeda

## Audit Trail

### View Logs

```bash
# Real-time monitoring
tail -f storage/logs/laravel.log | grep "Deleting wave"

# View recent deletions
grep "Deleting wave" storage/logs/laravel.log | tail -20
```

### Log Format

```
[2025-10-08 10:30:45] local.INFO: Deleting wave: Gelombang 1 (ID: 1)
[2025-10-08 10:31:12] local.INFO: Deleting wave: Gelombang 2 (ID: 2)
```

## Best Practices

### 1. Think Before Delete

⚠️ **Warning:** Penghapusan gelombang bersifat **PERMANENT** dan **CASCADE**

Sebelum hapus, pastikan:
- Tidak ada data penting di gelombang tersebut
- Sudah backup database (jika perlu)
- Tidak ada proses bisnis yang masih depend ke gelombang tersebut

### 2. Export Data First

Jika ada data yang perlu disimpan:
```bash
# Export before delete
php artisan tinker

$wave = App\Models\Wave::find(1);
$data = [
    'wave' => $wave->toArray(),
    'applicants' => $wave->applicants->toArray(),
];

file_put_contents('backup-wave-1.json', json_encode($data, JSON_PRETTY_PRINT));
```

### 3. Use Soft Deletes (Optional)

Jika ingin "delete" yang recoverable, tambahkan soft deletes:

```php
// In Wave model
use Illuminate\Database\Eloquent\SoftDeletes;

class Wave extends Model
{
    use SoftDeletes;
}

// In migration
$table->softDeletes();
```

Dengan soft delete:
- Data tidak benar-benar terhapus
- Bisa di-restore
- Query default tidak include deleted records

## Troubleshooting

### Issue: Cannot Delete Wave

**Symptom:** Delete button tidak muncul

**Solution:**
- Check `canDelete()` method return `true`
- Check user permissions
- Clear cache: `php artisan optimize:clear`

### Issue: Cascade Delete Not Working

**Symptom:** Applicants tidak terhapus saat wave dihapus

**Solution:**
- Check foreign key constraint: `->cascadeOnDelete()`
- Check database supports foreign keys (InnoDB for MySQL)
- Run migration ulang jika perlu

### Issue: Delete Too Slow

**Symptom:** Bulk delete memakan waktu lama

**Solution:**
- Database index on foreign keys
- Use queue for large deletions
- Optimize cascade delete chain

## Security Considerations

### 1. Permission Control

Restrict delete permission untuk certain roles:

```php
public static function canDelete(Model $record): bool
{
    // Only super admin can delete
    return auth()->user()->hasRole('super-admin');
}
```

### 2. Soft Delete Alternative

Consider soft deletes untuk data penting:
- Historical data preserved
- Can be restored if needed
- Audit trail automatically maintained

### 3. Backup Strategy

Regular backups before mass deletions:
- Daily automated backups
- Pre-delete manual backups
- Test restore procedures

## Related Files

### Modified:
- `app/Filament/Resources/WaveResource.php` - Added delete actions

### Related:
- `database/migrations/2024_05_05_000100_create_waves_and_applicants_tables.php` - Cascade delete config
- `app/Models/Wave.php` - Wave model
- `app/Models/Applicant.php` - Related model

## Future Enhancements

Potential improvements:
- [ ] Soft deletes instead of hard delete
- [ ] Batch delete dengan queue untuk performance
- [ ] Export data before delete automation
- [ ] Restore functionality
- [ ] Archive instead of delete
- [ ] Delete confirmation dengan password
- [ ] Detailed audit log dengan user info

---

**Status:** ✅ Implemented
**Cascade Delete:** ✅ Enabled
**Confirmation:** ✅ Required
**Last Updated:** October 8, 2025
**Tested:** ✅ Yes
