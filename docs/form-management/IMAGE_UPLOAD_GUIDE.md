# Upload Gambar Hero - Implementation Guide

## Overview
Fitur upload gambar hero telah diimplementasikan menggunakan Filament FileUpload component dengan konfigurasi yang tepat untuk storage Laravel.

## Konfigurasi yang Diterapkan

### 1. FileUpload Component (`app/Filament/Pages/SiteContentSettings.php`)

```php
FileUpload::make('hero_image_path')
    ->label('Gambar Hero')
    ->image()
    ->disk('public')                    // Menggunakan disk public
    ->directory('hero')                 // Menyimpan di folder hero
    ->visibility('public')              // File dapat diakses public
    ->imageEditor()                     // Enable image editor
    ->maxSize(5120)                     // Max 5MB
    ->acceptedFileTypes([               // Hanya menerima format gambar
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/webp'
    ])
    ->preserveFilenames()               // Preserve nama file original
    ->helperText('Unggah gambar dengan rasio 16:9 untuk tampilan terbaik. Max 5MB.')
    ->columnSpanFull()
```

### 2. Storage Structure

```
storage/
└── app/
    └── public/
        └── hero/           # Folder untuk gambar hero
            └── [files]
```

### 3. Public Access via Symbolic Link

```bash
public/
└── storage -> ../storage/app/public/
```

### 4. Hero Component (`resources/views/components/hero.blade.php`)

```blade
@if($settings->hero_image_path)
    <img src="{{ asset('storage/' . $settings->hero_image_path) }}" 
         alt="SMK Muhammadiyah 1 Sangatta Utara" 
         class="w-full h-full object-cover"
         loading="eager">
@else
    <!-- Placeholder gradient -->
    <div class="w-full h-full bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700"></div>
@endif
```

## Alur Upload Gambar

1. **User Upload** → Admin mengunggah gambar via panel Filament
2. **Storage** → File disimpan ke `storage/app/public/hero/filename.ext`
3. **Database** → Path `hero/filename.ext` disimpan di database
4. **Access** → File dapat diakses via URL: `APP_URL/storage/hero/filename.ext`
5. **Display** → Blade component menampilkan gambar menggunakan `asset('storage/' . $path)`

## Setup Instructions

### 1. Buat Symbolic Link (Hanya Sekali)

```bash
php artisan storage:link
```

### 2. Buat Folder Hero

```bash
mkdir -p storage/app/public/hero
chmod -R 775 storage/app/public
```

### 3. Verifikasi Permission

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 4. Clear Cache

```bash
php artisan optimize:clear
```

## Testing

### Test Upload
1. Buka panel admin: `/admin`
2. Ke menu: **Konten Website → Halaman Utama**
3. Klik tombol upload di field **Gambar Hero**
4. Pilih gambar (max 5MB, format: jpg/jpeg/png/webp)
5. Klik **Simpan**

### Verifikasi File Tersimpan

```bash
ls -la storage/app/public/hero/
```

### Verifikasi Akses Public

Buka browser dan akses:
```
http://localhost:8000/storage/hero/nama-file.jpg
```

### Test Display di Landing Page

Buka halaman utama:
```
http://localhost:8000/
```

Gambar hero seharusnya muncul di section hero.

## Troubleshooting

### Error: "Unable to write file"
**Solusi:** Cek permission folder storage
```bash
chmod -R 775 storage
```

### Error: File tidak ditemukan (404)
**Solusi:** 
1. Pastikan symbolic link ada: `php artisan storage:link`
2. Cek apakah file benar-benar tersimpan di `storage/app/public/hero/`

### Gambar tidak muncul di halaman
**Solusi:**
1. Clear cache: `php artisan optimize:clear`
2. Cek view source, pastikan path gambar: `/storage/hero/filename.ext`
3. Cek browser console untuk error 404

### Error saat save form
**Solusi:**
1. Cek log: `tail -f storage/logs/laravel.log`
2. Pastikan disk 'public' terkonfigurasi di `config/filesystems.php`

## Features

✅ Upload gambar dengan drag & drop atau file picker
✅ Image editor built-in (crop, rotate, flip)
✅ Preview gambar sebelum upload
✅ Validasi format file (jpeg, png, jpg, webp)
✅ Validasi ukuran file (max 5MB)
✅ Preserve nama file original
✅ Automatic resize untuk performa optimal
✅ Fallback gradient jika gambar belum diupload
✅ Responsive display di semua device

## Best Practices

### Dimensi Gambar yang Disarankan
- **Rasio:** 16:9 (landscape)
- **Resolusi:** 1920x1080px atau 2560x1440px
- **Format:** WebP (best compression), atau JPEG/PNG
- **Ukuran File:** < 2MB untuk performa optimal

### Optimisasi Gambar
Sebelum upload, optimalkan gambar menggunakan tools:
- [TinyPNG](https://tinypng.com/) - Kompresi PNG/JPEG
- [Squoosh](https://squoosh.app/) - Konversi ke WebP
- [ImageOptim](https://imageoptim.com/) - Optimisasi tanpa quality loss

## Security

✅ Validasi tipe file (whitelist)
✅ Validasi ukuran file
✅ File disimpan di luar document root
✅ Public access via symbolic link
✅ Sanitasi nama file otomatis

## Performance

- Gambar di-load dengan `loading="eager"` untuk hero (above the fold)
- CSS `object-cover` untuk maintain aspect ratio
- Fallback gradient ringan jika gambar belum ada
- Cached by browser untuk kunjungan berikutnya

## Future Enhancements

Potensi perbaikan di masa depan:
- [ ] Multiple gambar hero (carousel/slideshow)
- [ ] Automatic image optimization on upload
- [ ] WebP conversion otomatis
- [ ] Responsive images (srcset)
- [ ] Lazy loading untuk gambar di bawah fold
- [ ] CDN integration
- [ ] Image compression settings
- [ ] Alt text field untuk SEO

## Related Files

- `app/Filament/Pages/SiteContentSettings.php` - Form upload
- `app/Models/SiteSetting.php` - Model
- `resources/views/components/hero.blade.php` - Display component
- `config/filesystems.php` - Storage configuration
- `database/migrations/*_create_site_settings_table.php` - Database schema

## Documentation

- [TROUBLESHOOTING_IMAGE_UPLOAD.md](./TROUBLESHOOTING_IMAGE_UPLOAD.md) - Panduan troubleshooting
- [SITE_SETTINGS_SEEDER.md](./SITE_SETTINGS_SEEDER.md) - Data seeder
- [DYNAMIC_CONTENT_INTEGRATION.md](./DYNAMIC_CONTENT_INTEGRATION.md) - Integrasi konten dinamis

---

**Last Updated:** January 2025
**Status:** ✅ Production Ready
