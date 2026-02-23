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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->nullable()->constrained('staffs')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('customer_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->dateTime('date_time');
            $table->enum('status', ['pending', 'confirm', 'complete', 'cancel', 'rescheduled'])
                ->default('pending');
            $table->text('special_note')->nullable();
            $table->string('booking_by');
            $table->tinyInteger('payment_method')
                ->nullable()
                ->comment('credit_card=0, paypal=1, pay_at_store=2, cash=3');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
