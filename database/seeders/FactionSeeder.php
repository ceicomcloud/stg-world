<?php

namespace Database\Seeders;

use App\Models\Faction;
use Illuminate\Database\Seeder;

class FactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $factions = [
            [
                'name' => 'Tau\'ri',
                'slug' => 'tauri',
                'icon' => 'tauri.svg',
                'color_code' => '#3498db',
                'description' => 'Les Tau\'ri, également connus sous le nom de Terriens, sont les habitants de la Terre. Ils sont connus pour leur adaptabilité, leur ingéniosité et leur détermination face à l\'adversité.',
                'bonuses' => [
                    'building_speed' => 10, // +10% vitesse de construction
                    'defense_power' => 5,   // +5% puissance de défense
                ],
                'sort_order' => 1,
            ],
            [
                'name' => 'Goa\'uld',
                'slug' => 'goauld',
                'icon' => 'goauld.svg',
                'color_code' => '#e74c3c',
                'description' => 'Les Goa\'uld sont une race de parasites serpentiformes qui prennent le contrôle d\'hôtes humains. Ils sont connus pour leur technologie avancée et leur soif de pouvoir.',
                'bonuses' => [
                    'attack_power' => 10,    // +10% puissance d\'attaque
                    'ship_capacity' => 5,   // +5% capacité des vaisseaux
                ],
                'sort_order' => 2,
            ],
            [
                'name' => 'Asgard',
                'slug' => 'asgard',
                'icon' => 'asgard.svg',
                'color_code' => '#9b59b6',
                'description' => 'Les Asgard sont une race humanoïde technologiquement avancée. Ils sont connus pour leur intelligence supérieure et leur technologie de pointe.',
                'bonuses' => [
                    'technology_cost' => -10, // -10% coût des technologies
                    'ship_speed' => 5,       // +5% vitesse des vaisseaux
                ],
                'sort_order' => 3,
            ],
            [
                'name' => 'Wraith',
                'slug' => 'wraith',
                'icon' => 'wraith.svg',
                'color_code' => '#2ecc71',
                'description' => 'Les Wraith sont une espèce humanoïde qui se nourrit de l\'énergie vitale des humains. Ils sont connus pour leur régénération rapide et leur technologie organique.',
                'bonuses' => [
                    'resource_production' => 5, // +5% production de ressources
                    'building_cost' => -10, // -10% coût des constructions
                ],
                'is_active' => 0,
                'sort_order' => 4,
            ],
        ];

        foreach ($factions as $faction) {
            Faction::create($faction);
        }
    }
}