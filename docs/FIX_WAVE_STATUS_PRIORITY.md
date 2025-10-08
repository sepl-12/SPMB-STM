# Fix: Wave Status Priority - is_active Field

## Masalah

Ketika admin mengaktifkan Gelombang 3 dan menonaktifkan Gelombang 4, landing page masih menampilkan Gelombang 4 sebagai "Sedang Berlangsung" karena logic hanya berdasarkan tanggal (`start_datetime` dan `end_datetime`), tidak mempertimbangkan field `is_active`.

## Penyebab

Logic deteksi status sebelumnya:
```php
$isActive = $now->gte($wave->start_datetime) && $now->lte($wave->end_datetime);
$isClosed = $now->gt($wave->end_datetime);
$isUpcoming = $now->lt($wave->start_datetime);
```

Tidak memperhitungkan field `is_active` dari database.

## Solusi yang Diterapkan

### 1. Update Logic di Component View

**File:** `resources/views/components/registration-waves.blade.php`

**Logic Baru:**
```php
// Prioritize is_active field
// Active: is_active = true AND within date range
$isActive = $wave->is_active && $now->gte($wave->start_datetime) && $now->lte($wave->end_datetime);

// Closed: is_active = false OR past end_datetime
$isClosed = !$wave->is_active || $now->gt($wave->end_datetime);

// Upcoming: is_active = true AND before start_datetime
$isUpcoming = $wave->is_active && $now->lt($wave->start_datetime);
```

### 2. Update Logic di View Composer

**File:** `app/View/Composers/SiteSettingComposer.php`

**Logic Baru:**
```php
foreach ($waves as $wave) {
    // Priority: Check is_active first
    if (!$wave->is_active || $now->gt($wave->end_datetime)) {
        // Tidak aktif atau sudah lewat tanggal akhir
        $categorizedWaves['closed'][] = $wave;
    } elseif ($wave->is_active && $now->lt($wave->start_datetime)) {
        // Aktif tapi belum dimulai
        $categorizedWaves['upcoming'][] = $wave;
    } elseif ($wave->is_active && $now->gte($wave->start_datetime) && $now->lte($wave->end_datetime)) {
        // Aktif dan sedang berlangsung
        $categorizedWaves['active'][] = $wave;
    }
}
```

## Priority Logic

Status gelombang sekarang ditentukan dengan prioritas:

### 1️⃣ **Priority Pertama: is_active**

Field `is_active` adalah kontrol manual dari admin:
- `is_active = false` → Gelombang otomatis "Selesai" (tidak peduli tanggal)
- `is_active = true` → Check tanggal untuk tentukan status

### 2️⃣ **Priority Kedua: Datetime**

Jika `is_active = true`, cek rentang tanggal:
- Sebelum `start_datetime` → "Akan Datang"
- Dalam range `start_datetime` - `end_datetime` → "Sedang Berlangsung"
- Setelah `end_datetime` → "Selesai"

## Status Matrix

| is_active | Date Condition | Status | Display |
|-----------|----------------|--------|---------|
| `false` | Any | Closed | ⚫ Selesai (Gray) |
| `true` | Before start | Upcoming | 🔵 Akan Datang (Blue) |
| `true` | In range | Active | 🟢 Sedang Berlangsung (Green) |
| `true` | After end | Closed | ⚫ Selesai (Gray) |

## Use Cases

### Use Case 1: Manual Close Wave

Admin ingin menutup gelombang sebelum `end_datetime`:
```
Gelombang 4:
- start_datetime: 2025-10-01
- end_datetime: 2025-12-31
- is_active: false (admin set manually)

Result: Status "Selesai" (meskipun masih dalam range tanggal)
```

### Use Case 2: Manual Open Wave

Admin ingin membuka gelombang lebih awal:
```
Gelombang 5:
- start_datetime: 2026-01-01 (masih jauh)
- end_datetime: 2026-03-31
- is_active: true (admin set manually)

Result: Status "Akan Datang" (karena belum start_datetime)
```

