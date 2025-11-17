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
        Schema::create('planet_bunker_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planet_id')->constrained('planets')->onDelete('cascade');
            $table->foreignId('bunker_id')->constrained('planet_bunkers')->onDelete('cascade');
            $table->foreignId('resource_id')->constrained('template_resources')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('transaction_type', ['store', 'retrieve'])->comment('Type de transaction: stockage ou récupération');
            $table->bigInteger('amount')->comment('Quantité de ressources transférées');
            $table->bigInteger('bunker_amount_before')->comment('Quantité dans le bunker avant la transaction');
            $table->bigInteger('bunker_amount_after')->comment('Quantité dans le bunker après la transaction');
            $table->bigInteger('planet_amount_before')->comment('Quantité sur la planète avant la transaction');
            $table->bigInteger('planet_amount_after')->comment('Quantité sur la planète après la transaction');
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['planet_id', 'created_at'], 'pbt_planet_date_idx');
            $table->index(['bunker_id', 'created_at'], 'pbt_bunker_date_idx');
            $table->index(['resource_id', 'created_at'], 'pbt_resource_date_idx');
            $table->index(['user_id', 'created_at'], 'pbt_user_date_idx');
            $table->index(['transaction_type', 'created_at'], 'pbt_type_date_idx');
            $table->index('created_at', 'pbt_created_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planet_bunker_transactions');
    }
};