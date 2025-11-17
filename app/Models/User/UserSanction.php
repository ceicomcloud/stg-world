<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserSanction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sanctioned_by',
        'type',
        'reason',
        'expires_at',
        'is_active'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sanctionedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sanctioned_by');
    }

    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false; // Permanent sanction
        }
        
        return Carbon::now()->isAfter($this->expires_at);
    }

    public function isActive(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', Carbon::now());
                    });
    }

    public function scopeBans($query)
    {
        return $query->where('type', 'ban');
    }

    public function scopeMutes($query)
    {
        return $query->where('type', 'mute');
    }
}