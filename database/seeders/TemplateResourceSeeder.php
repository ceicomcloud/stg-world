<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template\TemplateResource;

class TemplateResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resources = [
            [
                'name' => 'metal',
                'display_name' => 'Métal',
                'description' => 'Ressource de base utilisée pour la construction.',
                'icon' => 'metal.png',
                'type' => 'basic',
                'base_production' => 25,
                'base_storage' => 120000,
                'trade_rate' => 1.0,
                'sort_order' => 1,
                'is_tradeable' => true,
                'is_active' => true,
            ],
            [
                'name' => 'crystal',
                'display_name' => 'Cristal',
                'description' => 'Ressource précieuse pour les technologies.',
                'icon' => 'crystal.png',
                'type' => 'basic',
                'base_production' => 15,
                'base_storage' => 60000,
                'trade_rate' => 2.0,
                'sort_order' => 2,
                'is_tradeable' => true,
                'is_active' => true,
            ],
            [
                'name' => 'deuterium',
                'display_name' => 'Deutérium',
                'description' => 'Carburant pour les vaisseaux.',
                'icon' => 'deuterium.png',
                'type' => 'basic',
                'base_production' => 7,
                'base_storage' => 30000,
                'trade_rate' => 3.0,
                'sort_order' => 3,
                'is_tradeable' => true,
                'is_active' => true,
            ],
            [
                'name' => 'research_points',
                'display_name' => 'Points de Recherche',
                'description' => 'Points nécessaires pour développer les technologies.',
                'icon' => 'research_points.png',
                'type' => 'special',
                'base_production' => 0,
                'base_storage' => 999999999,
                'trade_rate' => 0.0,
                'sort_order' => 4,
                'is_tradeable' => false,
                'is_active' => true,
            ]
        ];

        foreach ($resources as $resource) {
            TemplateResource::create($resource);
        }
    }
}