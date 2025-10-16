# Contact & Social Media Settings Guide

> Fitur untuk mengelola informasi kontak dan sosial media secara terpusat

**Tanggal:** 2025-01-16  
**Status:** ✅ Implemented  

---

## 📋 Overview

Fitur ini memungkinkan admin untuk mengatur informasi kontak dan sosial media di satu tempat (admin panel), dan data tersebut otomatis tampil di seluruh website.

### ✅ Fitur

- ✅ **Contact Information Management** - Email, WhatsApp, Phone, Address
- ✅ **Social Media Links** - Facebook, Instagram, Twitter, YouTube
- ✅ **Simple Form Interface** - Edit semua dalam 1 halaman
- ✅ **Cached for Performance** - Data di-cache 1 jam
- ✅ **Easy Access via Helper** - `setting('contact_email')`

---

## 🏗️ Arsitektur

```
Admin Panel (Filament)
        ↓
   app_settings Table (Key-Value)
        ↓
   AppSetting Model + Cache
        ↓
   setting() Helper Function
        ↓
   Views (Blade Templates)
```

---

## 📊 Database Structure

**Table:** `app_settings`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| setting_key | varchar(100) | Unique setting key (e.g., 'contact_email') |
| setting_value | text | Setting value |
| setting_type | varchar(20) | Type: string, text, boolean, json |
| setting_description | text | Description for admin |
| created_at | timestamp | Created timestamp |
| updated_at | timestamp | Updated timestamp |

**Default Settings:**

| Key | Default Value | Type |
|-----|---------------|------|
| `contact_email` | info@sekolah.com | string |
| `contact_whatsapp` | 628123456789 | string |
| `contact_phone` | (021) 12345678 | string |
| `contact_address` | Jl. Pendidikan No. 1, Jakarta | text |
| `social_facebook_url` | (empty) | string |
| `social_instagram_handle` | (empty) | string |
| `social_twitter_handle` | (empty) | string |
| `social_youtube_url` | (empty) | string |

---

## 🎨 Admin Panel Usage

### **Akses Menu:**

1. Login ke admin panel: `http://localhost:8000/admin`
2. Klik menu **"Pengaturan"** di sidebar
3. Pilih **"Kontak & Sosial Media"**

### **Edit Settings:**

```
┌─────────────────────────────────────────────┐
│  Informasi Kontak                           │
│  ─────────────────────────────────────────  │
│  📧 Email Kontak: info@sekolah.com         │
│  📱 WhatsApp: 628123456789                  │
│  ☎️  Telepon: (021) 12345678                │
│  📍 Alamat: Jl. Pendidikan No. 1...        │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│  Sosial Media                               │
│  ─────────────────────────────────────────  │
│  Facebook: https://facebook.com/...         │
│  Instagram: @sekolahkita                    │
│  Twitter: @sekolahkita                      │
│  YouTube: https://youtube.com/@...          │
└─────────────────────────────────────────────┘

[Simpan Perubahan]
```

### **Validasi:**

- ✅ Email harus format valid
- ✅ WhatsApp harus format: `628xxx` (9-13 digit)
- ✅ URL harus format valid
- ✅ Required fields tidak boleh kosong

---

## 💻 Developer Usage

### **1. Helper Function (Recommended)**

```php
// Get single setting
$email = setting('contact_email'); // Returns: "info@sekolah.com"
$whatsapp = setting('contact_whatsapp'); // Returns: "628123456789"

// With default value
$phone = setting('contact_phone', '(021) 00000000');
```

### **2. Get Group of Settings**

```php
// Get all contact settings
$contacts = setting_group('contact');
// Returns:
// [
//     'contact_email' => 'info@sekolah.com',
//     'contact_whatsapp' => '628123456789',
//     'contact_phone' => '(021) 12345678',
//     'contact_address' => 'Jl. ...',
// ]

// Get all social settings
$socials = setting_group('social');
```

### **3. Model Methods**

```php
use App\Models\AppSetting;

// Get setting
$value = AppSetting::get('contact_email');

// Set setting
AppSetting::set('contact_email', 'new@email.com');

// Check if exists
if (AppSetting::has('contact_email')) {
    // ...
}

// Delete setting
AppSetting::remove('contact_email');

// Clear cache
AppSetting::clearCache();

// Get all settings
$all = AppSetting::allSettings();
```

