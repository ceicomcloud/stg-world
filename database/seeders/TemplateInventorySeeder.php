<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template\TemplateInventory;
use App\Models\Template\TemplateBuild;
use App\Models\Template\TemplateResource;

class TemplateInventorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'key' => 'build_time_reduction_10_percent_1h',
                'name' => 'Réduction du temps de construction -10% (1h)',
                'type' => 'booster',
                'description' => 'Réduit de 10% le temps de construction des bâtiments pendant 1 heure.',
                'icon' => 'fas fa-hourglass-half',
                'rarity' => 'rare',
                'effect_type' => 'time_reduction',
                'effect_value' => 10,
                'effect_meta' => [
                    'scope' => 'building',
                    'mode' => 'percentage',
                ],
                'duration_seconds' => 3600,
                'usable' => true,
                'stackable' => false,
                'is_active' => true,
            ],
            [
                'key' => 'queue_time_skip_30m',
                'name' => 'Saut de file de 30 minutes',
                'type' => 'consumable',
                'description' => 'Réduit de 30 minutes la file de production active (bâtiments/units/defenses/ships).',
                'icon' => 'fas fa-fast-forward',
                'rarity' => 'epic',
                'effect_type' => 'time_reduction',
                'effect_value' => 1800,
                'effect_meta' => [
                    'scope' => 'queue',
                    'mode' => 'seconds',
                ],
                'duration_seconds' => null,
                'usable' => true,
                'stackable' => true,
                'is_active' => true,
            ],
            [
                'key' => 'vip_extend_7d',
                'name' => 'Extension VIP (7 jours)',
                'type' => 'consumable',
                'description' => 'Prolonge le statut VIP de 7 jours.',
                'icon' => 'fas fa-crown',
                'rarity' => 'rare',
                'effect_type' => 'vip_extend',
                'effect_value' => 7,
                'effect_meta' => [ 'unit' => 'days' ],
                'duration_seconds' => null,
                'usable' => true,
                'stackable' => true,
                'is_active' => true,
            ],
            [
                'key' => 'production_boost_10_percent_24h',
                'name' => 'Boost Production +10% (24h)',
                'type' => 'booster',
                'description' => 'Augmente de 10% la production des ressources pendant 24 heures.',
                'icon' => 'fas fa-chart-line',
                'rarity' => 'epic',
                'effect_type' => 'production_boost',
                'effect_value' => 10,
                'effect_meta' => [ 'scope' => 'resource' ],
                'duration_seconds' => 86400,
                'usable' => true,
                'stackable' => false,
                'is_active' => true,
            ],
            [
                'key' => 'storage_boost_10_percent_24h',
                'name' => 'Boost Stockage +10% (24h)',
                'type' => 'booster',
                'description' => 'Augmente de 10% la capacité de stockage sur toutes les ressources pendant 24 heures.',
                'icon' => 'fas fa-box-open',
                'rarity' => 'epic',
                'effect_type' => 'storage_boost',
                'effect_value' => 10,
                'effect_meta' => [ 'scope' => 'resource' ],
                'duration_seconds' => 86400,
                'usable' => true,
                'stackable' => false,
                'is_active' => true,
            ],
            [
                'key' => 'energy_boost_10_percent_24h',
                'name' => 'Boost Énergie +10% (24h)',
                'type' => 'booster',
                'description' => 'Augmente de 10% la production d’énergie pendant 24 heures.',
                'icon' => 'fas fa-bolt',
                'rarity' => 'epic',
                'effect_type' => 'energy_boost',
                'effect_value' => 10,
                'effect_meta' => [ 'scope' => 'energy' ],
                'duration_seconds' => 86400,
                'usable' => true,
                'stackable' => false,
                'is_active' => true,
            ],
            [
                'key' => 'vip_extend_30d',
                'name' => 'Extension VIP (30 jours)',
                'type' => 'consumable',
                'description' => 'Prolonge le statut VIP de 30 jours.',
                'icon' => 'fas fa-crown',
                'rarity' => 'rare',
                'effect_type' => 'vip_extend',
                'effect_value' => 30,
                'effect_meta' => [ 'unit' => 'days' ],
                'duration_seconds' => null,
                'usable' => true,
                'stackable' => true,
                'is_active' => true,
            ],
        ];

        foreach ($items as $data) {
            TemplateInventory::updateOrCreate(['key' => $data['key']], $data);
        }

        // Génération dynamique des packs 250/500/1000 pour unités/défenses/vaisseaux
        $amounts = [250, 500, 1000];

        // Unités
        $units = TemplateBuild::active()->byType(TemplateBuild::TYPE_UNIT)->get();
        foreach ($units as $u) {
            foreach ($amounts as $qty) {
                TemplateInventory::updateOrCreate(
                    ['key' => "unit_pack_{$u->name}_{$qty}"],
                    [
                        'name' => "Pack Unités: {$u->label} x{$qty}",
                        'type' => 'pack',
                        'description' => "Ajoute {$qty} unités {$u->label} sur la planète sélectionnée.",
                        'icon' => 'fas fa-users',
                        'rarity' => 'rare',
                        'effect_type' => 'add_units',
                        'effect_value' => $qty,
                        'effect_meta' => [ 'unit_key' => $u->name ],
                        'duration_seconds' => null,
                        'usable' => true,
                        'stackable' => true,
                        'is_active' => true,
                    ]
                );
            }
        }

        // Défenses
        $defenses = TemplateBuild::active()->byType(TemplateBuild::TYPE_DEFENSE)->get();
        foreach ($defenses as $d) {
            foreach ($amounts as $qty) {
                TemplateInventory::updateOrCreate(
                    ['key' => "defense_pack_{$d->name}_{$qty}"],
                    [
                        'name' => "Pack Défenses: {$d->label} x{$qty}",
                        'type' => 'pack',
                        'description' => "Ajoute {$qty} défenses {$d->label} sur la planète sélectionnée.",
                        'icon' => 'fas fa-shield-alt',
                        'rarity' => 'rare',
                        'effect_type' => 'add_defenses',
                        'effect_value' => $qty,
                        'effect_meta' => [ 'defense_key' => $d->name ],
                        'duration_seconds' => null,
                        'usable' => true,
                        'stackable' => true,
                        'is_active' => true,
                    ]
                );
            }
        }

        // Vaisseaux
        $ships = TemplateBuild::active()->byType(TemplateBuild::TYPE_SHIP)->get();
        foreach ($ships as $s) {
            foreach ($amounts as $qty) {
                TemplateInventory::updateOrCreate(
                    ['key' => "ship_pack_{$s->name}_{$qty}"],
                    [
                        'name' => "Pack Vaisseaux: {$s->label} x{$qty}",
                        'type' => 'pack',
                        'description' => "Ajoute {$qty} vaisseaux {$s->label} sur la planète sélectionnée.",
                        'icon' => 'fas fa-space-shuttle',
                        'rarity' => 'rare',
                        'effect_type' => 'add_ships',
                        'effect_value' => $qty,
                        'effect_meta' => [ 'ship_key' => $s->name ],
                        'duration_seconds' => null,
                        'usable' => true,
                        'stackable' => true,
                        'is_active' => true,
                    ]
                );
            }
        }

        // Packs de ressources 10k / 100k / 300k
        $resourceAmounts = [10000, 100000, 300000];
        $resourceIcons = [
            TemplateResource::TYPE_METAL => 'fas fa-hammer',
            TemplateResource::TYPE_CRYSTAL => 'fas fa-gem',
            TemplateResource::TYPE_DEUTERIUM => 'fas fa-flask',
        ];
        $rarityByAmount = [
            10000 => 'common',
            100000 => 'rare',
            300000 => 'epic',
        ];

        $resources = TemplateResource::active()
            ->whereIn('name', [
                TemplateResource::TYPE_METAL,
                TemplateResource::TYPE_CRYSTAL,
                TemplateResource::TYPE_DEUTERIUM,
            ])->get();

        foreach ($resources as $r) {
            $label = $r->display_name ?? ucfirst($r->name);
            foreach ($resourceAmounts as $qty) {
                $icon = $resourceIcons[$r->name] ?? 'fas fa-cubes';
                $rarity = $rarityByAmount[$qty] ?? 'common';
                $formatted = number_format($qty, 0, '.', ' ');
                TemplateInventory::updateOrCreate(
                    ['key' => "resource_pack_{$r->name}_{$qty}"],
                    [
                        'name' => "Pack {$label} ({$formatted})",
                        'type' => 'pack',
                        'description' => "Ajoute {$formatted} {$label} sur la planète sélectionnée.",
                        'icon' => $icon,
                        'rarity' => $rarity,
                        'effect_type' => 'add_resources',
                        'effect_value' => $qty,
                        'effect_meta' => [ 'resource_key' => $r->name ],
                        'duration_seconds' => null,
                        'usable' => true,
                        'stackable' => true,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}