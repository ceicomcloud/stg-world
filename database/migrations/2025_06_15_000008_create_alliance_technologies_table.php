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
        Schema::create('alliance_technologies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alliance_id');
            $table->string('technology_type'); // 'members' ou 'bank'
            $table->integer('level')->default(0);
            $table->integer('max_level')->default(15);
            $table->timestamps();
            
            $table->foreign('alliance_id')->references('id')->on('alliances')->onDelete('cascade');
            $table->unique(['alliance_id', 'technology_type']);
            $table->index('alliance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alliance_technologies');
    }
};