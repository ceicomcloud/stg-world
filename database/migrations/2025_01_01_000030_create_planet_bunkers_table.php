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
        Schema::create('planet_bunkers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planet_id')->constrained('planets')->onDelete('cascade');
            $table->foreignId('resource_id')->constrained('template_resources')->onDelete('cascade');
            $table->bigInteger('stored_amount')->default(0);
            $table->bigInteger('max_storage')->default(1000);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_update')->nullable();
            $table->timestamps();
            
            $table->index(['planet_id', 'is_active']);
            $table->index(['planet_id', 'resource_id']);
            $table->index('resource_id');
            $table->index('last_update');
            $table->unique(['planet_id', 'resource_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planet_bunkers');
    }
};