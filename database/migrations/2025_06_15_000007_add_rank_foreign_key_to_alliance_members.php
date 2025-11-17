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
        Schema::table('alliance_members', function (Blueprint $table) {
            $table->foreign('rank_id')->references('id')->on('alliance_ranks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alliance_members', function (Blueprint $table) {
            $table->dropForeign(['rank_id']);
        });
    }
};