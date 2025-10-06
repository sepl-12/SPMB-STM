# Site Settings Seeder Documentation

## 📋 Overview

Seeder ini dibuat untuk mengisi tabel `site_settings` dengan data default untuk landing page PPDB SMK Muhammadiyah 1 Sangatta Utara.

## 🗂️ Struktur Data

### Fields yang di-seed:

1. **hero_title_text**
   - Judul utama di hero section
   - Default: "Penerimaan Peserta Didik Baru Online 2025/2026"

2. **hero_subtitle_text**
   - Subtitle/deskripsi di hero section
   - Default: Deskripsi tentang pendaftaran siswa baru

3. **hero_image_path**
   - Path ke gambar background hero
   - Default: "hero-bg.jpg"

4. **requirements_markdown**
   - Daftar persyaratan pendaftaran
   - Format: Markdown list (numbered)
   - Berisi 6 persyaratan utama

5. **faq_items_json**
   - Array of FAQ items
   - Struktur: `[{ question: "", answer: "" }, ...]`
   - Berisi 5 FAQ default

6. **cta_button_label**
   - Label tombol Call-to-Action
   - Default: "Daftar Sekarang"

7. **cta_button_url**
   - URL tujuan CTA button
   - Default: "/daftar"

8. **timeline_items_json**
   - Array alur pendaftaran (4 langkah)
   - Struktur: `[{ step: 1, title: "", description: "", icon: "" }, ...]`

## 🚀 Cara Menjalankan

### Menjalankan hanya SiteSettingSeeder:
```bash
php artisan db:seed --class=SiteSettingSeeder
```

### Menjalankan semua seeders (termasuk SiteSettingSeeder):
```bash
php artisan db:seed
```

### Fresh migration dengan seed:
```bash
php artisan migrate:fresh --seed
```

## 📝 Cara Update Data

### 1. Update via Seeder File
Edit file `database/seeders/SiteSettingSeeder.php` lalu jalankan ulang seeder.

### 2. Update via Tinker
```bash
php artisan tinker
```
```php
$setting = \App\Models\SiteSetting::first();
$setting->hero_title_text = "Judul Baru";
$setting->save();
```

### 3. Update via Filament Admin Panel
Jika sudah dibuat resource Filament untuk SiteSetting, bisa update langsung melalui admin panel.

## 🔄 Reset Data

Jika ingin reset data site_settings:
```bash
php artisan db:seed --class=SiteSettingSeeder
```

Atau hapus dulu lalu seed ulang:
```bash
php artisan tinker
```
```php
\App\Models\SiteSetting::truncate();
exit
```
```bash
php artisan db:seed --class=SiteSettingSeeder
```

## 📊 Data yang Di-seed

### FAQ Items (5 items):
1. Apa saja persyaratan pendaftaran?
2. Berapa biaya pendaftaran?
3. Apakah ada jalur prestasi?
4. Kapan pengumuman hasil seleksi?
5. Bagaimana jika saya mengalami kesulitan saat mendaftar?

### Timeline Items (4 steps):
1. Buat Akun & Isi Formulir
2. Seleksi Berkas
3. Pengumuman Hasil
4. Daftar Ulang

### Requirements (6 items):
1. Mengisi formulir pendaftaran
2. Pas foto ukuran 3x4 (2 lembar)
3. Fotocopy Kartu Keluarga (KK)
4. Fotocopy Akta Kelahiran
5. Fotocopy Kartu/surat keterangan NISN
6. Mengikuti test seleksi

## ⚠️ Notes

- Seeder ini hanya membuat **1 record** di tabel site_settings
- Jika data sudah ada, akan terjadi error. Hapus dulu data lama atau gunakan `updateOrCreate()`
- Data JSON harus sesuai format array PHP
- Field `hero_image_path` harus sesuai dengan nama file yang ada di `public/` directory

## 🔧 Troubleshooting

### Error: "SQLSTATE[23000]: Integrity constraint violation"
Data sudah ada di database. Hapus dulu atau gunakan:
```bash
php artisan migrate:fresh --seed
```

### Error: "Class 'SiteSettingSeeder' not found"
Jalankan:
```bash
composer dump-autoload
php artisan db:seed --class=SiteSettingSeeder
```

## 📞 Support

Jika ada pertanyaan atau masalah, hubungi tim development.
