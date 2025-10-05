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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->foreignId('form_version_id')->constrained('form_versions')->cascadeOnDelete();
            $table->json('answers_json');
            $table->dateTime('submitted_datetime');
            $table->timestamps();
        });

        Schema::create('submission_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->foreignId('form_field_id')->constrained('form_fields')->cascadeOnDelete();
            $table->string('field_key', 100);
            $table->text('answer_value_text')->nullable();
            $table->decimal('answer_value_number', 18, 4)->nullable();
            $table->boolean('answer_value_boolean')->nullable();
            $table->date('answer_value_date')->nullable();
            $table->timestamps();
        });

        Schema::create('submission_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->foreignId('form_version_id')->constrained('form_versions')->cascadeOnDelete();
            $table->string('applicant_email_address', 150);
            $table->string('current_step_key', 50)->nullable();
            $table->json('answers_json')->nullable();
            $table->char('resume_token_uuid', 36)->unique();
            $table->dateTime('expires_datetime')->nullable();
            $table->timestamps();
        });

        Schema::create('submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->foreignId('form_field_id')->constrained('form_fields')->cascadeOnDelete();
            $table->string('stored_disk_name', 50);
            $table->string('stored_file_path', 255);
            $table->string('original_file_name', 255);
            $table->string('mime_type_name', 100);
            $table->unsignedBigInteger('file_size_bytes');
            $table->timestamp('uploaded_datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_files');
        Schema::dropIfExists('submission_answers');
        Schema::dropIfExists('submission_drafts');
        Schema::dropIfExists('submissions');
    }
};
