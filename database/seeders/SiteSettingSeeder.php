<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder ini mengisi app_settings table dengan data default untuk landing page
     */
    public function run(): void
    {
        $settings = [
            // Hero Section
            'hero_title' => 'Penerimaan Peserta Didik Baru Online 2025/2026',
            'hero_subtitle' => 'SMK Muhammadiyah 1 Sangatta Utara membuka pendaftaran siswa baru. Daftar sekarang juga dan jadi bagian dari kami.',
            'hero_image' => 'hero-bg.jpg',
            
            // CTA Button
            'cta_button_label' => 'Daftar Sekarang',
            'cta_button_url' => '/daftar',
            
            // Requirements
            'requirements_text' => "1. Mengisi formulir pendaftaran\n2. Pas foto ukuran 3x4 (2 lembar)\n3. Fotocopy Kartu Keluarga (KK)\n4. Fotocopy Akta Kelahiran\n5. Fotocopy Kartu/surat keterangan NISN\n6. Mengikuti test seleksi",
            
            // FAQ Items (JSON)
            'faq_items' => json_encode([
                [
                    'question' => 'Apa saja persyaratan pendaftaran?',
                    'answer' => 'Persyaratan pendaftaran meliputi mengisi formulir online, pas foto 3x4 (2 lembar), fotocopy Kartu Keluarga (KK), fotocopy Akta Kelahiran, fotocopy kartu/surat keterangan NISN, dan mengikuti test seleksi.'
                ],
                [
                    'question' => 'Berapa biaya pendaftaran?',
                    'answer' => 'Biaya pendaftaran adalah Rp 300.000 yang dapat dibayarkan melalui transfer bank atau pembayaran langsung ke sekolah. Biaya ini sudah termasuk biaya test seleksi dan formulir pendaftaran.'
                ],
                [
                    'question' => 'Apakah ada jalur prestasi?',
                    'answer' => 'Ya, kami menyediakan jalur prestasi untuk siswa yang memiliki prestasi akademik atau non-akademik. Calon siswa dengan prestasi dapat melampirkan sertifikat atau piagam penghargaan saat mendaftar untuk mendapatkan nilai tambahan.'
                ],
                [
                    'question' => 'Kapan pengumuman hasil seleksi?',
                    'answer' => 'Pengumuman hasil seleksi akan diumumkan 7 hari setelah test seleksi dilaksanakan. Hasil dapat dilihat secara online melalui website ini dengan memasukkan nomor pendaftaran Anda.'
                ],
                [
                    'question' => 'Bagaimana jika saya mengalami kesulitan saat mendaftar?',
                    'answer' => 'Jika mengalami kesulitan saat mendaftar, Anda dapat menghubungi tim support kami melalui WhatsApp, telepon, atau email yang tertera di bagian kontak. Tim kami siap membantu Anda dari hari Senin - Jumat pukul 08:00 - 16:00 WIB.'
                ]
            ]),
            
            // Timeline Items (JSON)
            'timeline_items' => json_encode([
                [
                    'step' => 1,
                    'title' => 'Buat Akun & Isi Formulir',
                    'description' => 'Calon siswa membuat akun dan mengisi formulir pendaftaran secara online dengan data yang lengkap dan benar.',
                    'icon' => 'user-plus'
                ],
                [
                    'step' => 2,
                    'title' => 'Seleksi Berkas',
                    'description' => 'Panitia PPDB akan melakukan verifikasi dan validasi berkas pendaftaran yang telah diunggah oleh calon siswa.',
                    'icon' => 'document'
                ],
                [
                    'step' => 3,
                    'title' => 'Pengumuman Hasil',
                    'description' => 'Hasil seleksi akan diumumkan secara online melalui website ini. Calon siswa dapat melihat status kelulusan.',
                    'icon' => 'check-circle'
                ],
                [
                    'step' => 4,
                    'title' => 'Daftar Ulang',
                    'description' => 'Siswa yang dinyatakan lulus seleksi diwajibkan melakukan daftar ulang sesuai jadwal yang telah ditentukan.',
                    'icon' => 'currency'
                ]
            ]),
            
            // Contact Information
            'contact_email' => 'info@smkmuh1sangatta.sch.id',
            'contact_whatsapp' => '6281234567890',
            'contact_phone' => '(0549) 123456',
            'contact_address' => 'Jl. Pendidikan No. 123, Sangatta Utara, Kutai Timur, Kalimantan Timur',
            
            // Social Media
            'social_facebook_url' => 'https://facebook.com/smkmuh1sangatta',
            'social_instagram_handle' => '@smkmuh1sangatta',
            'social_twitter_handle' => '@smkmuh1sangatta',
            'social_youtube_url' => 'https://youtube.com/@smkmuh1sangatta',
        ];
        
        // Insert each setting into app_settings table
        foreach ($settings as $key => $value) {
            AppSetting::set($key, $value);
        }
        
        echo "✅ Site settings berhasil di-seed ke app_settings table!\n";
    }
}
