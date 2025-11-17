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
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('buyer_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_planet_id')->constrained('planets')->onDelete('cascade');
            $table->foreignId('buyer_planet_id')->nullable()->constrained('planets')->onDelete('cascade');
            
            // Ressource offerte
            $table->foreignId('offered_resource_id')->constrained('template_resources')->onDelete('cascade');
            $table->integer('offered_amount');
            
            // Ressource demandée
            $table->foreignId('requested_resource_id')->constrained('template_resources')->onDelete('cascade');
            $table->integer('requested_amount');
            
            // Statut de l'échange
            $table->enum('status', ['pending', 'accepted', 'cancelled', 'completed'])->default('pending');
            
            // Timestamps
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['status', 'expires_at']);
            $table->index(['seller_id', 'status']);
            $table->index(['offered_resource_id', 'requested_resource_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};