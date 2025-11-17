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
        Schema::create('template_build_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('build_id')->constrained('template_builds')->onDelete('cascade');
            $table->foreignId('resource_id')->constrained('template_resources')->onDelete('cascade');
            $table->integer('base_cost');
            $table->decimal('cost_multiplier', 8, 2)->default(2.00);
            $table->integer('level')->default(1); // For which level this cost applies
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['build_id', 'resource_id', 'level']);
            $table->index(['resource_id', 'is_active']);
            $table->unique(['build_id', 'resource_id', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_build_costs');
    }
};