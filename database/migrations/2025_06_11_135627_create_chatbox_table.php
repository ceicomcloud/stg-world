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
        Schema::create('chatbox', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->text('message');
            $table->string('channel')->default('general'); // Canal de chat (général, alliance, etc.)
            $table->boolean('is_system_message')->default(false); // Messages système
            $table->timestamp('deleted_at')->nullable(); // Soft delete
            $table->timestamps();
            
            $table->index(['channel', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbox');
    }
};
