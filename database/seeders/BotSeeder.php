<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetResource;
use App\Models\Template\TemplatePlanet;
use App\Models\Template\TemplateResource;
use App\Models\Server\ServerConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BotSeeder extends Seeder
{
    /**
     * Create a bot user that owns all non-colonizable planets.
     */
    public function run(): void
    {
        // Create the bot user
        $botUser = User::create([
            'name' => 'Bot Système',
            'email' => 'bot@system.local',
            'password' => Hash::make(Str::random(32)),
            'role' => 'bot', // Custom role for the bot
            'experience' => ['level' => 1, 'actual' => 0],
            'research_points' => 0,
            'is_active' => true,
        ]);

        $this->command->info('Bot user created successfully.');

        // Get all non-colonizable template planets
        $nonColonizablePlanets = TemplatePlanet::where('is_colonizable', false)
            ->where('is_active', true)
            ->get();

        $this->command->info("Found {$nonColonizablePlanets->count()} non-colonizable planets.");

        // Create planets for the bot user based on non-colonizable template planets
        $createdCount = 0;
        foreach ($nonColonizablePlanets as $templatePlanet) {
            // Create a planet for the bot user
            $planet = Planet::create([
                'user_id' => $botUser->id,
                'template_planet_id' => $templatePlanet->id,
                'name' => $templatePlanet->name,
                'description' => "Cette {$templatePlanet->type} est contrôlée par le système.",
                'used_fields' => 0,
                'is_main_planet' => false,
                'last_update' => now(),
                'is_active' => true
            ]);

            $this->createInitialResources($planet);

            // Mark the template planet as occupied
            $templatePlanet->markAsOccupied();

            $createdCount++;
        }

        // Set the first planet as the main planet for the bot
        if ($createdCount > 0) {
            $firstPlanet = Planet::where('user_id', $botUser->id)->first();
            if ($firstPlanet) {
                $botUser->update([
                    'main_planet_id' => $firstPlanet->id,
                    'actual_planet_id' => $firstPlanet->id,
                ]);
            }
        }

        $this->command->info("Created {$createdCount} planets for the bot user.");
    }

    private function createInitialResources(Planet $planet)
    {
        // Récupérer les ressources de départ depuis la configuration
        $startingResources = ServerConfig::getStartingResources();
        
        // Récupérer les templates de ressources
        $resources = TemplateResource::whereIn('name', ['metal', 'crystal', 'deuterium'])->get()->keyBy('name');
        
        foreach ($startingResources as $resourceName => $amount) {
            if (isset($resources[$resourceName])) {
                PlanetResource::create([
                    'planet_id' => $planet->id,
                    'resource_id' => $resources[$resourceName]->id,
                    'current_amount' => $amount,
                    'max_storage' => $resources[$resourceName]->base_storage ?? 10000,
                    'production_rate' => $resources[$resourceName]->base_production ?? 0,
                    'last_update' => now(),
                    'is_active' => true
                ]);
            }
        }
    }
}