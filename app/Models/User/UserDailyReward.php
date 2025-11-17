<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDailyReward extends Model
{
    use HasFactory;

    protected $table = 'user_daily_rewards';

    protected $fillable = [
        'user_id',
        'current_streak',
        'last_seen_at',
        'last_claim_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'last_claim_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}