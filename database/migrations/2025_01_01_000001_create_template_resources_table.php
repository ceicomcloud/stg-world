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
        Schema::create('template_resources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->enum('type', ['basic', 'energy', 'special'])->default('basic');
            $table->integer('base_production')->default(0);
            $table->integer('base_storage')->default(10000);
            $table->decimal('trade_rate', 8, 2)->default(1.00);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_tradeable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_resources');
    }
};