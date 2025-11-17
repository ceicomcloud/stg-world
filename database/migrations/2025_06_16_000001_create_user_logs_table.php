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
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('action_type', 50); // connexion, resource_spend, building_purchase, private_message, etc.
            $table->string('action_category', 30); // auth, resource, building, message, etc.
            $table->text('description'); // Description détaillée de l'action
            $table->json('metadata')->nullable(); // Données supplémentaires (montants, IDs, etc.)
            $table->string('ip_address', 45)->nullable(); // Support IPv4 et IPv6
            $table->string('user_agent')->nullable();
            $table->unsignedBigInteger('planet_id')->nullable(); // Planète concernée si applicable
            $table->unsignedBigInteger('target_user_id')->nullable(); // Utilisateur cible si applicable
            $table->enum('severity', ['info', 'warning', 'error', 'critical'])->default('info');
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['user_id', 'created_at'], 'ul_user_date_idx');
            $table->index(['action_type', 'created_at'], 'ul_action_date_idx');
            $table->index(['action_category', 'created_at'], 'ul_category_date_idx');
            $table->index('created_at', 'ul_created_at_idx');
            $table->index(['user_id', 'action_type'], 'ul_user_action_idx');
            
            // Clés étrangères
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('planet_id')->references('id')->on('planets')->onDelete('set null');
            $table->foreign('target_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_logs');
    }
};