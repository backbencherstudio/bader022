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
        Schema::create('why_choose_us', function (Blueprint $table) {
            $table->id();
            $table->string('section_title')->nullable();
            $table->string('section_subtitle')->nullable();

            $table->string('feature_one_image')->nullable();
            $table->string('feature_one_title')->nullable();
            $table->text('feature_one_des')->nullable();

            $table->string('feature_two_image')->nullable();
            $table->string('feature_two_title')->nullable();
            $table->text('feature_two_des')->nullable();

            $table->string('feature_three_image')->nullable();
            $table->string('feature_three_title')->nullable();
            $table->text('feature_three_des')->nullable();

            $table->string('background_color')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('why_choose_us');
    }
};
