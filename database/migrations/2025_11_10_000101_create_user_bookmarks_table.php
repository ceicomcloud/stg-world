<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('planet_id')->nullable();
            $table->string('label');
            $table->unsignedInteger('galaxy')->nullable();
            $table->unsignedInteger('system')->nullable();
            $table->unsignedInteger('position')->nullable();
            $table->string('mission_type')->default('attack');
            $table->string('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('planet_id')->references('id')->on('planets')->onDelete('set null');
            $table->unique(['user_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_bookmarks');
    }
};