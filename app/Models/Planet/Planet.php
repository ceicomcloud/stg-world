<?php

namespace App\Models\Planet;

use App\Models\Template\TemplatePlanet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Planet\PlanetEquip;

class Planet extends Model
{
    use HasFactory;

    protected $table = 'planets';

    protected $fillable = [
        'user_id',
        'template_planet_id',
        'name',
        'description',
        'used_fields',
        'is_main_planet',
        'last_update',
        'is_active',
        'shield_protection_active',
        'shield_protection_start',
        'shield_protection_end',
        'last_shield_activation',
        'stargate_active',
        'last_stargate_toggle'
    ];

    protected $casts = [
        'galaxy' => 'integer',
        'system' => 'integer',
        'position' => 'integer',
        'size' => 'integer',
        'temperature' => 'integer',
        'used_fields' => 'integer',
        'is_main_planet' => 'boolean',
        'last_update' => 'datetime',
        'is_active' => 'boolean',
        'shield_protection_active' => 'boolean',
        'shield_protection_start' => 'datetime',
        'shield_protection_end' => 'datetime',
        'last_shield_activation' => 'datetime',
        'stargate_active' => 'boolean',
        'last_stargate_toggle' => 'datetime'
    ];

    /**
     * Check if stargate is currently active
     */
    public function isStargateActive(): bool
    {
        return (bool)$this->stargate_active;
    }

    /**
     * Check if stargate toggle is in 24h cooldown
     */
    public function isStargateInCooldown(): bool
    {
        if (!$this->last_stargate_toggle) return false;
        return $this->last_stargate_toggle->copy()->addHours(24)->isFuture();
    }

    /**
     * Check if the stargate is locked (active and in cooldown)
     */
    public function isStargateLocked(): bool
    {
        return $this->isStargateActive() && $this->isStargateInCooldown();
    }

    /**
     * Get the user who owns this planet
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the template planet this planet is based on
     */
    public function templatePlanet(): BelongsTo
    {
        return $this->belongsTo(TemplatePlanet::class, 'template_planet_id');
    }

    /**
     * Get all buildings on this planet
     */
    public function buildings(): HasMany
    {
        return $this->hasMany(PlanetBuilding::class);
    }
    
    /**
     * Check if the planet is currently under shield protection
     */
    public function isShieldProtectionActive(): bool
    {
        return $this->shield_protection_active && 
               $this->shield_protection_end && 
               $this->shield_protection_end->isFuture();
    }
    
    /**
     * Check if the planet can activate shield protection
     * (30 days must have passed since last activation)
     */
    public function canActivateShieldProtection(): bool
    {
        if (!$this->last_shield_activation) {
            return true;
        }
        
        return $this->last_shield_activation->addDays(30)->isPast();
    }
    
    /**
     * Get remaining shield protection time in seconds
     */
    public function getRemainingShieldProtectionTime(): int
    {
        if (!$this->isShieldProtectionActive()) {
            return 0;
        }

        return max(0, now()->diffInSeconds($this->shield_protection_end));
    }
    
    /**
     * Get shield protection progress percentage
     */
    public function getShieldProtectionProgress(): float
    {
        if (!$this->shield_protection_active || !$this->shield_protection_start || !$this->shield_protection_end) {
            return 0;
        }

        $totalTime = $this->shield_protection_start->diffInSeconds($this->shield_protection_end);
        $remainingTime = now()->diffInSeconds($this->shield_protection_end);
        $elapsedTime = $totalTime - $remainingTime;

        if ($totalTime <= 0) {
            return 100;
        }

        return min(100, ($elapsedTime / $totalTime) * 100);
    }

    /**
     * Get all units on this planet
     */
    public function units(): HasMany
    {
        return $this->hasMany(PlanetUnit::class);
    }

    /**
     * Get all defenses on this planet
     */
    public function defenses(): HasMany
    {
        return $this->hasMany(PlanetDefense::class);
    }

