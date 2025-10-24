# Rencana Migrasi Validasi Registrasi ke Rule Factory

## 1. Sasaran
- Mengganti `FormFieldValidationService` lama dengan lapisan rule factory + FormRequest yang modular.
- Menjamin setiap tipe field menghasilkan rule & pesan validasi konsisten.
- Memungkinkan validasi per-step dan full-submit memakai pipeline yang sama.
- Memudahkan penambahan field baru tanpa mengedit banyak tempat.

## 2. Kondisi Saat Ini (Ringkas)
- Controller dan action masih memanggil `FormFieldValidationService::validateFormData` dengan koleksi field.
- Service lama bertanggung jawab untuk rules, pesan, attribut, bahkan post-validation email check → tanggung jawab terlalu lebar.
- Tidak ada pemisahan antara rule per tipe field dan rule tambahan per step (mis. file optional jika sudah ada di session).
- Penanganan custom (email disposable, rule file) tertanam di service tanpa test granular.

## 3. Target Arsitektur Validasi
```
app/Registration/Validators/
    RegistrationRuleFactory.php      # Bangun array rule & message per field meta
    RegistrationAttributeFactory.php # (opsional) label mapping
    EmailFieldInspector.php          # After validation hook khusus email
    StepValidationScenario.php       # Konteks rule per step (mis. file lama)
app/Registration/Requests/
    SaveStepRequest.php
    SubmitRegistrationRequest.php
```
- **Rule Factory** hanya mengubah metadata field → array rule Laravel.
- **Scenario/Context** menambahkan rule dinamis (mis. file required bila belum pernah upload).
- **FormRequest** (save & submit) injeksi wizard + session store untuk menyusun data, memanggil factory, dan men-trigger after hooks.
- Action cukup menerima data tervalidasi dari FormRequest → tidak lagi panggil service lama.

## 4. Roadmap Implementasi

### Tahap 1 – Persiapan & Abstraksi Dasar
1. Buat folder `app/Registration/Validators` dan `app/Registration/Requests`.
2. Pindahkan struktur enum/helper yang dibutuhkan (mis. email pattern, allowed mime) ke class kecil (`EmailFieldRules`, `FileRuleHelper`).
3. Tuliskan unit test awal untuk field rule factory minimal 3 tipe field (text, select, file) untuk memastikan skeleton jalan.

### Tahap 2 – Implementasi Rule Factory Modular
1. Implement `RegistrationRuleFactory` dengan metode `buildRules(FormField $field, RegistrationValidationContext $context)`.
   - Context memuat info step index, data session, data request.
2. Kembalikan struktur: `['rules' => [...], 'messages' => [...], 'attributes' => ...]` agar FormRequest mudah consume.
3. Pindahkan logic pesan custom dari service lama ke factory (atau helper message builder).
4. Buat `EmailFieldInspector` sebagai kelas after validation dengan method `inspect(array $validatedData, Collection $fields, Validator $validator)`.

### Tahap 3 – FormRequest Save Step
1. Buat `SaveStepRequest` yang:
   - Resolve wizard + session store via container.
   - Menentukan step aktif + field yang perlu validasi (mirip logika lama).
   - Memanggil RuleFactory untuk tiap field → merged ke validator Laravel.
   - Memanggil `EmailFieldInspector` pada `withValidator`.
   - Menghasilkan data tervalidasi (harus sudah merge file path existing bila diperlukan).
2. Update `SaveRegistrationStepAction` agar menerima `SaveStepRequest` (atau data tervalidasi + metadata) sehingga tanggung jawab validasi keluar dari action.

### Tahap 4 – FormRequest Submit Registration
1. Implement `SubmitRegistrationRequest` untuk full payload:
   - Ambil semua field non-arsip → rule factory.
   - Tambahkan scenario khusus file (pastikan file path sudah tersimpan di session/disk).
   - Gunakan `EmailFieldInspector` & validator custom lain (mis. domain disposable).
2. Refactor `SubmitRegistrationAction` supaya menerima data tervalidasi langsung dari request.
3. Hapus logic validasi manual file di action; ganti dengan pengecekan lewat rule/inspector.

### Tahap 5 – Dekontaminasi Service lama
1. Setelah kedua request berjalan stabil, refactor `FormFieldValidationService` ke adaptor tipis atau tandai deprecated.
2. Cari pemakaian lain di codebase; migrasikan ke rule factory baru.
3. Jika sudah tidak terpakai, hapus file service dan update dokumentasi.

## 5. Testing Strategy
- **Unit Test:**
  - RuleFactory untuk setiap tipe field (string, select, multi-select, file, image, email, number).
  - Email inspector (kasus valid/invalid, domain disposable).
- **Feature Test:**
  - Save step happy path, required fail, file upload scenario.
  - Submit success + error (quota dibiarkan di action untuk diuji terpisah).
- **Integration Test (opsional):**
  - Kombinasi FormRequest + action untuk memastikan data tervalidasi masuk ke session/DB sesuai harapan.

## 6. Dokumentasi & Communication
- Perbarui `docs/registration-module.md` (atau buat baru) dengan diagram validasi baru, cara menambah rule tipe field.
- Tambahkan catatan ke changelog internal: “Validasi registrasi sekarang memakai RuleFactory + FormRequest. `FormFieldValidationService` deprecated.”

## 7. Resiko & Mitigasi
- **Regresi multi-step:** tangani dengan feature test + QA manual.
- **Rule tak lengkap:** gunakan feature flag/rollback plan dengan menahan service lama selama fase awal (mis. config toggle) bila perlu.
- **Email check berubah perilaku:** pastikan inspector baru meng-cover semua custom message dari implementasi lama.

## 8. Target Timeline (Estimasi)
- Tahap 1–2: 2 hari kerja (termasuk unit test).
- Tahap 3–4: 3 hari kerja (refactor action + feature test).
- Tahap 5: 1 hari kerja.
- QA + dokumentasi: 1 hari kerja.

Total ±7 hari kerja dengan asumsi 1 developer fokus.
