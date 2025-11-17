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
        Schema::table('player_attack_logs', function (Blueprint $table) {
            // Stockage du snapshot complet du rapport (unités, meta, rounds, puissances...)
            $table->json('report_data')->nullable()->after('combat_result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_attack_logs', function (Blueprint $table) {
            $table->dropColumn('report_data');
        });
    }
};