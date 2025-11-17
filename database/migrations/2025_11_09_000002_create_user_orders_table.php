<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('package_key');
            $table->integer('gold_amount');
            $table->decimal('amount_eur', 8, 2);
            $table->string('status')->default('pending'); // pending, paid, failed, refunded
            $table->string('provider')->default('paypal');
            $table->string('provider_order_id')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'user_orders_user_created_idx');
            $table->index(['status', 'created_at'], 'user_orders_status_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_orders');
    }
};