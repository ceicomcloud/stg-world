<?php

namespace Database\Seeders;

use App\Models\Template\TemplateBadge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create level badges
        TemplateBadge::createDefaultLevelBadges();
        
        // Create experience badges
        $experienceBadges = [
            ['xp' => 1000, 'name' => 'Premier Pas', 'rarity' => TemplateBadge::RARITY_COMMON],
            ['xp' => 5000, 'name' => 'Collectionneur d\'XP', 'rarity' => TemplateBadge::RARITY_UNCOMMON],
            ['xp' => 25000, 'name' => 'Accumulateur', 'rarity' => TemplateBadge::RARITY_RARE],
            ['xp' => 100000, 'name' => 'Maître de l\'Expérience', 'rarity' => TemplateBadge::RARITY_EPIC],
            ['xp' => 500000, 'name' => 'Légende Vivante', 'rarity' => TemplateBadge::RARITY_LEGENDARY]
        ];

        foreach ($experienceBadges as $badge) {
            TemplateBadge::updateOrCreate(
                [
                    'type' => TemplateBadge::TYPE_EXPERIENCE,
                    'requirement_type' => TemplateBadge::REQUIREMENT_TOTAL_EXPERIENCE,
                    'requirement_value' => $badge['xp']
                ],
                [
                    'name' => $badge['name'],
                    'description' => "Accumuler {$badge['xp']} points d'expérience au total",
                    'icon' => 'experience-' . $badge['xp'],
                    'rarity' => $badge['rarity'],
                    'points_reward' => intval($badge['xp'] / 100),
                    'is_active' => true
                ]
            );
        }
        
        // Create research badges
        $researchBadges = [
            ['points' => 100, 'name' => 'Chercheur Débutant', 'rarity' => TemplateBadge::RARITY_COMMON],
            ['points' => 500, 'name' => 'Scientifique', 'rarity' => TemplateBadge::RARITY_UNCOMMON],
            ['points' => 2000, 'name' => 'Inventeur', 'rarity' => TemplateBadge::RARITY_RARE],
            ['points' => 10000, 'name' => 'Génie', 'rarity' => TemplateBadge::RARITY_EPIC],
            ['points' => 50000, 'name' => 'Visionnaire', 'rarity' => TemplateBadge::RARITY_LEGENDARY]
        ];

        foreach ($researchBadges as $badge) {
            TemplateBadge::updateOrCreate(
                [
                    'type' => TemplateBadge::TYPE_RESEARCH,
                    'requirement_type' => TemplateBadge::REQUIREMENT_RESEARCH_POINTS,
                    'requirement_value' => $badge['points']
                ],
                [
                    'name' => $badge['name'],
                    'description' => "Accumuler {$badge['points']} points de recherche",
                    'icon' => 'research-' . $badge['points'],
                    'rarity' => $badge['rarity'],
                    'points_reward' => intval($badge['points'] / 10),
                    'is_active' => true
                ]
            );
        }
        
        // Create special achievement badges
        $specialBadges = [
            [
                'name' => 'Premier Connexion',
                'description' => 'Se connecter pour la première fois',
                'type' => TemplateBadge::TYPE_SPECIAL,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 1,
                'rarity' => TemplateBadge::RARITY_COMMON,
                'points_reward' => 10
            ],
            [
                'name' => 'Explorateur Galactique',
                'description' => 'Découvrir tous les systèmes de base',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 1,
                'rarity' => TemplateBadge::RARITY_RARE,
                'points_reward' => 100
            ],
            [
                'name' => 'Bêta Testeur',
                'description' => 'Participer à la phase bêta du jeu',
                'type' => TemplateBadge::TYPE_SPECIAL,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 1,
                'rarity' => TemplateBadge::RARITY_LEGENDARY,
                'points_reward' => 500
            ]
        ];

        foreach ($specialBadges as $badge) {
            TemplateBadge::updateOrCreate(
                [
                    'name' => $badge['name'],
                    'type' => $badge['type']
                ],
                [
                    'description' => $badge['description'],
                    'icon' => strtolower(str_replace(' ', '-', $badge['name'])),
                    'requirement_type' => $badge['requirement_type'],
                    'requirement_value' => $badge['requirement_value'],
                    'rarity' => $badge['rarity'],
                    'points_reward' => $badge['points_reward'],
                    'is_active' => true
                ]
            );
        }
        
        // Create forum badges
        $forumBadges = [
            [
                'name' => 'Premier Message',
                'description' => 'Poster votre premier message sur le forum',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 1,
                'rarity' => TemplateBadge::RARITY_COMMON,
                'points_reward' => 25
            ],
            [
                'name' => 'Bavard',
                'description' => 'Poster 10 messages sur le forum',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 10,
                'rarity' => TemplateBadge::RARITY_COMMON,
                'points_reward' => 50
            ],
            [
                'name' => 'Communicateur',
                'description' => 'Poster 50 messages sur le forum',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 50,
                'rarity' => TemplateBadge::RARITY_UNCOMMON,
                'points_reward' => 100
            ],
            [
                'name' => 'Orateur',
                'description' => 'Poster 200 messages sur le forum',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 200,
                'rarity' => TemplateBadge::RARITY_RARE,
                'points_reward' => 250
            ],
            [
                'name' => 'Maître du Forum',
                'description' => 'Poster 1000 messages sur le forum',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 1000,
                'rarity' => TemplateBadge::RARITY_EPIC,
                'points_reward' => 500
            ],
            [
                'name' => 'Créateur de Sujet',
                'description' => 'Créer votre premier sujet sur le forum',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 1,
                'rarity' => TemplateBadge::RARITY_COMMON,
                'points_reward' => 30
            ],
            [
                'name' => 'Initiateur',
                'description' => 'Créer 10 sujets sur le forum',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 10,
                'rarity' => TemplateBadge::RARITY_UNCOMMON,
                'points_reward' => 150
            ],
            [
                'name' => 'Modérateur Citoyen',
                'description' => 'Signaler 5 messages inappropriés',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 5,
                'rarity' => TemplateBadge::RARITY_RARE,
                'points_reward' => 200
            ]
        ];

        foreach ($forumBadges as $badge) {
            TemplateBadge::updateOrCreate(
                [
                    'name' => $badge['name'],
                    'type' => $badge['type']
                ],
                [
                    'description' => $badge['description'],
                    'icon' => 'forum-' . strtolower(str_replace(' ', '-', $badge['name'])),
                    'requirement_type' => $badge['requirement_type'],
                    'requirement_value' => $badge['requirement_value'],
                    'rarity' => $badge['rarity'],
                    'points_reward' => $badge['points_reward'],
                    'is_active' => true
                ]
            );
        }
        
        // Create building badges
        $buildingBadges = [
            [
                'name' => 'Premier Bâtiment',
                'description' => 'Construire votre premier bâtiment',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 1,
                'rarity' => TemplateBadge::RARITY_COMMON,
                'points_reward' => 20
            ],
            [
                'name' => 'Architecte Débutant',
                'description' => 'Construire 10 bâtiments',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 10,
                'rarity' => TemplateBadge::RARITY_COMMON,
                'points_reward' => 75
            ],
            [
                'name' => 'Constructeur',
                'description' => 'Construire 50 bâtiments',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 50,
                'rarity' => TemplateBadge::RARITY_UNCOMMON,
                'points_reward' => 200
            ],
            [
                'name' => 'Maître Architecte',
                'description' => 'Construire 200 bâtiments',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 200,
                'rarity' => TemplateBadge::RARITY_RARE,
                'points_reward' => 500
            ],
            [
                'name' => 'Empereur Bâtisseur',
                'description' => 'Construire 1000 bâtiments',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 1000,
                'rarity' => TemplateBadge::RARITY_LEGENDARY,
                'points_reward' => 1500
            ],
            [
                'name' => 'Industriel',
                'description' => 'Atteindre le niveau 10 pour une mine de métal',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 10,
                'rarity' => TemplateBadge::RARITY_UNCOMMON,
                'points_reward' => 100
            ],
            [
                'name' => 'Cristallier',
                'description' => 'Atteindre le niveau 10 pour une mine de cristal',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 10,
                'rarity' => TemplateBadge::RARITY_UNCOMMON,
                'points_reward' => 100
            ],
            [
                'name' => 'Raffineur',
                'description' => 'Atteindre le niveau 10 pour un synthétiseur de deutérium',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 10,
                'rarity' => TemplateBadge::RARITY_UNCOMMON,
                'points_reward' => 100
            ],
            [
                'name' => 'Énergéticien',
                'description' => 'Atteindre le niveau 15 pour une centrale électrique',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 15,
                'rarity' => TemplateBadge::RARITY_RARE,
                'points_reward' => 200
            ],
            [
                'name' => 'Défenseur',
                'description' => 'Construire votre première défense',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 1,
                'rarity' => TemplateBadge::RARITY_COMMON,
                'points_reward' => 50
            ],
            [
                'name' => 'Forteresse',
                'description' => 'Construire 100 unités de défense',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 100,
                'rarity' => TemplateBadge::RARITY_RARE,
                'points_reward' => 300
            ],
            [
                'name' => 'Amiral',
                'description' => 'Construire votre premier vaisseau',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 1,
                'rarity' => TemplateBadge::RARITY_COMMON,
                'points_reward' => 75
            ],
            [
                'name' => 'Commandant de Flotte',
                'description' => 'Construire 50 vaisseaux',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 50,
                'rarity' => TemplateBadge::RARITY_UNCOMMON,
                'points_reward' => 250
            ]
        ];

        foreach ($buildingBadges as $badge) {
            TemplateBadge::updateOrCreate(
                [
                    'name' => $badge['name'],
                    'type' => $badge['type']
                ],
                [
                    'description' => $badge['description'],
                    'icon' => 'building-' . strtolower(str_replace(' ', '-', $badge['name'])),
                    'requirement_type' => $badge['requirement_type'],
                    'requirement_value' => $badge['requirement_value'],
                    'rarity' => $badge['rarity'],
                    'points_reward' => $badge['points_reward'],
                    'is_active' => true
                ]
            );
        }
        
        // Create combat badges - Earth Attack
        $earthAttackBadges = [
            [
                'name' => 'Premier Assaut Terrestre',
                'description' => 'Remporter votre première victoire en attaque terrestre',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 1,
                'rarity' => TemplateBadge::RARITY_COMMON,
                'points_reward' => 50
            ],
            [
                'name' => 'Conquérant Terrestre',
                'description' => 'Remporter 10 victoires en attaque terrestre',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 10,
                'rarity' => TemplateBadge::RARITY_UNCOMMON,
                'points_reward' => 150
            ],
            [
                'name' => 'Général Terrestre',
                'description' => 'Remporter 50 victoires en attaque terrestre',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 50,
                'rarity' => TemplateBadge::RARITY_RARE,
                'points_reward' => 400
            ],
            [
                'name' => 'Maréchal Terrestre',
                'description' => 'Remporter 200 victoires en attaque terrestre',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 200,
                'rarity' => TemplateBadge::RARITY_EPIC,
                'points_reward' => 1000
            ],
            [
                'name' => 'Empereur des Terres',
                'description' => 'Remporter 1000 victoires en attaque terrestre',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 1000,
                'rarity' => TemplateBadge::RARITY_LEGENDARY,
                'points_reward' => 3000
            ]
        ];

        foreach ($earthAttackBadges as $badge) {
            TemplateBadge::updateOrCreate(
                [
                    'name' => $badge['name'],
                    'type' => $badge['type']
                ],
                [
                    'description' => $badge['description'],
                    'icon' => 'earth-attack-' . strtolower(str_replace(' ', '-', $badge['name'])),
                    'requirement_type' => $badge['requirement_type'],
                    'requirement_value' => $badge['requirement_value'],
                    'rarity' => $badge['rarity'],
                    'points_reward' => $badge['points_reward'],
                    'is_active' => true
                ]
            );
        }
        
        // Create combat badges - Earth Defense
        $earthDefenseBadges = [
            [
                'name' => 'Première Défense Terrestre',
                'description' => 'Repousser votre première attaque terrestre',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 1,
                'rarity' => TemplateBadge::RARITY_COMMON,
                'points_reward' => 50
            ],
            [
                'name' => 'Gardien Terrestre',
                'description' => 'Repousser 10 attaques terrestres',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 10,
                'rarity' => TemplateBadge::RARITY_UNCOMMON,
                'points_reward' => 150
            ],
            [
                'name' => 'Protecteur Terrestre',
                'description' => 'Repousser 50 attaques terrestres',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 50,
                'rarity' => TemplateBadge::RARITY_RARE,
                'points_reward' => 400
            ],
            [
                'name' => 'Bastion Terrestre',
                'description' => 'Repousser 200 attaques terrestres',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 200,
                'rarity' => TemplateBadge::RARITY_EPIC,
                'points_reward' => 1000
            ],
            [
                'name' => 'Forteresse Inexpugnable',
                'description' => 'Repousser 1000 attaques terrestres',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 1000,
                'rarity' => TemplateBadge::RARITY_LEGENDARY,
                'points_reward' => 3000
            ]
        ];

        foreach ($earthDefenseBadges as $badge) {
            TemplateBadge::updateOrCreate(
                [
                    'name' => $badge['name'],
                    'type' => $badge['type']
                ],
                [
                    'description' => $badge['description'],
                    'icon' => 'earth-defense-' . strtolower(str_replace(' ', '-', $badge['name'])),
                    'requirement_type' => $badge['requirement_type'],
                    'requirement_value' => $badge['requirement_value'],
                    'rarity' => $badge['rarity'],
                    'points_reward' => $badge['points_reward'],
                    'is_active' => true
                ]
            );
        }
        
        // Create combat badges - Spatial Attack
        $spatialAttackBadges = [
            [
                'name' => 'Premier Assaut Spatial',
                'description' => 'Remporter votre première victoire en attaque spatiale',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 1,
                'rarity' => TemplateBadge::RARITY_COMMON,
                'points_reward' => 75
            ],
            [
                'name' => 'Conquérant Spatial',
                'description' => 'Remporter 10 victoires en attaque spatiale',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 10,
                'rarity' => TemplateBadge::RARITY_UNCOMMON,
                'points_reward' => 200
            ],
            [
                'name' => 'Amiral Spatial',
                'description' => 'Remporter 50 victoires en attaque spatiale',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 50,
                'rarity' => TemplateBadge::RARITY_RARE,
                'points_reward' => 500
            ],
            [
                'name' => 'Grand Amiral',
                'description' => 'Remporter 200 victoires en attaque spatiale',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 200,
                'rarity' => TemplateBadge::RARITY_EPIC,
                'points_reward' => 1250
            ],
            [
                'name' => 'Seigneur de l\'Espace',
                'description' => 'Remporter 1000 victoires en attaque spatiale',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 1000,
                'rarity' => TemplateBadge::RARITY_LEGENDARY,
                'points_reward' => 4000
            ]
        ];

        foreach ($spatialAttackBadges as $badge) {
            TemplateBadge::updateOrCreate(
                [
                    'name' => $badge['name'],
                    'type' => $badge['type']
                ],
                [
                    'description' => $badge['description'],
                    'icon' => 'spatial-attack-' . strtolower(str_replace(' ', '-', $badge['name'])),
                    'requirement_type' => $badge['requirement_type'],
                    'requirement_value' => $badge['requirement_value'],
                    'rarity' => $badge['rarity'],
                    'points_reward' => $badge['points_reward'],
                    'is_active' => true
                ]
            );
        }
        
        // Create combat badges - Spatial Defense
        $spatialDefenseBadges = [
            [
                'name' => 'Première Défense Spatiale',
                'description' => 'Repousser votre première attaque spatiale',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 1,
                'rarity' => TemplateBadge::RARITY_COMMON,
                'points_reward' => 75
            ],
            [
                'name' => 'Gardien Spatial',
                'description' => 'Repousser 10 attaques spatiales',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 10,
                'rarity' => TemplateBadge::RARITY_UNCOMMON,
                'points_reward' => 200
            ],
            [
                'name' => 'Protecteur Spatial',
                'description' => 'Repousser 50 attaques spatiales',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 50,
                'rarity' => TemplateBadge::RARITY_RARE,
                'points_reward' => 500
            ],
            [
                'name' => 'Station Défensive',
                'description' => 'Repousser 200 attaques spatiales',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 200,
                'rarity' => TemplateBadge::RARITY_EPIC,
                'points_reward' => 1250
            ],
            [
                'name' => 'Citadelle Stellaire',
                'description' => 'Repousser 1000 attaques spatiales',
                'type' => TemplateBadge::TYPE_ACHIEVEMENT,
                'requirement_type' => TemplateBadge::REQUIREMENT_CUSTOM,
                'requirement_value' => 1000,
                'rarity' => TemplateBadge::RARITY_LEGENDARY,
                'points_reward' => 4000
            ]
        ];

        foreach ($spatialDefenseBadges as $badge) {
            TemplateBadge::updateOrCreate(
                [
                    'name' => $badge['name'],
                    'type' => $badge['type']
                ],
                [
                    'description' => $badge['description'],
                    'icon' => 'spatial-defense-' . strtolower(str_replace(' ', '-', $badge['name'])),
                    'requirement_type' => $badge['requirement_type'],
                    'requirement_value' => $badge['requirement_value'],
                    'rarity' => $badge['rarity'],
                    'points_reward' => $badge['points_reward'],
                    'is_active' => true
                ]
            );
        }
    }
}