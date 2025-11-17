<?php

namespace App\Models\Planet;

use App\Models\User;
use App\Models\Template\TemplateBuild;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PlanetMission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_planet_id',
        'to_planet_id',
        'to_galaxy',
        'to_system',
        'to_position',
        'mission_type',
        'ships',
        'resources',
        'departure_time',
        'arrival_time',
        'return_time',
        'status',
        'result'
    ];

    protected $casts = [
        'ships' => 'array',
        'resources' => 'array',
        'result' => 'array',
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'return_time' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromPlanet(): BelongsTo
    {
        return $this->belongsTo(Planet::class, 'from_planet_id');
    }

    public function toPlanet(): BelongsTo
    {
        return $this->belongsTo(Planet::class, 'to_planet_id');
    }
    
    
    public function templatePlanet(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Template\TemplatePlanet::class, 'template_planet_id');
    }

    /**
     * Calculate mission duration based on distance between systems, ship speed and user's technology.
     * Optionally applies delays from ambient galactic events (e.g., wormhole_drift) when galaxy info is provided.
     */
    public static function calculateMissionDuration(int $fromSystem, int $toSystem, int $shipSpeed = 100, int $userId = null, ?int $fromGalaxy = null, ?int $toGalaxy = null): int
    {
        // Base duration for any mission
        $baseDuration = 5; // 5 minutes minimum
        
        // If same system, return base duration
        if ($fromSystem === $toSystem) {
            return $baseDuration;
        }
        
        // Calculate distance between systems
        $distance = abs($fromSystem - $toSystem);
        
        // Apply technology bonus to ship speed if user ID is provided
        if ($userId) {
            // Get movement speed bonus from user's technologies (like hyperpropulsion)
            $speedBonus = \App\Models\Template\TemplateBuildAdvantage::getMovementSpeedBonus($userId);
            
            // Apply the bonus to ship speed (percentage increase)
            if ($speedBonus > 0) {
                $shipSpeed = $shipSpeed * (1 + ($speedBonus / 100));
            }
            
            // Apply faction bonus to ship speed if user has a faction
            $user = \App\Models\User::find($userId);
            if ($user && $user->faction) {
                $factionSpeedBonus = $user->faction->getBonusShipSpeed();
                if ($factionSpeedBonus > 0) {
                    $shipSpeed = $shipSpeed * (1 + ($factionSpeedBonus / 100));
                }
            }
        }
        
        // Calculate additional time based on distance and ship speed
        // Higher speed = shorter travel time
        // Formula: base + (distance * factor / speed)
        // The factor 300 is chosen to maintain reasonable travel times
        $additionalTime = ceil(($distance * 300) / $shipSpeed);
        
        $duration = $baseDuration + $additionalTime;

        // Apply ambient event delays (e.g., wormhole_drift) when galaxy info is available
        if ($fromGalaxy !== null && $toGalaxy !== null) {
            try {
                /** @var \App\Services\GalacticEventService $eventService */
                $eventService = app(\App\Services\GalacticEventService::class);

                $delayPercent = 0.0;

                // Origin sector events
                $originEvents = $eventService->getActiveEventsForSector((int) $fromGalaxy, (int) $fromSystem);
                foreach ($originEvents as $ev) {
                    if ((string) $ev->key === 'wormhole_drift' && $ev->position === null && $ev->isActive()) {
                        $severity = strtolower((string) ($ev->severity ?? 'medium'));
                        $severityFactor = $severity === 'high' ? 1.25 : ($severity === 'low' ? 0.75 : 1.0);
                        $delayPercent += 0.10 * $severityFactor; // ~10% base delay
                        break; // only count once per sector
                    }
                }

                // Destination sector events
                $destEvents = $eventService->getActiveEventsForSector((int) $toGalaxy, (int) $toSystem);
                foreach ($destEvents as $ev) {
                    if ((string) $ev->key === 'wormhole_drift' && $ev->position === null && $ev->isActive()) {
                        $severity = strtolower((string) ($ev->severity ?? 'medium'));
                        $severityFactor = $severity === 'high' ? 1.25 : ($severity === 'low' ? 0.75 : 1.0);
                        $delayPercent += 0.10 * $severityFactor;
                        break;
                    }
                }

                // Cap total delay to avoid extremes (e.g., 25%)
                $delayPercent = min($delayPercent, 0.25);

                if ($delayPercent > 0.0) {
                    $duration += (int) ceil($duration * $delayPercent);
                }
            } catch (\Throwable $e) {
                // Fail-safe: ignore event delays if any error occurs
            }
        }

        return $duration;
    }

    /**
     * Calculate fuel consumption for a mission
     *
     * @param array $selectedShips
     * @param int $originSystem
     * @param int $destinationSystem
     * @param bool $isRoundTrip
     * @return int
     */
    public static function calculateFuelConsumption($selectedShips, $originSystem, $destinationSystem, $isRoundTrip = true)
    {
        $distance = abs($originSystem - $destinationSystem);
        $fuelConsumption = 0;
        $tripMultiplier = $isRoundTrip ? 2 : 1;

        foreach ($selectedShips as $id => $quantity) {
            if ($quantity > 0) {
                $ship = TemplateBuild::find($id);
                if ($ship) {
                    $fuelConsumption += $quantity * $ship->fuel_consumption * $distance * $tripMultiplier;
                }
            }
        }

        return $fuelConsumption;
    }

    /**
     * Calculate the slowest speed among selected ships
     *
     * @param array $selectedShips
     * @param array $availableShips
     * @return int
     */
    public static function calculateSpeed($selectedShips, $availableShips)
    {
        $slowestSpeed = PHP_INT_MAX;
        
        foreach ($selectedShips as $id => $quantity) {
            if ($quantity > 0) {
                $ship = collect($availableShips)->firstWhere('id', $id);
                if ($ship && $ship['speed'] < $slowestSpeed) {
                    $slowestSpeed = $ship['speed'];
                }
            }
        }
        
        return $slowestSpeed;
    }

    /**
     * Check if mission is ready to arrive
     */
    public function isReadyToArrive(): bool
    {
        return $this->status === 'traveling' && Carbon::now()->gte($this->arrival_time);
    }

    /**
     * Check if mission is ready to return
     */
    public function isReadyToReturn(): bool
    {
        return $this->status === 'returning' && $this->return_time && Carbon::now()->gte($this->return_time);
    }

    /**
     * Get missions that need processing
     */
    public static function getPendingMissions()
    {
        return static::whereIn('status', ['traveling', 'returning'])
            ->where(function ($query) {
                $query->where('status', 'traveling')
                      ->where('arrival_time', '<=', Carbon::now())
                      ->orWhere(function ($subQuery) {
                          $subQuery->where('status', 'returning')
                                   ->where('return_time', '<=', Carbon::now());
                      });
            })
            ->get();
    }

    /**
     * Get translated mission type
     */
    public function getType(): string
    {
        return match($this->mission_type) {
            'colonize' => 'Colonisation',
            'attack' => 'Attaque',
            'spy' => 'Espionnage',
            'transport' => 'Transport',
            'defend' => 'Défense',
            'harvest' => 'Récolte',
            'explore' => 'Exploration',
            'extract' => 'Extraction',
            'basement' => "Basement",
            default => ucfirst($this->mission_type)
        };
    }

    /**
     * Get translated mission status
     */
    public function getStatus(): string
    {
        return match($this->status) {
            'traveling' => 'En route',
            'returning' => 'En retour',
            'collecting' => 'En cours de collecte',
            'exploring' => 'En cours d\'exploration',
            'arrived' => 'Arrivée',
            'completed' => 'Terminée',
            'failed' => 'Échouée',
            'cancelled' => 'Annulée',
            'pending' => 'En attente',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get remaining time for the mission
     */
    public function getTimeRemaining(): string
    {
        $now = Carbon::now();
        $targetTime = null;

        if ($this->status === 'traveling' && $this->arrival_time) {
            $targetTime = $this->arrival_time;
        } elseif (($this->status === 'returning' || $this->status === 'collecting') && $this->return_time) {
            $targetTime = $this->return_time;
        }

        if (!$targetTime) {
            return 'Temps non défini';
        }

        if ($now->gte($targetTime)) {
            return 'Arrivée!';
        }

        $diff = $targetTime->diff($now);
        
        $hours = $diff->h + ($diff->days * 24);
        $minutes = $diff->i;
        $seconds = $diff->s;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Count missions that are currently flying (traveling or returning) for a user
     */
    public static function countUserFlyingMissions(int $userId): int
    {
        return static::where('user_id', $userId)
            ->whereIn('status', ['traveling', 'returning', 'collecting', 'exploring'])
            ->count();
    }

    /**
     * Get command center level for a planet
     */
    public static function getCommandCenterLevelForPlanet(int $planetId): int
    {
        $commandCenter = \App\Models\Template\TemplateBuild::where('name', 'centre_commandement')->first();
        if (!$commandCenter) {
            return 0;
        }

        $planetBuilding = \App\Models\Planet\PlanetBuilding::where('planet_id', $planetId)
            ->where('building_id', $commandCenter->id)
            ->where('is_active', true)
            ->first();


        return (int) ($planetBuilding?->level ?? 0);
    }

    /**
     * Compute allowed flying fleets based on command center level.
     * Rule: 1 fleet per 2 levels, minimum 1.
     */
    public static function getAllowedFlyingFleetsForPlanet(int $planetId): int
    {
        // Try advantage-based capacity if configured
        $advCap = \App\Models\Template\TemplateBuildAdvantage::getFleetCapacity($planetId);
        if ($advCap !== null) {
            return (int) $advCap;
        }

        // Fallback: compute from command center level
        $level = self::getCommandCenterLevelForPlanet($planetId);
        $allowed = (int) floor($level / 2);
        return max(1, $allowed);
    }
}