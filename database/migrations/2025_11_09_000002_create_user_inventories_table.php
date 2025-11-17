<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('template_inventory_id');
            $table->integer('quantity')->default(0);
            $table->timestamp('acquired_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('template_inventory_id')->references('id')->on('template_inventories')->onDelete('cascade');
            $table->index(['user_id', 'template_inventory_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_inventories');
    }
};