---

## 🎨 Blade Template Usage

### **Example 1: Footer**

```blade
{{-- resources/views/layouts/footer.blade.php --}}

<footer class="bg-gray-800 text-white py-8">
    <div class="container mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Contact Info --}}
        <div>
            <h3 class="font-bold text-lg mb-4">Kontak Kami</h3>
            <div class="space-y-2">
                <p>
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:{{ setting('contact_email') }}">
                        {{ setting('contact_email') }}
                    </a>
                </p>
                <p>
                    <i class="fab fa-whatsapp"></i>
                    <a href="https://wa.me/{{ setting('contact_whatsapp') }}">
                        {{ setting('contact_whatsapp') }}
                    </a>
                </p>
                <p>
                    <i class="fas fa-phone"></i>
                    {{ setting('contact_phone') }}
                </p>
            </div>
        </div>

        {{-- Address --}}
        <div>
            <h3 class="font-bold text-lg mb-4">Alamat</h3>
            <p>{{ setting('contact_address') }}</p>
        </div>

        {{-- Social Media --}}
        <div>
            <h3 class="font-bold text-lg mb-4">Follow Us</h3>
            <div class="flex space-x-4">
                @if(setting('social_facebook_url'))
                    <a href="{{ setting('social_facebook_url') }}" target="_blank">
                        <i class="fab fa-facebook fa-2x"></i>
                    </a>
                @endif

                @if(setting('social_instagram_handle'))
                    <a href="https://instagram.com/{{ trim(setting('social_instagram_handle'), '@') }}" target="_blank">
                        <i class="fab fa-instagram fa-2x"></i>
                    </a>
                @endif

                @if(setting('social_twitter_handle'))
                    <a href="https://twitter.com/{{ trim(setting('social_twitter_handle'), '@') }}" target="_blank">
                        <i class="fab fa-twitter fa-2x"></i>
                    </a>
                @endif

                @if(setting('social_youtube_url'))
                    <a href="{{ setting('social_youtube_url') }}" target="_blank">
                        <i class="fab fa-youtube fa-2x"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
</footer>
```

### **Example 2: Contact Page**

```blade
{{-- resources/views/contact.blade.php --}}

<div class="contact-page">
    <h1>Hubungi Kami</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Contact Info --}}
        <div class="contact-info">
            <h2>Informasi Kontak</h2>

            <div class="contact-item">
                <strong>Email:</strong>
                <a href="mailto:{{ setting('contact_email') }}">
                    {{ setting('contact_email') }}
                </a>
            </div>

            <div class="contact-item">
                <strong>WhatsApp:</strong>
                <a href="https://wa.me/{{ setting('contact_whatsapp') }}" 
                   class="btn btn-success">
                    <i class="fab fa-whatsapp"></i> Chat WhatsApp
                </a>
            </div>

            <div class="contact-item">
                <strong>Telepon:</strong>
                <a href="tel:{{ setting('contact_phone') }}">
                    {{ setting('contact_phone') }}
                </a>
            </div>

            <div class="contact-item">
                <strong>Alamat:</strong>
                <p>{{ setting('contact_address') }}</p>
            </div>
        </div>

        {{-- Contact Form --}}
        <div class="contact-form">
            <h2>Kirim Pesan</h2>
            <form action="/contact/send" method="POST">
                @csrf
                <input type="email" name="to" value="{{ setting('contact_email') }}" hidden>
                {{-- Form fields... --}}
            </form>
        </div>
    </div>
</div>
```

### **Example 3: Email Template**

```blade
{{-- resources/views/emails/registration-confirmation.blade.php --}}

<p>Terima kasih telah mendaftar!</p>

<p>
    Jika ada pertanyaan, silakan hubungi kami:
</p>

<ul>
    <li>Email: {{ setting('contact_email') }}</li>
    <li>WhatsApp: {{ setting('contact_whatsapp') }}</li>
    <li>Telepon: {{ setting('contact_phone') }}</li>
</ul>

<p>
    {{ setting('contact_address') }}
</p>
```

---

## ⚡ Performance: Caching

Settings di-cache selama **1 jam** untuk performance optimal.

### **Cache Behavior:**

