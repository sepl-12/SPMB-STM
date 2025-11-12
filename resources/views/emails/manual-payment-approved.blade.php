<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Manual Disetujui</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table role="presentation" style="width: 600px; max-width: 100%; border-collapse: collapse; background-color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: bold;">
                                ✅ Pembayaran Disetujui!
                            </h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #333333;">
                                Yth. <strong>{{ $name }}</strong>,
                            </p>

                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #333333;">
                                Selamat! Pembayaran manual Anda telah <strong style="color: #16a34a;">DISETUJUI</strong> oleh admin kami. Terima kasih atas pembayaran Anda.
                            </p>

                            <!-- Payment Info Box -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f0fdf4; border-radius: 8px; margin: 30px 0; border: 2px solid #16a34a;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <p style="margin: 0 0 16px; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Detail Pembayaran</p>

                                        <table role="presentation" style="width: 100%; border-collapse: collapse;">
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #64748b; width: 40%;">No. Pendaftaran</td>
                                                <td style="padding: 8px 0; font-size: 16px; color: #15803d; font-weight: bold;">{{ $registrationNumber }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #64748b; border-top: 1px solid #bbf7d0;">Jumlah Dibayar</td>
                                                <td style="padding: 8px 0; font-size: 18px; color: #16a34a; font-weight: bold; border-top: 1px solid #bbf7d0;">
                                                    Rp {{ $amount }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #64748b; border-top: 1px solid #bbf7d0;">Metode Pembayaran</td>
                                                <td style="padding: 8px 0; font-size: 14px; color: #1e293b; border-top: 1px solid #bbf7d0;">
                                                    Transfer Manual (QRIS)
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #64748b; border-top: 1px solid #bbf7d0;">Tanggal Approval</td>
                                                <td style="padding: 8px 0; font-size: 14px; color: #1e293b; border-top: 1px solid #bbf7d0;">{{ $approvalDate }} WIB</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #64748b; border-top: 1px solid #bbf7d0;">Disetujui Oleh</td>
                                                <td style="padding: 8px 0; font-size: 14px; color: #1e293b; border-top: 1px solid #bbf7d0;">{{ $approvedBy }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #64748b; border-top: 1px solid #bbf7d0;">Status</td>
                                                <td style="padding: 8px 0; font-size: 14px; border-top: 1px solid #bbf7d0;">
                                                    <span style="background-color: #16a34a; color: #ffffff; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; text-transform: uppercase;">
                                                        DISETUJUI
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <div style="background-color: #dbeafe; border-left: 4px solid #0284c7; padding: 16px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0; font-size: 14px; color: #1e3a8a; line-height: 1.6;">
                                    <strong>📋 Langkah Selanjutnya:</strong><br>
                                    Pembayaran Anda telah dikonfirmasi. Kami akan melakukan verifikasi berkas Anda. Kartu ujian akan dikirimkan melalui email setelah semua berkas terverifikasi.
                                </p>
                            </div>

                            <!-- CTA Buttons -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin: 30px 0;">
                                <tr>
                                    <td align="center" style="padding-bottom: 10px;">
                                        <a href="{{ $applicant->getExamCardUrl() }}"
                                           style="display: inline-block; padding: 14px 32px; background-color: #16a34a; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 6px; font-size: 16px;">
                                            📄 Download Kartu Ujian
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <a href="{{ $applicant->getStatusUrl() }}"
                                           style="display: inline-block; padding: 14px 32px; background-color: #0284c7; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 6px; font-size: 16px;">
                                            🔍 Cek Status Pendaftaran
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0; font-size: 12px; color: #92400e; line-height: 1.5;">
                                    <strong>🔒 Keamanan:</strong> Link di atas adalah link aman yang khusus dibuat untuk Anda dan akan kedaluarsa dalam waktu tertentu. Jangan bagikan link ini kepada orang lain.
                                </p>
                            </div>

                            <p style="margin: 30px 0 0; font-size: 14px; line-height: 1.6; color: #64748b;">
                                Jika Anda memiliki pertanyaan, silakan hubungi kami di:<br>
                                📧 {{ setting('contact_email', 'info@sekolah.com') }}<br>
                                📱 WhatsApp: {{ setting('contact_whatsapp', '628xxx') }}
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 24px 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0 0 8px; font-size: 14px; color: #64748b;">
                                {{ config('app.name') }}
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                                {{ setting('contact_address', 'Alamat Sekolah') }}
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
