<?php

namespace App\Models\Planet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanetEquip extends Model
{
    use HasFactory;

    protected $table = 'planet_equips';

    protected $fillable = [
        'planet_id',
        'category',
        'label',
        'team_index',
        'notes',
        'payload',
        'is_active',
    ];

    protected $casts = [
        'team_index' => 'integer',
        'payload' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const CATEGORY_EARTH = 'earth';
    public const CATEGORY_SPATIAL = 'spatial';

    public function planet(): BelongsTo
    {
        return $this->belongsTo(Planet::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPlanet($query, int $planetId)
    {
        return $query->where('planet_id', $planetId);
    }

    public function scopeEarth($query)
    {
        return $query->where('category', self::CATEGORY_EARTH);
    }

    public function scopeSpatial($query)
    {
        return $query->where('category', self::CATEGORY_SPATIAL);
    }
}