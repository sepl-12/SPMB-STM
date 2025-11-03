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
        Schema::table('form_fields', function (Blueprint $table) {
            // Mengubah field_help_text dari varchar(255) menjadi text untuk menghilangkan limit panjang
            $table->text('field_help_text')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            // Mengembalikan field_help_text ke varchar(255) jika rollback diperlukan
            $table->string('field_help_text', 255)->nullable()->change();
        });
    }
};