    /**
     * Get all ships on this planet
     */
    public function ships(): HasMany
    {
        return $this->hasMany(PlanetShip::class);
    }

    /**
     * Get all teams/equips configured on this planet
     */
    public function equips(): HasMany
    {
        return $this->hasMany(PlanetEquip::class);
    }

    /**
     * Get all resources on this planet
     */
    public function resources(): HasMany
    {
        return $this->hasMany(PlanetResource::class);
    }

    /**
     * Get all bunker resources on this planet
     */
    public function bunkers(): HasMany
    {
        return $this->hasMany(PlanetBunker::class);
    }

    /**
     * Get all missions originating from this planet
     */
    public function fromMissions(): HasMany
    {
        return $this->hasMany(PlanetMission::class, 'from_planet_id');
    }

    /**
     * Get all missions going to this planet
     */
    public function toMissions(): HasMany
    {
        return $this->hasMany(PlanetMission::class, 'to_planet_id');
    }

    /**
     * Get all missions targeting this planet
     */
    public function targetMissions(): HasMany
    {
        return $this->hasMany(PlanetMission::class, 'to_planet_id');
    }

    /**
     * Get galaxy from template planet
     */
    public function getGalaxyAttribute(): int
    {
        return $this->templatePlanet->galaxy ?? 0;
    }

    /**
     * Get system from template planet
     */
    public function getSystemAttribute(): int
    {
        return $this->templatePlanet->system ?? 0;
    }

    /**
     * Get position from template planet
     */
    public function getPositionAttribute(): int
    {
        return $this->templatePlanet->position ?? 0;
    }

    /**
     * Get size from template planet
     */
    public function getSizeAttribute()
    {
        return $this->templatePlanet->size ?? 0;
    }

    /**
     * Get coordinates as string
     */
    public function getCoordinatesAttribute(): string
    {
        return "{$this->galaxy}:{$this->system}:{$this->position}";
    }

    /**
     * Get available fields
     */
    public function getAvailableFieldsAttribute(): int
    {
        return $this->size - $this->used_fields;
    }

    /**
     * Get field usage percentage
     */
    public function getFieldUsagePercentageAttribute(): float
    {
        if ($this->size <= 0) return 0;
        return ($this->used_fields / $this->size) * 100;
    }

    /**
     * Check if planet has available fields
     */
    public function hasAvailableFields(int $requiredFields = 1): bool
    {
        $availableFields = $this->templatePlanet->fields - $this->used_fields;
        return $availableFields >= $requiredFields;
    }

    /**
     * Use fields for construction
     */
    public function useFields(int $fields): bool
    {
        if (!$this->hasAvailableFields($fields)) {
            return false;
        }

        $this->increment('used_fields', $fields);
        return true;
    }

    /**
     * Free fields after destruction
     */
    public function freeFields(int $fields): void
    {
        $this->decrement('used_fields', $fields);
        
        // Ensure used_fields doesn't go below 0
        if ($this->used_fields < 0) {
            $this->update(['used_fields' => 0]);
        }
    }

    /**
     * Get total storage capacity
     */
    public function getStorageCapacity($resourceId = null): int
    {
        $baseStorage = \App\Models\Template\TemplateResource::where('id', $resourceId)->first()->base_storage;
        $bonusStorage = \App\Models\Template\TemplateBuildAdvantage::getStorageBonus($this->id, $resourceId);
        $capacity = $baseStorage + $bonusStorage;

        // Bonus VIP: +10% de capacité de stockage
        $user = $this->user; // relation vers l'utilisateur propriétaire
        if ($user && ($user->vip_active ?? false)) {
            $capacity = (int) floor($capacity * 1.10);
        }

        // Boosts temporaires de stockage (UserEffect)
        $storageBoostPercent = $this->getActiveBoostPercent('storage_boost', $resourceId);
        if ($storageBoostPercent > 0) {
            $capacity = (int) floor($capacity * (1 + ($storageBoostPercent / 100)));
        }

        // Appliquer le multiplicateur global de stockage (ServerConfig)
        try {
            $globalStorageRate = \App\Models\Server\ServerConfig::getStorageRate();
            if ($globalStorageRate && $globalStorageRate != 1.0) {
                $capacity = (int) floor($capacity * $globalStorageRate);
            }
        } catch (\Throwable $e) {
            // Ignorer silencieusement si ServerConfig n'est pas prêt
        }

        return $capacity;
    }

