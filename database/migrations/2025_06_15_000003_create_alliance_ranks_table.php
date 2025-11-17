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
        Schema::create('alliance_ranks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alliance_id');
            $table->string('name');
            $table->integer('level')->default(1); // 1 = membre, 2 = officier, 3 = vice-leader, 4 = leader
            $table->json('permissions'); // manage_members, manage_ranks, manage_bank, manage_wars, etc.
            $table->timestamps();
            
            $table->foreign('alliance_id')->references('id')->on('alliances')->onDelete('cascade');
            $table->index(['alliance_id', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alliance_ranks');
    }
};