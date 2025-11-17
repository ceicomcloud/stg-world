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
        Schema::create('template_build_requireds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('build_id')->constrained('template_builds')->onDelete('cascade');
            $table->foreignId('required_build_id')->constrained('template_builds')->onDelete('cascade');
            $table->integer('required_level')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['build_id', 'is_active']);
            $table->index(['required_build_id', 'required_level']);
            $table->unique(['build_id', 'required_build_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_build_requireds');
    }
};