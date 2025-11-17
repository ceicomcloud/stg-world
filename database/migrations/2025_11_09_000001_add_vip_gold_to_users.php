<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'gold_balance')) {
                $table->unsignedBigInteger('gold_balance')->default(0)->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'vip_active')) {
                $table->boolean('vip_active')->default(false)->after('gold_balance');
            }
            if (!Schema::hasColumn('users', 'vip_until')) {
                $table->timestamp('vip_until')->nullable()->after('vip_active');
            }
            if (!Schema::hasColumn('users', 'vip_badge_enabled')) {
                $table->boolean('vip_badge_enabled')->default(true)->after('vip_until');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'vip_badge_enabled')) {
                $table->dropColumn('vip_badge_enabled');
            }
            if (Schema::hasColumn('users', 'vip_until')) {
                $table->dropColumn('vip_until');
            }
            if (Schema::hasColumn('users', 'vip_active')) {
                $table->dropColumn('vip_active');
            }
            if (Schema::hasColumn('users', 'gold_balance')) {
                $table->dropColumn('gold_balance');
            }
        });
    }
};