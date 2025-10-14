# Fitur Ekspor Data Pendaftar

Dokumentasi lengkap untuk fitur ekspor data pendaftar PPDB menggunakan template yang dapat dikustomisasi.

## 📋 Overview

Sistem ekspor data pendaftar memungkinkan admin untuk:
- Membuat template ekspor kustom dengan kolom yang dapat dikonfigurasi
- Mengekspor data pendaftar ke format Excel (.xlsx)
- Menggunakan template default untuk ekspor cepat
- Preview template sebelum digunakan
- Ekspor single record atau bulk export

## 🚀 Fitur Utama

### 1. **Template Ekspor**
Sistem menggunakan `ExportTemplate` yang berisi:
- Nama template
- Deskripsi
- Status default
- Kolom-kolom ekspor yang dapat dikustomisasi

### 2. **Kolom Template**
Setiap template memiliki kolom dengan konfigurasi:
- **Header Label**: Nama kolom di Excel
- **Order**: Urutan kolom
- **Source Type**: 
  - `form_field`: Data dari field formulir
  - `expression`: Data dari ekspresi/atribut model
- **Source Key**: Field key atau expression path
- **Format Hint**: Format output (optional)

### 3. **Format Hint yang Didukung**
- `uppercase`: Mengubah ke huruf kapital
- `lowercase`: Mengubah ke huruf kecil
- `capitalize`: Kapitalisasi setiap kata
- `date`: Format tanggal (dd/mm/yyyy)
- `datetime`: Format tanggal waktu (dd/mm/yyyy HH:mm)
- `number`: Format angka dengan pemisah ribuan
- `decimal`: Format desimal dengan 2 digit

## 📁 File yang Terlibat

### Export Class
- **File**: `app/Exports/ApplicantsExport.php`
- **Fungsi**: Class utama untuk mengekspor data menggunakan Laravel Excel
- **Interface**: 
  - `FromCollection`: Sumber data
  - `WithHeadings`: Header kolom
  - `WithMapping`: Mapping data ke kolom
  - `WithTitle`: Nama sheet
  - `WithStyles`: Styling Excel

### Resource Files
1. **ApplicantResource.php**
   - Method `makeExportAction()`: Export single record
   - Method `makeBulkExportAction()`: Export multiple records

2. **ListApplicants.php**
   - Action `quickExport`: Export semua data dengan template default

3. **ExportTemplateResource.php**
   - CRUD template ekspor
   - Action `preview`: Uji coba template dengan 10 data sample
   - Action `setDefault`: Set template sebagai default

## 🎯 Cara Penggunaan

### Membuat Template Ekspor

1. **Buka menu "Template Ekspor"** di sidebar (grup PPDB)

2. **Klik "Create"** untuk membuat template baru

3. **Isi form template**:
   ```
   - Formulir: Pilih formulir yang akan diekspor
   - Nama Template: Contoh "Rekap Lengkap Pendaftar"
   - Deskripsi: Deskripsi singkat template
   - Jadikan default: Toggle untuk set sebagai template default
   ```

4. **Tambah kolom** pada tab "Kolom Template":
   - Klik "Tambah Kolom"
   - Pilih **Sumber Data**:
     - **Field Formulir**: Pilih field dari dropdown
     - **Ekspresi**: Tulis expression path
   - Isi **Header**: Label kolom di Excel
   - (Optional) Isi **Format**: Format output
   - Simpan

5. **Uji coba template**:
   - Klik icon mata (👁️) "Uji Coba"
   - File Excel dengan 10 data sample akan terdownload

### Expression Path yang Tersedia

#### Data Applicant Standar
```
registration_number         → Nomor Registrasi
registered_datetime        → Tanggal Daftar
created_at                 → Tanggal Dibuat
updated_at                 → Tanggal Diupdate
```

#### Data Relasi (dot notation)
```
wave.wave_name            → Nama Gelombang
wave.year                 → Tahun Gelombang
wave.start_date           → Tanggal Mulai Gelombang
wave.end_date             → Tanggal Selesai Gelombang
form.form_name            → Nama Formulir
```

#### Contoh Penggunaan
```
Kolom: No. Pendaftaran
Source: expression
Key: registration_number

Kolom: Gelombang
Source: expression  
Key: wave.wave_name

Kolom: Nama Lengkap
Source: form_field
Key: full_name (pilih dari dropdown)
```

### Ekspor Data

#### 1. Export Single Record
1. Buka halaman **"Data Pendaftar"**
2. Klik icon download di row yang ingin diekspor
3. Pilih template ekspor dari dropdown
4. Klik "Submit"
5. File Excel akan terdownload dengan nama: `pendaftar_{no_registrasi}_{timestamp}.xlsx`

#### 2. Bulk Export
1. Buka halaman **"Data Pendaftar"**
2. Centang checkbox pendaftar yang ingin diekspor
3. Klik "Export terpilih" di bulk actions
4. Konfirmasi aksi
5. Pilih template ekspor
6. File Excel akan terdownload dengan nama: `pendaftar_bulk_{timestamp}.xlsx`

