<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('server_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'truce' | 'bonus'
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->json('payload')->nullable();
            $table->string('message')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_schedules');
    }
};