<?php

namespace App\Models\Template;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplatePlanet extends Model
{
    use HasFactory;

    protected $table = 'template_planets';

    protected $fillable = [
        'name',
        'galaxy',
        'system',
        'position',
        'type',
        'size',
        'diameter',
        'min_temperature',
        'max_temperature',
        'fields',
        'is_colonizable',
        'is_occupied',
        'is_available',
        'metal_bonus',
        'crystal_bonus',
        'deuterium_bonus',
        'energy_bonus',
        'is_active'
    ];

    protected $casts = [
        'galaxy' => 'integer',
        'system' => 'integer',
        'position' => 'integer',
        'diameter' => 'integer',
        'min_temperature' => 'integer',
        'max_temperature' => 'integer',
        'fields' => 'integer',
        'is_colonizable' => 'boolean',
        'is_occupied' => 'boolean',
        'is_available' => 'boolean',
        'metal_bonus' => 'float',
        'crystal_bonus' => 'float',
        'deuterium_bonus' => 'float',
        'energy_bonus' => 'float',
        'is_active' => 'boolean'
    ];

    // Planet types
    const TYPE_ROCKY = 'rocky';
    const TYPE_DESERT = 'desert';
    const TYPE_JUNGLE = 'jungle';
    const TYPE_NORMAL = 'normal';
    const TYPE_WATER = 'water';
    const TYPE_ICE = 'ice';
    const TYPE_TUNDRA = 'tundra';
    const TYPE_RADIATED = 'radiated';
    const TYPE_VOLCANIC = 'volcanic';

    // Size categories
    const SIZE_TINY = 50;
    const SIZE_SMALL = 100;
    const SIZE_MEDIUM = 150;
    const SIZE_LARGE = 200;
    const SIZE_HUGE = 250;

    /**
     * Get all planets created from this template
     */
    public function planets(): HasMany
    {
        return $this->hasMany(\App\Models\Planet\Planet::class, 'template_planet_id');
    }

    /**
     * Get coordinates as string
     */
    public function getCoordinatesAttribute(): string
    {
        return "{$this->galaxy}:{$this->system}:{$this->position}";
    }

    /**
     * Get temperature range
     */
    public function getTemperatureRangeAttribute(): array
    {
        $base = $this->temperature;
        return [
            'min' => $base - 40,
            'max' => $base + 40
        ];
    }

    /**
     * Get maximum fields based on size
     */
    public function getMaxFieldsAttribute(): int
    {
        return $this->size;
    }

    /**
     * Check if planet is available for colonization
     */
    public function isAvailableForColonization(): bool
    {
        return $this->is_colonizable && !$this->is_occupied && $this->is_active;
    }

    /**
     * Mark planet as occupied
     */
    public function markAsOccupied(): void
    {
        $this->update(['is_occupied' => true]);
    }

    /**
     * Mark planet as free
     */
    public function markAsFree(): void
    {
        $this->update(['is_occupied' => false]);
    }

    /**
     * Get random available planet for new user
     */
    public static function getRandomAvailablePlanet(): ?self
    {
        return self::where('is_colonizable', true)
            ->where('is_occupied', false)
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();
    }

    /**
     * Scope for colonizable planets
     */
    public function scopeColonizable($query)
    {
        return $query->where('is_colonizable', true);
    }

    /**
     * Scope for available planets
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_colonizable', true)
            ->where('is_occupied', false)
            ->where('is_active', true);
    }

    /**
     * Scope for occupied planets
     */
    public function scopeOccupied($query)
    {
        return $query->where('is_occupied', true);
    }

    /**
     * Scope for active planets
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by galaxy
     */
    public function scopeInGalaxy($query, $galaxy)
    {
        return $query->where('galaxy', $galaxy);
    }

    /**
     * Scope by system
     */
    public function scopeInSystem($query, $galaxy, $system)
    {
        return $query->where('galaxy', $galaxy)->where('system', $system);
    }

    /**
     * Scope by coordinates
     */
    public function scopeAtCoordinates($query, $galaxy, $system, $position)
    {
        return $query->where('galaxy', $galaxy)
            ->where('system', $system)
            ->where('position', $position);
    }

    /**
     * Scope by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by size range
     */
    public function scopeBySizeRange($query, $minSize, $maxSize)
    {
        return $query->whereBetween('size', [$minSize, $maxSize]);
    }
}