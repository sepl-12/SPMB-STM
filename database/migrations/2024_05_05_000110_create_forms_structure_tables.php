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
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('form_name', 100);
            $table->string('form_code', 50)->unique();
            $table->timestamps();
        });

        Schema::create('form_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->boolean('is_active');
            $table->timestamp('published_datetime')->nullable();
            $table->timestamps();
        });

        Schema::create('form_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_version_id')->constrained('form_versions')->cascadeOnDelete();
            $table->string('step_key', 50);
            $table->string('step_title', 120);
            $table->text('step_description')->nullable();
            $table->unsignedInteger('step_order_number');
            $table->boolean('is_visible_for_public');
            $table->timestamps();
        });

        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_version_id')->constrained('form_versions')->cascadeOnDelete();
            $table->foreignId('form_step_id')->constrained('form_steps')->cascadeOnDelete();
            $table->string('field_key', 100);
            $table->string('field_label', 255);
            $table->string('field_type', 30);
            $table->json('field_options_json')->nullable();
            $table->boolean('is_required');
            $table->boolean('is_filterable');
            $table->boolean('is_exportable');
            $table->boolean('is_archived');
            $table->string('field_placeholder_text', 255)->nullable();
            $table->string('field_help_text', 255)->nullable();
            $table->unsignedInteger('field_order_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('form_steps');
        Schema::dropIfExists('form_versions');
        Schema::dropIfExists('forms');
    }
};
