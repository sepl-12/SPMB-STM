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
        Schema::table('payments', function (Blueprint $table) {
            $table->softDeletes(); // adds deleted_at column
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('deletion_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['deleted_by', 'deletion_reason']);
        });
    }
};
