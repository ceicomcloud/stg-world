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
        Schema::create('planet_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planet_id')->constrained('planets')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('template_builds')->onDelete('cascade');
            $table->bigInteger('quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['planet_id', 'is_active']);
            $table->index(['planet_id', 'unit_id']);
            $table->index('unit_id');
            $table->unique(['planet_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planet_units');
    }
};