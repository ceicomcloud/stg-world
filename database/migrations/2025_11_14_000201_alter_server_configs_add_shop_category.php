<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter 'shop' à l'ENUM de la colonne category
        DB::statement("ALTER TABLE server_configs MODIFY category ENUM('general','production','storage','research','building','combat','fleet','planet','user','economy','shop') NOT NULL DEFAULT 'general'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Retirer 'shop' de l'ENUM (rollback)
        DB::statement("ALTER TABLE server_configs MODIFY category ENUM('general','production','storage','research','building','combat','fleet','planet','user','economy') NOT NULL DEFAULT 'general'");
    }
};