<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'exploring' to status enum
        DB::statement("ALTER TABLE planet_missions MODIFY status ENUM('pending','traveling','arrived','returning','completed','cancelled','collecting','exploring') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum without 'exploring'
        DB::statement("ALTER TABLE planet_missions MODIFY status ENUM('pending','traveling','arrived','returning','completed','cancelled','collecting') NOT NULL DEFAULT 'pending'");
    }
};