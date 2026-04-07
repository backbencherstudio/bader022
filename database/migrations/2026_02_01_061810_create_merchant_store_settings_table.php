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
        Schema::create('merchant_store_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('store_name')->nullable();
            $table->string('business_logo')->nullable();
            $table->enum('business_category', ['salon_beauty','home_services','health','fitness_pro_gym','others',])->nullable();
            $table->text('business_address')->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('time_zone', 100)->default('Asia/Riyadh');
            $table->string('currency', 10)->default('SAR');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_store_settings');
    }
};
