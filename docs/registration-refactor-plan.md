# Rencana Refactor Modul Registrasi PPDB

## 1. Tujuan dan Prinsip
- Menjadikan modul registrasi mudah dibaca, mudah diuji, dan minim dependensi rapuh.
- Memecah tanggung jawab berdasarkan lapisan (Controller → Action/Service → Model) supaya perubahan lokal tidak merembet.
- Mengurangi duplikasi logika (terutama pemuatan Form, validasi, dan manajemen sesi) lewat reuse komponen.
- Memanfaatkan pola desain bawaan Laravel (FormRequest, Action/Command class, Service class, ViewModel) agar tim cepat familiar.
- Menjaga perilaku bisnis tetap stabil melalui refactor terukur + penambahan test otomatis.

## 2. Diagnosa Singkat Kondisi Saat Ini
- `RegistrationController` berperan sebagai "god class": memuat form, mengelola sesi, validasi, penyimpanan file, transaksi DB, hingga kirim email dalam satu metode.
- Query form & wave diulang di beberapa metode dan bahkan dilakukan langsung di Blade (`registration.blade.php`), membuat view berat dan sulit diuji.
- Data registrasi disimpan sebagai array bebas di sesi tanpa kontrak eksplisit → rawan typo, sulit diinspeksi, dan bikin validasi tidak konsisten.
- `FormFieldValidationService` menangani banyak hal sekaligus (menyusun rules, custom message, validasi lanjutan email) dan memiliki logika yang tumpang tindih.
- Pemetaan jawaban ke model (`Submission`, `SubmissionAnswer`, `SubmissionFile`) dilakukan inline tanpa abstraksi, sehingga sulit dimodifikasi dan diverifikasi.
- Error handling menggunakan catch-all `\Exception` tanpa pencatatan khusus, sehingga debugging insiden nyata cukup menyulitkan.
- Tidak ada lapisan ViewModel/presenter; Blade bergantung langsung pada struktur model Eloquent dan session.

## 3. Target Arsitektur

### 3.1 Batas Modul & Struktur Direktori
```
app/Registration/
    Actions/            # Kelas use-case (SaveStepAction, SubmitRegistrationAction, JumpToStepAction)
    Data/               # Data Transfer Object untuk tiap langkah & keseluruhan payload
    Services/           # Wizard loader, Session store, File persister, Code generator
    Support/            # Helper kecil (enum, mapper, rule builder)
    Validators/         # FormRequest/Form validator khusus per langkah / keseluruhan
resources/views/registration/
    index.blade.php     # View utama tanpa query DB
    partials/           # Komponen partial per tipe field (opsional)
```
- `app/Services` tetap dipakai untuk utilitas lintas bounded-context (mis. `GmailMailableSender`), tetapi logika bisnis registrasi pindah ke folder khusus.
- Pertahankan model Eloquent tetap di `app/Models`; buat repository ringan atau query object jika diperlukan caching.

### 3.2 Komponen Inti
- **RegistrationWizardLoader (Service):** memuat form aktif + langkah + field sekali saja, menyediakan cache (mis. cache menit-an) dan API nyaman (`currentStep($index)`).
- **RegistrationSessionStore (Service):** pembungkus akses session; mengembalikan/menyimpan `RegistrationData` DTO, menangani file path lama agar tidak orphan.
- **Data Objects (`app/Registration/Data`):**
  - `RegistrationStepData` untuk data per langkah.
  - `RegistrationPayload` untuk data gabungan siap submit.
  - Gunakan Laravel DTO sederhana (mis. class pure PHP dengan typed property) agar validasi & serialisasi jelas.
- **Actions:**
  - `SaveRegistrationStepAction` menerima Request validated + wizard + session store → tanggung jawab: merge data, simpan file sementara, trigger event bila perlu.
  - `SubmitRegistrationAction` menjalankan transaksi DB, memanggil mapper jawaban, mengirim email (lewat event/job), dan mengosongkan session.
  - `JumpToStepAction` hanya mengubah state wizard (menghindari logika di controller).
- **Validation Pipeline:**
  - Gunakan `FormRequest` per langkah (`SaveStepRequest`) + `SubmitRegistrationRequest` yang memakai `RegistrationFieldRuleFactory` untuk menyusun rule dari metadata field.
  - Validasi file dipisah menjadi Rule object custom agar reusable (`FileExistsInTemporaryStore`, `RequiredFileWhenVisible`).
  - `FormFieldValidationService` dipecah menjadi `RegistrationFieldRuleFactory` + `EmailFieldValidator` (after hook).
- **Answer Mappers:** buat `AnswerMapper` yang mengubah `RegistrationPayload` → array `SubmissionAnswer`/`SubmissionFile` sehingga controller tidak perlu switch-case panjang.
- **Event/Listener Sederhana:** emit `ApplicantRegisteredEvent` setelah commit, listener menangani pengiriman email dan proses async lain (pattern observer Laravel).

### 3.3 Alur Permintaan Baru
1. Controller hanya memanggil Action + mengirim ViewModel.
2. ViewModel (`RegistrationPageViewModel`) menyuplai data wizard ke Blade tanpa query di view.
3. Step save:
   - Request → `SaveStepRequest` → `SaveRegistrationStepAction`.
   - Action menyimpan data via `RegistrationSessionStore`, memvalidasi sesuai metadata, dan menentukan step berikut.