### Use Case 3: Auto Close by Date

Gelombang yang sudah lewat end_datetime:
```
Gelombang 1:
- start_datetime: 2025-01-01
- end_datetime: 2025-03-31
- is_active: true
- Current date: 2025-10-08 (after end_datetime)

Result: Status "Selesai" (auto-closed by datetime)
```

## Admin Control

Admin dapat mengontrol status gelombang via:
1. **Field is_active** (Manual toggle)
   - Toggle on/off di Filament admin panel
   - Immediate effect tanpa perlu ubah tanggal

2. **Datetime Range** (Auto control)
   - Set start_datetime dan end_datetime
   - System auto-check saat render

## Testing

### Test Scenario 1: Deactivate Wave

```bash
# Via Tinker
php artisan tinker

$wave = App\Models\Wave::where('wave_name', 'Gelombang 4')->first();
$wave->is_active = false;
$wave->save();

# Refresh landing page
# Expected: Gelombang 4 shows "Selesai" (Gray)
```

### Test Scenario 2: Activate Wave

```bash
# Via Tinker
php artisan tinker

$wave = App\Models\Wave::where('wave_name', 'Gelombang 3')->first();
$wave->is_active = true;
$wave->save();

# Refresh landing page
# Expected: Gelombang 3 shows status based on datetime
```

### Test Scenario 3: Check All Waves

```bash
php artisan tinker

foreach(App\Models\Wave::all() as $wave) {
    $now = now();
    $isActive = $wave->is_active && $now->gte($wave->start_datetime) && $now->lte($wave->end_datetime);
    $status = !$wave->is_active ? 'Closed (Manual)' : 
              ($now->lt($wave->start_datetime) ? 'Upcoming' : 
              ($now->gt($wave->end_datetime) ? 'Closed (Auto)' : 'Active'));
    
    echo "{$wave->wave_name}: is_active={$wave->is_active}, status={$status}\n";
}
```

## Admin Panel Integration

Di Filament Resource (WaveResource):
- Toggle `is_active` untuk manual control
- Field `start_datetime` dan `end_datetime` untuk auto control
- Visual indicator di table list untuk status real-time

## Benefits

✅ **Flexible Control** - Admin dapat manual override status
✅ **Auto Management** - System auto-close berdasarkan tanggal
✅ **Clear Priority** - is_active > datetime
✅ **User Friendly** - Tampilan jelas di landing page
✅ **No Confusion** - Status akurat sesuai admin setting

## Edge Cases Handled

### Edge Case 1: All Waves Inactive
```
Result: Semua waves tampil "Selesai"
Landing page tetap show cards dengan status gray
```

### Edge Case 2: Multiple Active Waves
```
Result: Semua waves yang is_active=true dan dalam range tampil "Sedang Berlangsung"
Admin dapat open multiple waves simultaneously
```

### Edge Case 3: Future Wave Activated Early
```
Result: Status "Akan Datang" dengan countdown
User aware ada gelombang baru yang akan buka
```

## Migration

Tidak perlu migration karena:
- Field `is_active` sudah ada di tabel waves
- Hanya update logic di view dan composer
- Backwards compatible

## Related Files

### Modified:
1. `resources/views/components/registration-waves.blade.php` - Updated status logic
2. `app/View/Composers/SiteSettingComposer.php` - Updated categorization logic

### Related:
- `app/Models/Wave.php` - Wave model
- `app/Filament/Resources/WaveResource.php` - Admin panel
- `database/migrations/2024_05_05_000100_create_waves_and_applicants_tables.php` - Schema

## Documentation Updates

Update existing docs:
- `docs/DYNAMIC_WAVE_COMPONENT.md` - Add is_active priority section
- `docs/SEEDER_DOCUMENTATION.md` - Update WaveSeeder examples

---

**Status:** ✅ Fixed
**Priority:** is_active > datetime
**Last Updated:** October 8, 2025
**Tested:** ✅ Yes
