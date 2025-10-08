# Dynamic Wave Component - Informasi Gelombang Pendaftaran

## Overview

Komponen informasi gelombang pendaftaran yang dinamis menggunakan data dari tabel `waves`. Komponen ini secara otomatis mendeteksi status gelombang (selesai, sedang berlangsung, akan datang) berdasarkan tanggal saat ini.

## Features

✅ **Dynamic Data** - Mengambil data dari tabel `waves`
✅ **Auto Status Detection** - Deteksi otomatis status gelombang
✅ **Real-time Quota** - Menampilkan sisa kuota secara real-time
✅ **Responsive Design** - Tampilan optimal di semua device
✅ **Empty State** - Handle ketika belum ada gelombang
✅ **Visual Indicators** - Badge dan warna untuk setiap status

## Status Detection Logic

### Status Gelombang:

1. **Selesai (Closed)**
   - Kondisi: Tanggal sekarang > `end_datetime`
   - Warna: Abu-abu
   - Icon: Checkmark
   - Badge: "Selesai"

2. **Sedang Berlangsung (Active)**
   - Kondisi: `start_datetime` ≤ Tanggal sekarang ≤ `end_datetime`
   - Warna: Hijau
   - Icon: Calendar
   - Badge: "Dibuka" + Badge tambahan "Sedang Berlangsung"
   - Border: 2px solid green

3. **Akan Datang (Upcoming)**
   - Kondisi: Tanggal sekarang < `start_datetime`
   - Warna: Biru
   - Icon: Clock
   - Badge: "Akan Datang"

## Data Flow

### 1. View Composer

**File:** `app/View/Composers/SiteSettingComposer.php`

```php
public function compose(View $view)
{
    // Get all waves
    $waves = Wave::orderBy('start_datetime', 'asc')->get();
    $now = now();
    
    // Categorize waves
    $categorizedWaves = [
        'closed' => [],
        'active' => [],
        'upcoming' => [],
    ];
    
    foreach ($waves as $wave) {
        if ($now->lt($wave->start_datetime)) {
            $categorizedWaves['upcoming'][] = $wave;
        } elseif ($now->gt($wave->end_datetime)) {
            $categorizedWaves['closed'][] = $wave;
        } else {
            $categorizedWaves['active'][] = $wave;
        }
    }
    
    $view->with([
        'waves' => $waves,
        'categorizedWaves' => $categorizedWaves,
    ]);
}
```

### 2. Component View

**File:** `resources/views/components/registration-waves.blade.php`

Komponen menggunakan data `$waves` dan menampilkan:
- Nama gelombang
- Rentang tanggal
- Biaya pendaftaran
- Kuota tersisa
- Status gelombang

## Displayed Information

Setiap card gelombang menampilkan:

### 1. Wave Name
```blade
{{ $wave->wave_name }}
```
Contoh: "Gelombang 1", "Gelombang 2"

### 2. Date Range
```blade
{{ $wave->start_datetime->format('d M') }} - {{ $wave->end_datetime->format('d M Y') }}
```
Contoh: "01 Jan - 31 Mar 2025"

### 3. Registration Fee
```blade
Rp {{ number_format($wave->registration_fee_amount, 0, ',', '.') }}
```
Contoh: "Rp 300.000"

### 4. Quota Information
```blade
@if($wave->quota_limit)
    Kuota Tersisa: {{ $remainingSlots }} / {{ $wave->quota_limit }}
@endif
```

Features:
- Progress bar visual
- Real-time calculation: `quota_limit - applicants_count`
- Warning color jika kuota ≤ 10
- Hidden jika quota_limit = null (unlimited)

### 5. Status Badge

Dynamic badge berdasarkan status:
- **Selesai**: Gray badge
- **Dibuka**: Green badge + "Sedang Berlangsung" top badge
- **Akan Datang**: Blue outlined badge

## Timeline Progress Bar

Visual timeline di atas cards:
- Dot untuk setiap gelombang
- Warna berubah sesuai status
- Responsive (hidden di mobile)

## Empty State

Jika belum ada gelombang di database:
```blade
@if($waves->isEmpty())
    <div class="text-center py-12">
        <!-- Empty state message -->
    </div>
@endif
```

## Usage

Komponen automatically included di halaman utama:

```blade
<x-registration-waves />
```

Data automatically available via View Composer.

## Seeder Integration

Data gelombang dibuat via `WaveSeeder`:

```bash
php artisan db:seed --class=WaveSeeder
```

