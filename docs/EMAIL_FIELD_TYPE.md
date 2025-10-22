# Email Field Type - Dokumentasi

## Overview

Field type `email` adalah field type khusus yang telah ditambahkan ke sistem form untuk menangani input email dengan validasi yang lebih ketat dan komprehensif.

## Fitur Utama

### 1. Validasi Email Standar
- Validasi format email sesuai RFC standard
- Validasi DNS domain (memastikan domain email valid)
- Validasi panjang email (min 5, max 254 karakter)
- Validasi panjang local part (sebelum @, max 64 karakter)

### 2. Validasi Email Lanjutan
- **Pattern Validation**: Menolak email dengan pola mencurigakan:
  - Email yang dimulai dengan angka saja (contoh: `123@domain.com`)
  - Email dengan double dots atau double @ (contoh: `test..test@domain.com`)
  - Email yang dimulai atau diakhiri dengan titik
  - Email yang mengandung whitespace

### 3. Proteksi Disposable Email
Sistem akan menolak email dari domain disposable/temporary seperti:
- 10minutemail.com
- tempmail.org
- guerrillamail.com
- mailinator.com
- throwaway.email

### 4. Normalisasi Data
- Email akan disimpan dalam format lowercase
- Whitespace di awal dan akhir akan dihapus otomatis

## Implementasi

### 1. Enum FormFieldType
```php
enum FormFieldType: string
{
    case EMAIL = 'email';
    // ... other types
    
    public function label(): string
    {
        return match ($this) {
            self::EMAIL => '📧 Email - Input email dengan validasi',
            // ... other labels
        };
    }
}
```

### 2. Validation Service
```php
class FormFieldValidationService
{
    protected function validateEmailAddress(string $email, FormField $field): array
    {
        // Enhanced email validation with custom rules
        // - Pattern validation
        // - Length validation
        // - Disposable domain check
    }
}
```

### 3. Form Field Configuration
Di Filament admin, field type email akan muncul dengan:
- **Label**: 📧 Email - Input email dengan validasi
- **Badge Color**: Primary (biru)
- **Validasi**: Otomatis diterapkan saat form submission

## Penggunaan

### 1. Membuat Field Email di Admin
1. Buka Form Management di admin panel
2. Pilih form yang ingin diedit
3. Tambah field baru atau edit field existing
4. Pilih "Email" dari dropdown "Tipe Input"
5. Atur label, placeholder, dan help text sesuai kebutuhan
6. Centang "Wajib Isi" jika diperlukan

### 2. Validasi Otomatis
Ketika user mengisi form:
- Validasi akan berjalan saat user pindah ke step berikutnya
- Error message akan ditampilkan jika email tidak valid
- Email akan dinormalisasi sebelum disimpan

### 3. Penyimpanan Data
- Email disimpan di kolom `answer_value_text` dalam format lowercase
- Untuk field sistem, email juga disimpan di `applicant_email_address`

## Error Messages

### Bahasa Indonesia
- **Format tidak valid**: "Format Email tidak valid. Pastikan menggunakan format email yang benar (contoh: nama@domain.com)."
- **RFC validation**: "Format Email tidak sesuai standar RFC. Gunakan format email yang valid."
- **DNS validation**: "Domain email pada Email tidak valid atau tidak dapat diverifikasi."
- **Pattern validation**: "Email mengandung karakter atau pola yang tidak diizinkan."
- **Length validation**: "Email terlalu pendek/panjang. Minimal 5 karakter, maksimal 254 karakter."
- **Disposable email**: "Email tidak boleh menggunakan layanan email sementara."

## Testing

### Unit Tests
File: `tests/Unit/FormFieldValidationServiceTest.php`

Test cases meliputi:
- Validasi email valid
- Penolakan email invalid
- Penolakan disposable email domains
- Validasi required/optional fields
- Validasi field types lainnya

### Manual Testing
1. Buat form dengan field email
2. Test dengan berbagai format email:
   - Valid: `test@example.com`
   - Invalid: `invalid-email`, `test@`, `@domain.com`
   - Disposable: `test@10minutemail.com`
3. Verifikasi error messages muncul dengan benar
4. Verifikasi data tersimpan dalam format yang benar

## Migration Guide

### Untuk Field Existing
Jika sudah ada field dengan type `text` yang digunakan untuk email:
1. Edit field di admin panel
2. Ubah type dari "Teks" ke "Email"
3. Field akan otomatis menggunakan validasi email yang baru

### Untuk Development
```php
// Membuat field email programmatically
FormField::create([
    'field_key' => 'user_email',
    'field_label' => 'Alamat Email',
    'field_type' => FormFieldType::EMAIL->value,
    'is_required' => true,
    'field_placeholder_text' => 'contoh@domain.com',
    'field_help_text' => 'Masukkan alamat email yang valid dan aktif',
]);
```

## Konfigurasi Lanjutan

### Menambah Disposable Domain
Edit file `app/Services/FormFieldValidationService.php`:
```php
$disposableDomains = [
    '10minutemail.com',
    'tempmail.org',
    // tambahkan domain baru di sini
    'newdisposable.com',
];
```

### Custom Validation Rules
Untuk menambah validasi khusus, edit method `validateEmailAddress()` dalam `FormFieldValidationService`.

## Troubleshooting

### Email Valid Tapi Ditolak
- Periksa apakah domain termasuk dalam daftar disposable domains
- Pastikan email tidak mengandung pattern yang dilarang
- Cek panjang local part (sebelum @) tidak lebih dari 64 karakter

### DNS Validation Error
- Pastikan server memiliki akses internet untuk DNS lookup
- Beberapa domain mungkin tidak dapat diverifikasi karena konfigurasi DNS
- Pertimbangkan untuk menonaktifkan DNS validation jika diperlukan

### Performance Issues
- DNS validation dapat memperlambat proses validasi
- Pertimbangkan untuk menggunakan cache atau background job untuk validasi DNS
- Monitor performa dan sesuaikan timeout jika diperlukan