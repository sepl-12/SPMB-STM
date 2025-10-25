# Implementasi Signed URLs untuk Keamanan Email Links

## 📋 Overview

Dokumen ini menjelaskan implementasi signed URLs untuk mengamankan link yang dikirim melalui email kepada applicant. Implementasi ini mengatasi masalah hardcoded URLs dan sensitive data exposure.

---

## 🎯 Masalah yang Diselesaikan

### Sebelum Implementasi
❌ **Masalah:**
- URL hardcoded dengan ID yang mudah ditebak
- Siapa saja bisa mengakses data applicant dengan menebak ID
- Tidak ada expiration pada link
- Link bisa dibagikan dan disalahgunakan
- Risiko akses tidak sah ke data sensitif

**Contoh URL Lama:**
```
https://app.com/payment/{applicant_id}
https://app.com/exam-card/{applicant_id}
https://app.com/status/{applicant_id}
```

### Setelah Implementasi
✅ **Solusi:**
- Signed URLs dengan signature verification
- Link otomatis expire sesuai kebutuhan
- Setiap link unik dan tidak bisa diprediksi
- Validasi signature oleh Laravel middleware
- Proteksi terhadap tampering dan unauthorized access

**Contoh URL Baru:**
```
https://app.com/secure/pembayaran/PMB-2025-001?expires=1698307200&signature=abc123...
https://app.com/secure/kartu-ujian/PMB-2025-001?expires=1698307200&signature=xyz789...
https://app.com/secure/status/PMB-2025-001?expires=1698307200&signature=def456...
```

---

## 🏗️ Arsitektur

### 1. Model Layer (`Applicant.php`)

**Methods untuk Generate Signed URLs:**
```php
// Generate signed URL untuk pembayaran (expire 7 hari)
$applicant->getPaymentUrl()

// Generate signed URL untuk status (expire 30 hari)
$applicant->getStatusUrl()

// Generate signed URL untuk kartu ujian (expire 60 hari)
$applicant->getExamCardUrl()
```

**Accessors untuk Email Templates:**
```php
// Bisa diakses di email template sebagai attribute
{{ $applicant->payment_url }}
{{ $applicant->status_url }}
{{ $applicant->exam_card_url }}
```

**Custom Expiration:**
```php
// Custom expiration time
$applicant->getPaymentUrl(14)  // 14 hari
$applicant->getExamCardUrl(90) // 90 hari
```

### 2. Routes Layer (`routes/web.php`)

**Secured Routes dengan `signed` Middleware:**
```php
Route::middleware('signed')->group(function () {
    Route::get('/secure/pembayaran/{registration_number}', 
        [PaymentController::class, 'showSecure'])
        ->name('payment.show-secure');
    
    Route::get('/secure/status/{registration_number}', 
        [PaymentController::class, 'statusSecure'])
        ->name('applicant.status-secure');
    
    Route::get('/secure/kartu-ujian/{registration_number}', 
        [PaymentController::class, 'examCard'])
        ->name('exam-card.show');
});
```

**Legacy Routes (Backward Compatibility):**
```php
// Masih ada untuk compatibility, tapi akan dihapus nanti
Route::get('/pembayaran/{registration_number}', 
    [PaymentController::class, 'show'])
    ->name('payment.show');
```

### 3. Controller Layer (`PaymentController.php`)

**Secure Methods:**
- `showSecure()` - Menampilkan halaman pembayaran via signed URL
- `examCard()` - Menampilkan kartu ujian (dengan validasi payment)
- `statusSecure()` - Menampilkan status pendaftaran

**Security Checks:**
```php
// Laravel automatically validates signature via middleware
// Additional checks dapat ditambahkan di controller:

// Check payment status before showing exam card
if (!$applicant->hasSuccessfulPayment()) {
    return response()->view('errors.payment-required', [...], 403);
}
```

### 4. Exception Handling (`bootstrap/app.php`)

**Handle InvalidSignatureException:**
```php
$exceptions->render(function (InvalidSignatureException $e, $request) {
    return response()->view('errors.expired-link', [], 403);
});
```

Ketika URL expired atau signature invalid, user akan diarahkan ke halaman error yang informatif.

---

## 📧 Email Templates

### Payment Confirmation Email
**File:** `resources/views/emails/payment-confirmed.blade.php`

