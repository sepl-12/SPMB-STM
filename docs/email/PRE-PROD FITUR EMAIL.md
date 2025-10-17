🎯 **Fitur Email Yang Dibutuhkan**

   1. Email Pendaftaran Berhasil
     •  Dikirim setelah user submit formulir
     •  Berisi: Nomor pendaftaran, nama, info selanjutnya

   2. Email Pembayaran Berhasil
     •  Dikirim setelah payment confirmed (Midtrans callback)
     •  Berisi: Invoice, jumlah, status pembayaran

   3. Email Kartu Tes
     •  Dikirim setelah admin approve/setelah pembayaran
     •  Attachment: PDF kartu tes dengan nomor peserta

   ──────────────────────────────────────────

   🛠️ **Teknologi Email (Open Source & Mudah)**

   **Rekomendasi Berdasarkan Kebutuhan:**

   | Service | Free Tier | Setup | Speed | Reliability | Best For |
   |---------|-----------|-------|-------|-------------|----------|
   | Mailtrap | Unlimited (dev only) | ⭐⭐⭐⭐⭐ | Fast | High | Development/Testing |
   | Gmail SMTP | 500/day | ⭐⭐⭐⭐ | Medium | High | Small scale (<500/day) |
   | Mailgun | 5,000/month | ⭐⭐⭐⭐ | Fast | High | Production (scalable) |
   | SendGrid | 100/day | ⭐⭐⭐ | Fast | High | Production |
   | Resend | 3,000/month | ⭐⭐⭐⭐⭐ | Fast | High | Modern API |
   | Amazon SES | 62,000/month | ⭐⭐ | Fast | High | High volume |

   ──────────────────────────────────────────

   **✅ REKOMENDASI UNTUK PPDB:**

   **Development (Testing):**

     Mailtrap
     - Free unlimited testing emails
     - No real emails sent
     - Web interface untuk lihat email
     - Perfect untuk development

   **Production (Sekolah):**

   Pilihan 1: Gmail SMTP (Paling Simple)

     Pros:
     ✅ Gratis 500 email/hari
     ✅ Setup sangat mudah
     ✅ Tidak perlu verifikasi domain
     ✅ Reliable

     Cons:
     ❌ Limited 500/hari (cukup untuk 1 sekolah)
     ❌ Perlu App Password (2FA)
     ❌ Tidak scalable untuk multiple sekolah

   Pilihan 2: Mailgun (Recommended)

     Pros:
     ✅ Free 5,000 email/bulan
     ✅ API modern & mudah
     ✅ Email tracking & analytics
     ✅ Scalable
     ✅ Support attachment

     Cons:
     ❌ Perlu verifikasi domain
     ❌ Sedikit lebih kompleks setup

   Pilihan 3: Resend (Modern & Clean)

     Pros:
     ✅ Free 3,000 email/bulan
     ✅ API paling modern
     ✅ Dashboard simple
     ✅ React email templates

     Cons:
     ❌ Baru (tapi backed by Vercel)
     ❌ Perlu domain verification

   ──────────────────────────────────────────

   📋 **Tahapan Implementasi**

   **FASE 1: Setup Email Service**

 READ  (.env.example, limit: 50)
 ↳ Read 52 lines.

