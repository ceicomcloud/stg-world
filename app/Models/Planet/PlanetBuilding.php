<?php

namespace App\Models\Planet;

use App\Models\Template\TemplateBuild;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanetBuilding extends Model
{
    use HasFactory;

    protected $table = 'planet_buildings';

    protected $fillable = [
        'planet_id',
        'building_id',
        'level',
        'is_active'
    ];

    protected $casts = [
        'level' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * Boot the model and set up event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // When a building level is updated, manage field usage
        static::updating(function ($building) {
            $originalLevel = $building->getOriginal('level');
            $newLevel = $building->level;
            
            // Calculate field difference
            $fieldDifference = $newLevel - $originalLevel;
            
            if ($fieldDifference > 0) {
                // Building is being upgraded, use more fields
                if (!$building->planet->hasAvailableFields($fieldDifference)) {
                    throw new \Exception('Pas assez de champs disponibles pour cette amélioration.');
                }
                $building->planet->useFields($fieldDifference);
            } elseif ($fieldDifference < 0) {
                // Building is being downgraded, free fields
                $building->planet->freeFields(abs($fieldDifference));
            }
        });

        // When a building is created with level > 0, use fields
        static::created(function ($building) {
            if ($building->level > 0) {
                if (!$building->planet->hasAvailableFields($building->level)) {
                    throw new \Exception('Pas assez de champs disponibles pour cette construction.');
                }
                $building->planet->useFields($building->level);
            }
        });

        // When a building is deleted, free its fields
        static::deleting(function ($building) {
            if ($building->level > 0) {
                $building->planet->freeFields($building->level);
            }
        });
    }

    /**
     * Get the planet this building belongs to
     */
    public function planet(): BelongsTo
    {
        return $this->belongsTo(Planet::class);
    }

    /**
     * Get the template build this building is based on
     */
    public function build(): BelongsTo
    {
        return $this->belongsTo(TemplateBuild::class, 'building_id');
    }

    /**

    * Alias for build() method for backward compatibility
     */
    public function building(): BelongsTo
    {
        return $this->build();
    }

    /**
     * Upgrade building to next level (called by Queue system)
     */
    public function upgradeToLevel(int $newLevel): void
    {
        $this->update([
            'level' => $newLevel
        ]);
    }

    /**
     * Get building cost for next level
     */
    public function getUpgradeCost(): array
    {
        $costs = [];
        $nextLevel = $this->level + 1;
        
        foreach ($this->build->costs as $cost) {
            $costs[$cost->resource->name] = $cost->calculateCostForLevel($nextLevel);
        }
        
        return $costs;
    }

    /**
     * Get building time for next level
     */
    public function getUpgradeTime(): int
    {
        $nextLevel = $this->level + 1;
        $baseTime = $this->build->base_build_time;
        
        // Formula: base_time * (1.5 ^ (level - 1))
        return (int) ($baseTime * pow(1.5, $nextLevel - 1));
    }

    /**
     * Check if building can be upgraded
     */
    public function canUpgrade(): bool
    {
        // Check max level
        if ($this->build->max_level > 0 && $this->level >= $this->build->max_level) {
            return false;
        }

        // Check requirements
        foreach ($this->build->requirements as $requirement) {
            if (!$requirement->isMetForPlanet($this->planet_id)) {
                return false;
            }
        }

        // Check if planet has enough fields
        if ($this->level == 0 && !$this->planet->hasAvailableFields(1)) {
            return false;
        }

        return true;
    }

    /**
     * Check if planet has enough resources for upgrade
     */
    public function hasEnoughResourcesForUpgrade(): bool
    {
        $costs = $this->getUpgradeCost();
        
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
     * Consume resources for upgrade
     */
    public function consumeUpgradeResources(): void
    {
        $costs = $this->getUpgradeCost();
        
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
     * Scope for active buildings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }



    /**
     * Scope by planet
     */
    public function scopeByPlanet($query, $planetId)
    {
        return $query->where('planet_id', $planetId);
    }

    /**
     * Scope by build type
     */
    public function scopeByBuildType($query, $type)
    {
        return $query->whereHas('build', function($q) use ($type) {
            $q->where('type', $type);
        });
    }

    /**
     * Scope by minimum level
     */
    public function scopeMinLevel($query, $level)
    {
        return $query->where('level', '>=', $level);
    }



    /**
     * Get building name with level
     */
    public function getBuildingDisplayName(): string
    {
        $name = $this->build->label ?? $this->build->name;
        return $name . ' (Niveau ' . ($this->level + 1) . ')';
    }
}