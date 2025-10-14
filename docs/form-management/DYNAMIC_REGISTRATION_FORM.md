# Halaman Pendaftaran Siswa Baru - Dynamic Wizard Form

## Overview
Halaman pendaftaran siswa baru telah dibuat dengan sistem wizard form yang dinamis. Form ini mengambil data dari tabel `forms`, `form_versions`, `form_steps`, dan `form_fields` yang dikonfigurasi di admin panel.

## Fitur Utama

### 1. **Dynamic Form Generation**
- Form secara otomatis dibuat berdasarkan konfigurasi di admin panel
- Mendukung multi-step wizard dengan navigasi yang mudah
- Setiap step dapat memiliki banyak field dengan berbagai tipe

### 2. **Wizard Navigation**
- **Progress Indicator**: Menampilkan langkah-langkah form dengan visual yang jelas
- **Previous/Next Navigation**: Tombol navigasi antar langkah
- **Quick Jump**: Navigasi cepat ke langkah mana pun tanpa batasan
- **No Validation on Navigation**: User dapat berpindah langkah tanpa harus mengisi field required terlebih dahulu

### 3. **Komponen Form yang Tersedia**

#### Text Input (`text`, `email`, `tel`)
```blade
<x-form.text-input
    label="Nama Lengkap"
    name="nama_lengkap"
    :required="true"
    placeholder="Masukkan nama lengkap"
    helpText="Sesuai KTP/Akta"
/>
```

#### Textarea
```blade
<x-form.textarea
    label="Alamat"
    name="alamat"
    :required="true"
    :rows="4"
/>
```

#### Number Input
```blade
<x-form.number-input
    label="Umur"
    name="umur"
    :min="15"
    :max="25"
/>
```

#### Date Input
```blade
<x-form.date-input
    label="Tanggal Lahir"
    name="tanggal_lahir"
    :required="true"
/>
```

#### Select (Dropdown)
```blade
<x-form.select
    label="Agama"
    name="agama"
    :options="[
        ['label' => 'Islam', 'value' => 'Islam'],
        ['label' => 'Kristen', 'value' => 'Kristen']
    ]"
    :required="true"
/>
```

#### Multi-Select (Checkbox List)
```blade
<x-form.multi-select
    label="Hobi"
    name="hobi"
    :options="[
        ['label' => 'Olahraga', 'value' => 'olahraga'],
        ['label' => 'Membaca', 'value' => 'membaca']
    ]"
/>
```

#### Radio Button
```blade
<x-form.radio
    label="Jenis Kelamin"
    name="jenis_kelamin"
    :options="[
        ['label' => 'Laki-laki', 'value' => 'L'],
        ['label' => 'Perempuan', 'value' => 'P']
    ]"
    :required="true"
/>
```

#### File Upload
```blade
<x-form.file-upload
    label="Upload Ijazah"
    name="ijazah"
    accept="application/pdf"
    maxSize="2MB"
    :required="true"
/>
```

#### Checkbox
```blade
<x-form.checkbox
    label="Saya setuju dengan syarat dan ketentuan"
    name="persetujuan"
    :required="true"
/>
```

## Struktur Database

### Form Structure
```
forms
├── form_versions
    ├── form_steps
    │   └── form_fields (dynamic fields)
    └── submissions
        ├── submission_answers
        └── submission_files
```

## Routes

```php
// Registration routes
Route::get('/daftar', [RegistrationController::class, 'index'])->name('registration.index');
Route::post('/daftar/save-step', [RegistrationController::class, 'saveStep'])->name('registration.save-step');
Route::post('/daftar/jump-to-step', [RegistrationController::class, 'jumpToStep'])->name('registration.jump-to-step');
Route::get('/daftar/success/{registration_number}', [RegistrationController::class, 'success'])->name('registration.success');
```

## Flow Pendaftaran

1. **User mengakses `/daftar`**
   - Sistem mengecek apakah ada wave yang aktif
   - Jika tidak ada, tampilkan halaman "Pendaftaran Ditutup"
   - Jika ada, tampilkan wizard form

2. **User mengisi form step by step**
   - Data disimpan di session setiap kali user navigasi
   - User dapat berpindah langkah kapan saja (previous/next/quick jump)
   - File yang diupload disimpan sementara

3. **User mengirim formulir (Submit)**
   - Data divalidasi dan disimpan ke database
   - Buat record `Applicant` dengan nomor pendaftaran unik
   - Buat record `Submission` dengan snapshot data lengkap
   - Buat record `SubmissionAnswer` untuk setiap field
   - Upload file disimpan di `storage/app/public/registration-files`
   - Session dibersihkan

4. **Redirect ke halaman sukses**
   - Tampilkan nomor pendaftaran
   - Informasi pembayaran
   - Download bukti pendaftaran (future feature)

## Session Management

Data form disimpan di session dengan struktur:
```php
session('registration_data', [
    'nama_lengkap' => 'John Doe',
    'nisn' => '1234567890',
    'tanggal_lahir' => '2005-01-01',
    // ... field lainnya
]);

session('current_step', 0); // Index step saat ini
```

## Seeder

Jalankan seeder untuk membuat contoh form:
```bash
php artisan db:seed --class=FormSeeder
```

Form seeder akan membuat:
- 4 Steps:
  1. Data Siswa (7 fields)
  2. Data Orang Tua (6 fields)
  3. Upload Berkas (4 fields)
  4. Pembayaran (3 fields)

## Customization

### Menambah Field Type Baru

1. Buat komponen Blade di `resources/views/components/form/`
2. Tambahkan case di `registration.blade.php`:
```blade
@case('custom_type')
    <x-form.custom-type
        :label="$field->field_label"
        :name="$field->field_key"
        // ... props lainnya
    />
    @break
```

3. Handle data saving di `RegistrationController`:
```php
case 'custom_type':
    $answerData['answer_value_text'] = $fieldValue;
    break;
```

### Styling

Semua komponen menggunakan:
- **Tailwind CSS** untuk styling
- **Alpine.js** untuk interaksi (file upload drag & drop)
- **Gradient** green untuk branding

## File Upload

File yang diupload disimpan dengan struktur:
```
storage/app/public/registration-files/
├── {random-hash}_filename.pdf
└── {random-hash}_photo.jpg
```

Pastikan symbolic link dibuat:
```bash
php artisan storage:link
```

## Testing

### Manual Testing
1. Pastikan ada wave aktif di admin panel
2. Buat form dengan steps dan fields di admin panel
3. Akses `/daftar`
4. Isi form dan test navigasi
5. Submit dan cek data di database

### Test Data
Gunakan FormSeeder untuk test data:
```bash
php artisan db:seed --class=FormSeeder
```

## Future Improvements

1. **Auto-save**: Simpan data ke session secara otomatis saat user mengetik
2. **Progress persistence**: Simpan progress ke database untuk resume nanti
3. **File validation**: Validasi ukuran dan tipe file lebih detail
4. **Multi-language**: Support bahasa Indonesia dan Inggris
5. **Form preview**: Preview form sebelum submit
6. **PDF generation**: Generate bukti pendaftaran dalam bentuk PDF
7. **Email notification**: Kirim email konfirmasi setelah submit
8. **Payment integration**: Integrasi dengan payment gateway

## Support

Untuk pertanyaan atau masalah, silakan hubungi tim developer atau buka issue di repository.
