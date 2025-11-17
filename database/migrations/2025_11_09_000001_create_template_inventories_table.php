<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('template_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('type'); // e.g., consumable, pack, booster, cosmetic
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('rarity')->default('common'); // common, rare, epic, legendary
            $table->string('effect_type'); // e.g., add_resources, add_units, time_reduction, vip_extend, production_boost, cosmetic
            $table->integer('effect_value')->default(0); // numeric intensity or amount
            $table->json('effect_meta')->nullable(); // extra data like resource_key, unit_key, duration, scope
            $table->integer('duration_seconds')->nullable();
            $table->boolean('usable')->default(true);
            $table->boolean('stackable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_inventories');
    }
};