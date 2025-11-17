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
        Schema::create('alliances', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('tag', 10)->unique();
            $table->text('external_description')->nullable();
            $table->text('internal_description')->nullable();
            $table->string('logo')->nullable();
            $table->unsignedBigInteger('leader_id');
            $table->integer('max_members')->default(50);
            $table->boolean('open_recruitment')->default(true);
            $table->bigInteger('deuterium_bank')->default(0);
            $table->timestamps();
            
            $table->foreign('leader_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['name', 'tag']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alliances');
    }
};