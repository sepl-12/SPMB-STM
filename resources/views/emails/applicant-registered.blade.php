<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Berhasil</title>
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
                                ✅ Pendaftaran Berhasil!
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
                                Selamat! Pendaftaran Anda telah berhasil kami terima. Berikut adalah informasi pendaftaran Anda:
                            </p>

                            <!-- Info Box -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f8fafc; border-radius: 8px; margin: 30px 0;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <table role="presentation" style="width: 100%; border-collapse: collapse;">
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #64748b; width: 40%;">Nomor Pendaftaran</td>
                                                <td style="padding: 8px 0; font-size: 16px; color: #16a34a; font-weight: bold;">{{ $registrationNumber }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #64748b; border-top: 1px solid #e2e8f0;">Nama Lengkap</td>
                                                <td style="padding: 8px 0; font-size: 14px; color: #1e293b; border-top: 1px solid #e2e8f0;">{{ $name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #64748b; border-top: 1px solid #e2e8f0;">Gelombang</td>
                                                <td style="padding: 8px 0; font-size: 14px; color: #1e293b; border-top: 1px solid #e2e8f0;">{{ $wave->name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #64748b; border-top: 1px solid #e2e8f0;">Tanggal Daftar</td>
                                                <td style="padding: 8px 0; font-size: 14px; color: #1e293b; border-top: 1px solid #e2e8f0;">{{ $applicant->registered_datetime->format('d F Y, H:i') }} WIB</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0; font-size: 14px; color: #92400e; line-height: 1.6;">
                                    <strong>⚠️ Langkah Selanjutnya:</strong><br>
                                    Silakan lakukan pembayaran biaya pendaftaran untuk melanjutkan proses seleksi. Anda dapat melakukan pembayaran melalui halaman dashboard Anda.
                                </p>
                            </div>

                            <!-- CTA Button -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ config('app.url') }}/payment/{{ $applicant->id }}" 
                                           style="display: inline-block; padding: 14px 32px; background-color: #16a34a; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 6px; font-size: 16px;">
                                            Bayar Sekarang
                                        </a>
                                    </td>
                                </tr>
                            </table>

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
