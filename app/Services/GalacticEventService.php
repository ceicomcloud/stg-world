<?php

namespace App\Services;

use App\Models\Planet\PlanetMission;
use App\Models\Other\GalacticEvent;
use App\Models\Planet\Planet;

class GalacticEventService
{
    /**
     * Lancera (ou non) un évènement selon le type de mission.
     * Retourne un tableau décrivant l'évènement ou null s'il n'y a pas d'évènement.
     */
    public function rollForEvent(string $missionType): ?array
    {
        $events = $this->getEventsForType($missionType);
        if (empty($events)) {
            return null;
        }

        // Chance globale qu'un évènement survienne (≈ 35%)
        $globalRoll = random_int(1, 100);
        if ($globalRoll > 35) {
            return null;
        }

        // Sélection pondérée d'un évènement
        $totalWeight = array_sum(array_column($events, 'weight'));
        $pick = random_int(1, max(1, $totalWeight));
        $acc = 0;
        foreach ($events as $ev) {
            $acc += (int) $ev['weight'];
            if ($pick <= $acc) {
                return $ev;
            }
        }

        return null;
    }

    /**
     * Applique l'évènement à l'extraction: ajuste les ressources et éventuellement le délai.
     * Retourne [resources => array, delay_minutes => int, notes => string, event => array|null]
     */
    public function applyExtractionEvent(PlanetMission $mission, array $extracted): array
    {
        $event = $this->rollForEvent('extract');
        $delay = 0;
        $notes = '';

        if ($event && ($event['key'] ?? null) === 'pirate_ambush') {
            $factor = random_int(50, 80) / 100.0; // -20% à -50%
            foreach ($extracted as $rid => $amount) {
                $extracted[$rid] = (int) floor($amount * $factor);
            }
            $delay = random_int(0, 20);
            $notes = "Embuscade : une partie de la cargaison a été perdue.";
        }

        return [
            'resources' => $extracted,
            'delay_minutes' => $delay,
            'notes' => $notes,
            'event' => $event,
        ];
    }

    /**
     * Applique l'évènement à l'exploration: ajuste le loot et éventuellement le délai.
     * Retourne [resources => array, delay_minutes => int, notes => string, event => array|null]
     */
    public function applyExplorationEvent(PlanetMission $mission, array $loot): array
    {
        $event = $this->rollForEvent('explore');
        $delay = 0;
        $notes = '';

        if ($event && ($event['key'] ?? null) === 'pirate_ambush') {
            $factor = random_int(60, 85) / 100.0; // -15% à -40%
            foreach ($loot as $rid => $amount) {
                $loot[$rid] = (int) floor($amount * $factor);
            }
            $delay = random_int(0, 25);
            $notes = "Embuscade : partie du butin perdue.";
        }

        return [
            'resources' => $loot,
            'delay_minutes' => $delay,
            'notes' => $notes,
            'event' => $event,
        ];
    }

    /**
     * Définition des évènements disponibles par type.
     */
    protected function getEventsForType(string $type): array
    {
        $common = [
            [
                'key' => 'solar_flare',
                'title' => 'Éruption solaire',
                'severity' => 'medium',
                'weight' => 35,
                'icon' => '☀️',
                'description' => "Activité solaire intense détectée. Perturbations possibles et efficacité réduite."
            ],
            [
                'key' => 'pirate_ambush',
                'title' => 'Embuscade',
                'severity' => 'high',
                'weight' => 25,
                'icon' => '🛸',
                'description' => "Présence accrue de Wraiths signalée. Risque élevé d'interception en mission."
            ],
            [
                'key' => 'nebula_anomaly',
                'title' => 'Anomalie nébuleuse',
                'severity' => 'low',
                'weight' => 25,
                'icon' => '🌀',
                'description' => "Anomalies nébuleuses observées. Rendements potentiellement améliorés durant l'événement."
            ],
            [
                'key' => 'wormhole_drift',
                'title' => 'Dérive de trou de ver',
                'severity' => 'medium',
                'weight' => 15,
                'icon' => '🕳️',
                'description' => "Instabilités de trous de ver détectées. Retards possibles sur les trajets."
            ],
        ];

        switch ($type) {
            case 'extract':
            case 'explore':
                return [
                    [
                        'key' => 'pirate_ambush',
                        'title' => 'Embuscade',
                        'severity' => 'high',
                        'weight' => 100,
                        'icon' => '🛸',
                        'description' => "Présence accrue de Wraiths signalée. Risque élevé d'interception en mission."
                    ],
                ];
            case 'ambient':
                return $common;
            default:
                return [];
        }
    }

