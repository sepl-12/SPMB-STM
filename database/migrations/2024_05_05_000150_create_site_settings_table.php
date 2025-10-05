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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('hero_title_text', 120);
            $table->text('hero_subtitle_text')->nullable();
            $table->string('hero_image_path', 255)->nullable();
            $table->longText('requirements_markdown')->nullable();
            $table->json('faq_items_json')->nullable();
            $table->string('cta_button_label', 50)->nullable();
            $table->string('cta_button_url', 255)->nullable();
            $table->json('timeline_items_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
