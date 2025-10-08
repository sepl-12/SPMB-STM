# Quick Guide: Ekspor Data Pendaftar

## 🎯 Cara Cepat Ekspor Data

### 1. Export Semua Data (Rekap Cepat)
```
1. Buka menu "Data Pendaftar"
2. Klik tombol "Export Rekap Cepat" di pojok kanan atas
3. File Excel akan terdownload otomatis
```

### 2. Export Data Terpilih
```
1. Buka menu "Data Pendaftar"  
2. Centang checkbox pada data yang ingin diekspor
3. Klik "Export terpilih" di bulk actions
4. Pilih template → Klik Submit
5. File Excel akan terdownload
```

### 3. Export Single Data
```
1. Buka menu "Data Pendaftar"
2. Klik icon download (⬇️) di row yang diinginkan
3. Pilih template → Klik Submit
4. File Excel akan terdownload
```

## 📋 Mengelola Template Ekspor

### Membuat Template Baru
```
1. Buka menu "Template Ekspor"
2. Klik "Create"
3. Isi: Nama, Formulir, Deskripsi
4. Centang "Jadikan default" (optional)
5. Save
6. Tambahkan kolom di tab "Kolom Template"
```

### Menambah Kolom
```
1. Buka template → Tab "Kolom Template"
2. Klik "Tambah Kolom"
3. Pilih Sumber Data:
   - Field Formulir: Pilih field dari dropdown
   - Ekspresi: Tulis path (contoh: wave.wave_name)
4. Isi Header kolom
5. (Optional) Isi Format
6. Save
```

### Preview Template
```
1. Buka menu "Template Ekspor"
2. Klik icon mata (👁️) "Uji Coba"
3. Download file Excel sample (10 data)
```

## 🔧 Expression Path yang Sering Digunakan

| Expression | Deskripsi |
|------------|-----------|
| `registration_number` | No. Registrasi |
| `registered_datetime` | Tanggal Daftar |
| `wave.wave_name` | Nama Gelombang |
| `wave.year` | Tahun Gelombang |
| `wave.start_date` | Mulai Gelombang |
| `wave.end_date` | Selesai Gelombang |

## 📊 Format Hint yang Tersedia

| Format | Hasil |
|--------|-------|
| `uppercase` | HURUF KAPITAL |
| `lowercase` | huruf kecil |
| `capitalize` | Huruf Kapital Setiap Kata |
| `date` | 01/01/2025 |
| `datetime` | 01/01/2025 10:30 |
| `number` | 1.000.000 |
| `decimal` | 1.000.000,50 |

## ⚡ Tips

- Template default muncul di tombol "Export Rekap Cepat"
- Satu formulir hanya bisa punya 1 template default
- Urutkan kolom dengan drag & drop
- Test template dengan "Uji Coba" sebelum digunakan

## 📚 Dokumentasi Lengkap

Lihat [EXPORT_DATA_FEATURE.md](./EXPORT_DATA_FEATURE.md) untuk dokumentasi lengkap.