    /**
     * Récupère les évènements actifs pour une galaxie/système et désactive ceux expirés.
     */
    public function getActiveEventsForSector(int $galaxy, int $system)
    {
        // Désactiver ceux expirés d'abord (sécurité)
        GalacticEvent::where('galaxy', $galaxy)
            ->where('system', $system)
            ->where('is_active', true)
            ->where('end_at', '<=', now())
            ->update(['is_active' => false]);

        return GalacticEvent::active()->forSector($galaxy, $system)->orderBy('end_at')->get();
    }

    /**
     * Calcule les modificateurs d'événements applicables à une planète.
     * Retourne des pourcentages (peuvent être négatifs) à appliquer aux calculs:
     *  - energy_prod_percent
     *  - prod_metal_percent
     *  - prod_crystal_percent
     *  - prod_deuterium_percent
     */
    public function getModifiersForPlanet(Planet $planet): array
    {
        $mods = [
            'energy_prod_percent' => 0.0,
            'prod_metal_percent' => 0.0,
            'prod_crystal_percent' => 0.0,
            'prod_deuterium_percent' => 0.0,
        ];

        $tpl = $planet->templatePlanet;
        if (!$tpl) {
            return $mods;
        }

        $events = $this->getActiveEventsForSector((int) $tpl->galaxy, (int) $tpl->system);
        if (!$events || $events->isEmpty()) {
            return $mods;
        }

        foreach ($events as $ev) {
            $severity = strtolower((string) ($ev->severity ?? 'medium'));
            $severityFactor = $severity === 'high' ? 1.25 : ($severity === 'low' ? 0.75 : 1.0);

            switch ($ev->key) {
                case 'solar_flare':
                    // Diminue la production d'énergie
                    $mods['energy_prod_percent'] += -15.0 * $severityFactor;
                    break;
                case 'nebula_anomaly':
                    // Améliore la production des trois ressources
                    $impact = 15.0 * $severityFactor;
                    $mods['prod_metal_percent'] += $impact;
                    $mods['prod_crystal_percent'] += $impact;
                    $mods['prod_deuterium_percent'] += $impact;
                    break;
                default:
                    // autres évènements: pas d'impact planétaire pour l'instant
                    break;
            }
        }

        return $mods;
    }

    /**
     * Crée un évènement d'ambiance aléatoire pour un secteur.
     */
    public function spawnAmbientEvent(int $galaxy, int $system, ?int $position = null): GalacticEvent
    {
        $events = $this->getEventsForType('ambient');
        if (empty($events)) {
            throw new \RuntimeException('No ambient events defined');
        }

        // Choix pondéré
        $totalWeight = array_sum(array_column($events, 'weight'));
        $pick = random_int(1, max(1, $totalWeight));
        $acc = 0;
        $selected = $events[0];
        foreach ($events as $ev) {
            $acc += (int) $ev['weight'];
            if ($pick <= $acc) { $selected = $ev; break; }
        }

        // Durée aléatoire (30 à 180 minutes), ajustée par sévérité
        $baseMinutes = random_int(30, 180);
        $severity = $selected['severity'] ?? 'medium';
        $mult = $severity === 'high' ? 1.4 : ($severity === 'low' ? 0.8 : 1.0);
        $duration = (int) floor($baseMinutes * $mult);

        $now = now();
        $end = (clone $now)->addMinutes($duration);

        return GalacticEvent::create([
            'galaxy' => $galaxy,
            'system' => $system,
            'position' => $position,
            'key' => $selected['key'],
            'title' => $selected['title'],
            'severity' => $selected['severity'],
            'icon' => $selected['icon'] ?? null,
            'description' => $selected['description'] ?? null,
            'start_at' => $now,
            'end_at' => $end,
            'is_active' => true,
        ]);
    }

    /**
     * Peut, avec une faible probabilité, créer un évènement d'ambiance.
     */
    public function maybeSpawnAmbientEvent(int $galaxy, int $system, int $planetsPerSystem): ?GalacticEvent
    {
        $activeCount = GalacticEvent::active()->forSector($galaxy, $system)->count();
        if ($activeCount >= 2) { return null; }

        $roll = random_int(1, 100);
        if ($roll > 10) { return null; }

        $posRoll = random_int(0, 100);
        $position = $posRoll < 70 ? random_int(1, max(1, $planetsPerSystem)) : null; // 70% événements positionnels
        return $this->spawnAmbientEvent($galaxy, $system, $position);
    }
}