<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('planet_equips', function (Blueprint $table) {
            // Ajouter la catégorie d'équipe: 'earth' (terrestre) ou 'spatial'
            $table->string('category')->default('earth')->after('planet_id');

            // Rendre team_index non nul pour mieux gérer l'unicité
            $table->unsignedInteger('team_index')->nullable(false)->change();

            // Contrainte d'unicité par planète et index d'équipe
            $table->unique(['planet_id', 'team_index']);
        });
    }

    public function down(): void
    {
        Schema::table('planet_equips', function (Blueprint $table) {
            // Supprimer la contrainte unique et la colonne
            $table->dropUnique(['planet_id', 'team_index']);
            $table->dropColumn('category');

            // Revenir à nullable si nécessaire
            $table->unsignedInteger('team_index')->nullable()->change();
        });
    }
};