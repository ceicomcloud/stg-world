<?php

namespace App\Models\Template;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateBuildDisadvantage extends Model
{
    use HasFactory;

    protected $table = 'template_build_disadvantages';

    protected $fillable = [
        'build_id',
        'resource_id',
        'disadvantage_type',
        'target_type',
        'base_value',
        'value_per_level',
        'calculation_type',
        'is_percentage',
        'is_active'
    ];

    protected $casts = [
        'build_id' => 'integer',
        'resource_id' => 'integer',
        'base_value' => 'decimal:2',
        'value_per_level' => 'decimal:2',
        'is_percentage' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Disadvantage types (aligned with migration)
    const TYPE_ENERGY_CONSUMPTION = 'energy_consumption';
    const TYPE_MAINTENANCE_COST = 'maintenance_cost';
    const TYPE_PRODUCTION_PENALTY = 'production_penalty';
    const TYPE_STORAGE_PENALTY = 'storage_penalty';
    const TYPE_RESEARCH_PENALTY = 'research_penalty';
    const TYPE_BUILD_PENALTY = 'build_penalty';
    const TYPE_DEFENSE_PENALTY = 'defense_penalty';
    const TYPE_ATTACK_PENALTY = 'attack_penalty';
    const TYPE_SPEED_PENALTY = 'speed_penalty';
    const TYPE_RESOURCE_CONSUMPTION = 'resource_consumption';

    // Target types (aligned with migration)
    const TARGET_RESOURCE = 'resource';
    const TARGET_BUILD = 'building'; // Legacy alias
    const TARGET_RESEARCH = 'research';
    const TARGET_TECHNOLOGY = 'technology';
    const TARGET_UNIT = 'unit';
    const TARGET_DEFENSE = 'defense';
    const TARGET_SHIP = 'ship';
    const TARGET_PLANET = 'planet';
    const TARGET_GLOBAL = 'global';

    // Calculation types (aligned with migration)
    const CALC_ADDITIVE = 'additive';  // base_value + (level * value_per_level)
    const CALC_MULTIPLICATIVE = 'multiplicative';  // base_value * (level * value_per_level)
    const CALC_EXPONENTIAL = 'exponential';  // base_value * (value_per_level ^ level)
    const CALC_PERCENTAGE = 'additive';  // Legacy alias for additive

    /**
     * Get the build this disadvantage belongs to
     */
    public function build(): BelongsTo
    {
        return $this->belongsTo(TemplateBuild::class, 'build_id');
    }

    /**
     * Get the target resource if target_type is resource
     */
    public function targetResource(): BelongsTo
    {
        return $this->belongsTo(TemplateResource::class, 'resource_id');
    }

    /**
     * Get the resource (alias for targetResource for compatibility)
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(TemplateResource::class, 'resource_id');
    }

    /**
     * Relation vers le bâtiment cible
     */
    public function targetBuild(): BelongsTo
    {
        return $this->belongsTo(TemplateBuild::class, 'resource_id');
    }

    /**
     * Calculate disadvantage value for a specific level
     */
    public function calculateValueForLevel(int $level): float
    {
        if ($level <= 0) {
            return 0;
        }

        switch ($this->calculation_type) {
            case self::CALC_ADDITIVE:
                return $this->base_value + ($level * $this->value_per_level);
            
            case self::CALC_MULTIPLICATIVE:
                return $this->base_value * ($level * $this->value_per_level);
            
            case self::CALC_EXPONENTIAL:
                return $this->base_value * pow($this->value_per_level, $level);
            
            default:
                return $this->base_value + ($level * $this->value_per_level);
        }
    }

    /**
     * Get description for this disadvantage
     */
    public function getDescriptionAttribute(): string
    {
        $value = $this->formatValue($this->base_value);
        $perLevel = $this->formatValue($this->value_per_level);
        $percentage = $this->is_percentage ? '%' : '';
        $resourceName = $this->targetResource ? $this->targetResource->display_name : '';
        
        switch ($this->disadvantage_type) {
            case self::TYPE_ENERGY_CONSUMPTION:
                return "Consommation d'énergie : -{$value}" . 
                       ($this->value_per_level > 0 ? " (-{$perLevel}/niveau)" : "");
                
            case self::TYPE_MAINTENANCE_COST:
                if ($resourceName) {
                    return "Maintenance en {$resourceName} : -{$value}{$percentage}" . 
                           ($this->value_per_level > 0 ? " (-{$perLevel}{$percentage}/niveau)" : "");
                }
                return "Coût de maintenance : -{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (-{$perLevel}{$percentage}/niveau)" : "");
                
            case self::TYPE_PRODUCTION_PENALTY:
                if ($resourceName) {
                    return "Pénalité de production de {$resourceName} : -{$value}{$percentage}" . 
                           ($this->value_per_level > 0 ? " (-{$perLevel}{$percentage}/niveau)" : "");
                }
                return "Pénalité de production : -{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (-{$perLevel}{$percentage}/niveau)" : "");
                
            case self::TYPE_STORAGE_PENALTY:
                if ($resourceName) {
                    return "Pénalité de stockage de {$resourceName} : -{$value}{$percentage}" . 
                           ($this->value_per_level > 0 ? " (-{$perLevel}{$percentage}/niveau)" : "");
                }
                return "Pénalité de stockage : -{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (-{$perLevel}{$percentage}/niveau)" : "");
                
