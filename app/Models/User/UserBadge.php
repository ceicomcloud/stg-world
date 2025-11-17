<?php

namespace App\Models\User;

use App\Models\User;
use App\Models\Template\TemplateBadge;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    use HasFactory;

    protected $table = 'user_badges';

    protected $fillable = [
        'user_id',
        'badge_id',
        'earned_at'
    ];

    protected $casts = [
        'earned_at' => 'datetime'
    ];

    /**
     * The user who earned the badge
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The badge that was earned
     */
    public function badge(): BelongsTo
    {
        return $this->belongsTo(TemplateBadge::class, 'badge_id');
    }

    /**
     * Scope to get badges earned today
     */
    public function scopeEarnedToday($query)
    {
        return $query->whereDate('earned_at', today());
    }

    /**
     * Scope to get badges earned this week
     */
    public function scopeEarnedThisWeek($query)
    {
        return $query->whereBetween('earned_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope to get badges earned this month
     */
    public function scopeEarnedThisMonth($query)
    {
        return $query->whereMonth('earned_at', now()->month)
                    ->whereYear('earned_at', now()->year);
    }

    /**
     * Scope to get badges by rarity
     */
    public function scopeByRarity($query, string $rarity)
    {
        return $query->whereHas('badge', function ($q) use ($rarity) {
            $q->where('rarity', $rarity);
        });
    }

    /**
     * Scope to get badges by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->whereHas('badge', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }

    /**
     * Get the total points from this badge
     */
    public function getPointsAttribute(): int
    {
        return $this->badge->points_reward ?? 0;
    }

    /**
     * Get the badge name
     */
    public function getBadgeNameAttribute(): string
    {
        return $this->badge->name ?? '';
    }

    /**
     * Get the badge rarity
     */
    public function getBadgeRarityAttribute(): string
    {
        return $this->badge->rarity ?? '';
    }

    /**
     * Get the badge type
     */
    public function getBadgeTypeAttribute(): string
    {
        return $this->badge->type ?? '';
    }

    /**
     * Check if the badge was earned recently (within last 24 hours)
     */
    public function isRecentlyEarned(): bool
    {
        return $this->earned_at && $this->earned_at->isAfter(now()->subDay());
    }

    /**
     * Get formatted earned date
     */
    public function getFormattedEarnedDateAttribute(): string
    {
        return $this->earned_at ? $this->earned_at->format('d/m/Y H:i') : '';
    }

    /**
     * Get human readable earned date
     */
    public function getHumanEarnedDateAttribute(): string
    {
        return $this->earned_at ? $this->earned_at->diffForHumans() : '';
    }
}