⛬  Perfect! Saya lihat saat ini email masih menggunakan log driver. Mari saya jelaskan tahapan lengkapnya:

   ──────────────────────────────────────────

   📝 **TAHAPAN IMPLEMENTASI LENGKAP**

   **FASE 1: Setup Email Service (Development)**

   Step 1.1: Setup Mailtrap (Free Testing)

   1. Daftar di https://mailtrap.io (gratis)
   2. Buat inbox baru
   3. Copy credentials
   4. Update .env:

   env
     MAIL_MAILER=smtp
     MAIL_HOST=sandbox.smtp.mailtrap.io
     MAIL_PORT=2525
     MAIL_USERNAME=your_username
     MAIL_PASSWORD=your_password
     MAIL_ENCRYPTION=tls
     MAIL_FROM_ADDRESS="noreply@smkmuh1sangatta.sch.id"
     MAIL_FROM_NAME="${APP_NAME}"

   Benefit Mailtrap:
   •  Tidak kirim email ke user (aman untuk testing)
   •  Lihat email di web dashboard
   •  Test attachment, HTML template, etc

   ──────────────────────────────────────────

   **FASE 2: Buat Email Templates**

   Laravel punya 3 cara buat email:

   Cara 1: Mailable (Recommended)

     app/Mail/ApplicantRegistered.php
     app/Mail/PaymentConfirmed.php
     app/Mail/ExamCardReady.php

   Cara 2: Notification

     app/Notifications/RegistrationSuccess.php

   Cara 3: Raw Mail

     Mail::send() // Tidak recommended

   ✅ Saya Rekomendasikan: Mailable + Queue

   Alasan:
   •  Clean separation
   •  Reusable
   •  Testable
   •  Support attachment
   •  Can be queued (async)

   ──────────────────────────────────────────

   **FASE 3: Struktur File**

     app/
     ├── Mail/
     │   ├── ApplicantRegistered.php      # Email pendaftaran
     │   ├── PaymentConfirmed.php         # Email payment sukses
     │   └── ExamCardReady.php            # Email kartu tes
     │
     ├── Services/
     │   └── PdfService.php               # Generate PDF kartu tes
     │
     ├── Jobs/
     │   └── SendExamCardEmail.php        # Queue job untuk kartu tes
     │
     resources/views/emails/
     ├── applicant-registered.blade.php   # Template email pendaftaran
     ├── payment-confirmed.blade.php      # Template payment
     └── exam-card-ready.blade.php        # Template kartu tes

   ──────────────────────────────────────────

   **FASE 4: Flow Implementasi**

   **Email 1: Pendaftaran Berhasil**

   Trigger: Setelah submit formulir

     User Submit Form
         ↓
     Controller: store() method
         ↓
     Save to DB
         ↓
     Dispatch: Mail::to($applicant->email)->send(new ApplicantRegistered($applicant))
         ↓
     Email sent!

   Content Email:
   •  Nomor pendaftaran
   •  Nama lengkap
   •  Info pembayaran
   •  Link ke halaman status
   •  Kontak admin

   ──────────────────────────────────────────

   **Email 2: Pembayaran Berhasil**

   Trigger: Midtrans callback settlement

     Midtrans Webhook
         ↓
     PaymentController: handleNotification()
         ↓
     Verify signature
         ↓
     Update payment status
         ↓
     Mail::to($applicant->email)->send(new PaymentConfirmed($payment))
         ↓
     Email sent!

   Content Email:
   •  Invoice number
   •  Jumlah dibayar
   •  Tanggal pembayaran
   •  Status: LUNAS
   •  Info selanjutnya (tes)

   ──────────────────────────────────────────

   **Email 3: Kartu Tes + PDF**

   Trigger: Admin approve ATAU auto setelah payment

     Payment Confirmed
         ↓
     Check if approved
         ↓
     Generate PDF (kartu tes)
         ↓
     Dispatch Queue Job: SendExamCardEmail
         ↓
     Email sent with PDF attachment

   Content Email:
   •  Selamat! Anda lolos verifikasi
   •  Info jadwal tes
   •  Lokasi tes
   •  Hal yang perlu dibawa
   •  Attachment: kartu-tes-{nomor}.pdf

   ──────────────────────────────────────────

   **FASE 5: Queue System (Penting!)**

   Kenapa pakai Queue?
   •  Email sending itu lambat (2-5 detik)
   •  User tidak perlu tunggu
   •  Jika email gagal, bisa retry
   •  Scalable untuk banyak email

   Setup Queue:

   env
     # .env
     QUEUE_CONNECTION=database  # Already set!

   Run Queue Worker:

   bash
     php artisan queue:work

   Untuk Production:

   bash
     # Gunakan supervisor atau systemd
     php artisan queue:work --tries=3 --timeout=90

   ──────────────────────────────────────────

   🎨 **Desain Email Template**

   **Best Practices:**

   1. Responsive Design
     •  Mobile-friendly
     •  Max width 600px
     •  Use tables for layout (old school tapi reliable)

   2. Clear CTA Button
     •  Warna kontras
     •  Text jelas
     •  Link ke halaman relevant

   3. Branding
     •  Logo sekolah
     •  Warna sekolah
     •  Footer dengan kontak

   4. Plain Text Alternative
     •  Laravel auto-generate dari HTML
     •  Untuk email client yang tidak support HTML

   ──────────────────────────────────────────

   📦 **Libraries Tambahan (Optional)**

   **1. PDF Generation**

   Pilihan:

     Laravel Snappy (wkhtmltopdf)
     - Fast
     - HTML to PDF
     - Styling dengan CSS

     DomPDF
     - Pure PHP (no dependencies)
     - Slower
     - Good enough

     Laravel PDF (Spatie)
     - Wrapper untuk browser-shot
     - Perfect quality
     - Perlu Chrome/Puppeteer

   Rekomendasi: DomPDF (simplest, no dependency)

   bash
     composer require barryvdh/laravel-dompdf

   ──────────────────────────────────────────

   **2. Email Template Builder (Optional)**

   MJML Framework:
   •  Framework untuk email responsive
   •  Compile to HTML
   •  https://mjml.io

   Laravel Markdown:
   •  Built-in Laravel
   •  Simple syntax
   •  Auto-styled

   ──────────────────────────────────────────

   🔒 **Keamanan & Best Practices**

   **1. Validasi Email**

   php
     // Before sending
     if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
         return;
     }

   **2. Rate Limiting**

   php
     // Max 5 emails per user per hour
     RateLimiter::attempt('send-email:'.$userId, 5, function() {
         // Send email
     });

   **3. Unsubscribe Link (Opsional)**

   php
     // Untuk marketing email
     // PPDB tidak perlu karena transactional

   **4. SPF & DKIM**

     Setup di domain DNS
     - SPF: Authorized sender
     - DKIM: Email signature
     - DMARC: Policy

   **5. Test Email Content**

   php
     // Use Mailtrap untuk test
     // Jangan langsung production

   ──────────────────────────────────────────

   💰 **Estimasi Cost (Production)**

   **Skenario: 500 pendaftar/tahun**

     Email per pendaftar:
     - 1x Pendaftaran
     - 1x Payment konfirmasi
     - 1x Kartu tes
     Total: 1,500 emails/tahun

     Gmail SMTP:
     ✅ FREE (< 500/day)

     Mailgun:
     ✅ FREE (< 5,000/month)

     Resend:
     ✅ FREE (< 3,000/month)

     Kesimpulan: Gratis untuk volume PPDB! 🎉

   ──────────────────────────────────────────

   🚀 **Rekomendasi Workflow Untuk Anda**

   **Approach yang Saya Sarankan:**

   Development:

     1. Setup Mailtrap (testing)
     2. Buat 3 Mailable classes
     3. Buat Blade templates
     4. Test di local
     5. Setup queue worker

   Production:

     Option 1 (Paling Mudah):
     - Gmail SMTP
     - No domain verification needed
     - Free 500/day (cukup!)

     Option 2 (Scalable):
     - Mailgun
     - Verify domain
     - Free 5,000/month
     - Better for multiple sekolah

   ──────────────────────────────────────────

   📊 **Perbandingan Effort vs Result**

   | Approach | Setup Time | Maintenance | Features | Cost |
   |----------|-----------|-------------|----------|------|
   | Gmail SMTP | 15 min | Low | Basic | Free |
   | Mailgun | 1 hour | Low | Advanced | Free |
   | Custom SMTP | 2 hours | High | Full control | Server cost |

   ──────────────────────────────────────────

   ✅ **Next Steps - Mau Saya Implementasikan?**

   Jika Anda setuju dengan approach ini, saya bisa langsung implementasikan dengan tahapan:

   1. ✅ Setup Mailtrap untuk testing
   2. ✅ Buat Mailable classes (3 email types)
   3. ✅ Buat Blade email templates (responsive)
   4. ✅ Integrate dengan existing flow (registration, payment)
   5. ✅ Setup queue untuk async sending
   6. ✅ Buat PDF generator untuk kartu tes
   7. ✅ Testing & documentation

   Estimasi waktu:
   •  Core features: 2-3 jam
   •  PDF + templates: 1-2 jam
   •  Testing: 1 jam
   Total: 4-6 jam development time