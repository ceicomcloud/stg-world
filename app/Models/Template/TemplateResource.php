<?php

namespace App\Models\Template;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateResource extends Model
{
    use HasFactory;

    protected $table = 'template_resources';

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'icon',
        'color',
        'type',
        'base_production',
        'base_storage',
        'trade_rate',
        'sort_order',
        'is_tradeable',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Resource types constants
    const TYPE_METAL = 'metal';
    const TYPE_CRYSTAL = 'crystal';
    const TYPE_DEUTERIUM = 'deuterium';

    /**
     * Get all planet resources using this template
     */
    public function planetResources(): HasMany
    {
        return $this->hasMany(\App\Models\Planet\PlanetResource::class, 'resource_id');
    }

    /**
     * Get all build costs using this resource
     */
    public function buildCosts(): HasMany
    {
        return $this->hasMany(TemplateBuildCost::class, 'resource_id');
    }

    /**
     * Scope for active resources
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get resource by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('name', $type);
    }
}