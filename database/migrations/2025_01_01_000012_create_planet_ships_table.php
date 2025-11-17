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
        Schema::create('planet_ships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planet_id')->constrained('planets')->onDelete('cascade');
            $table->foreignId('ship_id')->constrained('template_builds')->onDelete('cascade');
            $table->bigInteger('quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['planet_id', 'is_active']);
            $table->index(['planet_id', 'ship_id']);
            $table->index('ship_id');
            $table->unique(['planet_id', 'ship_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planet_ships');
    }
};