            case self::TYPE_DEFENSE_PENALTY:
                return "Pénalité défensive : -{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (-{$perLevel}{$percentage}/niveau)" : "");
                
            case self::TYPE_SPEED_PENALTY:
                return "Pénalité de vitesse : -{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (-{$perLevel}{$percentage}/niveau)" : "");
                
            default:
                return "Effet négatif : -{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (-{$perLevel}{$percentage}/niveau)" : "");
        }
    }

    /**
     * Format numeric values for display
     */
    private function formatValue($value): string
    {
        // Remove unnecessary decimal places
        if ($value == (int)$value) {
            return (string)(int)$value;
        }
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    /**
     * Get total energy consumption for a planet
     */
    public static function getEnergyConsumption($planetId): float
    {
        $disadvantages = self::join('planet_buildings', 'template_build_disadvantages.build_id', '=', 'planet_buildings.building_id')
            ->where('planet_buildings.planet_id', $planetId)
            ->where('planet_buildings.is_active', true)
            ->where('template_build_disadvantages.disadvantage_type', self::TYPE_ENERGY_CONSUMPTION)
            ->where('template_build_disadvantages.is_active', true)
            ->get(['template_build_disadvantages.*', 'planet_buildings.level']);
        
        $totalConsumption = 0;
        foreach ($disadvantages as $disadvantage) {
            $totalConsumption += $disadvantage->calculateValueForLevel($disadvantage->level);
        }
        
        return $totalConsumption;
    }

    /**
     * Get maintenance cost for a planet
     */
    public static function getMaintenanceCost($planetId, $resourceId = null): float
    {
        $query = self::join('planet_buildings', 'template_build_disadvantages.build_id', '=', 'planet_buildings.building_id')
            ->where('planet_buildings.planet_id', $planetId)
            ->where('planet_buildings.is_active', true)
            ->where('template_build_disadvantages.disadvantage_type', self::TYPE_MAINTENANCE_COST)
            ->where('template_build_disadvantages.is_active', true);

        if ($resourceId) {
            $query->where(function($q) use ($resourceId) {
                $q->where('template_build_disadvantages.target_type', self::TARGET_RESOURCE)
                  ->where('template_build_disadvantages.resource_id', $resourceId)
                  ->orWhere('template_build_disadvantages.target_type', self::TARGET_GLOBAL);
            });
        } else {
            $query->where('template_build_disadvantages.target_type', self::TARGET_GLOBAL);
        }

        $disadvantages = $query->get(['template_build_disadvantages.*', 'planet_buildings.level']);
        
        $totalCost = 0;
        foreach ($disadvantages as $disadvantage) {
            $totalCost += $disadvantage->calculateValueForLevel($disadvantage->level);
        }
        
        return $totalCost;
    }

    /**
     * Get production penalty for a resource on a planet
     */
    public static function getProductionPenalty($planetId, $resourceId): float
    {
        $disadvantages = self::join('planet_buildings', 'template_build_disadvantages.build_id', '=', 'planet_buildings.building_id')
            ->where('planet_buildings.planet_id', $planetId)
            ->where('planet_buildings.is_active', true)
            ->where('template_build_disadvantages.disadvantage_type', self::TYPE_PRODUCTION_PENALTY)
            ->where('template_build_disadvantages.target_type', self::TARGET_RESOURCE)
            ->where('template_build_disadvantages.resource_id', $resourceId)
            ->where('template_build_disadvantages.is_active', true)
            ->get(['template_build_disadvantages.*', 'planet_buildings.level']);
        
        $totalPenalty = 0;
        foreach ($disadvantages as $disadvantage) {
            $totalPenalty += $disadvantage->calculateValueForLevel($disadvantage->level);
        }
        
        return $totalPenalty;
    }

    /**
     * Scope for active disadvantages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by disadvantage type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('disadvantage_type', $type);
    }

    /**
     * Scope by target type
     */
    public function scopeByTargetType($query, $targetType)
    {
        return $query->where('target_type', $targetType);
    }
}