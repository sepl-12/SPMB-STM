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
        Schema::create('form_previews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->index();
            $table->foreignId('form_version_id')->constrained()->onDelete('cascade');
            $table->json('preview_data');
            $table->integer('step_index')->default(0);
            $table->timestamp('previewed_at')->nullable();
            $table->boolean('converted_to_submission')->default(false);
            $table->timestamps();

            $table->index(['session_id', 'applicant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_previews');
    }
};
