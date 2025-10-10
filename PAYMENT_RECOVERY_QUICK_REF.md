# 🔄 Payment Recovery - Quick Reference

## 🚀 Access Points

### For Users:
```
🌐 Check Payment Form
http://localhost:8000/cek-pembayaran
```

### For Developers:
```bash
# Test command
php artisan midtrans:test --create

# Check routes
php artisan route:list --path=pembayaran
```

---

## 📋 User Scenarios

### Scenario 1: User Keluar dari Halaman Bayar
```
1. Buka: /cek-pembayaran
2. Input: Registration Number + Email
3. Klik: "Cek Status Pembayaran"
4. → Redirect ke halaman yang sesuai
```

### Scenario 2: Lupa Link Pembayaran
```
1. Buka: /cek-pembayaran
2. Input data
3. Klik: "Kirim Ulang Link"
4. → Link akan ditampilkan
```

### Scenario 3: Direct URL Access
```
Format: /pembayaran/{registration_number}
Contoh: /pembayaran/PPDB-2024-00001
```

---

## 🔑 API Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/cek-pembayaran` | Show form |
| POST | `/cek-pembayaran` | Find payment |
| POST | `/kirim-ulang-link` | Resend link |

---

## 🧪 Quick Tests

### Test 1: Form Access
```bash
open http://localhost:8000/cek-pembayaran
```

### Test 2: With Test Data
```bash
# Create test applicant
php artisan midtrans:test --create

# Use registration number in form
```

### Test 3: Via Tinker
```bash
php artisan tinker
```
```php
$app = App\Models\Applicant::first();
echo route('payment.check-form') . "\n";
echo "Reg: " . $app->registration_number . "\n";
echo "Email: " . $app->getLatestAnswerForField('email');
```

---

## 📊 Status Flow

```
Check Form
    ↓
Find Applicant
    ├─→ Not Found → Error
    ├─→ Email Mismatch → Error
    ├─→ No Payment → Create & Redirect to Pay
    ├─→ PAID → Redirect to Success
    └─→ PENDING → Redirect to Pay
```

---

## 🎯 Key Features

- ✅ Email validation
- ✅ Auto uppercase registration number
- ✅ Resend link functionality
- ✅ Mobile responsive
- ✅ Alert messages (success/error/info)
- ✅ Help section with contacts
- ✅ AJAX for resend link
- ✅ Activity logging

---

## 🔒 Security

- ✅ CSRF protection
- ✅ Email verification
- ✅ Input validation
- ✅ Rate limiting
- ✅ Activity logging

---

## 📱 Contact Info (Update in View)

```
📞 Phone: 0812-3456-7890
💬 WhatsApp: 0812-3456-7890
📧 Email: ppdb@stm.ac.id
```

---

## 🐛 Common Issues

### "Data tidak ditemukan"
→ Check registration number typo

### "Email tidak sesuai"
→ Use email from registration

### Form not submitting
→ Check browser console & CSRF token

### Page not loading
→ Clear cache: `php artisan optimize:clear`

---

## 📝 Files Created

```
✅ PaymentController (updated)
   - checkPaymentForm()
   - findPayment()
   - resendPaymentLink()

✅ routes/web.php (updated)
   - GET /cek-pembayaran
   - POST /cek-pembayaran
   - POST /kirim-ulang-link

✅ Views
   - payment/check-status.blade.php
```

---

## ✅ Implementation Status

| Feature | Status |
|---------|--------|
| Check Form | ✅ Done |
| Find Payment | ✅ Done |
| Resend Link | ✅ Done |
| Email Validation | ✅ Done |
| Mobile Responsive | ✅ Done |
| Error Handling | ✅ Done |
| Logging | ✅ Done |
| Documentation | ✅ Done |

---

## 🎉 Ready to Use!

**Test now:**
```bash
open http://localhost:8000/cek-pembayaran
```

**Or test with command:**
```bash
php artisan midtrans:test --create
# Then use the registration number in the form
```

---

**Version**: 1.0.0  
**Status**: ✅ Production Ready  
**Last Updated**: October 10, 2025
