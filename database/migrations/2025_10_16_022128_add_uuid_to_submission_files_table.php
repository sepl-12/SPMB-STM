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
        Schema::table('submission_files', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
            $table->unique('uuid');
            $table->index('uuid');
        });

        // Generate UUID for existing records
        DB::table('submission_files')->whereNull('uuid')->chunkById(100, function ($files) {
            foreach ($files as $file) {
                DB::table('submission_files')
                    ->where('id', $file->id)
                    ->update(['uuid' => (string) Illuminate\Support\Str::uuid()]);
            }
        });

        // Make UUID non-nullable after populating
        Schema::table('submission_files', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_files', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropIndex(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
