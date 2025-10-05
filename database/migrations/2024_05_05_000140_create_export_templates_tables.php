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
        Schema::create('export_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->string('template_name', 100);
            $table->text('template_description')->nullable();
            $table->boolean('is_default');
            $table->timestamps();
        });

        Schema::create('export_template_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('export_template_id')->constrained('export_templates')->cascadeOnDelete();
            $table->string('source_type_name', 30);
            $table->string('source_key_name', 150);
            $table->string('column_header_label', 150);
            $table->unsignedInteger('column_order_number');
            $table->string('column_format_hint', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_template_columns');
        Schema::dropIfExists('export_templates');
    }
};
