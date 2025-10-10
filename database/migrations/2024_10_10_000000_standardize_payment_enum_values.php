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
     * This migration helps standardize existing payment data to match the new Enum values
     * Only for PaymentStatus since we only use Midtrans and IDR
     */
    public function up()
    {
        // Update payment_status_name values to match enum cases
        DB::table('payments')->where('payment_status_name', 'pending')->update(['payment_status_name' => 'PENDING']);
        DB::table('payments')->where('payment_status_name', 'paid')->update(['payment_status_name' => 'PAID']);
        DB::table('payments')->where('payment_status_name', 'failed')->update(['payment_status_name' => 'FAILED']);
        DB::table('payments')->where('payment_status_name', 'settlement')->update(['payment_status_name' => 'SETTLEMENT']);
        DB::table('payments')->where('payment_status_name', 'cancel')->update(['payment_status_name' => 'CANCEL']);
        DB::table('payments')->where('payment_status_name', 'deny')->update(['payment_status_name' => 'DENY']);
        DB::table('payments')->where('payment_status_name', 'expire')->update(['payment_status_name' => 'EXPIRE']);
        DB::table('payments')->where('payment_status_name', 'capture')->update(['payment_status_name' => 'CAPTURE']);

        // Standardize payment_gateway_name to always be 'Midtrans'
        DB::table('payments')->where('payment_gateway_name', 'midtrans')->update(['payment_gateway_name' => 'Midtrans']);
        DB::table('payments')->whereNull('payment_gateway_name')->update(['payment_gateway_name' => 'Midtrans']);

        // Update common payment_method_name values to match enum
        DB::table('payments')->where('payment_method_name', 'midtrans_snap')->update(['payment_method_name' => 'Midtrans Snap']);
        DB::table('payments')->whereNull('payment_method_name')->update(['payment_method_name' => 'Midtrans Snap']);

        // Set default currency_code to IDR (since we only use IDR)
        DB::table('payments')->whereNull('currency_code')->update(['currency_code' => 'IDR']);
        DB::table('payments')->where('currency_code', '!=', 'IDR')->update(['currency_code' => 'IDR']);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Reverse the changes if needed
        DB::table('payments')->where('payment_status_name', 'PENDING')->update(['payment_status_name' => 'pending']);
        DB::table('payments')->where('payment_status_name', 'PAID')->update(['payment_status_name' => 'paid']);
        DB::table('payments')->where('payment_status_name', 'FAILED')->update(['payment_status_name' => 'failed']);
        DB::table('payments')->where('payment_status_name', 'SETTLEMENT')->update(['payment_status_name' => 'settlement']);
        DB::table('payments')->where('payment_status_name', 'CANCEL')->update(['payment_status_name' => 'cancel']);
        DB::table('payments')->where('payment_status_name', 'DENY')->update(['payment_status_name' => 'deny']);
        DB::table('payments')->where('payment_status_name', 'EXPIRE')->update(['payment_status_name' => 'expire']);
        DB::table('payments')->where('payment_status_name', 'CAPTURE')->update(['payment_status_name' => 'capture']);

        DB::table('payments')->where('payment_gateway_name', 'Midtrans')->update(['payment_gateway_name' => 'midtrans']);
    }
};
