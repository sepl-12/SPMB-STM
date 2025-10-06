# Dokumentasi Perubahan Bahasa Admin Panel

## 📋 Overview

Admin panel Filament telah diubah menjadi **Bahasa Indonesia** yang mudah dipahami dan sesuai dengan konteks penggunaan sehari-hari.

---

## 🔄 Perubahan yang Dilakukan

### 1. **Konfigurasi Locale**

#### File: `config/app.php`

**Perubahan:**
```php
// Sebelumnya
'locale' => env('APP_LOCALE', 'en'),
'timezone' => 'UTC',

// Sesudahnya
'locale' => env('APP_LOCALE', 'id'),
'timezone' => 'Asia/Makassar',
```

**Efek:**
- Semua tanggal dan waktu menggunakan timezone Asia/Makassar (WITA)
- Default bahasa aplikasi menjadi Bahasa Indonesia

---

### 2. **Translation File (lang/id.json)**

File ini berisi translasi custom untuk istilah-istilah umum di Filament:

**Contoh Translasi:**
- `"Create"` → `"Tambah Baru"`
- `"Save"` → `"Simpan"`
- `"Delete"` → `"Hapus"`
- `"View"` → `"Lihat Detail"`
- `"Search..."` → `"Cari data..."`
- `"Showing :first to :last of :total results"` → `"Menampilkan :first sampai :last dari :total data"`
- `"Are you sure you want to delete this record?"` → `"Yakin ingin menghapus data ini?"`

---

### 3. **Resource Labels**

#### ApplicantResource (Calon Siswa)
```php
protected static ?string $modelLabel = 'Calon Siswa';
protected static ?string $pluralModelLabel = 'Calon Siswa';
```

**Label Kolom:**
- `'Status Bayar'` dengan nilai: `'Lunas'`, `'Belum Bayar'`, `'Menunggu'`, `'Gagal'`, `'Dikembalikan'`

---

#### PaymentResource (Pembayaran)
```php
protected static ?string $navigationLabel = 'Pembayaran';
protected static ?string $modelLabel = 'Pembayaran';
```

**Label Kolom:**
- `'Kode Order'`
- `'Nama Siswa'`
- `'Metode Bayar'`
- `'Jumlah'`
- `'Terakhir Update'`

**Status:**
- `'PAID'` → `'Lunas'`
- `'PENDING'` → `'Menunggu'`
- `'FAILED'` → `'Gagal'`
- `'REFUNDED'` → `'Dikembalikan'`

---

#### WaveResource (Gelombang)
```php
protected static ?string $navigationLabel = 'Gelombang';
protected static ?string $modelLabel = 'Gelombang Pendaftaran';
```

**Form Fields:**
- `'Nama Gelombang'`
- `'Kode'`
- `'Mulai'` / `'Selesai'`
- `'Kuota (opsional)'`
- `'Biaya Pendaftaran'`
- `'Aktif?'`

---

#### FormResource (Formulir)
```php
protected static ?string $modelLabel = 'Formulir Pendaftaran';
protected static ?string $pluralModelLabel = 'Formulir Pendaftaran';
```

---

#### ExportTemplateResource (Template Ekspor)
```php
protected static ?string $modelLabel = 'Template Ekspor';
```

---

### 4. **Pages Labels**

#### PpdbOverview (Rekap & Statistik)
```php
protected static ?string $navigationGroup = 'Laporan';
protected static ?string $navigationLabel = 'Rekap & Statistik';
protected static ?string $title = 'Rekap & Statistik PPDB';
```

---

#### SiteContentSettings (Halaman Utama)
```php
protected static ?string $navigationGroup = 'Konten Website';
protected static ?string $navigationLabel = 'Halaman Utama';
protected static ?string $title = 'Pengaturan Halaman Utama';
```

**Section Labels:**
- `'Hero Halaman'` - "Atur teks dan gambar hero pada halaman utama."
- `'Syarat & Informasi'`
- `'FAQ'` - "Pertanyaan Umum"
- `'Call To Action'`
- `'Timeline'` - "Tahapan Timeline"

---

## 📊 Perbandingan Before/After

### Navigation Menu

| Before | After |
|--------|-------|
| Applicants | Calon Siswa |
| Payments | Pembayaran |
| Waves | Gelombang |
| Forms | Formulir |
| Export Templates | Template Ekspor |
| PPDB Overview | Rekap & Statistik |
| Site Content | Halaman Utama |

