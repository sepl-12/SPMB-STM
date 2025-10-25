# Strategi Automated Test Modul Registrasi PPDB

## 1. Tujuan
- Menjadikan modul registrasi memiliki perlindungan regresi otomatis saat refactor lanjutan.
- Menjamin alur wizard (step-by-step) dan submit final berjalan sesuai aturan bisnis utama.
- Memastikan aturan validasi baru (Rule Factory + FormRequest) bekerja konsisten pada tiap tipe field.

## 2. Lapisan Pengujian

### 2.1 Feature Tests (`tests/Feature/Registration/`)
1. `RegistrationWizardFlowTest`
   ```php
   /**
    * Cakupan: alur wizard dari index → isi step → next/previous → submit.
    * Cara kerja: mock Form/FormStep/Wave, simulate request POST tiap step
    *             sambil memeriksa session dan redirect.
    * Target: memastikan data tersimpan di session, validasi muncul bila tak sesuai,
    *         quick jump & previous tidak memicu validasi.
    */
   ```
2. `RegistrationSubmitSuccessTest`
   ```php
   /**
    * Cakupan: submit payload lengkap dan memastikan Applicant + Submission tercipta.
    * Cara kerja: seed field, unggah file dummy (storage fake), jalankan POST submit.
    * Target: entry DB sesuai, email event dipancarkan, session dibersihkan.
    */
   ```
3. `RegistrationSubmitValidationFailureTest`
   ```php
   /**
    * Cakupan: validasi submit gagal (file hilang / email disposable).
    * Cara kerja: isi session dengan data, jalankan submit dengan data invalid.
    * Target: redirect back dengan error yang tepat, data session dipertahankan.
    */
   ```

### 2.2 Unit Tests (`tests/Unit/Registration/Validators/`)
1. `RegistrationRuleFactoryTest`
   ```php
   /**
    * Cakupan: memeriksa rule per field type (text, email, select, multi_select, file, image, radio, boolean).
    * Cara kerja: instansiasi dummy FormField, bangun context, panggil factory, assert rules & messages.
    * Target: rule sesuai meta data, required logic & scenario step/submit sesuai.
    */
   ```
2. `EmailFieldInspectorTest`
   ```php
   /**
    * Cakupan: email valid & invalid (double dot, whitespace, disposable domain).
    * Cara kerja: panggil inspect, periksa error ditambahkan ke validator.
    * Target: error hanya keluar untuk pola terlarang; domain biasa lolos.
    */
   ```
3. `RegistrationValidatorTest`
   ```php
   /**
    * Cakupan: integrasi kecil rule factory + inspector.
    * Cara kerja: supply koleksi FormField campuran, context step/submit, cek data tervalidasi.
    * Target: data dikembalikan sesuai rule, error muncul bila perlu.
    */
   ```

### 2.3 Integration Tests (`tests/Feature/Registration/Integration/`)
1. `SaveStepActionIntegrationTest`
   ```php
   /**
    * Cakupan: action `SaveRegistrationStepAction` bersama session store.
    * Cara kerja: bootstrapping wizard, session fake, panggil action secara langsung.
    * Target: step_data tervalidasi, file tersimpan, session diupdate.
    */
   ```
2. `SubmitRegistrationActionIntegrationTest`
   ```php
   /**
    * Cakupan: action submit, termasuk quota guard & answer mapper.
    * Cara kerja: buat wave, form, field, applicant count; jalankan action.
    * Target: DB transaction berjalan, quota dicek, answers & files tercatat.
    */
   ```

### 2.4 Browser/HTTP Tests (opsional)
- Gunakan Laravel Dusk / Pest + Laravel HTTP assertions.
- Scenario: form real di browser (JavaScript, quick jump buttons, upload) untuk capture regresi front-end.

## 3. Infrastruktur Test
- Gunakan `RefreshDatabase` / migrasi in-memory untuk feature test.
- `Storage::fake('public')` guna isolasi file upload.
- Buat factory untuk `Form`, `FormVersion`, `FormStep`, `FormField`, `Wave`, `Applicant` agar setup test minimal.
- Siapkan helper `RegistrationTestFactory` untuk mempermudah generasi form multi-step.

## 4. Coverage Prioritas
| Area                        | Jenis Test              | Keterangan                                |
|-----------------------------|-------------------------|--------------------------------------------|
| Validasi per field type     | Unit                    | RuleFactory, EmailInspector                 |
| Step navigation             | Feature                 | Flow wizard next/previous/jump             |
| Submission pipeline         | Feature & Integration   | Quota, nominee creation, answer mapping    |
| File handling               | Feature & Integration   | File upload required & optional            |
| Session store behavior      | Integration             | Save step merge/clear                      |
| Event dispatch (email)      | Feature                 | Pastikan event terkirim setelah submit     |

## 5. Workflow & Automasi
- Tambahkan skrip `composer test` → `php artisan test --testsuite=Unit,Feature`.
- Integrasi CI (GitHub Actions / GitLab CI) untuk menjalankan test pada push/PR.
- Gunakan coverage report (Xdebug + pcov) untuk memonitor area belum terlindungi.

## 6. Langkah Implementasi Bertahap
1. Setup factory + helper test (Phase 0).
2. Tuliskan unit test RuleFactory + EmailInspector.
3. Buat feature test wizard flow (save step happy path + validasi).
4. Tambahkan integration test action submit + file handling.
5. Opsional: tambahkan Dusk/Browser test untuk UI.
6. Integrasikan ke CI + tambahkan status badge ke README.

## 7. Dokumentasi
- Tambahkan panduan run test ke `DEVELOPER_GUIDE.md` (composer test, artisan test).
- Sertakan cheat-sheet debugging (Storage fake path, database log).
- Update change log setelah test suite siap.

> Catatan: setiap file test diberi komentar docblock seperti contoh di atas agar tim baru memahami cakupan & tujuan test dengan cepat.
