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
        Schema::create('user_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->bigInteger('total_points')->default(0);
            $table->bigInteger('building_points')->default(0);
            $table->bigInteger('units_points')->default(0);
            $table->bigInteger('defense_points')->default(0);
            $table->bigInteger('ship_points')->default(0);
            $table->bigInteger('technology_points')->default(0);

            $table->bigInteger('earth_attack')->default(0);
            $table->bigInteger('earth_defense')->default(0);
            $table->integer('earth_attack_count')->default(0);
            $table->integer('earth_defense_count')->default(0);
            $table->integer('earth_loser_count')->default(0);
            
            $table->bigInteger('spatial_attack')->default(0);
            $table->bigInteger('spatial_defense')->default(0);
            $table->integer('spatial_attack_count')->default(0);
            $table->integer('spatial_defense_count')->default(0);
            $table->integer('spatial_loser_count')->default(0);

            $table->integer('current_rank')->nullable();
            $table->integer('previous_rank')->nullable();
            $table->integer('rank_change')->default(0);
            $table->date('last_rank_update')->nullable();

            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_stats');
    }
};
