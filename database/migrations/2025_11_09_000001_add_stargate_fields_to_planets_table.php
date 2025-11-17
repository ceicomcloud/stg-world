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
        Schema::table('planets', function (Blueprint $table) {
            $table->boolean('stargate_active')->default(false)->after('shield_protection_active');
            $table->timestamp('last_stargate_toggle')->nullable()->after('last_shield_activation');

            $table->index('stargate_active');
            $table->index('last_stargate_toggle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planets', function (Blueprint $table) {
            $table->dropIndex(['stargate_active']);
            $table->dropIndex(['last_stargate_toggle']);
            $table->dropColumn(['stargate_active', 'last_stargate_toggle']);
        });
    }
};