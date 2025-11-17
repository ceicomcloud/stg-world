<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_stats', function (Blueprint $table) {
            // Compteurs globaux (ne seront pas reset en fin d'event)
            $table->unsignedBigInteger('pillage')->default(0)->after('spatial_loser_count');
            $table->unsignedInteger('exploration_count')->default(0)->after('pillage');
            $table->unsignedInteger('extraction_count')->default(0)->after('exploration_count');
            $table->unsignedBigInteger('construction_spent')->default(0)->after('extraction_count');
        });
    }

    public function down(): void
    {
        Schema::table('user_stats', function (Blueprint $table) {
            $table->dropColumn(['pillage', 'exploration_count', 'extraction_count', 'construction_spent']);
        });
    }
};