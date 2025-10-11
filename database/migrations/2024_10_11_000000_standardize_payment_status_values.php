<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Standarisasi payment_status_name dan payment_method_name
     * sesuai dengan Midtrans API convention (lowercase)
     */
    public function up(): void
    {
        // Update payment_status_name to lowercase and map old values
        DB::table('payments')->update([
            'payment_status_name' => DB::raw("
                CASE 
                    WHEN UPPER(payment_status_name) IN ('PAID', 'SUCCESS') THEN 'settlement'
                    WHEN UPPER(payment_status_name) = 'PENDING' THEN 'pending'
                    WHEN UPPER(payment_status_name) IN ('FAILED', 'FAILURE') THEN 'failure'
                    WHEN UPPER(payment_status_name) = 'SETTLEMENT' THEN 'settlement'
                    WHEN UPPER(payment_status_name) = 'CAPTURE' THEN 'capture'
                    WHEN UPPER(payment_status_name) = 'CANCEL' THEN 'cancel'
                    WHEN UPPER(payment_status_name) = 'DENY' THEN 'deny'
                    WHEN UPPER(payment_status_name) = 'EXPIRE' THEN 'expire'
                    ELSE LOWER(payment_status_name)
                END
            ")
        ]);

        // Update payment_method_name dari 'Midtrans Snap' ke 'echannel'
        DB::table('payments')
            ->where('payment_method_name', 'Midtrans Snap')
            ->orWhere('payment_method_name', 'MIDTRANS_SNAP')
            ->update(['payment_method_name' => 'echannel']);

        // Standardize other payment method names to lowercase with underscore
        DB::table('payments')->update([
            'payment_method_name' => DB::raw("
                CASE 
                    WHEN payment_method_name LIKE '%Virtual Account%' THEN 'other_va'
                    WHEN payment_method_name LIKE '%BCA%' THEN 'bca_va'
                    WHEN payment_method_name LIKE '%BNI%' THEN 'bni_va'
                    WHEN payment_method_name LIKE '%BRI%' THEN 'bri_va'
                    WHEN payment_method_name LIKE '%Mandiri%' THEN 'mandiri_va'
                    WHEN payment_method_name LIKE '%GoPay%' THEN 'gopay'
                    WHEN payment_method_name LIKE '%OVO%' THEN 'ovo'
                    WHEN payment_method_name LIKE '%DANA%' THEN 'dana'
                    WHEN payment_method_name = 'QRIS' THEN 'qris'
                    WHEN payment_method_name LIKE '%Credit%' OR payment_method_name LIKE '%Debit%' THEN 'credit_card'
                    ELSE payment_method_name
                END
            ")
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to uppercase (original format)
        DB::table('payments')->update([
            'payment_status_name' => DB::raw("UPPER(payment_status_name)")
        ]);
    }
};