    /**
     * Get total energy production
     */
    public function getEnergyProduction(): int
    {
        $baseEnergy = 20; // Base energy production
        $bonusEnergy = \App\Models\Template\TemplateBuildAdvantage::getEnergyProduction($this->id);
        
        // Add template planet energy bonus
        $templateBonus = 0;
        if ($this->templatePlanet && $this->templatePlanet->energy_bonus) {
            $templateBonus = ($baseEnergy + $bonusEnergy) * ($this->templatePlanet->energy_bonus - 1);
        }
        $total = $baseEnergy + $bonusEnergy + $templateBonus;

        // Boosts temporaires d'énergie (UserEffect)
        $energyBoostPercent = $this->getActiveBoostPercent('energy_boost');
        if ($energyBoostPercent > 0) {
            $total = (int) floor($total * (1 + ($energyBoostPercent / 100)));
        }

        // Modificateurs d'événements galactiques (ex: solar_flare)
        try {
            $mods = (new \App\Services\GalacticEventService())->getModifiersForPlanet($this);
            $percent = (float) ($mods['energy_prod_percent'] ?? 0.0);
            if ($percent !== 0.0) {
                $total = (int) floor($total * (1 + ($percent / 100)));
            }
        } catch (\Throwable $e) {
            // Ignorer en cas d'erreur de service
        }

        return $total;
    }

    /**
     * Get total energy consumption
     */
    public function getEnergyConsumption(): int
    {
        return \App\Models\Template\TemplateBuildDisadvantage::getEnergyConsumption($this->id);
    }

    /**
     * Get net energy (production - consumption)
     */
    public function getNetEnergy(): int
    {
        return $this->getEnergyProduction() - $this->getEnergyConsumption();
    }

    /**
     * Check if planet has enough energy
     */
    public function hasEnoughEnergy(): bool
    {
        return $this->getNetEnergy() >= 0;
    }

