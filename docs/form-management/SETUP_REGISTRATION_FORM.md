# Setup Halaman Pendaftaran PPDB

## Prerequisites
- PHP 8.1+
- Composer
- Laravel 11
- MySQL/PostgreSQL
- Node.js & NPM (untuk asset compilation)

## Langkah Setup

### 1. Database Migration
Pastikan semua migrasi sudah dijalankan:
```bash
php artisan migrate
```

### 2. Storage Link
Buat symbolic link untuk storage (agar file upload bisa diakses):
```bash
php artisan storage:link
```

### 3. Seed Data

#### Seed Form (Wajib)
Untuk membuat contoh form pendaftaran:
```bash
php artisan db:seed --class=FormSeeder
```

Ini akan membuat:
- 1 Form: "Formulir PPDB SMK"
- 1 Form Version (aktif)
- 4 Steps: Data Siswa, Data Orang Tua, Upload Berkas, Pembayaran
- 20+ Fields dengan berbagai tipe

#### Seed Wave (Wajib)
Buat gelombang pendaftaran aktif:
```bash
php artisan db:seed --class=WaveSeeder
```

#### Full Seed (Opsional)
Untuk data lengkap termasuk dummy applicants:
```bash
php artisan db:seed
```

### 4. Konfigurasi Wave
Pastikan ada wave yang aktif:
1. Login ke admin panel (`/admin`)
2. Buka menu **Gelombang**
3. Edit wave yang ingin diaktifkan
4. Set **is_active** = true
5. Pastikan tanggal start dan end sesuai

### 5. Konfigurasi Form
1. Login ke admin panel
2. Buka menu **Formulir**
3. Pastikan form sudah aktif (toggle hijau)
4. Klik **Kelola** untuk melihat/edit steps dan fields

### 6. Test Halaman Pendaftaran
Akses: `http://localhost:8000/daftar`

Jika berhasil, Anda akan melihat:
- Header: "Formulir Pendaftaran Siswa Baru"
- Progress wizard dengan 4 langkah
- Form fields sesuai step pertama

## Troubleshooting

### Issue: "Pendaftaran Ditutup"
**Penyebab**: Tidak ada wave yang aktif
**Solusi**: 
```bash
php artisan db:seed --class=WaveSeeder
```
Atau aktifkan wave manual di admin panel

### Issue: "Formulir Belum Tersedia"
**Penyebab**: Tidak ada form atau form tidak aktif
**Solusi**:
```bash
php artisan db:seed --class=FormSeeder
```
Atau aktifkan form di admin panel

### Issue: File upload error
**Penyebab**: Storage link belum dibuat
**Solusi**:
```bash
php artisan storage:link
```

### Issue: Form fields tidak muncul
**Penyebab**: Form version tidak aktif atau tidak ada fields
**Solusi**:
1. Cek di admin panel → Formulir → Kelola
2. Pastikan ada steps dan fields
3. Pastikan toggle "Aktif" berwarna hijau

## File Structure

```
app/
├── Http/
│   └── Controllers/
│       └── RegistrationController.php
└── Models/
    ├── Form.php
    ├── FormVersion.php
    ├── FormStep.php
    ├── FormField.php
    ├── Submission.php
    ├── SubmissionAnswer.php
    └── SubmissionFile.php

database/
└── seeders/
    └── FormSeeder.php

resources/
└── views/
    ├── components/
    │   ├── form/
    │   │   ├── text-input.blade.php
    │   │   ├── textarea.blade.php
    │   │   ├── number-input.blade.php
    │   │   ├── date-input.blade.php
    │   │   ├── select.blade.php
    │   │   ├── multi-select.blade.php
    │   │   ├── radio.blade.php
    │   │   ├── file-upload.blade.php
    │   │   └── checkbox.blade.php
    │   └── wizard-progress.blade.php
    ├── registration.blade.php
    ├── registration-success.blade.php
    └── registration-closed.blade.php

routes/
└── web.php

docs/
└── DYNAMIC_REGISTRATION_FORM.md
```

## Customization

### Mengubah Jumlah Step
Edit di admin panel:
1. Formulir → Kelola → Tab "Langkah"
2. Tambah/Edit/Hapus langkah sesuai kebutuhan
3. Atur urutan dengan drag & drop

### Menambah Field Baru
Edit di admin panel:
1. Formulir → Kelola → Tab "Pertanyaan"
2. Klik "Tambah Pertanyaan"
3. Pilih tipe field dari dropdown
4. Isi label, key, placeholder, dll
5. Set apakah required atau tidak

### Mengubah Styling
Edit file:
- `resources/views/registration.blade.php` - Main form page
- `resources/views/components/form/*.blade.php` - Individual components
- `resources/views/components/wizard-progress.blade.php` - Progress indicator

## Production Checklist

- [ ] Set `APP_ENV=production` di `.env`
- [ ] Set `APP_DEBUG=false` di `.env`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Setup queue worker untuk email notifications (future)
- [ ] Setup backup otomatis untuk file uploads
- [ ] Test payment integration (future)
- [ ] Setup monitoring dan error logging

## Support

Dokumentasi lengkap: `docs/DYNAMIC_REGISTRATION_FORM.md`

Kontak: admin@smkmuh1.sch.id
