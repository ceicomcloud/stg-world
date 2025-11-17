<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStatEvent extends Model
{
    use HasFactory;

    protected $table = 'user_stat_events';

    protected $fillable = [
        'user_id',
        'attaque_points',
        'exploration_count',
        'extraction_count',
        'pillage_total',
        'construction_spent',
    ];

    protected $casts = [
        'attaque_points' => 'integer',
        'exploration_count' => 'integer',
        'extraction_count' => 'integer',
        'pillage_total' => 'integer',
        'construction_spent' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}