<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Ujian</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table role="presentation" style="width: 600px; max-width: 100%; border-collapse: collapse; background-color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #9333ea 0%, #7e22ce 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: bold;">
                                🎉 Selamat!
                            </h1>
                            <p style="margin: 10px 0 0; color: #e9d5ff; font-size: 16px;">
                                Anda Lolos Seleksi Berkas
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #333333;">
                                Yth. <strong>{{ $name }}</strong>,
                            </p>

                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #333333;">
                                Selamat! Anda telah <strong style="color: #16a34a;">DINYATAKAN LULUS</strong> seleksi administrasi dan berkas. Terlampir adalah kartu ujian Anda.
                            </p>

                            <!-- Exam Info Box -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%); border-radius: 8px; margin: 30px 0; border: 2px solid #9333ea;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <p style="margin: 0 0 16px; font-size: 12px; color: #6b21a8; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Informasi Ujian</p>
                                        
                                        <table role="presentation" style="width: 100%; border-collapse: collapse;">
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #6b21a8; width: 40%;">No. Pendaftaran</td>
                                                <td style="padding: 8px 0; font-size: 16px; color: #7e22ce; font-weight: bold;">{{ $registrationNumber }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #6b21a8; border-top: 1px solid #e9d5ff;">Nama Peserta</td>
                                                <td style="padding: 8px 0; font-size: 14px; color: #1e293b; border-top: 1px solid #e9d5ff;">{{ $name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #6b21a8; border-top: 1px solid #e9d5ff;">Gelombang</td>
                                                <td style="padding: 8px 0; font-size: 14px; color: #1e293b; border-top: 1px solid #e9d5ff;">{{ $wave->wave_name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #6b21a8; border-top: 1px solid #e9d5ff;">Tanggal Ujian</td>
                                                <td style="padding: 8px 0; font-size: 16px; color: #16a34a; font-weight: bold; border-top: 1px solid #e9d5ff;">
                                                    {{ $examDate }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #6b21a8; border-top: 1px solid #e9d5ff;">Lokasi Ujian</td>
                                                <td style="padding: 8px 0; font-size: 14px; color: #1e293b; border-top: 1px solid #e9d5ff;">{{ $examLocation }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0 0 12px; font-size: 14px; color: #92400e; line-height: 1.6; font-weight: bold;">
                                    📌 Hal yang Perlu Dibawa Saat Ujian:
                                </p>
                                <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #92400e; line-height: 1.8;">
                                    <li>Kartu ujian yang telah dicetak (terlampir)</li>
                                    <li>Kartu identitas asli (KTP/Kartu Pelajar)</li>
                                    <li>Alat tulis (pulpen, pensil, penghapus)</li>
                                    <li>Datang 30 menit sebelum ujian dimulai</li>
                                </ul>
                            </div>

                            <div style="background-color: #dcfce7; border-left: 4px solid #16a34a; padding: 16px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0; font-size: 14px; color: #166534; line-height: 1.6;">
                                    <strong>📎 Kartu Ujian Terlampir</strong><br>
                                    Kartu ujian Anda terlampir dalam email ini (file PDF). Silakan cetak kartu ujian dan bawa saat hari pelaksanaan ujian.
                                </p>
                            </div>

                            <!-- CTA Button -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $applicant->getStatusUrl() }}"
                                           style="display: inline-block; padding: 14px 32px; background-color: #9333ea; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 6px; font-size: 16px;">
                                            Lihat Detail Pendaftaran
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 30px 0 0; font-size: 14px; line-height: 1.6; color: #64748b;">
                                Jika Anda memiliki pertanyaan, silakan hubungi kami di:<br>
                                📧 {{ setting('contact_email', 'info@sekolah.com') }}<br>
                                📱 WhatsApp: {{ setting('contact_whatsapp', '628xxx') }}
                            </p>

                            <p style="margin: 20px 0 0; font-size: 14px; line-height: 1.6; color: #16a34a; font-weight: bold;">
                                Semoga sukses! 🍀
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
