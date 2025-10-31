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
        Schema::create('exam_card_field_configs', function (Blueprint $table) {
            $table->id();
            $table->string('field_key')->unique()->comment('Primary key for the field');
            $table->json('field_aliases')->nullable()->comment('Array of alternative field keys');
            $table->string('label')->comment('Display label for the field');
            $table->decimal('position_left', 8, 2)->comment('Horizontal position in mm');
            $table->decimal('position_top', 8, 2)->comment('Vertical position in mm');
            $table->decimal('width', 8, 2)->nullable()->comment('Field width in mm');
            $table->decimal('height', 8, 2)->nullable()->comment('Field height in mm');
            $table->enum('field_type', ['text', 'image', 'signature'])->default('text')->comment('Type of field');
            $table->decimal('font_size', 5, 2)->default(12.5)->comment('Font size in pt for text fields');
            $table->boolean('is_enabled')->default(true)->comment('Whether the field is shown on exam card');
            $table->integer('order')->default(0)->comment('Rendering order');
            $table->string('fallback_value')->nullable()->comment('Default value if field is empty');
            $table->boolean('is_required')->default(false)->comment('Whether the field must have a value');
            $table->timestamps();

            // Indexes for performance
            $table->index('field_key');
            $table->index('is_enabled');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_card_field_configs');
    }
};