Ini akan membuat 4 gelombang dengan data:
- Gelombang 1: Jan-Mar 2025 (Rp 300.000)
- Gelombang 2: Apr-Jun 2025 (Rp 350.000)
- Gelombang 3: Jul-Sep 2025 (Rp 400.000)
- Gelombang 4: Oct-Dec 2025 (Rp 450.000)

## Admin Management

Admin dapat manage gelombang via Filament admin panel:
- `/admin/waves`
- Create, Edit, Delete waves
- Set quota limits
- Set active status
- Set registration fees

## Real-time Quota Calculation

Quota dihitung real-time dari relasi `applicants`:

```php
$registeredCount = $wave->applicants()->count();
$remainingSlots = $wave->quota_limit - $registeredCount;
```

**Features:**
- Auto-update saat ada pendaftar baru
- Progress bar visualization
- Warning indicator untuk kuota menipis
- Support unlimited quota (null)

## Responsive Grid

Grid automatically adjusts:
- 1 column on mobile
- 2-3 columns on tablet/desktop
- Max 3 columns untuk readability

```blade
<div class="grid grid-cols-1 md:grid-cols-{{ min(count($waves), 3) }}">
```

## Visual Enhancements

### 1. Active Wave Highlight
- Border 2px solid green
- Shadow elevation
- Top badge "Sedang Berlangsung"

### 2. Color Coding
- **Gray**: Closed waves
- **Green**: Active waves
- **Blue**: Upcoming waves

### 3. Icons
- **Checkmark**: Completed
- **Calendar**: Active
- **Clock**: Upcoming

### 4. Hover Effects
```css
hover:shadow-lg transition-all duration-300
```

## Performance Optimization

1. **Eager Loading**
   ```php
   $waves = Wave::with('applicants')->get();
   ```

2. **Query Optimization**
   ```php
   $registeredCount = $wave->applicants()->count();
   ```

3. **Caching (Optional)**
   ```php
   Cache::remember('waves', 3600, function() {
       return Wave::all();
   });
   ```

## Testing

### Test Status Detection

```bash
# Via Tinker
php artisan tinker

$waves = App\Models\Wave::all();
$now = now();

foreach($waves as $wave) {
    $status = $now->lt($wave->start_datetime) ? 'upcoming' : 
              ($now->gt($wave->end_datetime) ? 'closed' : 'active');
    echo "{$wave->wave_name}: {$status}\n";
}
```

### Test Quota Calculation

```bash
php artisan tinker

$wave = App\Models\Wave::first();
echo "Total Quota: {$wave->quota_limit}\n";
echo "Registered: {$wave->applicants()->count()}\n";
echo "Remaining: " . ($wave->quota_limit - $wave->applicants()->count()) . "\n";
```

## Troubleshooting

### Issue: Waves tidak muncul

**Solution:**
1. Cek apakah ada data: `Wave::count()`
2. Run seeder: `php artisan db:seed --class=WaveSeeder`
3. Clear cache: `php artisan view:clear`

### Issue: Status tidak akurat

**Solution:**
1. Cek server timezone: `config('app.timezone')`
2. Cek database datetime format
3. Cek `start_datetime` dan `end_datetime` di database

### Issue: Kuota tidak update

**Solution:**
1. Cek relasi `applicants()` di Wave model
2. Clear query cache
3. Refresh halaman (hard refresh)

## Related Files

### Modified:
- `app/View/Composers/SiteSettingComposer.php` - Added waves data
- `resources/views/components/registration-waves.blade.php` - Dynamic component

### Related:
- `app/Models/Wave.php` - Wave model
- `app/Models/Applicant.php` - Applicant model
- `database/seeders/WaveSeeder.php` - Wave seeder
- `database/seeders/ApplicantSeeder.php` - Applicant seeder

## Best Practices

### 1. Always Order by Start Date
```php
Wave::orderBy('start_datetime', 'asc')->get()
```

### 2. Use Carbon for Date Comparison
```php
$now = now();
$now->gte($wave->start_datetime)
```

### 3. Handle Null Quota
```php
@if($wave->quota_limit)
    // Show quota info
@endif
```

### 4. Use Number Formatting
```php
number_format($amount, 0, ',', '.')
```

## Future Enhancements

Potential improvements:
- [ ] Add countdown timer untuk gelombang akan datang
- [ ] Email notification saat gelombang baru dibuka
- [ ] Auto-activate waves berdasarkan tanggal
- [ ] Wave registration statistics
- [ ] Export wave reports
- [ ] Multi-year wave management

---

**Status:** ✅ Implemented
**Last Updated:** October 8, 2025
**Tested:** ✅ Yes
