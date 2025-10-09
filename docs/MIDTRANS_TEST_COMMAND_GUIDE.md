# Midtrans Test Command - User Guide

## 📋 Overview

Command `php artisan midtrans:test` telah diupgrade untuk mendukung testing berkali-kali dengan berbagai opsi.

---

## 🚀 Cara Penggunaan

### **1. Test dengan Pilihan Interaktif (Default)**

```bash
php artisan midtrans:test
```

**Fitur:**
- Menampilkan daftar semua applicant yang tersedia
- Memilih applicant dari list atau random
- Membuat transaksi baru untuk testing

**Output:**
```
🔧 Testing Midtrans Configuration...

📋 Configuration:
Server Key: Mid-server-vqU...
Client Key: Mid-client-1tD...
Environment: 🟢 Sandbox

📋 Available Applicants:
 [1] PPDB-2024-00001 - John Doe
 [2] PPDB-2024-00002 - Jane Smith
 [3] PPDB-2024-00003 - Bob Wilson
 [Random]
 [Cancel]

Select applicant to test (or choose Random): Random

🎲 Selected random applicant
👤 Testing with applicant:
Registration Number: PPDB-2024-00002
Name: Jane Smith
Wave: Gelombang 1
Amount: Rp 500,000

🔄 Creating test transaction...
✅ Transaction created successfully!

📝 Transaction Details:
Order ID: ORD-PPDB-2024-00002-1728567890
Snap Token: abc123def456...
Payment ID: 42

✅ Midtrans integration is working correctly!

🌐 Payment URL:
http://localhost:8000/pembayaran/PPDB-2024-00002

🔗 Direct Snap URL (for testing):
https://app.sandbox.midtrans.com/snap/v3/redirection/abc123def456...
```

---

### **2. Test dengan Applicant Tertentu**

```bash
php artisan midtrans:test --registration_number=PPDB-2024-00001
```

**Kegunaan:**
- Test transaksi untuk applicant dengan nomor pendaftaran spesifik
- Cocok untuk test ulang dengan data yang sama

---

### **3. Test dengan Semua Applicant**

```bash
php artisan midtrans:test --all
```

**Kegunaan:**
- Membuat transaksi untuk semua applicant sekaligus
- Cocok untuk testing massal atau seeding payment data

**Output:**
```
🔄 Testing with 5 applicants...

Testing: PPDB-2024-00001 - John Doe
✅ Transaction created successfully!

Testing: PPDB-2024-00002 - Jane Smith
✅ Transaction created successfully!

Testing: PPDB-2024-00003 - Bob Wilson
✅ Transaction created successfully!

✅ Success: 3
❌ Failed: 0
```

---

### **4. Buat Test Applicant Baru**

```bash
php artisan midtrans:test --create
```

**Fitur:**
- Membuat applicant baru secara interaktif
- Otomatis generate registration number
- Langsung bisa test transaksi

**Interaksi:**
```
🔨 Creating test applicant...

 Applicant name [Test User 20241009183045]:
 > John Testing

 Email [test1728567045@test.com]:
 > john@test.com

 Phone [081234567890]:
 > 081298765432

✅ Test applicant created successfully!
Registration Number: PPDB-2024-00123
Name: John Testing

 Create test transaction for this applicant? (yes/no) [yes]:
 > yes

👤 Testing with applicant:
...
```

---

## 💡 Use Cases

### **Scenario 1: Testing Berkali-kali**

```bash
# Buat applicant baru
php artisan midtrans:test --create

# Test lagi dengan applicant berbeda
php artisan midtrans:test --create

# Test dengan random applicant yang ada
php artisan midtrans:test
```

### **Scenario 2: Test Payment Flow Lengkap**

```bash
# 1. Buat test applicant
php artisan midtrans:test --create

# 2. Copy payment URL dari output
# 3. Buka di browser
# 4. Lakukan pembayaran
# 5. Cek status di database

# 6. Test lagi dengan applicant lain
php artisan midtrans:test
```

### **Scenario 3: Seeding Payment Data**

```bash
# Buat transaksi untuk semua applicant
php artisan midtrans:test --all
```

### **Scenario 4: Test dengan Applicant Spesifik**

```bash
# Test dengan nomor pendaftaran tertentu
php artisan midtrans:test --registration_number=PPDB-2024-00001

# Test lagi dengan nomor yang sama (akan buat transaksi baru)
php artisan midtrans:test --registration_number=PPDB-2024-00001
```

---

## 🔍 Detail Output

