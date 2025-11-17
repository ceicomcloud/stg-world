<?php

namespace App\Models\User;

use App\Models\Planet\Planet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBookmark extends Model
{
    use HasFactory;

    protected $table = 'user_bookmarks';

    protected $fillable = [
        'user_id',
        'planet_id',
        'label',
        'galaxy',
        'system',
        'position',
        'mission_type',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'galaxy' => 'integer',
        'system' => 'integer',
        'position' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'mission_type' => '',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function planet(): BelongsTo
    {
        return $this->belongsTo(Planet::class);
    }

    public function getCoordinatesAttribute(): string
    {
        $g = $this->galaxy ?? ($this->planet->galaxy ?? null);
        $s = $this->system ?? ($this->planet->system ?? null);
        $p = $this->position ?? ($this->planet->position ?? null);
        if ($g === null || $s === null || $p === null) {
            return '';
        }
        return "$g:$s:$p";
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}