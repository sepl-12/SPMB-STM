<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->string('payment_gateway_name', 30);
            $table->string('merchant_order_code', 100);
            $table->decimal('paid_amount_total', 12, 2);
            $table->string('currency_code', 10);
            $table->string('payment_method_name', 50);
            $table->string('payment_status_name', 20);
            $table->dateTime('status_updated_datetime');
            $table->json('gateway_payload_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
