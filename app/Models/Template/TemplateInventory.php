<?php

namespace App\Models\Template;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateInventory extends Model
{
    use HasFactory;

    protected $table = 'template_inventories';

    protected $fillable = [
        'key',
        'name',
        'type',
        'description',
        'icon',
        'rarity',
        'effect_type',
        'effect_value',
        'effect_meta',
        'duration_seconds',
        'usable',
        'stackable',
        'is_active',
    ];

    protected $casts = [
        'effect_value' => 'integer',
        'effect_meta' => 'array',
        'duration_seconds' => 'integer',
        'usable' => 'boolean',
        'stackable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function userInventories(): HasMany
    {
        return $this->hasMany(\App\Models\User\UserInventory::class, 'template_inventory_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}