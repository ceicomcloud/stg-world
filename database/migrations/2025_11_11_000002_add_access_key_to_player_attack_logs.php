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
            $table->string('access_key')->nullable()->unique()->after('id');
            // Optionnel: si besoin d'un stockage JSON complet du rapport
            // $table->json('report_data')->nullable()->after('resources_pillaged');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_attack_logs', function (Blueprint $table) {
            if (Schema::hasColumn('player_attack_logs', 'access_key')) {
                $table->dropUnique(['access_key']);
                $table->dropColumn('access_key');
            }
            // if (Schema::hasColumn('player_attack_logs', 'report_data')) {
            //     $table->dropColumn('report_data');
            // }
        });
    }
};