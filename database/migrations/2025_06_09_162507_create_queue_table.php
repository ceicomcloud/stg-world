<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planet_id')->nullable()->constrained('planets')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('type'); // 'building', 'unit', 'defense', 'technology', 'ship'
            $table->foreignId('item_id'); // ID de l'élément à construire (building_id, unit_id, etc.)
            $table->integer('level')->default(1); // Niveau cible pour les bâtiments/technologies
            $table->integer('quantity')->default(1); // Quantité pour unités/défenses/vaisseaux
            // TIMESTAMP columns need explicit defaults or nullability in MySQL strict
            $table->timestamp('start_time')->useCurrent();
            $table->timestamp('end_time')->nullable();
            $table->integer('duration'); // Durée en secondes
            $table->json('cost'); // Coût des ressources au format JSON
            $table->boolean('is_active')->default(true);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->integer('position')->default(0); // Position dans la file d'attente
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['planet_id', 'is_active', 'position']);
            $table->index(['user_id', 'is_active', 'position']);
            $table->index(['planet_id', 'type']);
            $table->index(['user_id', 'type']);
            $table->index(['end_time', 'is_completed']);
            

        });
        
        // Ajouter la contrainte après la création de la table
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
