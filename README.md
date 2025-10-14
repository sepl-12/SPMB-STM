# SPMB STM - Sistem Penerimaan Peserta Didik Baru

> Modern, scalable student registration system built with Laravel 10 & Filament 3.

[![Laravel](https://img.shields.io/badge/Laravel-10.x-red.svg)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-orange.svg)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-blue.svg)](https://mysql.com)

---

## 📖 Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Documentation](#documentation)
- [Testing](#testing)

---

## 🎯 Overview

**SPMB STM** adalah sistem manajemen penerimaan peserta didik baru yang komprehensif dengan fokus pada:
- ✅ **User Experience** - Form wizard yang mudah digunakan
- ✅ **Flexibility** - Dynamic form builder untuk admin
- ✅ **Integration** - Payment gateway Midtrans terintegrasi
- ✅ **Scalability** - Architecture yang mudah dikembangkan

---

## ⭐ Key Features

### 🎨 **Dynamic Form Builder**
- Form builder dengan berbagai field types
- Form versioning untuk data integrity
- Multi-step wizard navigation
- **System fields protection** untuk field critical

### 💳 **Payment Gateway Integration**
- Midtrans Snap integration
- Auto-update payment status via webhook
- Single Source of Truth architecture
- Real-time status tracking

### 📊 **Data Management**
- Comprehensive applicant management
- File preview (images/PDFs inline)
- Excel export dengan customizable columns
- Dashboard statistics

### 🌊 **Wave Management**
- Kelola periode pendaftaran
- Set quotas & registration fees
- Active/inactive wave control

---

## 📋 Requirements

- **PHP:** 8.1 or higher
- **Composer:** Latest version
- **Node.js:** 16.x or higher
- **MySQL:** 8.0 or higher

---

## 🚀 Installation

### **1. Clone & Install**
```bash
git clone <repository-url>
cd SPMB-STM
composer install
npm install
```

### **2. Environment Setup**
```bash
cp .env.example .env
php artisan key:generate
```

### **3. Database**
Create database and configure `.env`:
```env
DB_DATABASE=spmb_stm
DB_USERNAME=root
DB_PASSWORD=your_password
```

### **4. Migrate & Seed**
```bash
php artisan migrate --seed
php artisan storage:link
```

### **5. Run**
```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

---

## ⚙️ Configuration

### **Admin Access**
- **URL:** `http://localhost:8000/admin`
- **Email:** `admin@example.com`
- **Password:** `password`

### **Midtrans Setup**
Add to `.env`:
```env
MIDTRANS_SERVER_KEY=your-server-key
MIDTRANS_CLIENT_KEY=your-client-key
MIDTRANS_IS_PRODUCTION=false
```

**Webhook URL:** `https://yourdomain.com/payment/notification`

---

## 📚 Documentation

### **📖 Essential Reading**

| Document | Description |
|----------|-------------|
| **[DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md)** | Complete developer handbook |
| **[ARCHITECTURE.md](ARCHITECTURE.md)** | System architecture & design |
| **[docs/INDEX.md](docs/INDEX.md)** | Documentation navigation |

### **📁 Documentation Structure**

```
docs/
├── payment/               # Payment integration (13 files)
├── form-management/       # Form system (8 files)
├── features/              # Feature docs (6 files)
├── troubleshooting/       # Bug fixes & solutions (7 files)
└── archived/              # Historical docs (2 files)
```

### **🔗 Quick Links**

- **Payment Setup:** [docs/payment/PAYMENT_GATEWAY_MIDTRANS.md](docs/payment/PAYMENT_GATEWAY_MIDTRANS.md)
- **Form Builder:** [docs/form-management/DYNAMIC_REGISTRATION_FORM.md](docs/form-management/DYNAMIC_REGISTRATION_FORM.md)
- **System Protection:** [docs/SYSTEM_FIELDS_PROTECTION_GUIDE.md](docs/SYSTEM_FIELDS_PROTECTION_GUIDE.md)
- **All Docs:** [docs/INDEX.md](docs/INDEX.md)

---

## 🧪 Testing

```bash
php artisan test
php artisan midtrans:test
```

---

## 🆘 Support

**Common Issues:**
- Payment not updating → Check webhook URL
- File upload fails → Check storage permissions
- Form not showing → Check active wave

**Documentation:** [docs/troubleshooting/](docs/troubleshooting/)

---

## 📄 License

Proprietary - All rights reserved

---

**Last Updated:** 2025-01-13  
**Version:** 1.0.0
