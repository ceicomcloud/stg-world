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
        Schema::create('planets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('template_planet_id')->constrained('template_planets')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('used_fields')->default(0);
            $table->boolean('is_main_planet')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('shield_protection_active')->default(false);
            $table->timestamp('shield_protection_start')->nullable();
            $table->timestamp('shield_protection_end')->nullable();
            $table->timestamp('last_shield_activation')->nullable();
            $table->timestamp('last_update')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'is_main_planet']);
            $table->index('template_planet_id');
            $table->index('last_update');
            $table->index('shield_protection_active');
            $table->index('shield_protection_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planets');
    }
};