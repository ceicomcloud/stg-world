<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserEffect;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetResource;
use App\Models\Planet\PlanetUnit;
use App\Models\Planet\PlanetDefense;
use App\Models\Planet\PlanetShip;
use App\Models\Template\TemplateResource;
use App\Models\Template\TemplateBuild;
use App\Models\User\UserInventory;

class InventoryService
{
    /**
     * Consommer un article d'inventaire et appliquer son effet
     */
    public function consumeItem(User $user, UserInventory $inventory, ?Planet $planet = null): array
    {
        $template = $inventory->template;
        $type = $template->effect_type;
        $meta = $template->effect_meta ?? [];
        $value = $template->effect_value ?? 0;
        $duration = (int) ($template->duration_seconds ?? 0);

        try {
            switch ($type) {
                case 'add_resources':
                    if (!$planet) {
                        return ['success' => false, 'message' => 'Sélectionnez une planète pour appliquer le pack.'];
                    }
                    $resourceName = $meta['resource'] ?? $this->inferResourceFromKey($template->key);
                    $amount = (int) ($meta['amount'] ?? $value ?? 0);
                    if (!$resourceName || $amount <= 0) {
                        return ['success' => false, 'message' => 'Pack de ressources invalide.'];
                    }
                    $resTemplate = TemplateResource::where('name', $resourceName)->first();
                    if (!$resTemplate) {
                        return ['success' => false, 'message' => 'Type de ressource introuvable.'];
                    }
                    $planetRes = PlanetResource::where('planet_id', $planet->id)
                        ->where('resource_id', $resTemplate->id)
                        ->first();
                    if (!$planetRes) {
                        // Crée la ressource si absente pour éviter l'erreur
                        $planetRes = PlanetResource::create([
                            'planet_id' => $planet->id,
                            'resource_id' => $resTemplate->id,
                            'current_amount' => 0,
                            'production_rate' => 100,
                            'last_update' => now(),
                            'is_active' => true,
                        ]);
                    }
                    $added = $planetRes->addResources($amount);
                    $inventory->decrement('quantity', 1);
                    return [
                        'success' => true,
                        'message' => 'Pack appliqué: +' . number_format($added) . ' ' . $resTemplate->display_name . ( $added < $amount ? ' (capacité limitée)' : '' )
                    ];

                case 'add_units':
                    if (!$planet) {
                        return ['success' => false, 'message' => 'Sélectionnez une planète pour ajouter des unités.'];
                    }
                    $unitKey = $meta['unit_key'] ?? $this->inferBuildKeyFromTemplateKey($template->key, 'unit_pack_');
                    $quantity = (int) ($value ?? 0);
                    if (!$unitKey || $quantity <= 0) {
                        return ['success' => false, 'message' => 'Pack d\'unités invalide.'];
                    }
                    $unitTemplate = TemplateBuild::where('name', $unitKey)->first();
                    if (!$unitTemplate) {
                        return ['success' => false, 'message' => 'Type d\'unité introuvable.'];
                    }
                    $planetUnit = PlanetUnit::where('planet_id', $planet->id)
                        ->where('unit_id', $unitTemplate->id)
                        ->first();
                    if (!$planetUnit) {
                        // Crée l'entrée d'unité si absente
                        $planetUnit = PlanetUnit::create([
                            'planet_id' => $planet->id,
                            'unit_id' => $unitTemplate->id,
                            'quantity' => 0,
                            'is_active' => true,
                        ]);
                    }
                    $planetUnit->addUnits($quantity);
                    $inventory->decrement('quantity', 1);
                    return [
                        'success' => true,
                        'message' => "Unités ajoutées: +" . number_format($quantity) . ' ' . ($unitTemplate->label ?? $unitTemplate->name)
                    ];

                case 'add_defenses':
                    if (!$planet) {
                        return ['success' => false, 'message' => 'Sélectionnez une planète pour ajouter des défenses.'];
                    }
                    $defenseKey = $meta['defense_key'] ?? $this->inferBuildKeyFromTemplateKey($template->key, 'defense_pack_');
                    $quantity = (int) ($value ?? 0);
                    if (!$defenseKey || $quantity <= 0) {
                        return ['success' => false, 'message' => 'Pack de défenses invalide.'];
                    }
                    $defenseTemplate = TemplateBuild::where('name', $defenseKey)->first();
                    if (!$defenseTemplate) {
                        return ['success' => false, 'message' => 'Type de défense introuvable.'];
                    }
                    $planetDefense = PlanetDefense::where('planet_id', $planet->id)
                        ->where('defense_id', $defenseTemplate->id)
                        ->first();
                    if (!$planetDefense) {
                        // Crée l'entrée de défense si absente
                        $planetDefense = PlanetDefense::create([
                            'planet_id' => $planet->id,
                            'defense_id' => $defenseTemplate->id,
                            'quantity' => 0,
                            'is_active' => true,
                        ]);
                    }
                    $planetDefense->addDefenses($quantity);
                    $inventory->decrement('quantity', 1);
                    return [
                        'success' => true,
                        'message' => "Défenses ajoutées: +" . number_format($quantity) . ' ' . ($defenseTemplate->label ?? $defenseTemplate->name)
                    ];

                case 'add_ships':
                    if (!$planet) {
                        return ['success' => false, 'message' => 'Sélectionnez une planète pour ajouter des vaisseaux.'];
                    }
                    $shipKey = $meta['ship_key'] ?? $this->inferBuildKeyFromTemplateKey($template->key, 'ship_pack_');
                    $quantity = (int) ($value ?? 0);
                    if (!$shipKey || $quantity <= 0) {
                        return ['success' => false, 'message' => 'Pack de vaisseaux invalide.'];
                    }
                    $shipTemplate = TemplateBuild::where('name', $shipKey)->first();
                    if (!$shipTemplate) {
                        return ['success' => false, 'message' => 'Type de vaisseau introuvable.'];
                    }
                    $planetShip = PlanetShip::where('planet_id', $planet->id)
                        ->where('ship_id', $shipTemplate->id)
                        ->first();
                    if (!$planetShip) {
                        // Crée l'entrée de vaisseau si absente
                        $planetShip = PlanetShip::create([
                            'planet_id' => $planet->id,
                            'ship_id' => $shipTemplate->id,
                            'quantity' => 0,
                            'is_active' => true,
                        ]);
                    }
                    $planetShip->addShips($quantity);
                    $inventory->decrement('quantity', 1);
                    return [
                        'success' => true,
                        'message' => "Vaisseaux ajoutés: +" . number_format($quantity) . ' ' . ($shipTemplate->label ?? $shipTemplate->name)
                    ];

                case 'production_boost':
                case 'storage_boost':
                case 'energy_boost':
                    $effect = new UserEffect([
                        'user_id' => $user->id,
                        'planet_id' => $planet?->id,
                        'effect_type' => $type,
                        'value' => (float) $value,
                        'meta' => $meta,
                        'started_at' => now(),
                        'expires_at' => $duration > 0 ? now()->addSeconds($duration) : null,
                        'is_active' => true,
                    ]);
                    $effect->save();
                    $inventory->decrement('quantity', 1);
                    return [
                        'success' => true,
                        'message' => 'Boost appliqué: ' . ($value) . '% ' . ($type === 'storage_boost' ? 'stockage' : ($type === 'production_boost' ? 'production' : 'énergie'))
                    ];

                case 'vip_extend':
                    $days = (int) ($meta['days'] ?? 30);
                    $user->vip_active = true;
                    $current = $user->vip_until && now()->lt($user->vip_until) ? $user->vip_until : now();
                    $user->vip_until = $current->copy()->addDays($days);
                    $user->save();
                    $inventory->decrement('quantity', 1);
                    return [
                        'success' => true,
                        'message' => 'VIP prolongé de ' . $days . ' jours.'
                    ];

                default:
                    return ['success' => false, 'message' => "Type d'effet inconnu: $type"];
            }
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    /**
     * Ajouter des ressources génériques à l'utilisateur (répartition sur la planète principale).
     * Répartit équitablement le montant entre métal, cristal et deutérium.
     * Respecte la capacité de stockage via PlanetResource::addResources.
     *
     * @return array { success: bool, message: string, details: array }
     */
    public function addGenericResourcesToUser(User $user, int $amount): array
    {
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Montant invalide'];
        }

        // Sélectionner la planète cible: principale puis actuelle puis première disponible
        $planet = $user->getMainPlanet() ?? $user->getActualPlanet() ?? $user->planets()->first();
        if (!$planet) {
            return ['success' => false, 'message' => 'Aucune planète trouvée pour cet utilisateur'];
        }

        // Récupérer les templates de ressources primaires
        $templates = TemplateResource::whereIn('name', ['metal', 'crystal', 'deuterium'])
            ->get()
            ->keyBy('name');

        if ($templates->count() < 3) {
            return ['success' => false, 'message' => 'Ressources primaires non configurées'];
        }

        // Répartition équitable et gestion du reste
        $per = intdiv($amount, 3);
        $rest = $amount % 3;
        $dist = [
            'metal' => $per + ($rest > 0 ? 1 : 0),
            'crystal' => $per + ($rest > 1 ? 1 : 0),
            'deuterium' => $per,
        ];

        // S’assurer que les PlanetResource existent et ajouter
        $added = ['metal' => 0, 'crystal' => 0, 'deuterium' => 0];
        foreach ($dist as $name => $qty) {
            /** @var TemplateResource $tpl */
            $tpl = $templates[$name];
            $planetRes = PlanetResource::where('planet_id', $planet->id)
                ->where('resource_id', $tpl->id)
                ->first();
            if (!$planetRes) {
                $planetRes = PlanetResource::create([
                    'planet_id' => $planet->id,
                    'resource_id' => $tpl->id,
                    'current_amount' => 0,
                    'production_rate' => $tpl->base_production ?? 0,
                    'last_update' => now(),
                    'is_active' => true,
                ]);
            }
            $added[$name] = $planetRes->addResources((int) $qty);
        }

        // Log de gain
        try {
            app(\App\Services\LogService::class)->logResourceGain(
                $user->id,
                [
                    'metal' => $added['metal'],
                    'crystal' => $added['crystal'],
                    'deuterium' => $added['deuterium'],
                ],
                'Récompense (ajout générique)',
                $planet->id
            );
        } catch (\Throwable $e) {
            // Ne pas bloquer sur le logging
        }

        $totalAdded = array_sum($added);
        $message = 'Ressources ajoutées: +' . number_format($totalAdded) . ' (métal ' . number_format($added['metal']) . ', cristal ' . number_format($added['crystal']) . ', deutérium ' . number_format($added['deuterium']) . ')';

        return [
            'success' => true,
            'message' => $message,
            'details' => [
                'planet_id' => $planet->id,
                'added' => $added,
                'requested' => $dist,
            ],
        ];
    }

    private function inferResourceFromKey(?string $key): ?string
    {
        if (!$key) return null;
        if (str_contains($key, 'metal')) return 'metal';
        if (str_contains($key, 'crystal')) return 'crystal';
        if (str_contains($key, 'deuterium')) return 'deuterium';
        return null;
    }

    private function inferBuildKeyFromTemplateKey(?string $key, string $prefix): ?string
    {
        if (!$key || !str_starts_with($key, $prefix)) return null;
        // Template keys like: "defense_pack_{build_name}_{qty}" => return build_name
        $parts = explode('_', $key);
        // Remove prefix words from the start
        // e.g., ['defense','pack','tour','surveillance','x250']
        // We need to reconstruct name between prefix and quantity
        $withoutPrefix = substr($key, strlen($prefix));
        // Strip trailing quantity suffix
        $chunks = explode('_', $withoutPrefix);
        // Last chunk is quantity (e.g., 250/500/1000)
        array_pop($chunks);
        return implode('_', $chunks);
    }
}