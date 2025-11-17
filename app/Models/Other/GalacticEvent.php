<?php

namespace App\Models\Other;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class GalacticEvent extends Model
{
    use HasFactory;

    protected $table = 'galactic_events';

    protected $fillable = [
        'galaxy',
        'system',
        'position',
        'key',
        'title',
        'severity',
        'icon',
        'description',
        'start_at',
        'end_at',
        'is_active',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('end_at', '>', now());
    }

    public function scopeForSector($query, int $galaxy, int $system)
    {
        return $query->where('galaxy', $galaxy)->where('system', $system);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active && $this->end_at && $this->end_at->isFuture();
    }
}