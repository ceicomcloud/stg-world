<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Badge system
        $this->call(BadgeSeeder::class);

        // Server configuration
        $this->call(ServerConfigSeeder::class);
        
        // Generate galaxies based on server config
        $this->call(GalaxySeeder::class);
        
        // Create bot user and assign non-colonizable planets
        $this->call(BotSeeder::class);
        
        // Game template data (must be seeded first)
        $this->call(TemplateResourceSeeder::class);
        $this->call(TemplateBuildSeeder::class);
        $this->call(TemplateBuildCostSeeder::class);
        $this->call(TechnologyResearchCostSeeder::class);
        $this->call(TemplateBuildRequiredSeeder::class);
        $this->call(TemplateBuildAdvantageSeeder::class);
        $this->call(TemplateBuildDisadvantageSeeder::class);

        // Inventory items templates
        $this->call(TemplateInventorySeeder::class);

        // Generate forum
        $this->call(ForumSeeder::class);
        
        $this->call([FactionSeeder::class]);
    }
}