```blade
<!-- Download Kartu Ujian -->
<a href="{{ $applicant->getExamCardUrl() }}">
    📄 Download Kartu Ujian
</a>

<!-- Cek Status -->
<a href="{{ $applicant->getStatusUrl() }}">
    🔍 Cek Status Pendaftaran
</a>

<!-- Security Notice -->
<div>
    🔒 Link ini aman dan akan kedaluarsa dalam waktu tertentu. 
    Jangan bagikan kepada orang lain.
</div>
```

### Registration Email
**File:** `resources/views/emails/applicant-registered.blade.php`

```blade
<!-- Payment Link -->
<a href="{{ $applicant->getPaymentUrl() }}">
    💳 Bayar Sekarang
</a>

<!-- Security Notice -->
<div>
    🔒 Link pembayaran ini akan kedaluarsa dalam 7 hari. 
    Jangan bagikan kepada orang lain.
</div>
```

---

## 🎨 Views

### 1. Error Views

**Expired Link (`errors/expired-link.blade.php`)**
- Tampil ketika signed URL expired atau invalid
- Memberikan instruksi untuk mendapatkan link baru
- Link ke "Cek Pembayaran" feature

**Payment Required (`errors/payment-required.blade.php`)**
- Tampil ketika user akses exam card sebelum bayar
- Informasi status pembayaran
- Link untuk melanjutkan pembayaran

### 2. Secure Pages

**Exam Card (`exam-card/show.blade.php`)**
- Kartu ujian dengan QR code
- Detail ujian (tanggal, waktu, lokasi)
- Petunjuk untuk peserta
- Print-friendly layout

**Status Page (`applicant/status-secure.blade.php`)**
- Informasi pendaftaran lengkap
- Status pembayaran real-time
- Action buttons (download kartu/bayar)
- Help section

---

## 🔒 Security Features

### 1. Signature Verification
- Laravel otomatis generate signature menggunakan APP_KEY
- Signature mencakup URL path, query parameters, dan expiration time
- Tidak bisa di-tamper tanpa APP_KEY

### 2. Expiration Time
```php
// Berbeda-beda sesuai use case:
Payment URL:    7 hari   (proses pendaftaran)
Status URL:     30 hari  (monitoring jangka menengah)
Exam Card URL:  60 hari  (persiapan ujian)
```

### 3. Rate Limiting
```php
// Bisa ditambahkan di routes:
Route::middleware(['signed', 'throttle:10,1'])->group(function () {
    // Max 10 requests per minute
});
```

### 4. HTTPS Only
```php
// Di production, pastikan APP_URL menggunakan https
APP_URL=https://yourdomain.com

// Laravel otomatis enforce HTTPS untuk signed URLs
```

---

## 📊 Expiration Strategy

| Link Type | Default Expiry | Reasoning |
|-----------|---------------|-----------|
| Payment | 7 hari | Cukup untuk proses pendaftaran, tidak terlalu lama |
| Status | 30 hari | Untuk monitoring berkala setelah pembayaran |
| Exam Card | 60 hari | Persiapan ujian membutuhkan waktu lebih lama |

**Catatan:** Expiration time bisa di-adjust per wave atau use case spesifik.

---

## 🧪 Testing

### Manual Testing

1. **Test Valid Signed URL:**
```bash
# Generate dan klik link dari email
# Harus berhasil akses halaman
```

2. **Test Expired URL:**
```bash
# Tunggu hingga URL expired atau manipulasi timestamp
# Harus redirect ke errors.expired-link
```

3. **Test Tampered URL:**
```bash
# Ubah query parameter tanpa update signature
# Harus redirect ke errors.expired-link
```

4. **Test Payment Required:**
```bash
# Akses exam card sebelum payment confirmed
# Harus tampil errors.payment-required
```

### Automated Testing

**Test Example:**
```php
test('signed payment URL grants access', function () {
    $applicant = Applicant::factory()->create();
    $signedUrl = $applicant->getPaymentUrl();
    
    $response = $this->get($signedUrl);
    
    $response->assertStatus(200);
    $response->assertViewIs('payment.show');
});

test('expired signed URL returns 403', function () {
    $applicant = Applicant::factory()->create();
    $expiredUrl = URL::temporarySignedRoute(
        'payment.show-secure',
        now()->subDay(),
        ['registration_number' => $applicant->registration_number]
    );
    
    $response = $this->get($expiredUrl);
    
    $response->assertStatus(403);
    $response->assertViewIs('errors.expired-link');
});
```

---

## 🚀 Deployment Checklist

