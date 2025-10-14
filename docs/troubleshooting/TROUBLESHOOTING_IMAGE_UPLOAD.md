# Troubleshooting Upload Gambar Hero

## Masalah Upload Gambar di Panel Admin

Jika terjadi error saat upload gambar hero di panel admin, ikuti langkah-langkah berikut:

### Error: "Unable to retrieve the file_size for file at location: livewire-tmp"

**Penyebab:** Livewire tidak dapat menemukan folder temporary untuk upload file.

**Solusi:**

1. Publish konfigurasi Livewire:
```bash
php artisan vendor:publish --tag=livewire:config
```

2. Edit file `config/livewire.php` dan pastikan konfigurasi temporary file upload:
```php
'temporary_file_upload' => [
    'disk' => 'local',                  // Gunakan disk 'local'
    'directory' => 'livewire-tmp',      // Nama folder temporary
    // ...existing config...
],
```

3. Buat folder livewire-tmp dengan permission yang benar:
```bash
mkdir -p storage/app/private/livewire-tmp
mkdir -p storage/app/livewire-tmp
chmod -R 775 storage/app
```

4. Clear dan rebuild cache:
```bash
php artisan optimize:clear
php artisan config:cache
```

5. Restart server development (jika menggunakan artisan serve).

### 1. Pastikan Symbolic Link Sudah Ada

Jalankan command berikut untuk membuat symbolic link dari `storage/app/public` ke `public/storage`:

```bash
php artisan storage:link
```

Verifikasi dengan:
```bash
ls -la public/ | grep storage
```

Output seharusnya menunjukkan symbolic link seperti:
```
lrwxr-xr-x  storage -> /path/to/project/storage/app/public
```

### 2. Pastikan Folder Hero Ada dan Memiliki Permission yang Benar

```bash
mkdir -p storage/app/public/hero
chmod -R 775 storage/app/public
```

### 3. Cek Permission Storage

Pastikan folder storage dan semua subfolder memiliki permission yang benar:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 4. Clear Cache

Setelah melakukan perubahan, clear semua cache:

```bash
php artisan optimize:clear
```

### 5. Verifikasi Konfigurasi FileUpload

File: `app/Filament/Pages/SiteContentSettings.php`

Pastikan konfigurasi FileUpload seperti berikut:

```php
FileUpload::make('hero_image_path')
    ->label('Gambar Hero')
    ->image()
    ->disk('public')                    // Disk yang digunakan
    ->directory('hero')                 // Folder tujuan
    ->visibility('public')              // Visibility file
    ->imageEditor()                     // Enable image editor
    ->maxSize(5120)                     // Max 5MB
    ->acceptedFileTypes([               // Tipe file yang diterima
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/webp'
    ])
    ->preserveFilenames()               // Preserve nama file original
    ->columnSpanFull()
```

### 6. Verifikasi Konfigurasi Disk

File: `config/filesystems.php`

Pastikan disk `public` sudah dikonfigurasi:

```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
    'throw' => false,
    'report' => false,
],
```

### 7. Cek .env File

Pastikan `APP_URL` sudah diset dengan benar:

```env
APP_URL=http://localhost:8000
# atau
APP_URL=https://yourdomain.com
```

### 8. Verifikasi Upload Berhasil

Setelah upload, cek apakah file tersimpan:

```bash
ls -la storage/app/public/hero/
```

File gambar seharusnya ada di folder tersebut.

### 9. Akses Gambar dari Browser

Jika symbolic link sudah benar, gambar bisa diakses via:

```
http://localhost:8000/storage/hero/nama-file.jpg
```

### 10. Debug Mode

Jika masih error, aktifkan debug mode di `.env`:

```env
APP_DEBUG=true
```

Kemudian cek log di `storage/logs/laravel.log` untuk melihat error detail.

## Catatan Penting

1. **Jangan gunakan disk 'local'** untuk file yang perlu diakses public, gunakan 'public'.
2. **Symbolic link wajib dibuat** agar file di `storage/app/public` bisa diakses via `/storage`.
3. **Permission penting** - pastikan web server (nginx/apache) bisa write ke folder storage.
4. File yang diupload akan disimpan di: `storage/app/public/hero/nama-file.ext`
5. File bisa diakses via URL: `APP_URL/storage/hero/nama-file.ext`

## Cara Menggunakan Gambar di Blade

Setelah tersimpan, gunakan di Blade template:

```blade
@if(isset($settings['hero_image_path']) && $settings['hero_image_path'])
    <img src="{{ Storage::disk('public')->url($settings['hero_image_path']) }}" 
         alt="Hero Image">
@endif
```

Atau dengan helper:

```blade
<img src="{{ asset('storage/' . $settings['hero_image_path']) }}" alt="Hero Image">
```

## Alternatif: Gunakan CDN/External Storage

Jika masih bermasalah, pertimbangkan menggunakan:
- Amazon S3
- DigitalOcean Spaces
- Cloudinary
- ImgBB

Cukup ubah disk di FileUpload dari 'public' ke disk yang diinginkan.
