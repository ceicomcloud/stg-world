<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'last_activity')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('last_activity')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'last_activity')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['last_activity']);
                $table->dropColumn('last_activity');
            });
        }
    }
};