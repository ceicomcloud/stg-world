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
        Schema::create('player_attack_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attacker_user_id');
            $table->unsignedBigInteger('defender_user_id');
            $table->unsignedBigInteger('attacker_planet_id');
            $table->unsignedBigInteger('defender_planet_id');
            $table->enum('attack_type', ['earth', 'spatial']);
            $table->json('attacker_units')->nullable(); // Unités/vaisseaux utilisés
            $table->json('combat_result')->nullable(); // Résultat du combat
            $table->boolean('attacker_won')->default(false);
            $table->integer('points_gained')->default(0);
            $table->json('resources_pillaged')->nullable();
            $table->timestamp('attacked_at');
            $table->timestamps();
            
            // Index pour optimiser les requêtes de limitation quotidienne
            $table->index(['attacker_user_id', 'defender_user_id', 'attacked_at'], 'pal_attacker_defender_date_idx');
            $table->index(['defender_user_id', 'attacked_at'], 'pal_defender_date_idx');
            $table->index('attacked_at', 'pal_attacked_at_idx');
            
            // Clés étrangères
            $table->foreign('attacker_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('defender_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('attacker_planet_id')->references('id')->on('planets')->onDelete('cascade');
            $table->foreign('defender_planet_id')->references('id')->on('planets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_attack_logs');
    }
};