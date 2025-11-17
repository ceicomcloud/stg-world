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
        Schema::create('template_build_disadvantages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('build_id')->constrained('template_builds')->onDelete('cascade');
            $table->foreignId('resource_id')->nullable()->constrained('template_resources')->onDelete('cascade');
            $table->enum('disadvantage_type', [
                'energy_consumption', 'maintenance_cost', 'production_penalty', 
                'storage_penalty', 'research_penalty', 'build_penalty', 
                'defense_penalty', 'attack_penalty', 'speed_penalty', 'resource_consumption'
            ]);
            $table->enum('target_type', [
                'resource', 'building', 'research', 'technology', 'unit', 'defense', 
                'ship', 'planet', 'global'
            ]);
            $table->decimal('base_value', 15, 2);
            $table->decimal('value_per_level', 15, 2)->default(0);
            $table->enum('calculation_type', ['additive', 'multiplicative', 'exponential'])->default('additive');
            $table->boolean('is_percentage')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['build_id', 'disadvantage_type', 'is_active'], 'tbd_build_disadv_type_active_idx');
            $table->index(['disadvantage_type', 'target_type'], 'tbd_disadv_target_type_idx');
            $table->index(['resource_id', 'is_active'], 'tbd_resource_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_build_disadvantages');
    }
};