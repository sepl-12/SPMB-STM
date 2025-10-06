# Dynamic Content Integration Documentation

## 📋 Overview

Landing page PPDB SMK Muhammadiyah 1 Sangatta Utara sekarang menggunakan data dinamis dari tabel `site_settings`. Semua konten utama dapat dikelola melalui admin panel Filament.

---

## 🔄 Komponen yang Menggunakan Data Dinamis

### 1. **Hero Section** (`components/hero.blade.php`)
**Data dari database:**
- ✅ `hero_title_text` - Judul utama
- ✅ `hero_subtitle_text` - Deskripsi singkat
- ✅ `hero_image_path` - Background image
- ✅ `cta_button_label` - Label tombol CTA
- ✅ `cta_button_url` - URL tujuan CTA

**Contoh:**
```blade
<h1>{{ $settings->hero_title_text }}</h1>
<p>{{ $settings->hero_subtitle_text }}</p>
```

---

### 2. **Registration Flow** (`components/registration-flow.blade.php`)
**Data dari database:**
- ✅ `timeline_items_json` - Array of timeline steps

**Struktur JSON:**
```json
[
  {
    "step": 1,
    "title": "Buat Akun & Isi Formulir",
    "description": "Calon siswa membuat akun...",
    "icon": "user-plus"
  }
]
```

**Icon Options:**
- `user-plus` - User dengan plus icon
- `document` - Document icon
- `check-circle` - Checkmark circle
- `currency` - Money/currency icon

---

### 3. **Requirements** (`components/requirements.blade.php`)
**Data dari database:**
- ✅ `requirements_markdown` - Persyaratan dalam format markdown

**Format Markdown:**
```
1. Mengisi formulir pendaftaran
2. Pas foto ukuran 3x4 (2 lembar)
3. Fotocopy Kartu Keluarga (KK)
```

Markdown akan di-parse otomatis dan ditampilkan sebagai list dengan numbering.

---

### 4. **FAQ** (`components/faq.blade.php`)
**Data dari database:**
- ✅ `faq_items_json` - Array of FAQ items

**Struktur JSON:**
```json
[
  {
    "question": "Apa saja persyaratan pendaftaran?",
    "answer": "Persyaratan pendaftaran meliputi..."
  }
]
```

---

## 🏗️ Arsitektur

### View Composer Pattern

Kami menggunakan **View Composer** untuk otomatis inject data `$settings` ke semua komponen yang membutuhkan.

**File:** `app/View/Composers/SiteSettingComposer.php`

```php
public function compose(View $view)
{
    $settings = SiteSetting::first();
    $view->with('settings', $settings);
}
```

**Registered in:** `app/Providers/AppServiceProvider.php`

```php
View::composer([
    'components.hero',
    'components.registration-flow',
    'components.requirements',
    'components.faq',
], SiteSettingComposer::class);
```

### Benefits:
✅ Tidak perlu pass `$settings` secara manual di setiap route  
✅ Automatic fallback jika data belum ada  
✅ Centralized data management  
✅ Easier maintenance  

---

## 📝 Cara Update Konten

### 1. Via Seeder (Development)
```bash
php artisan db:seed --class=SiteSettingSeeder
```

### 2. Via Tinker
```bash
php artisan tinker
```
```php
$setting = \App\Models\SiteSetting::first();
$setting->hero_title_text = "Judul Baru";
$setting->save();
```

### 3. Via Filament Admin Panel (Recommended)
1. Login ke `/admin`
2. Navigate ke Site Settings/Content
3. Edit fields
4. Save

---

## 🎯 Testing

### Test Homepage
```bash
php artisan serve
```
Buka browser: `http://localhost:8000`

### Check Data
```bash
php artisan tinker
```
```php
\App\Models\SiteSetting::first()->toArray();
```

---

## 🔧 Troubleshooting

### Error: "Undefined variable $settings"

**Solusi:**
1. Clear view cache:
```bash
php artisan view:clear
```

2. Clear config cache:
```bash
php artisan config:clear
```

3. Restart server

### Error: "Call to a member function ... on null"

**Penyebab:** Belum ada data di tabel `site_settings`

**Solusi:**
```bash
php artisan db:seed --class=SiteSettingSeeder
```

### FAQ tidak terbuka

**Solusi:**
1. Clear browser cache
2. Check JavaScript console for errors
3. Ensure Vite/assets compiled:
```bash
npm run build
```

---

## 📊 Database Schema

### Table: `site_settings`

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `hero_title_text` | varchar(120) | Hero section title |
| `hero_subtitle_text` | text | Hero section subtitle |
| `hero_image_path` | varchar(255) | Path to hero image |
| `requirements_markdown` | longtext | Requirements in markdown |
| `faq_items_json` | json | FAQ items array |
| `cta_button_label` | varchar(50) | CTA button text |
| `cta_button_url` | varchar(255) | CTA button URL |
| `timeline_items_json` | json | Timeline steps array |

---

## 🚀 Next Steps

### Recommended Improvements:

1. **Create Filament Resource for SiteSetting**
   - Easier content management
   - Rich text editor for descriptions
   - Image upload for hero background

2. **Add Caching**
   ```php
   $settings = Cache::remember('site_settings', 3600, function () {
       return SiteSetting::first();
   });
   ```

3. **Add Validation**
   - Ensure at least 1 FAQ item
   - Validate JSON structure
   - Image validation

4. **Multi-language Support**
   - Add locale field
   - Multiple records for different languages

---

## 📞 Support

Jika ada pertanyaan atau masalah:
1. Check documentation
2. Review code comments
3. Check Laravel logs: `storage/logs/laravel.log`

---

## ✅ Checklist

Pastikan semuanya sudah berjalan:

- [x] Seeder created and run
- [x] View Composer registered
- [x] Components updated to use `$settings`
- [x] AppServiceProvider configured
- [x] Testing completed
- [ ] Filament Resource created (optional)
- [ ] Caching implemented (optional)

---

## 📝 Change Log

**Version 1.0** (Current)
- Integrated dynamic content from `site_settings` table
- Implemented View Composer pattern
- Updated 4 main components (Hero, Timeline, Requirements, FAQ)
- Added fallback for missing data
- Created comprehensive documentation

**Date:** October 7, 2025
