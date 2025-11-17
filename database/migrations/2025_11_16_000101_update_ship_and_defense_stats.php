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
        DB::transaction(function () {
            $now = now();

            $updates = [
                'ship' => [
                    'drone_stratos' => [
                        'life' => 150,
                        'attack_power' => 30,
                        'defense_power' => 15,
                        'shield_power' => 8,
                    ],
                    'intercepteur_solaire' => [
                        'life' => 200,
                        'attack_power' => 80,
                        'defense_power' => 32,
                        'shield_power' => 24,
                    ],
                    'bombardier_vector' => [
                        'life' => 400,
                        'attack_power' => 150,
                        'defense_power' => 48,
                        'shield_power' => 64,
                    ],
                    'transporteur_delta' => [
                        'life' => 600,
                        'attack_power' => 40,
                        'defense_power' => 80,
                        'shield_power' => 96,
                    ],
                    'fregate_defensive' => [
                        'life' => 800,
                        'attack_power' => 100,
                        'defense_power' => 120,
                        'shield_power' => 160,
                    ],
                    'croiseur_orion' => [
                        'life' => 1200,
                        'attack_power' => 200,
                        'defense_power' => 96,
                        'shield_power' => 120,
                    ],
                    'vaisseau_commandement' => [
                        'life' => 1000,
                        'attack_power' => 120,
                        'defense_power' => 144,
                        'shield_power' => 200,
                    ],
                    'plateforme_siege' => [
                        'life' => 1500,
                        'attack_power' => 400,
                        'defense_power' => 160,
                        'shield_power' => 80,
                    ],
                    'scout_quantique' => [
                        'life' => 300,
                        'attack_power' => 50,
                        'defense_power' => 32,
                        'shield_power' => 48,
                    ],
                    'dreadnought_nova' => [
                        'life' => 3000,
                        'attack_power' => 800,
                        'defense_power' => 320,
                        'shield_power' => 400,
                    ],
                ],
                'defense' => [
                    'tourelle_cinetique' => [
                        'life' => 800,
                        'attack_power' => 80,
                        'defense_power' => 180,
                        'shield_power' => 0,
                    ],
                    'canon_laser' => [
                        'life' => 700,
                        'attack_power' => 60,
                        'defense_power' => 160,
                        'shield_power' => 0,
                    ],
                    'lance_missiles' => [
                        'life' => 1000,
                        'attack_power' => 120,
                        'defense_power' => 200,
                        'shield_power' => 0,
                    ],
                    'batterie_plasma' => [
                        'life' => 1500,
                        'attack_power' => 200,
                        'defense_power' => 240,
                        'shield_power' => 0,
                    ],
                    'champ_brouillage' => [
                        'life' => 600,
                        'attack_power' => 0,
                        'defense_power' => 400,
                        'shield_power' => 250,
                    ],
                    'generateur_bouclier' => [
                        'life' => 1300,
                        'attack_power' => 0,
                        'defense_power' => 300,
                        'shield_power' => 800,
                    ],
                    'tour_surveillance' => [
                        'life' => 500,
                        'attack_power' => 0,
                        'defense_power' => 120,
                        'shield_power' => 0,
                    ],
                    'canon_orbital' => [
                        'life' => 3000,
                        'attack_power' => 500,
                        'defense_power' => 450,
                        'shield_power' => 0,
                    ],
                    'mines_solaires' => [
                        'life' => 200,
                        'attack_power' => 300,
                        'defense_power' => 50,
                        'shield_power' => 0,
                    ],
                    'tourelle_emp' => [
                        'life' => 900,
                        'attack_power' => 100,
                        'defense_power' => 220,
                        'shield_power' => 0,
                    ],
                ],
            ];

            foreach ($updates as $type => $items) {
                foreach ($items as $name => $values) {
                    DB::table('template_builds')
                        ->where('name', $name)
                        ->where('type', $type)
                        ->update(array_merge($values, ['updated_at' => $now]));
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionnellement vide: les valeurs précédentes ne sont pas connues pour un rollback sûr.
    }
};