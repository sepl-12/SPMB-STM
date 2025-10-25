# 🚨 Email Troubleshooting Guide

## ❓ **Problem: Email Tidak Terkirim Meski Queue Worker "Done"**

### 🔍 **Symptoms:**
- Registrasi berhasil
- Queue worker menampilkan "Done"
- Log menunjukkan "Registration email queued successfully"
- ❌ Email tidak sampai di inbox

### ✅ **Root Cause:**
**Queue Worker tidak berjalan secara otomatis**. Email berhasil di-queue tetapi tidak ada worker yang memproses jobs secara real-time.

---

## 🔧 **Solutions**

### **Solution 1: Manual Queue Processing (Development)**

```bash
# Process satu job
php artisan queue:work --queue=emails --once

# Start queue worker daemon (recommended for development)
php artisan queue:work --queue=emails --daemon

# Process semua pending jobs
php artisan queue:work --queue=emails --stop-when-empty
```

### **Solution 2: Auto-Start Queue Worker (Development)**

Tambahkan ke startup script atau run di background:

```bash
# Background process (macOS/Linux)
nohup php artisan queue:work --queue=emails --daemon > storage/logs/queue.log 2>&1 &

# Monitoring queue worker
php artisan queue:monitor --queue=emails
```

### **Solution 3: Sync Email Driver (Development Only)**

Jika ingin email langsung dikirim tanpa queue untuk development:

```bash
# .env
QUEUE_CONNECTION=sync
```

**⚠️ Warning:** Hanya untuk development. Production sebaiknya tetap menggunakan queue.

### **Solution 4: Production Setup (Supervisor)**

Untuk production, gunakan Supervisor untuk manage queue worker:

```ini
# /etc/supervisor/conf.d/laravel-worker.conf
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=emails --sleep=3 --tries=3 --max-time=3600
directory=/path/to/project
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/worker.log
stopwaitsecs=3600
```

---

## 🧪 **Testing & Verification**

### **1. Check Queue Status:**

```bash
# Lihat jobs yang pending
php artisan queue:work --queue=emails --once

# Monitor queue in real-time
php artisan queue:monitor
```

### **2. Test Email Service:**

```bash
# Test health
php artisan email:health-check

# Test send email
php artisan email:test your-email@example.com
```

### **3. Monitor Logs:**

```bash
# Watch email logs
tail -f storage/logs/laravel.log | grep -E "(email|Email|gmail|Gmail)"

# Check for errors
tail -f storage/logs/laravel.log | grep -E "(ERROR|error|failed|Failed)"
```

---

## 📊 **Verification Checklist**

### ✅ **Email Successfully Sent When:**

1. **Queue worker is running:**
   ```bash
   php artisan queue:work --queue=emails --daemon
   ```

2. **Gmail API is healthy:**
   ```bash
   php artisan email:health-check
   # ✅ Gmail API Service (PRIMARY): Healthy
   ```

3. **Logs show success:**
   ```
   [INFO] Registration email queued successfully
   [INFO] Gmail email sent successfully  
   [INFO] Queued email sent successfully
   ```

4. **Email appears in recipient inbox** (check spam folder too)

---

## 🚀 **Recommended Development Workflow**

### **Step 1: Start Queue Worker**
```bash
php artisan queue:work --queue=emails --daemon
```

### **Step 2: Test Registration Flow**
1. Register new applicant
2. Check logs for "Registration email queued successfully"
3. Verify email received in inbox
4. Check payment flow and email

### **Step 3: Monitor & Debug**
```bash
# If email not received:
php artisan queue:work --queue=emails --once

# Check health:
php artisan email:health-check

# Test direct send:
php artisan email:test recipient@example.com
```

---

## 💡 **Pro Tips**

### **1. Queue Worker Management:**
```bash
# Start worker dengan auto-restart
php artisan queue:restart && php artisan queue:work --queue=emails --daemon

# Process all pending jobs and stop
php artisan queue:work --queue=emails --stop-when-empty
```

### **2. Performance Monitoring:**
```bash
# Monitor queue performance
php artisan queue:monitor --max=100

# Check failed jobs
php artisan queue:failed
```

### **3. Debugging:**
```bash
# Enable verbose logging in .env
LOG_LEVEL=debug

# Check specific email service logs
tail -f storage/logs/laravel.log | grep "gmail_api"
```

---

## 🎯 **Quick Fix for Current Issue**

**Immediate solution untuk development:**

```bash
# Terminal 1: Start queue worker
php artisan queue:work --queue=emails --daemon

# Terminal 2: Monitor logs
tail -f storage/logs/laravel.log | grep -E "(email|queue)"
```

**Coba registrasi lagi - email seharusnya langsung terkirim!** ✅

---

## 📋 **Production Checklist**

Untuk deployment production:

- [ ] Setup Supervisor untuk queue worker
- [ ] Configure queue monitoring
- [ ] Setup email delivery monitoring
- [ ] Configure log rotation
- [ ] Setup alerting untuk failed jobs
- [ ] Test failover mechanism (Gmail API → Laravel Mail)

**Email delivery sekarang should work perfectly!** 🎉
