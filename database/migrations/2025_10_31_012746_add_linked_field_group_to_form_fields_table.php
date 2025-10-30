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
            $table->string('linked_field_group', 100)
                  ->nullable()
                  ->after('field_options_json')
                  ->comment('Group name for linked select fields. Fields with same group share exclusive options.');

            $table->index(['form_version_id', 'linked_field_group'], 'idx_linked_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->dropIndex('idx_linked_group');
            $table->dropColumn('linked_field_group');
        });
    }
};
