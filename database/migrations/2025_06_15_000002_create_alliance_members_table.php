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
        Schema::create('alliance_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alliance_id')->constrained('alliances')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('rank_id')->nullable();
            $table->timestamp('joined_at');
            $table->bigInteger('contributed_deuterium')->default(0);
            $table->timestamps();
            
            $table->unique(['alliance_id', 'user_id']);
            $table->index('rank_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alliance_members');
    }
};