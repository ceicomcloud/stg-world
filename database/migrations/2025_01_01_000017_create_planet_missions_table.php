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
        Schema::create('planet_missions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('from_planet_id');
            $table->unsignedBigInteger('to_planet_id')->nullable(); // null for colonization to empty position
            $table->integer('to_galaxy');
            $table->integer('to_system');
            $table->integer('to_position');
            $table->enum('mission_type', ['attack', 'colonize', 'spy', 'transport', 'basement', 'extract']);
            $table->json('ships')->nullable(); // ships sent for the mission
            $table->json('resources')->nullable(); // resources for transport missions
            // Use explicit defaults to avoid MySQL invalid default for TIMESTAMP
            $table->timestamp('departure_time')->useCurrent();
            $table->timestamp('arrival_time')->nullable();
            $table->timestamp('return_time')->nullable();
            $table->enum('status', ['pending', 'traveling', 'arrived', 'returning', 'completed', 'cancelled', 'collecting'])->default('pending');
            $table->json('result')->nullable(); // mission result data
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('from_planet_id')->references('id')->on('planets')->onDelete('cascade');
            $table->foreign('to_planet_id')->references('id')->on('planets')->onDelete('cascade');

            $table->index(['user_id', 'status']);
            $table->index(['arrival_time', 'status']);
            $table->index(['return_time', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planet_missions');
    }
};