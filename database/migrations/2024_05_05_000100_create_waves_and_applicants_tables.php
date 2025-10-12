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
        Schema::create('waves', function (Blueprint $table) {
            $table->id();
            $table->string('wave_name', 50);
            $table->string('wave_code', 30)->unique();
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->integer('quota_limit')->nullable();
            $table->decimal('registration_fee_amount', 12, 2);
            $table->boolean('is_active');
            $table->timestamps();
        });

        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number', 30)->unique();
            $table->string('applicant_full_name', 150);
            $table->string('applicant_nisn', 20);
            $table->string('applicant_phone_number', 30);
            $table->string('applicant_email_address', 150);
            $table->string('chosen_major_name', 50);
            $table->foreignId('wave_id')->constrained('waves')->cascadeOnDelete();
            $table->string('payment_status', 20)->nullable();
            $table->dateTime('registered_datetime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicants');
        Schema::dropIfExists('waves');
    }
};
