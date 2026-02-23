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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 5)->default('SAR');
            $table->enum('payment_method', ['Stripe', 'Paypal', 'Tap']);
            $table->string('transaction_id')->unique();
            $table->enum('status', ['due', 'paid', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