---

### Status Pembayaran

| English | Bahasa Indonesia |
|---------|------------------|
| Paid | Lunas |
| Unpaid | Belum Bayar |
| Pending | Menunggu |
| Failed | Gagal |
| Refunded | Dikembalikan |

---

### Button Actions

| English | Bahasa Indonesia |
|---------|------------------|
| Create | Tambah Baru |
| Edit | Ubah |
| Delete | Hapus |
| View | Lihat Detail |
| Save | Simpan |
| Cancel | Batal |
| Export | Ekspor Data |
| Search | Cari |

---

## 🎯 Manfaat

✅ **Lebih Mudah Dipahami** - Bahasa sehari-hari yang familiar  
✅ **Konsisten** - Terminologi yang seragam di seluruh panel  
✅ **User Friendly** - Mengurangi kebingungan user  
✅ **Profesional** - Tetap terlihat profesional namun approachable  
✅ **Lokal** - Sesuai dengan konteks Indonesia  

---

## 🔧 Cara Menambah Translasi Baru

### 1. Tambahkan ke `lang/id.json`

```json
{
    "Your English Text": "Teks Bahasa Indonesia Anda",
    "Another text": "Teks lainnya"
}
```

### 2. Untuk Resource-specific labels

Edit file Resource terkait (contoh: `ApplicantResource.php`):

```php
TextColumn::make('column_name')
    ->label('Label Bahasa Indonesia')
    ->formatStateUsing(fn ($state) => match($state) {
        'value1' => 'Nilai 1',
        'value2' => 'Nilai 2',
        default => $state
    })
```

### 3. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## 📝 Best Practices

### DO ✅
- Gunakan bahasa yang jelas dan mudah dipahami
- Konsisten dengan istilah yang sudah ada
- Pertimbangkan konteks penggunaan
- Test dengan user sebenarnya

### DON'T ❌
- Jangan gunakan istilah teknis yang sulit dipahami
- Hindari Bahasa Inggris campur Indonesia
- Jangan terlalu formal atau kaku
- Hindari singkatan yang membingungkan

---

## 🗂️ File yang Dimodifikasi

1. ✅ `config/app.php` - Locale & timezone configuration
2. ✅ `lang/id.json` - Custom translations
3. ✅ `app/Filament/Resources/ApplicantResource.php`
4. ✅ `app/Filament/Resources/PaymentResource.php`
5. ✅ `app/Filament/Resources/WaveResource.php`
6. ✅ `app/Filament/Resources/FormResource.php`
7. ✅ `app/Filament/Resources/ExportTemplateResource.php`
8. ✅ `app/Filament/Pages/PpdbOverview.php`
9. ✅ `app/Filament/Pages/SiteContentSettings.php`

---

## 🚀 Testing

### Checklist

- [ ] Login ke admin panel
- [ ] Check navigation menu labels
- [ ] Test Create/Edit forms
- [ ] Verify table column labels
- [ ] Check status badges
- [ ] Test search & filters
- [ ] Verify notification messages
- [ ] Check date & time format
- [ ] Test bulk actions
- [ ] Verify export/import labels

---

## 📞 Support

Jika ada istilah yang perlu ditambahkan atau diubah:
1. Edit `lang/id.json` untuk translasi umum
2. Edit Resource/Page file untuk label spesifik
3. Run `php artisan cache:clear`
4. Refresh browser

---

## ✅ Status

**Version:** 1.0  
**Date:** October 7, 2025  
**Status:** ✅ Complete  
**Language:** Bahasa Indonesia (Sehari-hari)  
**Timezone:** Asia/Makassar (WITA)

---

## 📈 Future Improvements

Rekomendasi untuk pengembangan selanjutnya:

1. **Multi-language Support** - Tambahkan English sebagai opsi
2. **User Preferences** - Biarkan user pilih bahasa sendiri
3. **Role-specific Labels** - Label berbeda untuk role berbeda
4. **Context-aware Help** - Tooltip Bahasa Indonesia
5. **Error Messages** - Custom error message Bahasa Indonesia

---

**Dokumentasi ini dibuat untuk memudahkan maintenance dan pengembangan di masa depan.**
