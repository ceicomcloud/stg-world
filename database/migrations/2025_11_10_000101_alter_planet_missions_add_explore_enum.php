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
        DB::statement("ALTER TABLE planet_missions MODIFY mission_type ENUM('attack','colonize','spy','transport','basement','extract','explore') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE planet_missions MODIFY mission_type ENUM('attack','colonize','spy','transport','basement','extract') NOT NULL");
    }
};