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
        Schema::create('alliance_wars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attacker_alliance_id');
            $table->unsignedBigInteger('defender_alliance_id');
            $table->text('reason')->nullable();
            $table->enum('status', ['declared', 'active', 'ended'])->default('declared');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedBigInteger('declared_by');
            $table->unsignedBigInteger('ended_by')->nullable();
            $table->timestamps();
            
            $table->foreign('attacker_alliance_id')->references('id')->on('alliances')->onDelete('cascade');
            $table->foreign('defender_alliance_id')->references('id')->on('alliances')->onDelete('cascade');
            $table->foreign('declared_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('ended_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['attacker_alliance_id', 'defender_alliance_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alliance_wars');
    }
};