### Pre-Deployment
- [ ] Pastikan APP_KEY sudah di-set dan tidak berubah
- [ ] Pastikan APP_URL menggunakan HTTPS di production
- [ ] Test semua signed URLs di staging environment
- [ ] Verify email templates render dengan benar
- [ ] Test error pages (expired, payment-required)

### Post-Deployment
- [ ] Monitor log untuk InvalidSignatureException
- [ ] Check email delivery dengan signed URLs
- [ ] Verify expiration times sesuai requirement
- [ ] Update documentation untuk admin/support team

### Database Migration
Tidak ada migration yang diperlukan. Signed URLs purely generated on-the-fly.

---

## 📈 Monitoring

### Log Points

**Successful Access:**
```php
Log::info('Signed URL accessed', [
    'route' => 'payment.show-secure',
    'registration_number' => $registration_number,
    'ip' => $request->ip(),
]);
```

**Failed Access (Expired/Invalid):**
```php
// Automatically logged by Laravel exception handler
// Check logs/laravel.log for InvalidSignatureException
```

### Metrics to Track
- Jumlah signed URLs di-generate per hari
- Click-through rate dari email
- Expired URL access attempts
- Time to first payment setelah registration

---

## 🔄 Migration dari Legacy URLs

### Phase 1: Dual Support (Current)
- Signed URLs untuk email baru
- Legacy URLs masih berfungsi untuk backward compatibility

### Phase 2: Deprecation Notice
- Tambahkan warning di legacy routes
- Update semua email templates ke signed URLs
- Inform applicants via announcement

### Phase 3: Full Migration
- Disable legacy routes
- Redirect legacy URLs ke expired-link page
- Monitor for issues

---

## 🆘 Troubleshooting

### Issue: "Link Kadaluarsa" padahal baru dikirim

**Possible Causes:**
1. Server time tidak sync
2. APP_KEY berubah setelah URL generated
3. Cache issue

**Solutions:**
```bash
# Sync server time
sudo ntpdate -s time.nist.gov

# Clear cache
php artisan config:clear
php artisan cache:clear

# Verify APP_KEY tidak berubah
php artisan key:generate --show
```

### Issue: Signature Invalid

**Possible Causes:**
1. URL di-modify manually
2. APP_URL tidak match dengan actual domain
3. Proxy/CDN mengubah URL parameters

**Solutions:**
```bash
# Check APP_URL di .env
APP_URL=https://yourdomain.com

# Jangan gunakan trailing slash di APP_URL
# ❌ APP_URL=https://yourdomain.com/
# ✅ APP_URL=https://yourdomain.com
```

### Issue: Email tidak terkirim dengan signed URLs

**Check:**
1. Queue worker berjalan: `php artisan queue:work`
2. Email service configured dengan benar
3. Check logs: `tail -f storage/logs/laravel.log`

---

## 📚 References

- [Laravel Signed URLs Documentation](https://laravel.com/docs/urls#signed-urls)
- [Laravel Middleware Documentation](https://laravel.com/docs/middleware)
- [Email Security Best Practices](https://owasp.org/www-community/vulnerabilities/Insecure_URL)

---

## 🎓 Best Practices

1. **Always use HTTPS** untuk production
2. **Don't share APP_KEY** - jaga kerahasiaannya
3. **Use appropriate expiration** sesuai use case
4. **Monitor logs** untuk suspicious activity
5. **Test thoroughly** sebelum deployment
6. **Educate users** tentang keamanan link
7. **Regular security audits** untuk signed URL implementation

---

## ✅ Checklist Implementasi

### Backend
- [x] Add signed URL methods to Applicant model
- [x] Create secure routes with `signed` middleware
- [x] Implement secure controller methods
- [x] Add exception handling for InvalidSignatureException
- [x] Update email templates to use signed URLs

### Frontend/Views
- [x] Create error views (expired-link, payment-required)
- [x] Create exam card view with print layout
- [x] Create status secure view
- [x] Add security notices in email templates

### Testing
- [ ] Write unit tests for signed URL generation
- [ ] Write feature tests for secure routes
- [ ] Test expiration behavior
- [ ] Test invalid signature handling
- [ ] Test payment-required logic

### Documentation
- [x] Document signed URL implementation
- [x] Create troubleshooting guide
- [x] Document expiration strategy
- [x] Create deployment checklist

---

**Last Updated:** October 26, 2025  
**Version:** 1.0.0  
**Author:** Development Team