### **Transaction Details yang Ditampilkan:**

1. **Order ID** - Unique identifier untuk transaksi
2. **Snap Token** - Token untuk Snap popup
3. **Payment ID** - ID record di database
4. **Payment URL** - URL halaman pembayaran
5. **Direct Snap URL** - URL langsung ke Snap popup

### **Contoh:**

```
📝 Transaction Details:
Order ID: ORD-PPDB-2024-00001-1728567890
Snap Token: abc123def456ghi789jkl012mno345...
Payment ID: 42

🌐 Payment URL:
http://localhost:8000/pembayaran/PPDB-2024-00001

🔗 Direct Snap URL (for testing):
https://app.sandbox.midtrans.com/snap/v3/redirection/abc123def456ghi789jkl012mno345
```

---

## 🎯 Tips & Tricks

### **1. Test dengan Multiple Users**

```bash
# Buat 5 test users
for i in {1..5}; do
  php artisan midtrans:test --create
done
```

### **2. Quick Test Loop**

```bash
# Test 3 kali berturut-turut dengan random applicant
for i in {1..3}; do
  php artisan midtrans:test
done
```

### **3. Check Results**

```bash
# Setelah test, cek di database
php artisan tinker

# Lihat transaksi terakhir
\App\Models\Payment::latest()->take(5)->get(['merchant_order_code', 'payment_status_name', 'paid_amount_total'])
```

### **4. Clean Up Test Data**

```bash
# Hapus test payments
php artisan tinker

# Hapus payment dengan status PENDING
\App\Models\Payment::where('payment_status_name', 'PENDING')->delete()

# Atau hapus test applicants
\App\Models\Applicant::where('applicant_full_name', 'like', 'Test User%')->delete()
```

---

## 🐛 Troubleshooting

### **Error: No active wave found**

```bash
# Buat wave aktif dulu
php artisan tinker

\App\Models\Wave::create([
    'wave_name' => 'Test Wave',
    'start_date' => now(),
    'end_date' => now()->addDays(30),
    'status' => 'active',
    'registration_fee_amount' => 500000,
])
```

### **Error: Transaction creation failed**

1. Cek config Midtrans di `.env`
2. Cek koneksi internet
3. Clear cache: `php artisan config:clear`
4. Cek logs: `tail -f storage/logs/laravel.log`

### **Error: No applicants found**

```bash
# Buat test applicant
php artisan midtrans:test --create

# Atau jalankan seeder
php artisan db:seed --class=ApplicantSeeder
```

---

## 📊 Command Options Summary

| Option | Description | Example |
|--------|-------------|---------|
| (none) | Interactive selection | `php artisan midtrans:test` |
| `--create` | Create new test applicant | `php artisan midtrans:test --create` |
| `--all` | Test with all applicants | `php artisan midtrans:test --all` |
| `--registration_number=XXX` | Test specific applicant | `php artisan midtrans:test --registration_number=PPDB-2024-00001` |

---

## ✅ Best Practices

1. **Gunakan `--create`** untuk test berkali-kali dengan data berbeda
2. **Gunakan interactive mode** untuk pilih applicant yang ada
3. **Gunakan `--all`** untuk seeding payment data
4. **Save output** untuk reference (copy Order ID, Payment URL, dll)
5. **Clean up** test data setelah selesai testing

---

## 🎉 Examples

### **Test Flow 1: Complete Testing**

```bash
# 1. Create test user
php artisan midtrans:test --create
# Name: Test Payment 1
# Email: payment1@test.com
# Phone: 081212341234

# 2. Open payment URL in browser
# (dari output command)

# 3. Complete payment di Snap

# 4. Check status
php artisan tinker
$payment = \App\Models\Payment::latest()->first();
echo "Status: " . $payment->payment_status_name;
```

### **Test Flow 2: Multiple Transactions**

```bash
# Test dengan 3 applicant berbeda
php artisan midtrans:test --create
php artisan midtrans:test --create
php artisan midtrans:test --create

# Atau gunakan loop
for i in {1..3}; do php artisan midtrans:test --create; done
```

### **Test Flow 3: Regression Testing**

```bash
# Test dengan semua applicant yang ada
php artisan midtrans:test --all

# Check results
php artisan tinker
echo "Total Payments: " . \App\Models\Payment::count();
echo "Pending: " . \App\Models\Payment::where('payment_status_name', 'PENDING')->count();
```

---

**Sekarang Anda bisa test payment gateway berkali-kali dengan mudah!** 🎉
