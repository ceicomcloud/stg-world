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
        Schema::create('template_planets', function (Blueprint $table) {
            $table->id();
            $table->integer('galaxy');
            $table->integer('system');
            $table->integer('position');
            $table->string('name')->nullable();
            $table->enum('type', ['planet', 'moon', 'asteroid', 'debris']);
            $table->enum('size', ['tiny', 'small', 'medium', 'large', 'huge']);
            $table->integer('diameter')->default(0);
            $table->integer('min_temperature')->default(-50);
            $table->integer('max_temperature')->default(50);
            $table->integer('fields')->default(163); // Available building fields
            $table->decimal('metal_bonus', 5, 2)->default(1.00);
            $table->decimal('crystal_bonus', 5, 2)->default(1.00);
            $table->decimal('deuterium_bonus', 5, 2)->default(1.00);
            $table->decimal('energy_bonus', 5, 2)->default(1.00);
            $table->boolean('is_colonizable')->default(true);
            $table->boolean('is_occupied')->default(false);
            $table->boolean('is_available')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['galaxy', 'system', 'position']);
            $table->index(['galaxy', 'system']);
            $table->index(['type', 'is_colonizable', 'is_occupied']);
            $table->index(['is_available', 'is_active']);
            $table->index('size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_planets');
    }
};