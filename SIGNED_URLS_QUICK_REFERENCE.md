# 🔒 Signed URLs - Quick Reference

## 🚀 Quick Start

### Generate Signed URLs di Code
```php
use App\Models\Applicant;

$applicant = Applicant::find($id);

// Generate signed URLs (default expiration)
$paymentUrl = $applicant->getPaymentUrl();      // 7 days
$statusUrl = $applicant->getStatusUrl();        // 30 days
$examCardUrl = $applicant->getExamCardUrl();    // 60 days

// Custom expiration (in days)
$paymentUrl = $applicant->getPaymentUrl(14);    // 14 days
```

### Use in Email Templates
```blade
{{-- Payment Confirmation Email --}}
<a href="{{ $applicant->exam_card_url }}">Download Kartu Ujian</a>
<a href="{{ $applicant->status_url }}">Cek Status</a>

{{-- Registration Email --}}
<a href="{{ $applicant->payment_url }}">Bayar Sekarang</a>
```

### Test Signed URLs
```bash
# Generate URLs untuk testing
php artisan test:signed-urls

# With specific applicant
php artisan test:signed-urls G1-2025-0001
```

---

## 📋 Available Routes

### Secured Routes (require valid signature)
```
GET /secure/pembayaran/{registration_number}  → payment.show-secure
GET /secure/status/{registration_number}      → applicant.status-secure
GET /secure/kartu-ujian/{registration_number} → exam-card.show
```

### Legacy Routes (backward compatibility)
```
GET /pembayaran/{registration_number}         → payment.show
GET /pembayaran/status/{registration_number}  → payment.status
```

---

## 🔑 URL Examples

### Valid Signed URL
```
http://app.com/secure/pembayaran/G1-2025-0001?expires=1762014535&signature=165d9d99...
```

### Expired/Invalid URL → Shows Error Page
```
http://app.com/secure/pembayaran/G1-2025-0001?expires=1698307200&signature=invalid
```

---

## 🛠️ Common Tasks

### Generate Link in Tinker
```bash
php artisan tinker
>>> $a = App\Models\Applicant::first()
>>> echo $a->getPaymentUrl()
>>> echo $a->getExamCardUrl()
```

### Test in Browser
1. Copy URL from command output
2. Open in browser → Should load page
3. Modify signature → Should show error page

### Debug Signature Issues
```bash
# Clear config cache
php artisan config:clear

# Check APP_KEY
php artisan key:generate --show

# Check APP_URL
grep APP_URL .env
```

---

## ⚙️ Configuration

### Expiration Times
```php
// In Applicant model
getPaymentUrl(7)     // 7 days - pendaftaran
getStatusUrl(30)     // 30 days - monitoring
getExamCardUrl(60)   // 60 days - persiapan ujian
```

### Environment Variables
```bash
APP_URL=https://yourdomain.com  # Must be HTTPS in production
APP_KEY=base64:...              # Don't change after deployment
```

---

## 🎨 Views

### Error Views
- `errors/expired-link.blade.php` - Expired/invalid signature
- `errors/payment-required.blade.php` - Unpaid access to exam card

### Secure Views
- `exam-card/show.blade.php` - Kartu ujian dengan QR code
- `applicant/status-secure.blade.php` - Status pendaftaran

---

## 🐛 Troubleshooting

### Link shows as expired immediately
```bash
# Check & sync server time
date
sudo ntpdate -s time.nist.gov
```

### Signature validation fails
```bash
# Verify APP_KEY hasn't changed
grep APP_KEY .env

# Clear cache
php artisan config:clear
```

### Views not found
```bash
php artisan view:clear
ls -la resources/views/errors/
ls -la resources/views/exam-card/
```

---

## 📚 Documentation

- **Full Implementation Guide:** `docs/security/SIGNED_URLS_IMPLEMENTATION.md`
- **Summary:** `docs/security/SIGNED_URLS_SUMMARY.md`
- **Checklist:** `SIGNED_URLS_CHECKLIST.md`
- **Laravel Docs:** https://laravel.com/docs/urls#signed-urls

---

## ⚡ Quick Commands

```bash
# Test signed URLs
php artisan test:signed-urls

# List secure routes
php artisan route:list --name=secure

# Clear all caches
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear

# Monitor logs
tail -f storage/logs/laravel.log | grep InvalidSignature
```

---

**Last Updated:** October 26, 2025
