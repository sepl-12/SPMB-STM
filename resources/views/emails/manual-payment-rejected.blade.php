<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Manual Ditolak</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table role="presentation" style="width: 600px; max-width: 100%; border-collapse: collapse; background-color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: bold;">
                                ⚠️ Pembayaran Ditolak
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
                                Mohon maaf, pembayaran manual Anda dengan nomor pendaftaran <strong>{{ $registrationNumber }}</strong> telah <strong style="color: #dc2626;">DITOLAK</strong> oleh admin kami.
                            </p>

                            <!-- Rejection Reason Box -->
                            <div style="background-color: #fee2e2; border-left: 4px solid #dc2626; padding: 16px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0 0 8px; font-size: 14px; color: #991b1b; font-weight: 600;">
                                    📝 Alasan Penolakan:
                                </p>
                                <p style="margin: 0; font-size: 14px; color: #7f1d1d; line-height: 1.6;">
                                    {{ $rejectionReason }}
                                </p>
                            </div>

                            <!-- Payment Info Box -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #fef2f2; border-radius: 8px; margin: 30px 0; border: 2px solid #dc2626;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <p style="margin: 0 0 16px; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Detail Pembayaran</p>

                                        <table role="presentation" style="width: 100%; border-collapse: collapse;">
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #64748b; width: 40%;">No. Pendaftaran</td>
                                                <td style="padding: 8px 0; font-size: 16px; color: #b91c1c; font-weight: bold;">{{ $registrationNumber }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #64748b; border-top: 1px solid #fecaca;">Jumlah</td>
                                                <td style="padding: 8px 0; font-size: 18px; color: #1e293b; font-weight: bold; border-top: 1px solid #fecaca;">
                                                    Rp {{ $amount }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #64748b; border-top: 1px solid #fecaca;">Metode Pembayaran</td>
                                                <td style="padding: 8px 0; font-size: 14px; color: #1e293b; border-top: 1px solid #fecaca;">
                                                    Transfer Manual (QRIS)
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #64748b; border-top: 1px solid #fecaca;">Tanggal Penolakan</td>
                                                <td style="padding: 8px 0; font-size: 14px; color: #1e293b; border-top: 1px solid #fecaca;">{{ $rejectionDate }} WIB</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #64748b; border-top: 1px solid #fecaca;">Ditolak Oleh</td>
                                                <td style="padding: 8px 0; font-size: 14px; color: #1e293b; border-top: 1px solid #fecaca;">{{ $rejectedBy }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #64748b; border-top: 1px solid #fecaca;">Status</td>
                                                <td style="padding: 8px 0; font-size: 14px; border-top: 1px solid #fecaca;">
                                                    <span style="background-color: #dc2626; color: #ffffff; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; text-transform: uppercase;">
                                                        DITOLAK
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <div style="background-color: #dbeafe; border-left: 4px solid #0284c7; padding: 16px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0; font-size: 14px; color: #1e3a8a; line-height: 1.6;">
                                    <strong>🔄 Langkah Selanjutnya:</strong><br>
                                    Silakan lakukan pembayaran ulang dengan memperhatikan alasan penolakan di atas. Pastikan bukti pembayaran jelas dan jumlah sesuai dengan biaya pendaftaran.
                                </p>
                            </div>

                            <!-- CTA Button -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $applicant->getPaymentUrl() }}"
                                           style="display: inline-block; padding: 14px 32px; background-color: #0284c7; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 6px; font-size: 16px;">
                                            💳 Lakukan Pembayaran Ulang
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 30px 0 0; font-size: 14px; line-height: 1.6; color: #64748b;">
                                Jika Anda memiliki pertanyaan atau membutuhkan bantuan, silakan hubungi kami di:<br>
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
