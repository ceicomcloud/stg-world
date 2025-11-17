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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('icon')->nullable();
            $table->enum('type', ['niveau', 'expérience', 'recherche', 'accomplissement', 'spécial']);
            $table->enum('requirement_type', ['reach_level', 'total_experience', 'research_points', 'custom']);
            $table->integer('requirement_value');
            $table->enum('rarity', ['commun', 'peu commun', 'rare', 'épique', 'légendaire'])->default('commun');
            $table->integer('points_reward')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Index for performance
            $table->index(['type', 'requirement_type']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};