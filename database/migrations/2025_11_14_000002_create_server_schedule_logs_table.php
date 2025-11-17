<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('server_schedule_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id')->nullable();
            $table->string('type'); // 'apply'
            $table->timestamp('applied_at');
            $table->string('message')->nullable();
            $table->json('changes')->nullable();
            $table->timestamps();

            $table->index('applied_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_schedule_logs');
    }
};