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
        Schema::create('mini_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('hero_title')->nullable();
            $table->string('hero_subtitle')->nullable();
            $table->text('hero_description')->nullable();
            $table->string('cta_button_text')->nullable();
            $table->string('cta_button_text_two')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('hero_overlay_color')->nullable();
            $table->string('about_title')->nullable();
            $table->string('about_hero_image')->nullable();
            $table->text('about_description')->nullable();
            $table->string('background_color')->nullable();
            $table->string('about_padding')->nullable();
            $table->string('cta_title')->nullable();
            $table->string('cta_subtitle')->nullable();
            $table->string('cta_image')->nullable();
            $table->string('cta_overlay_color')->nullable();
            $table->string('cta_padding')->nullable();
            $table->string('service_title')->nullable();
            $table->text('service_description')->nullable();
            $table->text('service_background')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mini_sites');
    }
};
