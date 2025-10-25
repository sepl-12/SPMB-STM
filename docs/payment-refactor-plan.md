# Rencana Refactor Modul Pembayaran & Infrastruktur Terkait

## 1. Target & Prinsip
- Pisahkan logika bisnis pembayaran dari controller agar mudah diuji dan dikembangkan.
- Sediakan abstraksi jelas untuk interaksi Midtrans dan Gmail API sehingga dependensi eksternal tidak bocor ke layer presentasi.
- Jadikan rule/instruksi pembayaran mudah diubah via konfigurasi (bukan hard-code), dan siapkan ruang untuk multi-channel ke depannya.
- Pastikan proses pengiriman email berjalan asinkron serta aman (tidak ada raw token/test info di response).

## 2. Diagnosa Singkat
1. **PaymentController** (`app/Http/Controllers/PaymentController.php`)
   - Menangani UI, validasi biz logic, Midtrans call, governor quota, dan email link sekaligus.
   - Kode duplikat untuk verifikasi email & fetch applicant di `findPayment` / `resendPaymentLink`.
   - Sulit dites karena bergantung langsung pada Eloquent & MidtransService.
2. **MidtransService** (`app/Services/MidtransService.php`)
   - Konstruktor mengubah config global; menyulitkan testing paralel.
   - `handleNotification` mengirim email langsung, tidak ada queue job / event.
   - Mengembalikan array generik, tidak jelas skemanya.
3. **PaymentHelper** (`app/Helpers/PaymentHelper.php`)
   - Menampung mapping status, biaya, instruksi sebagai kode statis → perubahan butuh deploy.
   - Terdapat logika bisnis (perhitungan fee, expiry) yang seharusnya bisa dikonfigurasi.
4. **GmailMailableSender & GoogleOauthController**
   - Builder MIME manual raw; sulit dirawat.
   - OAuth callback menampilkan refresh token di browser tanpa proteksi.
5. **Filament FormFieldsRelationManager**
   - Schema 200+ baris; banyak logika UI repetitif; raw closure mengakses `$this`.
6. **AppSetting**
   - Pengelolaan cahce per-key; tidak ada typed access; logika setting menumpuk di static model.

## 3. Arsitektur Target

### Layer Service & Facade
```
app/
  Payment/
    DTO/
      SnapTransaction.php
      PaymentStatusResult.php
    Services/
      PaymentLinkService.php        # handle show/find/resend link
      PaymentStatusService.php      # status, success page, ajax check
      PaymentNotificationService.php# webhook handling, event dispatch
      MidtransTransactionFactory.php# create payload + transaction
    Actions/
      CreatePaymentLinkAction.php
      CheckPaymentQuotaAction.php
    Events/
      PaymentLinkRequested.php
      PaymentSettled.php
    Listeners/
      QueuePaymentConfirmationEmail.php
  Mail/
    PaymentConfirmed.php            # tetap, tapi trigger via listener/job
```
- **PaymentController** injeksi service/action; controller hanya mapping Request → DTO → Response (view/JSON).
- **MidtransService** dipecah: `MidtransTransactionFactory` (bangun params), `MidtransApi` (interface wrapper). Konfigurasi dilakukan di Service Provider (`app/Providers/MidtransServiceProvider`).
- Data dari service dikirim sebagai DTO typed (pakai PHP readonly class) supaya callsite jelas.

### Konfigurasi & Helper
- Tambah `config/payment.php` berisi:
  - daftar status mapping (Midtrans → internasional kita)
  - biaya per metode
  - instruksi/per category
  - expiry time per category
- `PaymentHelper` ubah jadi wrapper ke config (atau injeksi repository). Simpel: `PaymentConfig::instruction($method)`.

### Infrastruktur Email & OAuth
- `GmailMailableSender` diganti dengan class tipis yang menerima `MimeMessage` (dibuat oleh builder khusus). Pertimbangkan gunakan library Gmail resmi atau SwiftMailer.
- Tambah `app/Mail/Transport/GmailTransport.php` (implements `Symfony\Component\Mailer\Transport\TransportInterface`) agar email standar Laravel otomatis lewat Gmail API.
- `GoogleOauthController` → command artisan + route minimal: simpan refresh token ke `app_settings` (terenkripsi) & gunakan state untuk anti CSRF.

### Filament Form Fields Manager
- Pindahkan schema ke class kecil:
  - `FieldBasicsSection`, `FieldPlacementSection`, `FieldOptionsRepeater`, `FieldValidationSection`.
  - Setiap class return `Section::make()` yang dapat digunakan ulang.
