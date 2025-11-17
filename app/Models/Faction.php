<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'banner',
        'color_code',
        'description',
        'bonuses',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'bonuses' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the users that belong to this faction.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get resource production bonus
     * 
     * @return float|int Bonus value or 0 if not set
     */
    public function getBonusResourceProduction(): float|int
    {
        return $this->bonuses['resource_production'] ?? 0;
    }

    /**
     * Get building cost bonus
     * 
     * @return float|int Bonus value or 0 if not set
     */
    public function getBonusBuildingCost(): float|int
    {
        return $this->bonuses['building_cost'] ?? 0;
    }

    /**
     * Get technology cost bonus
     * 
     * @return float|int Bonus value or 0 if not set
     */
    public function getBonusTechnologyCost(): float|int
    {
        return $this->bonuses['technology_cost'] ?? 0;
    }

    /**
     * Get ship speed bonus
     * 
     * @return float|int Bonus value or 0 if not set
     */
    public function getBonusShipSpeed(): float|int
    {
        return $this->bonuses['ship_speed'] ?? 0;
    }

    /**
     * Get attack power bonus
     * 
     * @return float|int Bonus value or 0 if not set
     */
    public function getBonusAttackPower(): float|int
    {
        return $this->bonuses['attack_power'] ?? 0;
    }

        /**
     * Get defense power bonus
     * 
     * @return float|int Bonus value or 0 if not set
     */
    public function getBonusDefensePower(): float|int
    {
        return $this->bonuses['defense_power'] ?? 0;
    }

    /**
     * Get ship capacity bonus
     * 
     * @return float|int Bonus value or 0 if not set
     */
    public function getBonusShipCapacity(): float|int
    {
        return $this->bonuses['ship_capacity'] ?? 0;
    }

    /**
     * Get building speed bonus
     * 
     * @return float|int Bonus value or 0 if not set
     */
    public function getBonusBuildingSpeed(): float|int
    {
        return $this->bonuses['building_speed'] ?? 0;
    }

    /**
     * Get a specific bonus by key
     * 
     * @param string $key The bonus key
     * @return float|int Bonus value or 0 if not set
     */
    public function getBonus(string $key): float|int
    {
        return $this->bonuses[$key] ?? 0;
    }
}