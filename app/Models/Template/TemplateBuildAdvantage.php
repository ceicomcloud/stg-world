<?php

namespace App\Models\Template;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateBuildAdvantage extends Model
{
    use HasFactory;

    protected $table = 'template_build_advantages';

    protected $fillable = [
        'build_id',
        'resource_id',
        'advantage_type',
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

    // Advantage types (aligned with migration)
    const TYPE_PRODUCTION_BOOST = 'production_boost';
    const TYPE_STORAGE_BONUS = 'storage_bonus';
    const TYPE_STORAGE_CAPACITY = 'storage_capacity';
    const TYPE_ENERGY_PRODUCTION = 'energy_production';
    const TYPE_RESEARCH_SPEED = 'research_speed';
    const TYPE_BUILD_SPEED = 'build_speed';
    const TYPE_DEFENSE_BOOST = 'defense_boost';
    const TYPE_DEFENSE_BONUS = 'defense_bonus';
    const TYPE_ATTACK_BOOST = 'attack_boost';
    const TYPE_ATTACK_BONUS = 'attack_bonus';
    const TYPE_CAPACITY_INCREASE = 'capacity_increase';
    const TYPE_SHIELD_BONUS = 'shield_bonus';
    const TYPE_SPEED_BONUS = 'speed_bonus';
    const TYPE_GLOBAL_EFFICIENCY = 'global_efficiency';
    const TYPE_COMMAND_EFFICIENCY = 'command_efficiency';
    const TYPE_TERRITORY_EXPANSION = 'territory_expansion';
    const TYPE_ENERGY_EFFICIENCY = 'energy_efficiency';
    const TYPE_ARMOR_BOOST = 'armor_boost';
    const TYPE_ESPIONAGE_EFFICIENCY = 'espionage_efficiency';
    const TYPE_MOVEMENT_SPEED = 'movement_speed';
    const TYPE_WEAPON_POWER = 'weapon_power';
    const TYPE_ATTACK_RANGE = 'attack_range';
    const TYPE_STEALTH_BOOST = 'stealth_boost';
    const TYPE_PRODUCTION_SPEED = 'production_speed';
    const TYPE_RESOURCE_EFFICIENCY = 'resource_efficiency';
    const TYPE_ULTIMATE_BOOST = 'ultimate_boost';
    const TYPE_BUNKER_BOOST = 'bunker_boost';
    const TYPE_FLEET_CAPACITY = 'fleet_capacity';
    
    // Legacy constants for backward compatibility
    const TYPE_STORAGE_INCREASE = 'storage_bonus'; // Maps to storage_bonus

    // Target types (aligned with migration)
    const TARGET_RESOURCE = 'resource';
    const TARGET_BUILD = 'building'; 
    const TARGET_RESEARCH = 'research';
    const TARGET_TECHNOLOGY = 'technology';
    const TARGET_UNIT = 'unit';
    const TARGET_DEFENSE = 'defense';
    const TARGET_SHIP = 'ship';
    const TARGET_PLANET = 'planet';
    const TARGET_GLOBAL = 'global';
    const TARGET_DRONE = 'drone';
    const TARGET_MISSION = 'mission';
    const TARGET_FLEET = 'fleet';

    // Calculation types (aligned with migration)
    const CALC_ADDITIVE = 'additive';  // base_value + (level * value_per_level)
    const CALC_MULTIPLICATIVE = 'multiplicative';  // base_value * (level * value_per_level)
    const CALC_EXPONENTIAL = 'exponential';  // base_value * (value_per_level ^ level)
    const CALC_TRIANGULAR = 'triangular';  // base_value + value_per_level * (level * (level + 1) / 2)
    const CALC_PERCENTAGE = 'additive';  // Legacy alias for additive

    /**
     * Get the build this advantage belongs to
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
     * Get the target build if target_type is build
     */
    public function targetBuild(): BelongsTo
    {
        return $this->belongsTo(TemplateBuild::class, 'resource_id');
    }

    /**
     * Calculate advantage value for a specific level
     */
    public function calculateValueForLevel(int $level): float
    {
        if ($level <= 0) {
            return 0;
        }

        // Safety: storage advantages must be additive (base + per_level * level)
        // to match descriptions like "+120000 (+60000/niveau)".
        // Using multiplicative here leads to absurdly large capacities.
        if (in_array($this->advantage_type, [self::TYPE_STORAGE_BONUS, self::TYPE_STORAGE_CAPACITY], true)) {
            return (float) $this->base_value + ($level * (float) $this->value_per_level);
        }

        switch ($this->calculation_type) {
            case self::CALC_ADDITIVE:
                return $this->base_value + ($level * $this->value_per_level);
            
            case self::CALC_MULTIPLICATIVE:
                return $this->base_value * ($level * $this->value_per_level);
            
            case self::CALC_EXPONENTIAL:
                return $this->base_value * pow($this->value_per_level, $level);
            
            case self::CALC_TRIANGULAR:
                // Sum of 1..level multiplied by value_per_level
                return $this->base_value + ($this->value_per_level * (($level * ($level + 1)) / 2));
            
            default:
                return $this->base_value + ($level * $this->value_per_level);
        }
    }

    /**
     * Get description for this advantage
     */
    public function getDescriptionAttribute(): string
    {
        $value = $this->formatValue($this->base_value);
        $perLevel = $this->formatValue($this->value_per_level);
        $percentage = $this->is_percentage ? '%' : '';
        $resourceName = $this->targetResource ? $this->targetResource->display_name : '';
        
        switch ($this->advantage_type) {
            case self::TYPE_PRODUCTION_BOOST:
                if ($resourceName) {
                    return "Production de {$resourceName} : +{$value}{$percentage}" . 
                           ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");
                }
                return "Bonus de production : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");
                
            case self::TYPE_STORAGE_BONUS:
            case self::TYPE_STORAGE_CAPACITY:
            case self::TYPE_STORAGE_INCREASE: // Legacy support
                if ($resourceName) {
                    return "Stockage de {$resourceName} : +{$value}{$percentage}" . 
                           ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");
                }
                return "Capacité de stockage : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");
                
            case self::TYPE_ENERGY_PRODUCTION:
                return "Production d'énergie : +{$value}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}/niveau)" : "");
                
