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
     * Add is_system_field column to protect critical fields
     * that are mapped to applicants table columns.
     */
    public function up(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->boolean('is_system_field')->default(false)->after('is_archived');
        });

        // Mark system required fields
        $systemFields = [
            'nama_lengkap',
            'full_name', 
            'nisn',
            'no_hp',
            'phone',
            'telepon',
            'email',
            'email_address',
            'jurusan',
            'major',
            'program_studi',
        ];

        DB::table('form_fields')
            ->whereIn('field_key', $systemFields)
            ->update(['is_system_field' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->dropColumn('is_system_field');
        });
    }
};
