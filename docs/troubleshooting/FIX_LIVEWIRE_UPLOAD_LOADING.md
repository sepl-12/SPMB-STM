# Fix: File Upload Loading Terus-Menerus (Livewire)

## Masalah

File upload di panel admin (Gambar Hero) menunjukkan status "Menunggu ukuran berkas" dan loading terus-menerus tanpa berhasil mengupload.

## Penyebab

Livewire tidak dapat mengakses metadata file karena:

1. **Disk 'local' menggunakan private storage** (`storage/app/private`)
   - Private storage tidak dapat diakses via HTTP
   - Livewire butuh akses HTTP untuk mendapatkan file size

2. **ImageEditor dan preserveFilenames** 
   - Fitur tambahan yang bisa menyebabkan conflict
   - Menambah kompleksitas proses upload

## Solusi yang Diterapkan

### 1. Buat Disk Khusus untuk Livewire Temporary

**File:** `config/filesystems.php`

Tambahkan disk baru `livewire-tmp` yang menggunakan public storage:

```php
'livewire-tmp' => [
    'driver' => 'local',
    'root' => storage_path('app/public/livewire-tmp'),
    'url' => env('APP_URL').'/storage/livewire-tmp',
    'visibility' => 'public',
    'throw' => false,
    'report' => false,
],
```

**Mengapa ini penting:**
- ✅ File temporary dapat diakses via HTTP
- ✅ Livewire bisa mendapatkan metadata file
- ✅ Terpisah dari folder hero (organized)
- ✅ Auto-cleanup setelah upload selesai

### 2. Update Konfigurasi Livewire

**File:** `config/livewire.php`

```php
'temporary_file_upload' => [
    'disk' => 'livewire-tmp',  // Gunakan disk khusus
    'directory' => null,        // Tidak perlu subdirectory
    // ...rest of config
],
```

**Perubahan:**
- ❌ `disk' => 'local'` (private, tidak bisa diakses HTTP)
- ✅ `disk' => 'livewire-tmp'` (public, bisa diakses HTTP)
- ❌ `'directory' => 'livewire-tmp'` (nested path)
- ✅ `'directory' => null` (root of disk)

### 3. Simplify FileUpload Component

**File:** `app/Filament/Pages/SiteContentSettings.php`

**Dihapus:**
- ❌ `->imageEditor()` - Bisa menyebabkan conflict
- ❌ `->preserveFilenames()` - Tidak perlu untuk use case ini

**Ditambahkan:**
- ✅ `->downloadable()` - User bisa download gambar yang sudah diupload
- ✅ `->openable()` - User bisa preview gambar

**Hasil:**
```php
FileUpload::make('hero_image_path')
    ->label('Gambar Hero')
    ->image()
    ->disk('public')
    ->directory('hero')
    ->visibility('public')
    ->maxSize(5120)
    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
    ->helperText('Unggah gambar dengan rasio 16:9 untuk tampilan terbaik. Max 5MB.')
    ->downloadable()
    ->openable()
    ->columnSpanFull()
```

### 4. Buat Folder Livewire-tmp

```bash
mkdir -p storage/app/public/livewire-tmp
chmod -R 775 storage/app/public/livewire-tmp
```

### 5. Tambahkan .gitignore

File temporary tidak perlu di-commit ke git:

```gitignore
*
!.gitignore
```

## Cara Kerja Upload Sekarang

### Flow Upload:

1. **User select file** → File dikirim ke browser
2. **Livewire upload temporary** → File disimpan di `storage/app/public/livewire-tmp/`
3. **Get file metadata** → Livewire akses via HTTP: `/storage/livewire-tmp/filename`
4. **User submit form** → File dipindah dari temp ke `storage/app/public/hero/`
5. **Cleanup** → File temporary dihapus otomatis (24 jam)

### Struktur Folder:

```
storage/
└── app/
    └── public/
        ├── hero/                   # Permanent storage (gambar hero)
        │   └── uploaded-image.jpg
        └── livewire-tmp/           # Temporary storage (saat upload)
            └── [auto-cleaned]
```

## Langkah-langkah Perbaikan

### 1. Update Config Files

Edit `config/filesystems.php` dan `config/livewire.php` seperti di atas.

### 2. Create Folder

```bash
mkdir -p storage/app/public/livewire-tmp
chmod -R 775 storage/app/public/livewire-tmp
```