```php
// First access - Query database
$email = setting('contact_email'); // DB query

// Subsequent access (within 1 hour) - From cache
$email = setting('contact_email'); // From cache (fast!)

// After admin update - Cache cleared automatically
// Next access will query DB again and refresh cache
```

### **Manual Cache Clear:**

```php
use App\Models\AppSetting;

// Clear all settings cache
AppSetting::clearCache();

// Or via Artisan
php artisan cache:clear
```

---

## 🧪 Testing

### **1. Test via Tinker:**

```bash
php artisan tinker

# Get setting
>>> setting('contact_email')
=> "info@sekolah.com"

# Set setting
>>> AppSetting::set('contact_email', 'new@test.com')
>>> setting('contact_email')
=> "new@test.com"

# Get group
>>> setting_group('contact')
=> [
     "contact_email" => "new@test.com",
     "contact_whatsapp" => "628123456789",
     ...
   ]
```

### **2. Test in Blade:**

Create test route:

```php
// routes/web.php
Route::get('/test-settings', function () {
    return view('test-settings', [
        'email' => setting('contact_email'),
        'whatsapp' => setting('contact_whatsapp'),
        'contacts' => setting_group('contact'),
        'socials' => setting_group('social'),
    ]);
});
```

---

## 📝 Setup Instructions

### **1. Run Composer Dump Autoload:**

```bash
composer dump-autoload
```

### **2. Run Migration:**

```bash
php artisan migrate
```

### **3. Clear Cache:**

```bash
php artisan config:clear
php artisan cache:clear
```

### **4. Access Admin Panel:**

1. Go to: `http://localhost:8000/admin`
2. Login dengan credentials admin
3. Klik **"Pengaturan"** → **"Kontak & Sosial Media"**
4. Edit settings dan klik **"Simpan Perubahan"**

### **5. Test in Website:**

Add to any view:

```blade
<p>Email: {{ setting('contact_email') }}</p>
<p>WhatsApp: {{ setting('contact_whatsapp') }}</p>
```

---

## 🔄 Extending Settings

### **Add New Setting:**

```php
// Via Tinker atau Seeder
AppSetting::set('operating_hours', '08:00 - 16:00');
```

### **Add to Form (Optional):**

Edit: `app/Filament/Pages/ContactSettings.php`

```php
TextInput::make('operating_hours')
    ->label('Jam Operasional')
    ->placeholder('08:00 - 16:00'),
```

Don't forget to update:
- `getSettingsData()` method
- Add to save logic (already automatic via foreach)

---

## ⚠️ Important Notes

### **Cache Duration:**

- Default: 1 hour (3600 seconds)
- Change in: `app/Models/AppSetting.php` → `CACHE_DURATION`

### **WhatsApp Format:**

- Use format: `628xxx` (no +, spaces, or dashes)
- Example: `628123456789`
- Link generated: `https://wa.me/628123456789`

### **Social Media Handles:**

- Instagram/Twitter: Simpan dengan atau tanpa `@`
- System akan auto-strip `@` saat generate link

### **Security:**

- ✅ All inputs validated
- ✅ XSS protection via Blade `{{ }}` escaping
- ✅ Only admin can access settings page

---

## 📚 Files Created/Modified

| File | Purpose |
|------|---------|
| `database/migrations/2025_10_16_170029_create_app_settings_table.php` | Migration for app_settings table |
| `app/Models/AppSetting.php` | Model with cache & helper methods |
| `app/helpers.php` | Helper functions `setting()` and `setting_group()` |
| `app/Filament/Pages/ContactSettings.php` | Admin panel page |
| `resources/views/filament/pages/contact-settings.blade.php` | Blade view for settings page |
| `composer.json` | Added helpers.php to autoload |

---

## ✅ Summary

**Status:** ✅ **READY TO USE**

Fitur ini memberikan:
- 🎯 **Centralized Management** - Satu tempat untuk manage semua kontak
- 🚀 **Easy Access** - Simple helper function
- ⚡ **Fast Performance** - Cached untuk speed
- 🔒 **Secure** - Admin-only access dengan validation
- 📱 **Responsive** - Form mobile-friendly di admin panel

**Next Steps:**
1. Run setup commands (composer dump-autoload, migrate)
2. Login ke admin panel
3. Update contact info
4. Use `setting()` helper di views

---

**Last Updated:** 2025-01-16  
**Version:** 1.0.0
