<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('galactic_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('galaxy');
            $table->unsignedInteger('system');
            $table->unsignedInteger('position')->nullable(); // null => évènement affectant tout le système
            $table->string('key');
            $table->string('title');
            $table->string('severity')->default('medium');
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            // Utiliser dateTime (compatible avec strict mode MySQL, pas de default implicite requis)
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['galaxy', 'system']);
            $table->index(['position']);
            $table->index(['end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galactic_events');
    }
};