#### 3. Export Rekap Cepat
1. Buka halaman **"Data Pendaftar"**
2. Klik tombol **"Export Rekap Cepat"** di header
   - Tombol hanya muncul jika ada template default
3. Semua data pendaftar akan diekspor menggunakan template default
4. File Excel akan terdownload dengan nama: `rekap_pendaftar_{timestamp}.xlsx`

## 🔧 Konfigurasi

### Setting Template Default

Ada 2 cara set template default:

1. **Saat Membuat/Edit Template**:
   - Toggle "Jadikan default" saat create/edit template

2. **Dari List Template**:
   - Klik action "Set Default" (⭐) pada template yang diinginkan
   - Konfirmasi
   - Template lain akan otomatis di-unset sebagai default

**Note**: Hanya satu template per formulir yang bisa menjadi default.

## 📊 Contoh Template

### Template: Rekap Lengkap Pendaftar

| # | Header | Source Type | Source Key | Format |
|---|--------|-------------|------------|---------|
| 1 | No. Registrasi | expression | registration_number | - |
| 2 | Gelombang | expression | wave.wave_name | - |
| 3 | Tahun | expression | wave.year | - |
| 4 | Nama Lengkap | form_field | full_name | capitalize |
| 5 | Email | form_field | email | lowercase |
| 6 | No. Telepon | form_field | phone_number | - |
| 7 | Tanggal Lahir | form_field | birth_date | date |
| 8 | Alamat | form_field | address | - |
| 9 | Tanggal Daftar | expression | registered_datetime | datetime |

### Template: Rekap Singkat

| # | Header | Source Type | Source Key | Format |
|---|--------|-------------|------------|---------|
| 1 | No. Reg | expression | registration_number | - |
| 2 | Nama | form_field | full_name | capitalize |
| 3 | Email | form_field | email | - |
| 4 | Gelombang | expression | wave.wave_name | - |

## 🛠️ Technical Details

### Dependencies
```json
{
  "maatwebsite/excel": "^3.1"
}
```

### Export Process Flow
```
User Action
    ↓
Action Handler (Resource)
    ↓
Create ApplicantsExport Instance
    ↓
Load Template & Columns
    ↓
Map Data to Columns
    ↓
Apply Formatting
    ↓
Generate Excel File
    ↓
Download to Browser
```

### Data Mapping
```php
// Form Field
getFormFieldValue($applicant, $fieldKey)
    → $applicant->getLatestAnswerForField($fieldKey)
    → Format berdasarkan field type
    → Return formatted value

// Expression
evaluateExpression($applicant, $expression)
    → Parse expression path
    → Navigate object relationships
    → Return attribute value
```

## 🐛 Troubleshooting

### Template tidak muncul saat ekspor
**Solusi**: Pastikan template sudah dibuat dan memiliki kolom

### Kolom kosong di Excel
**Solusi**: 
- Cek source type dan source key sudah benar
- Pastikan data pendaftar memiliki jawaban untuk field tersebut
- Cek field key sesuai dengan field di formulir aktif

### Error saat ekspor
**Solusi**:
- Cek log error di notifikasi
- Pastikan semua relasi ter-load (wave, answers)
- Validasi expression path sudah benar

### Preview tidak menampilkan data
**Solusi**: Pastikan ada data pendaftar di database (minimal 1)

## 📝 Best Practices

1. **Buat template default untuk setiap formulir**
   - Memudahkan ekspor cepat
   - Konsistensi format ekspor

2. **Gunakan nama header yang jelas**
   - Memudahkan pembacaan Excel
   - Hindari singkatan yang ambigu

3. **Manfaatkan format hint**
   - Konsistensi format data
   - Readability lebih baik

4. **Test template dengan preview**
   - Cek format output sebelum ekspor massal
   - Validasi semua kolom terisi dengan benar

5. **Urutkan kolom secara logis**
   - Data identitas di awal
   - Data detail di tengah
   - Data sistem di akhir

## 🚀 Future Enhancements

Fitur yang bisa ditambahkan di masa depan:
- [ ] Export ke format CSV
- [ ] Export ke PDF
- [ ] Queue untuk ekspor data besar
- [ ] Email hasil ekspor
- [ ] Template ekspor dengan multiple sheets
- [ ] Custom formula di Excel
- [ ] Chart/grafik di Excel
- [ ] Export terjadwal (cron)
- [ ] Filter data sebelum ekspor
- [ ] Export dengan grouping

## 📞 Support

Jika menemui masalah atau butuh bantuan:
1. Cek dokumentasi ini terlebih dahulu
2. Cek error message di notifikasi
3. Hubungi admin sistem

---

**Last Updated**: 8 Oktober 2025
**Version**: 1.0.0
