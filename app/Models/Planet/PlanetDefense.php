<?php

namespace App\Models\Planet;

use App\Models\Template\TemplateBuild;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanetDefense extends Model
{
    use HasFactory;

    protected $table = 'planet_defenses';

    protected $fillable = [
        'planet_id',
        'defense_id',
        'quantity',
        'is_building',
        'build_queue',
        'build_start_time',
        'build_end_time',
        'is_active'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'is_building' => 'boolean',
        'build_queue' => 'integer',
        'build_start_time' => 'datetime',
        'build_end_time' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Get the planet this defense belongs to
     */
    public function planet(): BelongsTo
    {
        return $this->belongsTo(Planet::class);
    }

    /**
     * Get the template defense this is based on
     */
    public function defense(): BelongsTo
    {
        return $this->belongsTo(TemplateBuild::class, 'defense_id');
    }

    /**
     * Check if defenses are currently being built
     */
    public function isUnderConstruction(): bool
    {
        return $this->is_building && $this->build_end_time && $this->build_end_time->isFuture();
    }

    /**
     * Check if defense construction is completed
     */
    public function isConstructionCompleted(): bool
    {
        return $this->is_building && $this->build_end_time && $this->build_end_time->isPast();
    }

    /**
     * Get remaining build time in seconds
     */
    public function getRemainingBuildTime(): int
    {
        if (!$this->isUnderConstruction()) {
            return 0;
        }

        return max(0, $this->build_end_time->diffInSeconds(now()));
    }

    /**
     * Get build progress percentage
     */
    public function getBuildProgress(): float
    {
        if (!$this->is_building || !$this->build_start_time || !$this->build_end_time) {
            return 0;
        }

        $totalTime = $this->build_end_time->diffInSeconds($this->build_start_time);
        $elapsedTime = now()->diffInSeconds($this->build_start_time);

        if ($totalTime <= 0) {
            return 100;
        }

        return min(100, ($elapsedTime / $totalTime) * 100);
    }

    /**
     * Start building defenses
     */
    public function startBuilding(int $quantity, int $buildTimePerUnit): void
    {
        $totalBuildTime = $buildTimePerUnit * $quantity;
        
        $this->update([
            'is_building' => true,
            'build_queue' => $quantity,
            'build_start_time' => now(),
            'build_end_time' => now()->addSeconds($totalBuildTime)
        ]);
    }

    /**
     * Complete defense construction
     */
    public function completeConstruction(): void
    {
        $this->update([
            'quantity' => $this->quantity + $this->build_queue,
            'is_building' => false,
            'build_queue' => 0,
            'build_start_time' => null,
            'build_end_time' => null
        ]);
    }

    /**
     * Cancel defense construction
     */
    public function cancelConstruction(): void
    {
        $this->update([
            'is_building' => false,
            'build_queue' => 0,
            'build_start_time' => null,
            'build_end_time' => null
        ]);
    }

    /**
     * Add defenses to existing quantity
     */
    public function addDefenses(int $quantity): void
    {
        $this->increment('quantity', $quantity);
    }

    /**
     * Remove defenses from existing quantity (destroyed in battle)
     */
    public function removeDefenses(int $quantity): bool
    {
        if ($this->quantity < $quantity) {
            return false;
        }

        $this->decrement('quantity', $quantity);
        return true;
    }

    /**
     * Get defense cost for building quantity
     */
    public function getBuildCost(int $quantity = 1): array
    {
        $costs = [];
        
        foreach ($this->defense->costs as $cost) {
            $defenseCost = $cost->calculateCostForLevel(1); // Defenses are level 1
            $finalCost = $defenseCost * $quantity;
            
            // Appliquer le bonus de faction pour le coût des défenses
            if ($this->planet && $this->planet->user && $this->planet->user->faction) {
                $defenseCostBonus = $this->planet->user->faction->getBonusBuildingCost(); // Utiliser le même bonus que pour les bâtiments
                if ($defenseCostBonus < 0) { // Bonus négatif = réduction de coût
                    $finalCost = (int)($finalCost * (1 + $defenseCostBonus / 100));
                }
            }
            
            $costs[$cost->resource->name] = $finalCost;
        }
        
        return $costs;
    }

    /**
     * Get build time for quantity
     */
    public function getBuildTime(int $quantity = 1): int
    {
        $baseTime = $this->defense->base_build_time;
        $calculatedTime = $baseTime * $quantity;
        
        // Appliquer le bonus de faction pour la vitesse de construction
        if ($this->planet && $this->planet->user && $this->planet->user->faction) {
            $buildingSpeedBonus = $this->planet->user->faction->getBonusBuildingSpeed();
            if ($buildingSpeedBonus > 0) { // Bonus positif = réduction de temps
                $calculatedTime = (int)($calculatedTime * (1 - $buildingSpeedBonus / 100));
            }
        }
        
        return $calculatedTime;
    }

    /**
     * Check if defenses can be built
     */
    public function canBuild(int $quantity = 1): bool
    {
        // Check if already building
        if ($this->is_building) {
            return false;
        }

        // Check requirements
        foreach ($this->defense->requirements as $requirement) {
            if (!$requirement->isMetForPlanet($this->planet_id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if planet has enough resources for building
     */
    public function hasEnoughResourcesForBuild(int $quantity = 1): bool
    {
        $costs = $this->getBuildCost($quantity);
        
        foreach ($costs as $resourceName => $cost) {
            $planetResource = $this->planet->resources()
                ->whereHas('resource', function($query) use ($resourceName) {
                    $query->where('name', $resourceName);
                })
                ->first();
                
            if (!$planetResource || $planetResource->current_amount < $cost) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Consume resources for building
     */
    public function consumeBuildResources(int $quantity = 1): void
    {
        $costs = $this->getBuildCost($quantity);
        
        foreach ($costs as $resourceName => $cost) {
            $planetResource = $this->planet->resources()
                ->whereHas('resource', function($query) use ($resourceName) {
                    $query->where('name', $resourceName);
                })
                ->first();
                
            if ($planetResource) {
                $planetResource->decrement('current_amount', $cost);
            }
        }
    }

    /**
     * Get total defense power
     */
    public function getTotalDefensePower(): int
    {
        // This would be calculated based on defense stats and quantity
        // For now, return a placeholder
        return $this->quantity * 200; // Placeholder - defenses are stronger than units
    }

    /**
     * Get defense efficiency (considering damage taken)
     */
    public function getDefenseEfficiency(): float
    {
        // This could factor in damage taken, repairs needed, etc.
        // For now, return 100% efficiency
        return 1.0;
    }

    /**
     * Calculate repair cost for damaged defenses
     */
    public function getRepairCost(): array
    {
        // This would calculate cost to repair damaged defenses
        // For now, return empty array (no damage system implemented yet)
        return [];
    }

    /**
     * Repair defenses
     */
    public function repair(): void
    {
        // This would repair damaged defenses
        // Implementation depends on damage system
    }

    /**
     * Get planet's total defense rating
     */
    public static function getPlanetDefenseRating($planetId): int
    {
        return self::where('planet_id', $planetId)
            ->where('is_active', true)
            ->get()
            ->sum(function($defense) {
                return $defense->getTotalDefensePower();
            });
    }

    /**
     * Scope for active defenses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for defenses under construction
     */
    public function scopeUnderConstruction($query)
    {
        return $query->where('is_building', true)
            ->where('build_end_time', '>', now());
    }

    /**
     * Scope for completed constructions
     */
    public function scopeConstructionCompleted($query)
    {
        return $query->where('is_building', true)
            ->where('build_end_time', '<=', now());
    }

    /**
     * Scope by planet
     */
    public function scopeByPlanet($query, $planetId)
    {
        return $query->where('planet_id', $planetId);
    }

    /**
     * Scope by defense type
     */
    public function scopeByDefenseType($query, $defenseId)
    {
        return $query->where('defense_id', $defenseId);
    }

    /**
     * Scope with minimum quantity
     */
    public function scopeMinQuantity($query, $quantity)
    {
        return $query->where('quantity', '>=', $quantity);
    }
}