    /**
     * Get resource production rate for a specific resource
     */
    public function getResourceProductionRate($resourceId): float
    {
        $planetResource = $this->resources()->where('resource_id', $resourceId)->first();
        if (!$planetResource) {
            return 0;
        }

        $baseProduction = $planetResource->base_production;
        $productionBonus = \App\Models\Template\TemplateBuildAdvantage::getProductionBoost($this->id, $resourceId);
        $productionPenalty = \App\Models\Template\TemplateBuildDisadvantage::getProductionPenalty($this->id, $resourceId);
        
        $totalProduction = $baseProduction + $productionBonus - $productionPenalty;
        // Apply template planet resource bonus
        if ($this->templatePlanet) {
            $templateBonus = 0;
            // Resource IDs: 1=metal, 2=crystal, 3=deuterium (based on seeder order)
            switch ($resourceId) {
                case 1: // metal
                    if ($this->templatePlanet->metal_bonus) {
                        $templateBonus = $totalProduction * ($this->templatePlanet->metal_bonus - 1);
                    }
                    break;
                case 2: // crystal
                    if ($this->templatePlanet->crystal_bonus) {
                        $templateBonus = $totalProduction * ($this->templatePlanet->crystal_bonus - 1);
                    }
                    break;
                case 3: // deuterium
                    if ($this->templatePlanet->deuterium_bonus) {
                        $templateBonus = $totalProduction * ($this->templatePlanet->deuterium_bonus - 1);
                    }
                    break;
            }
            $totalProduction += $templateBonus;
        }
        
        // Apply faction resource production bonus if user has a faction
        if ($this->user && $this->user->faction) {
            $factionBonus = $this->user->faction->getBonusResourceProduction();
            if ($factionBonus > 0) {
                // Apply percentage bonus (e.g., 5% = 0.05)
                $totalProduction *= (1 + ($factionBonus / 100));
            }
        }
        // Apply temporary production boosts (UserEffect)
        $prodBoostPercent = $this->getActiveBoostPercent('production_boost', $resourceId);
        if ($prodBoostPercent > 0) {
            $totalProduction *= (1 + ($prodBoostPercent / 100));
        }

        // Modificateurs d'événements galactiques (ex: nebula_anomaly)
        try {
            $mods = (new \App\Services\GalacticEventService())->getModifiersForPlanet($this);
            $keyById = [1 => 'prod_metal_percent', 2 => 'prod_crystal_percent', 3 => 'prod_deuterium_percent'];
            if (isset($keyById[$resourceId])) {
                $percent = (float) ($mods[$keyById[$resourceId]] ?? 0.0);
                if ($percent !== 0.0) {
                    $totalProduction *= (1 + ($percent / 100));
                }
            }
        } catch (\Throwable $e) {
            // Ignorer en cas d'erreur de service
        }

        // Apply production rate modifier
        $totalProduction *= ($planetResource->production_rate / 100);
        
        // Apply energy penalty if not enough energy
        if (!$this->hasEnoughEnergy()) {
            $energyRatio = max(0, $this->getNetEnergy() / abs($this->getEnergyConsumption()));
            $totalProduction *= $energyRatio;
        }
        
        // Appliquer le multiplicateur global de production (ServerConfig)
        try {
            $globalProductionRate = \App\Models\Server\ServerConfig::getProductionRate();
            if ($globalProductionRate && $globalProductionRate != 1.0) {
                $totalProduction *= $globalProductionRate;
            }
        } catch (\Throwable $e) {
            // Ignorer silencieusement si ServerConfig n'est pas prêt
        }

        return max(0, $totalProduction);
    }

    /**
     * Sum active boost percentage for given type and optional resource scope
     */
    private function getActiveBoostPercent(string $type, ?int $resourceId = null): float
    {
        $query = \App\Models\UserEffect::active()
            ->where('user_id', $this->user_id)
            ->where('effect_type', $type)
            ->where(function ($q) {
                $q->whereNull('planet_id')
                  ->orWhere('planet_id', $this->id);
            });

        $effects = $query->get();
        $sum = 0.0;
        foreach ($effects as $eff) {
            $meta = $eff->meta ?? [];
            // If meta scopes to a resource, ensure it matches
            if ($resourceId && isset($meta['resource_id']) && (int)$meta['resource_id'] !== (int)$resourceId) {
                continue;
            }
            if ($resourceId && isset($meta['resource'])) {
                // Map resource name to id basic assumption: 1 metal,2 crystal,3 deuterium
                $name = strtolower($meta['resource']);
                $map = ['metal' => 1, 'crystal' => 2, 'deuterium' => 3];
                if (isset($map[$name]) && (int)$map[$name] !== (int)$resourceId) {
                    continue;
                }
            }
            $sum += (float) ($eff->value ?? 0);
        }
        return $sum;
    }

    /**
     * Update last update timestamp
     */
    public function updateLastUpdate(): void
    {
        $this->update(['last_update' => now()]);
    }

    /**
     * Scope for active planets
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for main planets
     */
    public function scopeMainPlanets($query)
    {
        return $query->where('is_main_planet', true);
    }

    /**
     * Scope by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope by coordinates
     */
    public function scopeAtCoordinates($query, $galaxy, $system, $position)
    {
        return $query->whereHas('templatePlanet', function ($q) use ($galaxy, $system, $position) {
            $q->where('galaxy', $galaxy)
              ->where('system', $system)
              ->where('position', $position);
        });
    }
}