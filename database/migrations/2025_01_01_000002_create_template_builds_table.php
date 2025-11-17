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
        Schema::create('template_builds', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->string('name'); // nom avec underscores pour les requêtes
            $table->string('label')->nullable(); // nom complet affiché
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->enum('type', ['building', 'technology', 'unit', 'defense', 'ship']);
            $table->enum('category', [
                'resource', 'energy', 'storage', 'technology', 'shipyard', 'defense',
                'espionage', 'combat', 'civil', 'military', 'special', 'shield', 'basic', 'facility'
            ])->nullable();
            $table->integer('base_build_time')->default(60); // seconds
            $table->decimal('build_time_multiplier', 8, 2)->default(2.00);
            $table->integer('max_level')->default(0); // 0 = unlimited
            $table->integer('fields_required')->default(1);
            $table->integer('life')->default(0);
            $table->integer('attack_power')->default(0);
            $table->integer('defense_power')->default(0);
            $table->integer('shield_power')->default(0);
            $table->integer('speed')->default(0);
            $table->integer('cargo_capacity')->default(0);
            $table->integer('fuel_consumption')->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['type', 'category', 'is_active']);
            $table->index('sort_order');
            $table->unique(['name', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_builds');
    }
};