<?php

namespace App\Models\Planet;

use App\Models\Template\TemplateBuild;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanetUnit extends Model
{
    use HasFactory;

    protected $table = 'planet_units';

    protected $fillable = [
        'planet_id',
        'unit_id',
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
     * Get the planet this unit belongs to
     */
    public function planet(): BelongsTo
    {
        return $this->belongsTo(Planet::class);
    }

    /**
     * Get the template unit this is based on
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(TemplateBuild::class, 'unit_id');
    }

    /**
     * Check if units are currently being built
     */
    public function isUnderConstruction(): bool
    {
        return $this->is_building && $this->build_end_time && $this->build_end_time->isFuture();
    }

    /**
     * Check if unit construction is completed
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
     * Start building units
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
     * Complete unit construction
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
     * Cancel unit construction
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
     * Add units to existing quantity
     */
    public function addUnits(int $quantity): void
    {
        $this->increment('quantity', $quantity);
    }

    /**
     * Remove units from existing quantity
     */
    public function removeUnits(int $quantity): bool
    {
        if ($this->quantity < $quantity) {
            return false;
        }

        $this->decrement('quantity', $quantity);
        return true;
    }

    /**
     * Get unit cost for building quantity
     */
    public function getBuildCost(int $quantity = 1): array
    {
        $costs = [];
        
        foreach ($this->unit->costs as $cost) {
            $unitCost = $cost->calculateCostForLevel(1); // Units are level 1
            $finalCost = $unitCost * $quantity;
            
            // Appliquer le bonus de faction pour le coût des unités
            if ($this->planet && $this->planet->user && $this->planet->user->faction) {
                $unitCostBonus = $this->planet->user->faction->getBonusBuildingCost(); // Utiliser le même bonus que pour les bâtiments
                if ($unitCostBonus < 0) { // Bonus négatif = réduction de coût
                    $finalCost = (int)($finalCost * (1 + $unitCostBonus / 100));
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
        $baseTime = $this->unit->base_build_time;
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
     * Check if units can be built
     */
    public function canBuild(int $quantity = 1): bool
    {
        // Check if already building
        if ($this->is_building) {
            return false;
        }

        // Check requirements
        foreach ($this->unit->requirements as $requirement) {
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
     * Get total attack power
     */
    public function getTotalAttackPower(): int
    {
        // This would be calculated based on unit stats and quantity
        // For now, return a placeholder
        return $this->quantity * 100; // Placeholder
    }

    /**
     * Get total defense power
     */
    public function getTotalDefensePower(): int
    {
        // This would be calculated based on unit stats and quantity
        // For now, return a placeholder
        return $this->quantity * 50; // Placeholder
    }

    /**
     * Get total cargo capacity
     */
    public function getTotalCargoCapacity(): int
    {
        $baseCapacity = $this->unit->cargo_capacity;
        
        // Apply faction bonus if available
        $user = $this->planet->user;
        if ($user && $user->faction) {
            $factionBonus = $user->faction->getBonusShipCapacity();
            if ($factionBonus > 0) {
                // Apply the bonus as a percentage increase
                $baseCapacity += $baseCapacity * ($factionBonus / 100);
            }
        }
        
        return (int)$baseCapacity;
    }

    /**
     * Scope for active units
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for units under construction
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
     * Scope by unit type
     */
    public function scopeByUnitType($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    /**
     * Scope with minimum quantity
     */
    public function scopeMinQuantity($query, $quantity)
    {
        return $query->where('quantity', '>=', $quantity);
    }
}