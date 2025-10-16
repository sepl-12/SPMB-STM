# Migration Guide: site_settings → app_settings

> **Tanggal:** 2025-01-16  
> **Status:** ✅ Ready to Apply

---

## 📋 Ringkasan Perubahan

**SEBELUM:**
- ❌ 2 table terpisah: `site_settings` (content) dan tidak ada contact/social settings
- ❌ 2 halaman admin terpisah
- ❌ Menggunakan `SiteSetting` model

**SESUDAH:**
- ✅ 1 table unified: `app_settings` (key-value)
- ✅ 1 halaman admin: **"Pengaturan Website"** (all-in-one)
- ✅ Menggunakan `AppSetting` model + helper `setting()`

---

## 🎯 Yang Sudah Dibuat

### **1. Database**
- ✅ Migration: `create_app_settings_table.php` 
  - Table dengan struktur key-value
  - Seed 16 settings (contact, social, site content)
- ✅ Migration: `drop_site_settings_table.php`
  - Drop table lama

### **2. Model & Helper**
- ✅ `AppSetting` model dengan caching
- ✅ Helper function: `setting($key, $default)`
- ✅ Registered di `composer.json`

### **3. Admin Panel**
- ✅ `SiteSettings` page (all-in-one)
  - Section: Hero, CTA, Requirements, FAQ, Timeline
  - Section: Contact, Social Media
- ✅ Deleted: `SiteContentSettings` page (old)

### **4. View Composer**
- ✅ Updated: `SiteSettingComposer` → uses `AppSetting`
- ✅ Backward compatible dengan existing views

---

## 🚀 Cara Mengaktifkan

### **Step 1: Composer Dump Autoload**
```bash
composer dump-autoload
```

### **Step 2: Fresh Migrate (RECOMMENDED)**

**⚠️ PERINGATAN:** Ini akan DROP semua table dan re-create dari awal.  
Pastikan ini dilakukan di development/local saja!

```bash
php artisan migrate:fresh --seed
```

**Atau Manual:**
```bash
# Drop all tables
php artisan migrate:reset

# Run all migrations
php artisan migrate

# Seed data (optional)
php artisan db:seed
```

### **Step 3: Clear Cache**
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## 🎨 Akses Admin Panel

1. Login: `http://localhost:8000/admin`
2. Menu: **Pengaturan** → **Pengaturan Website**
3. Edit semua settings dalam 1 halaman

**Sections Available:**
- 🖼️ Hero Halaman Utama (title, subtitle, image)
- 🎯 Call To Action (button label & URL)
- 📋 Syarat Pendaftaran (markdown)
- ❓ FAQ (repeater)
- 📅 Timeline Pendaftaran (repeater)
- 📞 Informasi Kontak (email, wa, phone, address)
- 🌐 Sosial Media (facebook, instagram, twitter, youtube)

---

## 💻 Cara Pakai di Views

### **Before (Old):**
```blade
{{-- Menggunakan SiteSetting model --}}
{{ $settings->hero_title_text }}
{{ $settings->hero_subtitle_text }}
```

### **After (New):**
```blade
{{-- Menggunakan helper function --}}
{{ setting('hero_title') }}
{{ setting('hero_subtitle') }}
{{ setting('contact_email') }}
{{ setting('contact_whatsapp') }}
```

### **View Composer (Sudah Auto-Injected):**

Views yang sudah auto-inject:
- `components.hero`
- `components.registration-flow`
- `components.registration-waves`
- `components.requirements`
- `components.faq`

Di views tersebut, `$settings` object masih bisa dipakai (backward compatible):
```blade
{{-- Still works! --}}
{{ $settings->hero_title_text }}
{{ $settings->hero_subtitle_text }}
{{ $settings->requirements_markdown }}
```

---

## 📊 Mapping Field Names

| Old (site_settings table) | New (app_settings key) |
|---------------------------|------------------------|
| `hero_title_text` | `hero_title` |
| `hero_subtitle_text` | `hero_subtitle` |
| `hero_image_path` | `hero_image` |
| `requirements_markdown` | `requirements_text` |
| `faq_items_json` | `faq_items` |
| `timeline_items_json` | `timeline_items` |
| `cta_button_label` | `cta_button_label` |
| `cta_button_url` | `cta_button_url` |
| (new) | `contact_email` |
| (new) | `contact_whatsapp` |
| (new) | `contact_phone` |
| (new) | `contact_address` |
| (new) | `social_facebook_url` |
| (new) | `social_instagram_handle` |
| (new) | `social_twitter_handle` |
| (new) | `social_youtube_url` |

---

## 🧪 Testing Checklist

- [ ] `composer dump-autoload` berhasil
- [ ] `php artisan migrate:fresh` berhasil
- [ ] Login admin panel berhasil
- [ ] Menu "Pengaturan Website" muncul
- [ ] Bisa edit & save settings
- [ ] Homepage loading tanpa error
- [ ] `setting('hero_title')` returns value di tinker
- [ ] `$settings->hero_title_text` works di component views

---

## ⚠️ Breaking Changes

**None for views!** View Composer sudah handle backward compatibility.

Files yang di-delete (tidak dipakai lagi):
- ❌ `app/Filament/Pages/SiteContentSettings.php`
- ❌ `app/Models/SiteSetting.php` (bisa dihapus manual jika masih ada)
- ❌ `resources/views/filament/pages/site-content-settings.blade.php`

---

## 🔄 Rollback (Jika Ada Masalah)

```bash
# Rollback 2 migration terakhir
php artisan migrate:rollback --step=2

# Akan mengembalikan ke state sebelumnya
```

---

## 📚 Files Changed/Created

### **Created:**
- `database/migrations/2025_10_16_170029_create_app_settings_table.php`
- `database/migrations/2025_10_16_174126_drop_site_settings_table.php`
- `app/Models/AppSetting.php`
- `app/helpers.php`
- `app/Filament/Pages/SiteSettings.php` (renamed from ContactSettings)
- `resources/views/filament/pages/site-settings.blade.php`

### **Modified:**
- `composer.json` (added helpers.php to autoload)
- `app/View/Composers/SiteSettingComposer.php` (uses AppSetting now)
- `app/Providers/AppServiceProvider.php` (no change needed)

### **Deleted:**
- `app/Filament/Pages/SiteContentSettings.php`
- `app/Filament/Pages/ContactSettings.php` (renamed to SiteSettings)
- `resources/views/filament/pages/site-content-settings.blade.php`
- `resources/views/filament/pages/contact-settings.blade.php` (renamed)

---

## ✅ Summary

**What You Get:**
- ✅ Single unified settings system
- ✅ All-in-one admin page
- ✅ Easy `setting()` helper
- ✅ Cached for performance
- ✅ Backward compatible views

**Next Action:**
```bash
composer dump-autoload && php artisan migrate:fresh --seed
```

---

**Last Updated:** 2025-01-16  
**Ready to Apply!** 🚀
