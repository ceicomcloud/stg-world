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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('alliance_id')->nullable()->after('actual_planet_id');
            $table->foreign('alliance_id')->references('id')->on('alliances')->onDelete('set null');
            $table->index('alliance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['alliance_id']);
            $table->dropIndex(['alliance_id']);
            $table->dropColumn('alliance_id');
        });
    }
};