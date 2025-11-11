<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('app_settings')->insert([
            // Toggle Emergency Mode
            [
                'setting_key' => 'emergency_payment_enabled',
                'setting_value' => 'false',
                'setting_type' => 'boolean',
                'setting_description' => 'Aktifkan mode pembayaran darurat (manual QRIS)',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // QRIS Image Path
            [
                'setting_key' => 'emergency_qris_image',
                'setting_value' => null,
                'setting_type' => 'string',
                'setting_description' => 'Path file gambar QRIS untuk pembayaran darurat',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Instructions Text
            [
                'setting_key' => 'emergency_payment_instructions',
                'setting_value' => "1. Scan QRIS di bawah menggunakan aplikasi pembayaran Anda\n2. Bayar sesuai jumlah biaya pendaftaran\n3. Screenshot/foto bukti pembayaran\n4. Upload bukti pembayaran di form yang tersedia\n5. Tunggu verifikasi dari admin (maksimal 1x24 jam)",
                'setting_type' => 'text',
                'setting_description' => 'Instruksi pembayaran darurat untuk user',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Payment Account Info (optional)
            [
                'setting_key' => 'emergency_payment_account_name',
                'setting_value' => 'Yayasan Pendidikan',
                'setting_type' => 'string',
                'setting_description' => 'Nama penerima QRIS',
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
        DB::table('app_settings')
            ->whereIn('setting_key', [
                'emergency_payment_enabled',
                'emergency_qris_image',
                'emergency_payment_instructions',
                'emergency_payment_account_name',
            ])
            ->delete();
    }
};
