<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserEffect extends Model
{
    use HasFactory;

    protected $table = 'user_effects';

    protected $fillable = [
        'user_id',
        'planet_id',
        'effect_type', // storage_boost, production_boost, energy_boost
        'value',       // percentage or numeric value
        'meta',        // json: resource, scope, etc.
        'started_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'float',
        'meta' => 'array',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }
}