            case self::TYPE_RESEARCH_SPEED:
                return "Vitesse de recherche : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");
                
            case self::TYPE_BUILD_SPEED:
                return "Vitesse de construction : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");
                
            case self::TYPE_DEFENSE_BOOST:
            case self::TYPE_DEFENSE_BONUS:
                return "Bonus défensif : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");
                
            case self::TYPE_ATTACK_BOOST:
            case self::TYPE_ATTACK_BONUS:
                return "Bonus offensif : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");
                
            case self::TYPE_CAPACITY_INCREASE:
                return "Capacité de transport : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");

            case self::TYPE_SHIELD_BONUS:
                return "Bonus de bouclier : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");

            case self::TYPE_SPEED_BONUS:
                return "Bonus de vitesse : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");

            case self::TYPE_BUNKER_BOOST:
                return "Capacité de stockage du bunker : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");

            case self::TYPE_FLEET_CAPACITY:
                return "Capacité de flottes en vol : +{$value}{$percentage}" .
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");
                
            case self::TYPE_GLOBAL_EFFICIENCY:
                return "Efficacité globale : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");

            case self::TYPE_COMMAND_EFFICIENCY:
                return "Efficacité de commandement : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");

            case self::TYPE_TERRITORY_EXPANSION:
                return "Expansion territoriale : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");

            case self::TYPE_ENERGY_EFFICIENCY:
                return "Efficacité énergétique : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");

            case self::TYPE_ARMOR_BOOST:
                return "Amélioration d'armure : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");

            case self::TYPE_ESPIONAGE_EFFICIENCY:
                return "Efficacité d'espionnage : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");

            case self::TYPE_MOVEMENT_SPEED:
                return "Vitesse de déplacement : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");

            case self::TYPE_WEAPON_POWER:
                return "Puissance d'arme : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");

            case self::TYPE_ATTACK_RANGE:
                return "Portée d'attaque : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");

            case self::TYPE_STEALTH_BOOST:
                return "Amélioration de furtivité : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");

            case self::TYPE_PRODUCTION_SPEED:
                return "Vitesse de production : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");

            case self::TYPE_RESOURCE_EFFICIENCY:
                return "Efficacité des ressources : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");

            case self::TYPE_ULTIMATE_BOOST:
                return "Amélioration ultime : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");
                
            default:
                return "Effet spécial : +{$value}{$percentage}" . 
                       ($this->value_per_level > 0 ? " (+{$perLevel}{$percentage}/niveau)" : "");
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
     * Get bunker boost value for a specific building level
     * 
     * @param int $buildId ID of the building (centre_commandement)
     * @param int $level Current level of the building
     * @return float Bunker boost value
     */
    public static function getBunkerBoost(int $buildId, int $level): float
    {
        $advantage = self::where('build_id', $buildId)
            ->where('advantage_type', self::TYPE_BUNKER_BOOST)
            ->where('is_active', true)
            ->first();
            
        if (!$advantage) {
            return 0;
        }
        // Bunker capacity follows a triangular progression: base + vpl * sum(1..level)
        return $advantage->base_value + ($advantage->value_per_level * (($level * ($level + 1)) / 2));
    }

    /**
     * Get storage bonus for a planet
     */
    public static function getStorageBonus($planetId, $resourceId = null): float
    {
        // Get planet to access user_id
        $planet = \App\Models\Planet\Planet::find($planetId);
        if (!$planet) {
            return 0;
        }

        $totalBonus = 0;

        // Get bonus from buildings
        $buildingQuery = self::join('planet_buildings', 'template_build_advantages.build_id', '=', 'planet_buildings.building_id')
            ->where('planet_buildings.planet_id', $planetId)
            ->where('planet_buildings.is_active', true)
            ->where('template_build_advantages.advantage_type', self::TYPE_STORAGE_BONUS)
            ->where('template_build_advantages.is_active', true);

        if ($resourceId) {
            $buildingQuery->where(function($q) use ($resourceId) {
                $q->where('template_build_advantages.target_type', self::TARGET_RESOURCE)
                  ->where('template_build_advantages.resource_id', $resourceId)
                  ->orWhere('template_build_advantages.target_type', self::TARGET_GLOBAL);
            });
        } else {
            $buildingQuery->where('template_build_advantages.target_type', self::TARGET_GLOBAL);
        }

        $buildingAdvantages = $buildingQuery->get(['template_build_advantages.*', 'planet_buildings.level']);
        
        foreach ($buildingAdvantages as $advantage) {
            $totalBonus += $advantage->calculateValueForLevel($advantage->level);
        }

        // Get bonus from technologies
        $technologyQuery = self::join('user_technologies', 'template_build_advantages.build_id', '=', 'user_technologies.technology_id')
            ->where('user_technologies.user_id', $planet->user_id)
            ->where('template_build_advantages.advantage_type', self::TYPE_STORAGE_BONUS)
            ->where('template_build_advantages.is_active', true)
            ->where('user_technologies.is_active', true)
            ->where('user_technologies.level', '>', 0);

        if ($resourceId) {
            $technologyQuery->where(function($q) use ($resourceId) {
                $q->where('template_build_advantages.target_type', self::TARGET_RESOURCE)
                  ->where('template_build_advantages.resource_id', $resourceId)
                  ->orWhere('template_build_advantages.target_type', self::TARGET_GLOBAL);
            });
        } else {
            $technologyQuery->where('template_build_advantages.target_type', self::TARGET_GLOBAL);
        }

        $technologyAdvantages = $technologyQuery->get(['template_build_advantages.*', 'user_technologies.level']);
        
        foreach ($technologyAdvantages as $advantage) {
            $totalBonus += $advantage->calculateValueForLevel($advantage->level);
        }
        
        return $totalBonus;
    }

    /**
     * Get energy production for a planet
     */
    public static function getEnergyProduction($planetId): float
    {
        // Get planet to access user_id
        $planet = \App\Models\Planet\Planet::find($planetId);
        if (!$planet) {
            return 0;
        }

        $totalProduction = 0;

        // Get production from buildings
        $buildingAdvantages = self::join('planet_buildings', 'template_build_advantages.build_id', '=', 'planet_buildings.building_id')
            ->where('planet_buildings.planet_id', $planetId)
            ->where('planet_buildings.is_active', true)
            ->where('template_build_advantages.advantage_type', self::TYPE_ENERGY_PRODUCTION)
            ->where('template_build_advantages.is_active', true)
            ->get(['template_build_advantages.*', 'planet_buildings.level']);
        
        foreach ($buildingAdvantages as $advantage) {
            $totalProduction += $advantage->calculateValueForLevel($advantage->level);
        }

        // Get production from technologies
        $technologyAdvantages = self::join('user_technologies', 'template_build_advantages.build_id', '=', 'user_technologies.technology_id')
            ->where('user_technologies.user_id', $planet->user_id)
            ->where('template_build_advantages.advantage_type', self::TYPE_ENERGY_PRODUCTION)
            ->where('template_build_advantages.is_active', true)
            ->where('user_technologies.is_active', true)
            ->where('user_technologies.level', '>', 0)
            ->get(['template_build_advantages.*', 'user_technologies.level']);
        
        foreach ($technologyAdvantages as $advantage) {
            $totalProduction += $advantage->calculateValueForLevel($advantage->level);
        }
        
        return $totalProduction;
    }

    /**
     * Get max fleets in flight capacity from advantages, if defined.
     * Returns null if no advantage is configured.
     */
    public static function getFleetCapacity(int $planetId): ?int
    {
        $planet = \App\Models\Planet\Planet::find($planetId);
        if (!$planet) {
            return null;
        }

        $advantages = self::join('planet_buildings', 'template_build_advantages.build_id', '=', 'planet_buildings.building_id')
            ->where('planet_buildings.planet_id', $planetId)
            ->where('planet_buildings.is_active', true)
            ->where('template_build_advantages.advantage_type', self::TYPE_FLEET_CAPACITY)
            ->where('template_build_advantages.is_active', true)
            ->get(['template_build_advantages.*', 'planet_buildings.level']);

        if ($advantages->isEmpty()) {
            return null;
        }

        $total = 0.0;
        foreach ($advantages as $adv) {
            $total += $adv->calculateValueForLevel((int)$adv->level);
        }

        // Capacité entière, avec minimum 1
        $cap = (int)floor($total);
        return max(1, $cap);
    }

    /**
     * Get production boost for a resource on a planet
     */
    public static function getProductionBoost($planetId, $resourceId): float
    {
        // Get planet to access user_id
        $planet = \App\Models\Planet\Planet::find($planetId);
        if (!$planet) {
            return 0;
        }

        $totalBoost = 0;

        // Get boost from buildings
        $buildingAdvantages = self::join('planet_buildings', 'template_build_advantages.build_id', '=', 'planet_buildings.building_id')
            ->where('planet_buildings.planet_id', $planetId)
            ->where('planet_buildings.is_active', true)
            ->where('template_build_advantages.advantage_type', self::TYPE_PRODUCTION_BOOST)
            ->where('template_build_advantages.target_type', self::TARGET_RESOURCE)
            ->where('template_build_advantages.resource_id', $resourceId)
            ->where('template_build_advantages.is_active', true)
            ->get(['template_build_advantages.*', 'planet_buildings.level']);
        
        foreach ($buildingAdvantages as $advantage) {
            $totalBoost += $advantage->calculateValueForLevel($advantage->level);
        }

        // Get boost from technologies
        $technologyAdvantages = self::join('user_technologies', 'template_build_advantages.build_id', '=', 'user_technologies.technology_id')
            ->where('user_technologies.user_id', $planet->user_id)
            ->where('template_build_advantages.advantage_type', self::TYPE_PRODUCTION_BOOST)
            ->where('template_build_advantages.target_type', self::TARGET_RESOURCE)
            ->where('template_build_advantages.resource_id', $resourceId)
            ->where('template_build_advantages.is_active', true)
            ->where('user_technologies.is_active', true)
            ->where('user_technologies.level', '>', 0)
            ->get(['template_build_advantages.*', 'user_technologies.level']);
        
        foreach ($technologyAdvantages as $advantage) {
            $totalBoost += $advantage->calculateValueForLevel($advantage->level);
        }
        
        return $totalBoost;
    }

    /**
     * Scope for active advantages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by advantage type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('advantage_type', $type);
    }

    /**
     * Scope by target type
     */
    public function scopeByTargetType($query, $targetType)
    {
        return $query->where('target_type', $targetType);
    }

    /**
     * Get research points production for a planet
     */
    public static function getResearchPointsProduction($planetId): float
    {
        // Get planet to access user_id
        $planet = \App\Models\Planet\Planet::find($planetId);
        if (!$planet) {
            return 0;
        }

        $totalBoost = 0;

        // Get boost from buildings
        $buildingAdvantages = self::join('planet_buildings', 'template_build_advantages.build_id', '=', 'planet_buildings.building_id')
            ->where('planet_buildings.planet_id', $planetId)
            ->where('planet_buildings.is_active', true)
            ->where('template_build_advantages.advantage_type', 'research_speed')
            ->where('template_build_advantages.is_active', true)
            ->get(['template_build_advantages.*', 'planet_buildings.level']);
        
        foreach ($buildingAdvantages as $advantage) {
            $totalBoost += $advantage->calculateValueForLevel($advantage->level);
        }

        // Get boost from technologies
        $technologyAdvantages = self::join('user_technologies', 'template_build_advantages.build_id', '=', 'user_technologies.technology_id')
            ->where('user_technologies.user_id', $planet->user_id)
            ->where('template_build_advantages.advantage_type', 'research_speed')
            ->where('template_build_advantages.is_active', true)
            ->where('user_technologies.is_active', true)
            ->where('user_technologies.level', '>', 0)
            ->get(['template_build_advantages.*', 'user_technologies.level']);
        
        foreach ($technologyAdvantages as $advantage) {
            $totalBoost += $advantage->calculateValueForLevel($advantage->level);
        }
        
        return $totalBoost;
    }

    /**
     * Get movement speed bonus for ships based on user's technologies
     */
    public static function getMovementSpeedBonus($userId): float
    {
        if (!$userId) {
            return 0;
        }

        $totalBoost = 0;

        // Get boost from technologies related to ship movement speed
        $technologyAdvantages = self::join('user_technologies', 'template_build_advantages.build_id', '=', 'user_technologies.technology_id')
            ->where('user_technologies.user_id', $userId)
            ->where('template_build_advantages.advantage_type', self::TYPE_MOVEMENT_SPEED)
            ->where('template_build_advantages.target_type', self::TARGET_SHIP)
            ->where('template_build_advantages.is_active', true)
            ->where('user_technologies.is_active', true)
            ->where('user_technologies.level', '>', 0)
            ->get(['template_build_advantages.*', 'user_technologies.level']);
        
        foreach ($technologyAdvantages as $advantage) {
            $totalBoost += $advantage->calculateValueForLevel($advantage->level);
        }
        
        return $totalBoost;
    }

    /**
     * Get build speed bonus for specific build type based on building level
     * 
     * @param int $planetId ID of the planet
     * @param string $targetType Type of target (unit, defense, ship)
     * @return float Build speed bonus percentage
     */
    public static function getBuildSpeedBonus($planetId, $targetType): float
    {
        // Get planet to access user_id
        $planet = \App\Models\Planet\Planet::find($planetId);
        if (!$planet) {
            return 0;
        }

        $totalBonus = 0;
        
        // Map target types to specific buildings
        $buildingMap = [
            self::TARGET_UNIT => 'caserne',
            self::TARGET_DEFENSE => 'plateforme_defensive',
            self::TARGET_SHIP => 'chantier_spatial',
            self::TARGET_BUILD => 'centre_commandement'
        ];
        
        // If target type is not mapped, return 0
        if (!isset($buildingMap[$targetType])) {
            return 0;
        }
        
        // Get the building that provides bonus for this target type
        $buildingName = $buildingMap[$targetType];
        $building = \App\Models\Planet\PlanetBuilding::where('planet_id', $planetId)
            ->whereHas('build', function($query) use ($buildingName) {
                $query->where('name', $buildingName);
            })
            ->with('build')
            ->first();
        
        if (!$building || $building->level <= 0) {
            return 0;
        }
        
        // Get the advantage for this building
        $advantage = self::where('build_id', $building->building_id)
            ->where('advantage_type', self::TYPE_BUILD_SPEED)
            ->where('is_active', true)
            ->first();
        
        if ($advantage) {
            $totalBonus = $advantage->calculateValueForLevel($building->level);
        }
        
        return $totalBonus;
    }

    /**
     * Get attack bonus for ground units based on planet buildings and user technologies
     */
    public static function getAttackBonus($planetId): float
    {
        // Get planet to access user_id
        $planet = \App\Models\Planet\Planet::find($planetId);
        if (!$planet) {
            return 0;
        }

        $totalBonus = 0;

        // Get bonus from buildings
        $buildingAdvantages = self::join('planet_buildings', 'template_build_advantages.build_id', '=', 'planet_buildings.building_id')
            ->where('planet_buildings.planet_id', $planetId)
            ->where('planet_buildings.is_active', true)
            ->where('template_build_advantages.advantage_type', self::TYPE_ATTACK_BONUS)
            ->where('template_build_advantages.target_type', self::TARGET_UNIT)
            ->where('template_build_advantages.is_active', true)
            ->get(['template_build_advantages.*', 'planet_buildings.level']);
        
        foreach ($buildingAdvantages as $advantage) {
            $totalBonus += $advantage->calculateValueForLevel($advantage->level);
        }

        // Get bonus from technologies
        $technologyAdvantages = self::join('user_technologies', 'template_build_advantages.build_id', '=', 'user_technologies.technology_id')
            ->where('user_technologies.user_id', $planet->user_id)
            ->where('template_build_advantages.advantage_type', self::TYPE_ATTACK_BONUS)
            ->where('template_build_advantages.target_type', self::TARGET_UNIT)
            ->where('template_build_advantages.is_active', true)
            ->where('user_technologies.is_active', true)
            ->where('user_technologies.level', '>', 0)
            ->get(['template_build_advantages.*', 'user_technologies.level']);
        
        foreach ($technologyAdvantages as $advantage) {
            $totalBonus += $advantage->calculateValueForLevel($advantage->level);
        }
        
        return $totalBonus;
    }

    /**
     * Get defense bonus for ground units based on planet buildings and user technologies
     */
    public static function getDefenseBonus($planetId): float
    {
        // Get planet to access user_id
        $planet = \App\Models\Planet\Planet::find($planetId);
        if (!$planet) {
            return 0;
        }

        $totalBonus = 0;

        // Get bonus from buildings
        $buildingAdvantages = self::join('planet_buildings', 'template_build_advantages.build_id', '=', 'planet_buildings.building_id')
            ->where('planet_buildings.planet_id', $planetId)
            ->where('planet_buildings.is_active', true)
            ->where('template_build_advantages.advantage_type', self::TYPE_DEFENSE_BONUS)
            ->where('template_build_advantages.target_type', self::TARGET_UNIT)
            ->where('template_build_advantages.is_active', true)
            ->get(['template_build_advantages.*', 'planet_buildings.level']);
        
        foreach ($buildingAdvantages as $advantage) {
            $totalBonus += $advantage->calculateValueForLevel($advantage->level);
        }

        // Get bonus from technologies
        $technologyAdvantages = self::join('user_technologies', 'template_build_advantages.build_id', '=', 'user_technologies.technology_id')
            ->where('user_technologies.user_id', $planet->user_id)
            ->where('template_build_advantages.advantage_type', self::TYPE_DEFENSE_BONUS)
            ->where('template_build_advantages.target_type', self::TARGET_UNIT)
            ->where('template_build_advantages.is_active', true)
            ->where('user_technologies.is_active', true)
            ->where('user_technologies.level', '>', 0)
            ->get(['template_build_advantages.*', 'user_technologies.level']);
        
        foreach ($technologyAdvantages as $advantage) {
            $totalBonus += $advantage->calculateValueForLevel($advantage->level);
        }
        
        return $totalBonus;
    }

    /**
     * Get shield bonus for ground units based on planet buildings and user technologies
     */
    public static function getShieldBonus($planetId): float
    {
        // Get planet to access user_id
        $planet = \App\Models\Planet\Planet::find($planetId);
        if (!$planet) {
            return 0;
        }

        $totalBonus = 0;

        // Get bonus from buildings
        $buildingAdvantages = self::join('planet_buildings', 'template_build_advantages.build_id', '=', 'planet_buildings.building_id')
            ->where('planet_buildings.planet_id', $planetId)
            ->where('planet_buildings.is_active', true)
            ->where('template_build_advantages.advantage_type', self::TYPE_SHIELD_BONUS)
            ->where('template_build_advantages.target_type', self::TARGET_UNIT)
            ->where('template_build_advantages.is_active', true)
            ->get(['template_build_advantages.*', 'planet_buildings.level']);
        
        foreach ($buildingAdvantages as $advantage) {
            $totalBonus += $advantage->calculateValueForLevel($advantage->level);
        }

        // Get bonus from technologies
        $technologyAdvantages = self::join('user_technologies', 'template_build_advantages.build_id', '=', 'user_technologies.technology_id')
            ->where('user_technologies.user_id', $planet->user_id)
            ->where('template_build_advantages.advantage_type', self::TYPE_SHIELD_BONUS)
            ->where('template_build_advantages.target_type', self::TARGET_UNIT)
            ->where('template_build_advantages.is_active', true)
            ->where('user_technologies.is_active', true)
            ->where('user_technologies.level', '>', 0)
            ->get(['template_build_advantages.*', 'user_technologies.level']);
        
        foreach ($technologyAdvantages as $advantage) {
            $totalBonus += $advantage->calculateValueForLevel($advantage->level);
        }
        
        return $totalBonus;
    }
}