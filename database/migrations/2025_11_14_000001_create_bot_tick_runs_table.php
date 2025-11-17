<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bot_tick_runs', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('running'); // running, completed, failed
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('planets_processed')->default(0);
            $table->longText('resources_generated_json')->nullable();
            $table->longText('resources_spent_json')->nullable();
            $table->string('details_path')->nullable(); // storage path for JSON details
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_tick_runs');
    }
};