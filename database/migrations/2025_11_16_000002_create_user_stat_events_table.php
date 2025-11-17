<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_stat_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            // Compteurs éphémères (reset à chaque fin d'event)
            $table->unsignedInteger('attaque_points')->default(0);
            $table->unsignedInteger('exploration_count')->default(0);
            $table->unsignedInteger('extraction_count')->default(0);
            $table->unsignedBigInteger('pillage_total')->default(0);
            $table->unsignedBigInteger('construction_spent')->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_stat_events');
    }
};