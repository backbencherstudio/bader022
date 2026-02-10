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
        Schema::create('global_settings', function (Blueprint $table) {
            $table->id();
            // Branding
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->unique();
            $table->string('branding_logo')->nullable();
            $table->enum('logo_position', ['left', 'center', 'right'])->default('left')->nullable();
            $table->string('logo_size')->nullable();

            // Design system
            $table->json('color_system')->nullable();
            $table->json('typography')->nullable();
            $table->string('body_text_size')->nullable();
            $table->enum('font_family', [
                'Inter',
                'Roboto',
                'Poppins',
                'Montserrat',
                'DM Sans',
            ])->default('Inter');

            $table->string('section_spacing')->nullable();

            // Website info
            $table->string('website_name')->nullable();

            // Footer
            $table->text('footer_des')->nullable();
            $table->string('footer_background')->nullable();
            $table->string('footer_text_color')->nullable();
            $table->json('social_links')->nullable();

            // Menu sections & URLs
            $table->string('home')->nullable();
            $table->string('home_url')->nullable();

            $table->string('about')->nullable();
            $table->string('about_url')->nullable();

            $table->string('why_choose_us')->nullable();
            $table->string('why_choose_us_url')->nullable();

            $table->string('service')->nullable();
            $table->string('service_url')->nullable();

            $table->string('contact_us')->nullable();
            $table->string('contact_url')->nullable();

            $table->string('privacy_policy')->nullable();
            $table->string('privacy_policy_url')->nullable();

            $table->string('terms_condition')->nullable();
            $table->string('terms_condition_url')->nullable();

            $table->json('contact_info')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('country')->nullable();

            // Control
            $table->tinyInteger('turn_off')
                ->default(1)
                ->comment('1 = ON, 0 = OFF');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_settings');
    }
};
