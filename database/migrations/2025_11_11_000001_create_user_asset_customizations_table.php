<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_asset_customizations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('template_build_id');
            $table->string('display_name')->nullable();
            $table->string('icon_path')->nullable(); // stored path on public disk
            $table->string('status')->default('approved'); // approved|pending|rejected (for future moderation)
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('template_build_id')->references('id')->on('template_builds')->onDelete('cascade');
            $table->unique(['user_id', 'template_build_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_asset_customizations');
    }
};