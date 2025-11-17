<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Server\ServerConfig;

class ServerConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            // General Settings
            [
                'key' => 'server_name',
                'value' => 'Stargate Universe',
                'type' => 'string',
                'description' => 'Nom du serveur affiché aux joueurs',
                'category' => 'general',
            ],
            [
                'key' => 'server_speed',
                'value' => '1',
                'type' => 'integer',
                'description' => 'Vitesse générale du serveur (multiplicateur)',
                'category' => 'general',
            ],
            [
                'key' => 'server_timezone',
                'value' => 'Europe/Paris',
                'type' => 'string',
                'description' => 'Fuseau horaire du serveur',
                'category' => 'general',
            ],
            [
                'key' => 'server_language',
                'value' => 'fr',
                'type' => 'string',
                'description' => 'Langue par défaut du serveur',
                'category' => 'general',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Mode maintenance activé',
                'category' => 'general',
            ],
            [
                'key' => 'maintenance_message',
                'value' => 'Le serveur est en maintenance. Veuillez réessayer plus tard.',
                'type' => 'string',
                'description' => 'Message affiché en mode maintenance',
                'category' => 'general',
            ],
            
            // Production Settings
            [
                'key' => 'production_rate',
                'value' => '1.0',
                'type' => 'float',
                'description' => 'Taux de production des ressources',
                'category' => 'production',
            ],
            [
                'key' => 'energy_efficiency',
                'value' => '1.0',
                'type' => 'float',
                'description' => 'Efficacité énergétique globale',
                'category' => 'production',
            ],
            [
                'key' => 'low_energy_penalty',
                'value' => '0.5',
                'type' => 'float',
                'description' => 'Pénalité de production en cas d\'énergie insuffisante',
                'category' => 'production',
            ],
            
            // Storage Settings
            [
                'key' => 'storage_rate',
                'value' => '1.0',
                'type' => 'float',
                'description' => 'Multiplicateur de capacité de stockage',
                'category' => 'storage',
            ],
            [
                'key' => 'max_storage_overflow',
                'value' => '1.1',
                'type' => 'float',
                'description' => 'Dépassement maximum de stockage autorisé',
                'category' => 'storage',
            ],
            
            // Research Settings
            [
                'key' => 'research_speed',
                'value' => '1.0',
                'type' => 'float',
                'description' => 'Vitesse de recherche',
                'category' => 'research',
            ],
            [
                'key' => 'starting_research_points',
                'value' => '1000',
                'type' => 'integer',
                'description' => 'Points de recherche de départ',
                'category' => 'research',
            ],
            
            // Building Settings
            [
                'key' => 'building_speed',
                'value' => '1.0',
                'type' => 'float',
                'description' => 'Vitesse de construction des bâtiments',
                'category' => 'building',
            ],
            
            // Fleet Settings
            [
                'key' => 'fleet_speed',
                'value' => '1.0',
                'type' => 'float',
                'description' => 'Vitesse des flottes',
                'category' => 'fleet',
            ],
            [
                'key' => 'fleet_save_time_limit',
                'value' => '3600',
                'type' => 'integer',
                'description' => 'Temps limite pour la sauvegarde de flotte (secondes)',
                'category' => 'fleet',
            ],
            
            // Combat Settings
            [
                'key' => 'debris_field_percentage',
                'value' => '0.3',
                'type' => 'float',
                'description' => 'Pourcentage de débris après combat',
                'category' => 'combat',
            ],
            [
                'key' => 'combat_report_retention_days',
                'value' => '7',
                'type' => 'integer',
                'description' => 'Durée de conservation des rapports de combat (jours)',
                'category' => 'combat',
            ],
            [
                'key' => 'spy_band_enabled',
                'value' => true,
                'type' => 'boolean',
                'description' => 'Active la limitation par tranche pour les missions d\'espionnage',
                'category' => 'combat',
            ],
            [
                'key' => 'spy_band_percentage',
                'value' => 0.3,
                'type' => 'float',
                'description' => 'Pourcentage de bande autorisée autour des points du joueur pour l\'espionnage',
                'category' => 'combat',
            ],
            [
                'key' => 'spy_band_points_source',
                'value' => 'total_points',
                'type' => 'string',
                'description' => 'Source de points pour la bande d\'espionnage: total_points|earth_attack|spatial_attack',
                'category' => 'combat',
            ],
            [
                'key' => 'attack_band_enabled',
                'value' => true,
                'type' => 'boolean',
                'description' => 'Active la limitation par tranche pour les attaques',
                'category' => 'combat',
            ],
            [
                'key' => 'attack_band_percentage',
                'value' => 0.3,
                'type' => 'float',
                'description' => 'Pourcentage de bande autorisée autour des points du joueur pour les attaques',
                'category' => 'combat',
            ],
            [
                'key' => 'attack_band_points_source',
                'value' => 'total_points',
                'type' => 'string',
                'description' => 'Source de points pour la bande d\'attaque: total_points|earth_attack|spatial_attack',
                'category' => 'combat',
            ],
            
            // Planet Settings
            [
                'key' => 'max_planets_per_user',
                'value' => '9',
                'type' => 'integer',
                'description' => 'Nombre maximum de planètes par joueur',
                'category' => 'planet',
            ],
            [
                'key' => 'total_planets',
                'value' => '10000',
                'type' => 'integer',
                'description' => 'Nombre total de planètes dans l\'univers',
                'category' => 'planet',
            ],
            [
                'key' => 'galaxies',
                'value' => '1',
                'type' => 'integer',
                'description' => 'Nombre de galaxies',
                'category' => 'planet',
            ],
            [
                'key' => 'systems_per_galaxy',
                'value' => '1000',
                'type' => 'integer',
                'description' => 'Nombre de systèmes par galaxie',
                'category' => 'planet',
            ],
            [
                'key' => 'planets_per_system',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Nombre de planètes par système',
                'category' => 'planet',
            ],
            
            // User Settings
            [
                'key' => 'newbie_protection_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Protection des nouveaux joueurs activée',
                'category' => 'user',
            ],
            [
                'key' => 'newbie_protection_limit',
                'value' => '50000',
                'type' => 'integer',
                'description' => 'Limite de points pour la protection débutant',
                'category' => 'user',
            ],
            [
                'key' => 'vacation_mode_min_days',
                'value' => '2',
                'type' => 'integer',
                'description' => 'Durée minimale du mode vacances (jours)',
                'category' => 'user',
            ],
            [
                'key' => 'vacation_mode_max_days',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Durée maximale du mode vacances (jours)',
                'category' => 'user',
            ],
            
            // Economy Settings
            [
                'key' => 'trade_ratio_metal_crystal',
                'value' => '2.0',
                'type' => 'float',
                'description' => 'Ratio d\'échange métal/cristal',
                'category' => 'economy',
            ],
            [
                'key' => 'trade_ratio_metal_deuterium',
                'value' => '3.0',
                'type' => 'float',
                'description' => 'Ratio d\'échange métal/deutérium',
                'category' => 'economy',
            ],
            [
                'key' => 'trade_ratio_crystal_deuterium',
                'value' => '1.5',
                'type' => 'float',
                'description' => 'Ratio d\'échange cristal/deutérium',
                'category' => 'economy',
            ],
            
            // Starting Resources
            [
                'key' => 'starting_metal',
                'value' => '500',
                'type' => 'integer',
                'description' => 'Métal de départ pour les nouveaux joueurs',
                'category' => 'user',
            ],
            [
                'key' => 'starting_crystal',
                'value' => '500',
                'type' => 'integer',
                'description' => 'Cristal de départ pour les nouveaux joueurs',
                'category' => 'user',
            ],
            [
                'key' => 'starting_deuterium',
                'value' => '0',
                'type' => 'integer',
                'description' => 'Deutérium de départ pour les nouveaux joueurs',
                'category' => 'user',
            ],
            [
                'key' => 'report_problem_enabled',
                'value' => true,
                'type' => 'boolean',
                'description' => 'Activer le système de signalement de problèmes',
                'category' => 'general',
            ],
            [
                'key' => 'report_problem_webhook_url',
                'value' => 'https://discord.com/api/webhooks/1383873051300466688/uOI3YVIqFVE9R2UcT-jYPAnyrvIXDtVuZX4tzCgq8ixh4RdHr6La6K0AzZaBlqlM2hPD',
                'type' => 'string',
                'description' => 'URL du webhook Discord pour les signalements de problèmes',
                'category' => 'general',
            ],
            [
                'key' => 'daily_attack_limit_enabled',
                'value' => true,
                'type' => 'boolean',
                'description' => 'Active ou désactive la limitation du nombre d\'attaques quotidiennes par joueur',
                'category' => 'combat',
            ],
            [
                'key' => 'daily_attack_limit_per_player',
                'value' => 5,
                'type' => 'integer',
                'description' => 'Nombre maximum d\'attaques qu\'un joueur peut effectuer contre un autre joueur par jour',
                'category' => 'combat',
            ],
            [
                'key' => 'daily_quests_count_min',
                'value' => 4,
                'type' => 'integer',
                'description' => 'Nombre minimum de quêtes quotidiennes générées par jour',
                'category' => 'general',
            ],
            [
                'key' => 'daily_quests_count_max',
                'value' => 6,
                'type' => 'integer',
                'description' => 'Nombre maximum de quêtes quotidiennes générées par jour',
                'category' => 'general',
            ],

            // Truce Settings (serveur en trêve)
            [
                'key' => 'truce_enabled',
                'value' => false,
                'type' => 'boolean',
                'description' => 'Active la trêve serveur (désactive certaines actions)',
                'category' => 'general',
            ],
            [
                'key' => 'truce_block_earth_attack',
                'value' => true,
                'type' => 'boolean',
                'description' => 'En trêve: interdire les attaques terrestres',
                'category' => 'general',
            ],
            [
                'key' => 'truce_block_spatial_attack',
                'value' => true,
                'type' => 'boolean',
                'description' => 'En trêve: interdire les attaques spatiales',
                'category' => 'general',
            ],
            [
                'key' => 'truce_block_spy',
                'value' => true,
                'type' => 'boolean',
                'description' => 'En trêve: interdire les missions d’espionnage',
                'category' => 'general',
            ],
            [
                'key' => 'truce_message',
                'value' => 'Trêve serveur active: certaines actions sont temporairement désactivées.',
                'type' => 'string',
                'description' => 'Message affiché quand une action est bloquée par la trêve',
                'category' => 'general',
            ],

            // Shop Settings
            [
                'key' => 'shop_enabled',
                'value' => true,
                'type' => 'boolean',
                'description' => 'Active ou désactive la boutique (achats)',
                'category' => 'shop',
            ],
            [
                'key' => 'shop_reward_rate',
                'value' => '1.0',
                'type' => 'float',
                'description' => 'Multiplicateur d\'or boutique (Happy Hours)',
                'category' => 'shop',
            ],
        ];
        
        foreach ($configs as $config) {
            // Upsert pour éviter les erreurs en cas de reseed
            ServerConfig::updateOrCreate(
                ['key' => $config['key']],
                array_merge($config, ['is_active' => true])
            );
        }
    }
}