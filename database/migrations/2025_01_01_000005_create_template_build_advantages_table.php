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
        Schema::create('template_build_advantages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('build_id')->constrained('template_builds')->onDelete('cascade');
            $table->foreignId('resource_id')->nullable()->constrained('template_resources')->onDelete('cascade');
            $table->enum('advantage_type', [
                'production_boost', 'storage_bonus', 'energy_production', 
                'research_speed', 'build_speed', 'defense_bonus', 
                'attack_bonus', 'shield_bonus', 'speed_bonus',
                'global_efficiency', 'command_efficiency', 'storage_capacity',
                'defense_boost', 'territory_expansion', 'energy_efficiency',
                'armor_boost', 'espionage_efficiency', 'movement_speed',
                'weapon_power', 'attack_range', 'stealth_boost',
                'production_speed', 'resource_efficiency', 'ultimate_boost',
                'bunker_boost','fleet_capacity'
            ]);
            $table->enum('target_type', [
                'resource', 'building', 'research', 'technology', 'unit', 'defense', 
                'ship', 'planet', 'global', 'ground_unit', 'drone', 'mission', 'fleet'
            ]);
            $table->decimal('base_value', 15, 2);
            $table->decimal('value_per_level', 15, 2)->default(0);
            $table->enum('calculation_type', ['additive', 'multiplicative', 'exponential'])->default('additive');
            $table->boolean('is_percentage')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['build_id', 'advantage_type', 'is_active'], 'tba_build_adv_type_active_idx');
            $table->index(['advantage_type', 'target_type'], 'tba_adv_target_type_idx');
            $table->index(['resource_id', 'is_active'], 'tba_resource_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_build_advantages');
    }
};