- Taruh di `app/Filament/Forms/FieldSchemas/`. Relation manager tinggal memanggil beberapa builder sehingga file < 100 baris.

### App Settings
- Buat `SettingsRepository` + interface; gunakan cache tags untuk invalidasi massal.
- Tambah typed accessor (mis. `GeneralSettings::heroTitle()`, `PaymentSettings::senderEmail()`), memanfaatkan config fallback.

## 4. Roadmap Implementasi

### Phase 1 – Foundation
1. Tambah `MidtransServiceProvider` untuk set config Midtrans sekali di boot.
2. Buat config baru `config/payment.php` + publish default.
3. Implement DTO: `SnapTransaction`, `PaymentStatusResult`.
4. Tambah `PaymentLinkService` (show/find/resend) dengan method stub + unit test skeleton.

### Phase 2 – Controller & Service Refactor
1. Refactor `PaymentController@show/status/success/checkStatus` ke `PaymentStatusService`.
2. `findPayment` & `resendPaymentLink` pindah ke `PaymentLinkService`, controller tinggal call service dan return view/json.
3. Tambah `CreatePaymentLinkAction` untuk menghindari duplikasi create transaction.
4. Update routes jika perlu, jaga kompatibilitas response.

### Phase 3 – Notification & Email Pipeline
1. `PaymentNotificationService` memvalidasi signature + update DB, lalu dispatch event `PaymentSettled`.
2. Listener `QueuePaymentConfirmationEmail` dispatch job `SendPaymentConfirmationJob` yang memanfaatkan `GmailTransport` (atau queue mailable default).
3. Pastikan `MidtransService` lama dihapus/dikosongkan bertahap; tanggung jawab dilimpahkan ke service baru.

### Phase 4 – Payment Helper & Config Driven Data
1. Migrasikan mapping (status, metode, instruksi, fee) ke config.
2. `PaymentHelper` ubah jadi wrapper ke config, tambahkan unit test.
3. Sediakan command untuk regenerasi data bila config berubah (opsional).

### Phase 5 – Gmail Transport & OAuth Hardening
1. Bangun `GmailTransport` + register via service provider.
2. `GmailMailableSender` dapat didepresiasi/diadaptasi jadi facade ke transport.
3. Ubah `GoogleOauthController` → command + secure storage refresh token (crypt + stored di `app_settings` / secrets).
4. Tambah dokumentasi cara rotate token di `docs/email/gmail-oauth.md`.

### Phase 6 – Filament & Settings
1. Refactor FormFieldsRelationManager untuk menggunakan builder components.
2. Jajaki `SettingsRepository` + typed settings class; ubah callsite `AppSetting::get()` → `Settings::get()`.
3. Tambahkan test ringkas untuk repository caching.

### Phase 7 – Clean-Up & Dokumentasi
1. Hapus service lama (MidtransService), update dependency injection.
2. Jalankan `phpstan` / `laravel pint` untuk memastikan style.
3. Update guide: `docs/payment`, `DEVELOPER_GUIDE.md`, `docs/email` (transport & oauth).

## 5. Pengujian
- **Unit:** Mapping config → payment helper, DTO builder, repository settings, payment link validation.
- **Feature:** Payment link flow (show → snap token reuse), resend link JSON response, payment success page.
- **Integration:** Notification handling (signature valid/invalid), event dispatch, queue job email.
- **End-to-end manual:** Test Midtrans sandbox, email confirm via Gmail API, Filament form manager create/edit field.

## 6. Risiko & Mitigasi
- **Perubahan API eksternal:** pastikan fallback error message jelas saat Midtrans/Gmail unavailable.
- **Migrasi config:** jalankan script untuk generate default `config/payment.php`, dokumentasikan di release note.
- **Queue dependency:** wajib pastikan queue worker aktif; tambahkan guard (fallback sync) di environment dev.

## 7. Timeline (Estimasi)
- Phase 1–2: 3 hari (refactor controller + service dasar)
- Phase 3–4: 3 hari (notification pipeline + config driven helper)
- Phase 5: 2 hari (gmail transport + oauth hardening)
- Phase 6: 2 hari (filament & settings)
- Phase 7 + testing/dokumentasi: 2 hari

Total estimasi: ±12 hari kerja satu developer (dengan review paralel).

---

> Catatan: Lakukan deployment incremental (feature flag jika diperlukan) agar perubahan payment gateway bisa diuji di sandbox sebelum di produksi.
