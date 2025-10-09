# 🚀 Quick Reference - Midtrans Test Command

## Command Syntax

```bash
php artisan midtrans:test [options]
```

---

## 📋 Options

| Command | Description |
|---------|-------------|
| `php artisan midtrans:test` | Interactive - pilih dari list atau random |
| `php artisan midtrans:test --create` | Buat test applicant baru |
| `php artisan midtrans:test --all` | Test dengan semua applicant |
| `php artisan midtrans:test --registration_number=XXX` | Test dengan nomor pendaftaran spesifik |

---

## 🎯 Common Tasks

### Test Berkali-kali (Recommended)

```bash
# Setiap kali ingin test, buat applicant baru
php artisan midtrans:test --create
```

### Test dengan Data yang Ada

```bash
# Pilih dari list atau random
php artisan midtrans:test
```

### Seeding Payment Data

```bash
# Buat transaksi untuk semua applicant
php artisan midtrans:test --all
```

### Test Applicant Spesifik

```bash
# Dengan registration number
php artisan midtrans:test --registration_number=PPDB-2024-00001
```

---

## 💻 Quick Commands

### Create 5 Test Applicants

```bash
for i in {1..5}; do php artisan midtrans:test --create; done
```

### Check Latest Payments

```bash
php artisan tinker
\App\Models\Payment::latest()->take(5)->get(['id', 'merchant_order_code', 'payment_status_name', 'paid_amount_total'])
```

### Clean Up Test Data

```bash
php artisan tinker

# Hapus pending payments
\App\Models\Payment::where('payment_status_name', 'PENDING')->delete()

# Hapus test users
\App\Models\Applicant::where('applicant_full_name', 'like', 'Test User%')->delete()
```

---

## 📊 What You Get

Setiap test menghasilkan:

1. ✅ **Order ID** - Unique transaction identifier
2. ✅ **Snap Token** - Token untuk popup pembayaran
3. ✅ **Payment ID** - Database record ID
4. ✅ **Payment URL** - Link halaman pembayaran
5. ✅ **Snap URL** - Link langsung ke Midtrans Snap

---

## 🔄 Typical Workflow

```bash
# 1. Buat test applicant
php artisan midtrans:test --create

# 2. Copy Payment URL dari output

# 3. Buka di browser

# 4. Lakukan pembayaran (gunakan test credentials)

# 5. Cek status di database atau UI

# 6. Ulangi untuk test lagi
php artisan midtrans:test --create
```

---

## 💳 Test Credentials (Sandbox)

```
Credit Card: 4811 1111 1111 1114
CVV: 123
Expiry: 01/25
OTP: 112233
```

---

## ⚡ Pro Tips

- Gunakan `--create` untuk testing berulang dengan data baru
- Gunakan interactive mode untuk eksplorasi data yang ada
- Save Order ID untuk tracking pembayaran
- Gunakan Direct Snap URL untuk quick testing tanpa UI

---

**Documentation**: [MIDTRANS_TEST_COMMAND_GUIDE.md](MIDTRANS_TEST_COMMAND_GUIDE.md)
