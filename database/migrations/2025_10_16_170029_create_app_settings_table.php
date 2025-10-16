<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 100)->unique();
            $table->text('setting_value')->nullable();
            $table->string('setting_type', 20)->default('string'); // string, text, boolean, json
            $table->text('setting_description')->nullable();
            $table->timestamps();
            
            $table->index('setting_key');
        });

        // Seed default settings
        DB::table('app_settings')->insert([
            // Contact Information
            [
                'setting_key' => 'contact_email',
                'setting_value' => 'info@sekolah.com',
                'setting_type' => 'string',
                'setting_description' => 'Email kontak utama',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'contact_whatsapp',
                'setting_value' => '628123456789',
                'setting_type' => 'string',
                'setting_description' => 'Nomor WhatsApp (format: 628xxx)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'contact_phone',
                'setting_value' => '(021) 12345678',
                'setting_type' => 'string',
                'setting_description' => 'Nomor telepon kantor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'contact_address',
                'setting_value' => 'Jl. Pendidikan No. 1, Jakarta',
                'setting_type' => 'text',
                'setting_description' => 'Alamat lengkap sekolah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Social Media
            [
                'setting_key' => 'social_facebook_url',
                'setting_value' => '',
                'setting_type' => 'string',
                'setting_description' => 'URL Facebook page',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'social_instagram_handle',
                'setting_value' => '',
                'setting_type' => 'string',
                'setting_description' => 'Instagram handle',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'social_twitter_handle',
                'setting_value' => '',
                'setting_type' => 'string',
                'setting_description' => 'Twitter/X handle',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'social_youtube_url',
                'setting_value' => '',
                'setting_type' => 'string',
                'setting_description' => 'URL YouTube channel',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Site Content
            [
                'setting_key' => 'hero_title',
                'setting_value' => 'Penerimaan Peserta Didik Baru Online 2025/2026',
                'setting_type' => 'string',
                'setting_description' => 'Judul hero halaman utama',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'hero_subtitle',
                'setting_value' => 'Membuka pendaftaran siswa baru tahun ajaran 2025/2026',
                'setting_type' => 'text',
                'setting_description' => 'Subjudul hero halaman utama',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'hero_image',
                'setting_value' => '',
                'setting_type' => 'string',
                'setting_description' => 'Path gambar hero',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'requirements_text',
                'setting_value' => "1. Mengisi formulir pendaftaran\n2. Pas foto ukuran 3x4 (2 lembar)\n3. Fotokopi ijazah/SKHUN",
                'setting_type' => 'text',
                'setting_description' => 'Syarat pendaftaran (markdown)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'faq_items',
                'setting_value' => '[]',
                'setting_type' => 'json',
                'setting_description' => 'FAQ items (JSON array)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'timeline_items',
                'setting_value' => '[]',
                'setting_type' => 'json',
                'setting_description' => 'Timeline items (JSON array)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'cta_button_label',
                'setting_value' => 'Daftar Sekarang',
                'setting_type' => 'string',
                'setting_description' => 'Label tombol CTA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'cta_button_url',
                'setting_value' => '/daftar',
                'setting_type' => 'string',
                'setting_description' => 'URL tombol CTA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
