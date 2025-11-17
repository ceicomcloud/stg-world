<?php

namespace App\Services;

use App\Models\Server\ServerConfig;

class EngagementBandService
{
    /**
     * Get banding config for a mission type
     * $type: 'spy' | 'earth_attack' | 'spatial_attack'
     */
    public function getConfig(string $type): array
    {
        // Defaults (shared)
        $sharedEnabled = (bool) ServerConfig::get('spy_band_enabled');
        $sharedPct = (float) ServerConfig::get('spy_band_percentage', 0.3);
        $sharedSrc = ServerConfig::get('spy_band_points_source', 'total_points');

        // Attack-specific config (fallback to shared if missing)
        $attackEnabled = (bool) ServerConfig::get('attack_band_enabled', $sharedEnabled);
        $attackPct = (float) ServerConfig::get('attack_band_percentage', $sharedPct);
        $attackSrc = ServerConfig::get('attack_band_points_source', $sharedSrc);

        switch ($type) {
            case 'earth_attack':
            case 'spatial_attack':
                return [
                    'enabled' => $attackEnabled,
                    'percentage' => $attackPct,
                    'source' => $attackSrc,
                ];
            case 'spy':
            default:
                return [
                    'enabled' => $sharedEnabled,
                    'percentage' => $sharedPct,
                    'source' => $sharedSrc,
                ];
        }
    }

    public function getSourceLabel(string $source): string
    {
        return match ($source) {
            'earth_attack' => "points d'attaque terrestre",
            'spatial_attack' => "points d'attaque spatial",
            default => 'points totaux',
        };
    }

    /**
     * Returns points for a user based on source.
     */
    public function getUserPoints($user, string $source): ?int
    {
        if (!$user || !$user->userStat) {
            return null;
        }

        return match ($source) {
            'earth_attack' => (int) ($user->userStat->earth_attack ?? 0),
            'spatial_attack' => (int) ($user->userStat->spatial_attack ?? 0),
            default => (int) ($user->userStat->total_points ?? 0),
        };
    }

    /**
     * Compute min/max band around attacker points.
     */
    public function computeBand($attacker, string $type): array
    {
        $config = $this->getConfig($type);
        $enabled = (bool) ($config['enabled'] ?? false);
        $pct = (float) ($config['percentage'] ?? 0.3);
        $source = (string) ($config['source'] ?? 'total_points');

        if (!$enabled) {
            return [
                'enabled' => false,
                'percentage' => $pct,
                'source' => $source,
                'label' => $this->getSourceLabel($source),
                'attacker_points' => null,
                'min' => null,
                'max' => null,
            ];
        }

        $attackerPoints = $this->getUserPoints($attacker, $source);
        if ($attackerPoints === null) {
            return [
                'enabled' => $enabled,
                'percentage' => $pct,
                'source' => $source,
                'label' => $this->getSourceLabel($source),
                'attacker_points' => null,
                'min' => null,
                'max' => null,
            ];
        }

        $pct = max(0, min(1, $pct));
        $min = (int) floor($attackerPoints * (1 - $pct));
        $max = (int) ceil($attackerPoints * (1 + $pct));

        return [
            'enabled' => $enabled,
            'percentage' => $pct,
            'source' => $source,
            'label' => $this->getSourceLabel($source),
            'attacker_points' => $attackerPoints,
            'min' => $min,
            'max' => $max,
        ];
    }

    /**
     * Check whether target falls within allowed band.
     */
    public function checkTargetAllowed($attacker, $target, string $type): array
    {
        $band = $this->computeBand($attacker, $type);
        $allowed = true;
        $targetPoints = null;

        // Non-colonized target (no user) is allowed
        if ($target && method_exists($target, 'userStat')) {
            $targetPoints = $this->getUserPoints($target, $band['source']);
            if ($band['enabled'] && $band['min'] !== null && $targetPoints !== null) {
                $allowed = ($targetPoints >= $band['min'] && $targetPoints <= $band['max']);
            }
        }

        // Build default message
        $message = null;
        if ($band['enabled'] && !$allowed) {
            $message = "Bande autorisée " . number_format($band['min']) . "–" . number_format($band['max']) . " " . $band['label'] . ". Cible: " . number_format($targetPoints ?? 0) . ".";
        }

        return [
            'enabled' => $band['enabled'],
            'allowed' => $allowed,
            'percentage' => $band['percentage'],
            'source' => $band['source'],
            'label' => $band['label'],
            'attacker_points' => $band['attacker_points'],
            'target_points' => $targetPoints,
            'min' => $band['min'],
            'max' => $band['max'],
            'message' => $message,
        ];
    }
}