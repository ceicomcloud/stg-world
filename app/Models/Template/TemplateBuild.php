<?php

namespace App\Models\Template;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateBuild extends Model
{
    use HasFactory;

    protected $table = 'template_builds';

    protected $fillable = [
        'uuid',
        'name',
        'label',
        'description',
        'type',
        'category',
        'icon',
        'max_level',
        'base_build_time',
        'is_active'
    ];

    protected $casts = [
        'max_level' => 'integer',
        'base_build_time' => 'integer',
        'is_active' => 'boolean'
    ];

    // Build types constants
    const TYPE_BUILDING = 'building';
    const TYPE_RESEARCH = 'technology';
    const TYPE_UNIT = 'unit';
    const TYPE_DEFENSE = 'defense';
    const TYPE_SHIP = 'ship';

    // Building categories
    const CATEGORY_RESOURCE = 'resource';
    const CATEGORY_FACILITY = 'facility';
    const CATEGORY_MILITARY = 'military';
    const CATEGORY_RESEARCH = 'technology';
    const CATEGORY_SHIPYARD = 'shipyard';

    /**
     * Get all costs for this build
     */
    public function costs(): HasMany
    {
        return $this->hasMany(TemplateBuildCost::class, 'build_id');
    }

    /**
     * Get all requirements for this build
     */
    public function requirements(): HasMany
    {
        return $this->hasMany(TemplateBuildRequired::class, 'build_id');
    }

    /**
     * Get all advantages for this build
     */
    public function advantages(): HasMany
    {
        return $this->hasMany(TemplateBuildAdvantage::class, 'build_id');
    }

    /**
     * Get all disadvantages for this build
     */
    public function disadvantages(): HasMany
    {
        return $this->hasMany(TemplateBuildDisadvantage::class, 'build_id');
    }

    /**
     * Get planet buildings using this template
     */
    public function planetBuildings(): HasMany
    {
        return $this->hasMany(\App\Models\Planet\PlanetBuilding::class, 'building_id');
    }

    /**
     * Get planet units using this template
     */
    public function planetUnits(): HasMany
    {
        return $this->hasMany(\App\Models\Planet\PlanetUnit::class, 'unit_id');
    }

    /**
     * Get planet defenses using this template
     */
    public function planetDefenses(): HasMany
    {
        return $this->hasMany(\App\Models\Planet\PlanetDefense::class, 'defense_id');
    }

    /**
     * Get planet ships using this template
     */
    public function planetShips(): HasMany
    {
        return $this->hasMany(\App\Models\Planet\PlanetShip::class, 'ship_id');
    }

    /**
     * Get user technologies using this template
     */
    public function userTechnologies(): HasMany
    {
        return $this->hasMany(\App\Models\User\UserTechnology::class, 'technology_id');
    }

    /**
     * Scope for active builds
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Check if this is a building
     */
    public function isBuilding(): bool
    {
        return $this->type === self::TYPE_BUILDING;
    }

    /**
     * Check if this is a research
     */
    public function isResearch(): bool
    {
        return $this->type === self::TYPE_RESEARCH;
    }

    /**
     * Check if this is a unit
     */
    public function isUnit(): bool
    {
        return $this->type === self::TYPE_UNIT;
    }

    /**
     * Check if this is a defense
     */
    public function isDefense(): bool
    {
        return $this->type === self::TYPE_DEFENSE;
    }

    /**
     * Check if this is a ship
     */
    public function isShip(): bool
    {
        return $this->type === self::TYPE_SHIP;
    }
}