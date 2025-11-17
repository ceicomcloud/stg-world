<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('planet_equips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('planet_id');
            $table->string('label');
            $table->unsignedInteger('team_index')->nullable();
            $table->string('notes')->nullable();
            $table->json('payload')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('planet_id')->references('id')->on('planets')->onDelete('cascade');
            $table->unique(['planet_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planet_equips');
    }
};