### 3. Clear Cache

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:clear
```

### 4. Restart Server

Jika menggunakan `php artisan serve`:
```bash
# Stop server (Ctrl+C)
# Start again
php artisan serve
```

### 5. Hard Refresh Browser

- Mac: `Cmd + Shift + R`
- Windows/Linux: `Ctrl + Shift + R`

### 6. Test Upload

1. Buka `/admin/site-content`
2. Scroll ke **Gambar Hero**
3. Drag & drop atau click untuk upload
4. Status seharusnya: ✅ File siap (bukan loading terus)
5. Click **Simpan**

## Verifikasi Fix Berhasil

### ✅ Checklist:

- [ ] File tidak loading terus-menerus
- [ ] Ukuran file terdeteksi dengan benar
- [ ] Preview gambar muncul setelah upload
- [ ] Form bisa di-submit tanpa error
- [ ] Gambar tersimpan di `storage/app/public/hero/`
- [ ] Gambar tampil di halaman utama
- [ ] Folder `livewire-tmp` terbuat otomatis
- [ ] File temporary di-cleanup setelah 24 jam

### Debug Checklist:

Jika masih bermasalah:

1. **Cek Symbolic Link:**
   ```bash
   ls -la public/storage
   ```
   Should point to: `storage/app/public`

2. **Cek Permission:**
   ```bash
   ls -la storage/app/public/
   ```
   Should be: `drwxrwxr-x` (775)

3. **Cek Disk Config:**
   ```bash
   php artisan tinker
   Storage::disk('livewire-tmp')->exists('.');
   # Should return: true
   ```

4. **Cek Browser Console:**
   - Open DevTools (F12)
   - Check Network tab saat upload
   - Cari error 404 atau 403

5. **Cek Laravel Log:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

## Common Issues & Solutions

### Issue 1: 404 Error on Livewire Endpoint

**Symptom:** File upload stuck, network tab shows 404 on `/livewire/upload-file`

**Solution:**
```bash
php artisan route:clear
php artisan config:cache
```

### Issue 2: Permission Denied

**Symptom:** Error "Unable to create directory"

**Solution:**
```bash
chmod -R 775 storage
chown -R www-data:www-data storage  # Linux
# atau
chown -R _www:_www storage  # Mac
```

### Issue 3: File Size Always "Waiting"

**Symptom:** Status tetap "Menunggu ukuran berkas"

**Solution:**
- Cek `APP_URL` di `.env` sudah benar
- Cek symbolic link: `php artisan storage:link`
- Cek disk config: `Storage::disk('livewire-tmp')->url('')`

### Issue 4: File Uploaded but Not Saved

**Symptom:** File ter-upload tapi tidak tersimpan setelah submit

**Solution:**
- Cek method `submit()` di SiteContentSettings
- Pastikan field name match: `hero_image_path`
- Cek apakah field ada di `$fillable` atau `$guarded` di model

## Performance & Security

### Auto-Cleanup

Livewire automatically cleanup temporary files older than 24 hours:

```php
'cleanup' => true,
```

**Manual cleanup:**
```bash
php artisan livewire:cleanup-temp-files
```

### Storage Optimization

**Recommended settings:**

```php
// config/livewire.php
'temporary_file_upload' => [
    'max_upload_time' => 5,  // 5 minutes max
    'cleanup' => true,        // Auto-cleanup
],
```

### Security Best Practices

1. **Validate file types:**
   ```php
   ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
   ```

2. **Limit file size:**
   ```php
   ->maxSize(5120)  // 5MB
   ```

3. **Use visibility public only for public files:**
   ```php
   ->visibility('public')
   ```

4. **Store sensitive files in private disk:**
   ```php
   ->disk('local')  // For private files
   ```

## Related Files

### Modified:
- `config/filesystems.php` - Added livewire-tmp disk
- `config/livewire.php` - Changed disk to livewire-tmp
- `app/Filament/Pages/SiteContentSettings.php` - Simplified FileUpload

### Created:
- `storage/app/public/livewire-tmp/` - Temporary upload folder
- `storage/app/public/livewire-tmp/.gitignore` - Ignore temp files

## References

- [Livewire File Upload Docs](https://livewire.laravel.com/docs/uploads)
- [Filament FileUpload Docs](https://filamentphp.com/docs/forms/fields/file-upload)
- [Laravel Filesystem Docs](https://laravel.com/docs/filesystem)

---

**Status:** ✅ Fixed
**Tested:** ✅ Yes
**Last Updated:** October 7, 2025
