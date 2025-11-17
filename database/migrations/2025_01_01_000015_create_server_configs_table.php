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
        Schema::create('server_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->enum('type', ['integer', 'float', 'string', 'boolean', 'json'])->default('string');
            $table->text('description')->nullable();
            $table->enum('category', [
                'general', 'production', 'storage', 'research', 'building', 
                'combat', 'fleet', 'planet', 'user', 'economy'
            ])->default('general');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_configs');
    }
};