4. Submit:
   - Request → `SubmitRegistrationRequest` (menggabungkan data dari session + payload request).
   - `SubmitRegistrationAction` menjalankan pipeline: validasi, pengecekan kuota (service), generate nomor, simpan submission, dispatch event.
5. Setelah submit, session dibersihkan via service agar konsisten.

## 4. Roadmap Implementasi Bertahap

### Phase 0 – Persiapan
- Audit dependensi modul registrasi + dokumentasikan form field yang saat ini aktif.
- Tambahkan feature test baseline (alur happy path save-step + submit) sebagai safety net sebelum refactor.
- Tandai bagian kode yang akan dipindah (comments TODO sementara) supaya tim aware.

### Phase 1 – View & Data Loading
- Introduce `RegistrationWizardLoader` dan ganti pemanggilan form/wave di controller.
- Buat ViewModel + composer atau injeksi data di controller; hilangkan query langsung di `registration.blade.php`.
- Sesuaikan Blade agar menerima data terstruktur (`steps`, `currentStep`, `sessionData`).

### Phase 2 – Session & Validation Layer
- Tambahkan `RegistrationSessionStore` + DTO; migrasikan penyimpanan session dari array bebas ke DTO bertipe.
- Implement `SaveStepRequest` yang memanfaatkan rule factory baru; gunakan di controller/Action menggantikan validasi manual.
- Refactor `FormFieldValidationService` menjadi `RegistrationFieldRuleFactory` + `EmailFieldInspector` (dipakai oleh FormRequest `after`).
- Pastikan migrasi bertahap: sediakan adapter sementara agar data lama di sesi masih terbaca.

### Phase 3 – Persistence & Submission Pipeline
- Ekstrak proses submit ke `SubmitRegistrationAction` dan `SubmissionWriter` (service yang mencatat `Applicant`, `Submission`, `SubmissionAnswer`, `SubmissionFile`).
- Bungkus logika pengecekan kuota dan nomor registrasi dalam service khusus (`QuotaGuard`, `RegistrationNumberGenerator`).
- Implement event `ApplicantRegisteredEvent`; listener mengirim email melalui `GmailMailableSender` agar controller bebas dari try/catch email.
- Tambahkan logging terstruktur dan error context di tiap service.

### Phase 4 – Controller Simplification
- Gunakan controller tipis: setiap method cukup resolve Action, ambil response (redirect/view), dan tangani session state minimal.
- Standarisasi respon redirect + flash message di satu tempat (`RegistrationResponseFactory`) untuk konsistensi UI.
- Pastikan route names tidak berubah; jika ada perubahan parameter, update tests + dokumentasi.

### Phase 5 – Frontend Cleanup & UX Guard
- Pecah form Blade menjadi komponen kecil per tipe field (`x-registration.field-text` dll) sehingga toggling behaviour mudah diatur.
- Gunakan Alpine/Livewire (opsional) atau Turbo agar wizard lebih resposif tanpa reload penuh (jika diinginkan di fase terpisah).
- Tambahkan indikator error per step (berdasarkan DTO) dan hentikan dependensi langsung pada `session()` dari Blade.

### Phase 6 – Peningkatan Opsional
- Queue pengiriman email + file cleanup job untuk menghapus upload orphan (file yang gagal submit).
- Tambah audit trail/log event penting (registrasi sukses/gagal) via `ActivityLog` atau channel log khusus.
- Dokumentasikan kontrak API internal (payload session, struktur answer) di `docs/registration-module.md`.

## 5. Strategi Testing & QA
- **Feature tests:** Alur save-step, kembali ke step sebelumnya, dan submit sukses/gagal (quota penuh, validasi file gagal).
- **Unit tests:**
  - Rule factory menghasilkan aturan sesuai metadata field.
  - Generator nomor registrasi dan guard kuota.
  - Mapper jawaban menghasilkan entri `SubmissionAnswer` dan `SubmissionFile` sesuai tipe field.
- **Integration tests:** Pastikan event `ApplicantRegisteredEvent` memicu email sender, dan file upload tersimpan di disk yang benar.
- Siapkan dataset Form/FormStep dummy via factory/seeder khusus test untuk mempercepat penulisan test.

## 6. Tooling & Developer Experience
- Tambahkan command artisan (mis. `registration:debug-session`) untuk menginspeksi data wizard selama pengembangan.
- Gunakan Laravel Pint + PHPStan level menengah pada folder `app/Registration` untuk standarisasi style & static analysis early.
- Perbarui `DEVELOPER_GUIDE.md` dengan diagram alur baru + cara menambah field baru tanpa menyentuh controller.

## 7. Definition of Done per Phase
- Tidak ada regression di feature test yang ditambal sebelum refactor.
- Code review checklist: controller <150 baris, action/service memiliki docblock singkat, DTO memiliki test.
- Dokumentasi (docs + komentar seperlunya) diperbarui begitu fase selesai.
- Opsional: siapkan changelog internal agar tim non-teknis mengetahui perubahan perilaku (mis. email queue).

---

> Catatan Implementasi: kerjakan fase secara incremental melalui PR kecil (maks 200-300 LOC) agar mudah direview. Pastikan setiap migrasi data sesi memiliki fallback sementara dan dilepas setelah deploy stabil.
