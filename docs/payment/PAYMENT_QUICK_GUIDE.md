# Quick Guide - Payment Gateway Midtrans

## 🚀 Quick Start

### 1. Setup (Already Done)
✅ Midtrans PHP SDK sudah terinstall
✅ Config sudah tersedia di `config/payment.php` (section midtrans)
✅ ENV variables sudah dikonfigurasi

### 2. Test Payment Flow

```bash
# 1. Jalankan aplikasi
php artisan serve

# 2. Akses pendaftaran
http://localhost:8000/daftar

# 3. Selesaikan form pendaftaran

# 4. Klik "Lanjutkan Pembayaran"

# 5. Klik "Bayar Sekarang" → Snap popup akan muncul
```

## 💳 Test Credentials (Sandbox)

### Credit Card
```
Card Number: 4811 1111 1111 1114
CVV: 123
Exp Date: 01/25
OTP: 112233
```

### BCA Virtual Account
VA Number akan digenerate otomatis, transaksi akan langsung success di sandbox.

### GoPay
QR Code akan muncul, scan atau klik untuk simulate payment.

## 📱 User Flow

```
1. Pendaftaran Selesai
   ↓
2. Klik "Lanjutkan Pembayaran"
   ↓
3. Halaman Payment (route: payment.show)
   ↓
4. Klik "Bayar Sekarang" → Snap Popup
   ↓
5. Pilih Metode Pembayaran
   ↓
6. Selesaikan Pembayaran
   ↓
7. Redirect ke Status Page (route: payment.status)
   ↓
8. Status Pembayaran Otomatis Terupdate
```

## 🔧 API Endpoints

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/pembayaran/{registration_number}` | Halaman pembayaran |
| POST | `/pembayaran/notification` | Webhook dari Midtrans |
| GET | `/pembayaran/finish` | Redirect setelah pembayaran |
| GET | `/pembayaran/status/{registration_number}` | Status pembayaran |
| POST | `/pembayaran/check-status` | Check status via AJAX |

## 🗄️ Database Changes

Payment record otomatis dibuat dengan status:
- `PENDING` - Menunggu pembayaran
- `PAID` / `settlement` - Pembayaran berhasil
- `FAILED` / `cancel` / `deny` - Pembayaran gagal

## 🧪 Testing Webhook Locally

```bash
# 1. Install ngrok
brew install ngrok

# 2. Run ngrok
ngrok http 8000

# 3. Copy URL (contoh: https://abc123.ngrok.io)

# 4. Set di Midtrans Dashboard
Settings → Configuration → Notification URL:
https://abc123.ngrok.io/pembayaran/notification
```

## 📊 Check Payment Status

### Via Database
```sql
SELECT * FROM payments 
WHERE applicant_id = 1 
ORDER BY created_at DESC;
```

### Via Logs
```bash
tail -f storage/logs/laravel.log | grep -i midtrans
```

### Via Midtrans Dashboard
Login ke: https://dashboard.sandbox.midtrans.com

## 🎨 Customize Views

File views ada di:
```
resources/views/payment/
├── show.blade.php      # Halaman pembayaran
├── status.blade.php    # Status pembayaran
└── success.blade.php   # Pembayaran berhasil
```

## 🔒 Security Notes

1. ✅ CSRF exemption sudah diset untuk webhook endpoint
2. ✅ Signature verification otomatis via Midtrans SDK
3. ✅ HTTPS required untuk production
4. ⚠️ Jangan commit `.env` dengan real credentials

## 🚨 Common Issues

### Snap tidak muncul
```javascript
// Check di browser console
// Pastikan script Snap.js terload
```

### Webhook tidak diterima
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Test webhook manually
curl -X POST http://localhost:8000/pembayaran/notification \
  -H "Content-Type: application/json" \
  -d '{"order_id": "test", "transaction_status": "settlement"}'
```

## 🎯 Production Checklist

- [ ] Ganti Midtrans credentials dengan production
- [ ] Set `IS_PRODUCTION=true` di .env
- [ ] Update Snap.js URL ke production
- [ ] Configure webhook URL di Midtrans Dashboard
- [ ] Test dengan transaksi nominal kecil
- [ ] Monitor logs untuk errors
- [ ] Setup email notification (optional)

## 📞 Support

**Midtrans Docs**: https://docs.midtrans.com
**Technical Support**: support@midtrans.com

## ✅ Features Implemented

- ✅ Midtrans Snap integration
- ✅ Multiple payment methods
- ✅ Auto status update via webhook
- ✅ Payment status checking
- ✅ Responsive UI
- ✅ Print receipt
- ✅ Error handling
- ✅ Logging
- ✅ Security measures

---

**Ready to use!** 🎉
