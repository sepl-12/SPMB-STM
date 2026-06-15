<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('app_settings')->updateOrInsert(
            ['setting_key' => 'post_payment_whatsapp_group_url'],
            [
                'setting_value' => '',
                'setting_type' => 'string',
                'setting_description' => 'Link grup WhatsApp yang diberikan setelah pembayaran berhasil',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('app_settings')
            ->where('setting_key', 'post_payment_whatsapp_group_url')
            ->delete();
    }
};
