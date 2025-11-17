<?php

namespace App\Models\Other;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chatbox extends Model
{
    use SoftDeletes;

    protected $table = 'chatbox';

    protected $fillable = [
        'user_id',
        'message',
        'channel',
        'is_system_message',
    ];

    protected $casts = [
        'is_system_message' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour filtrer par canal
     */
    public function scopeChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope pour les messages récents
     */
    public function scopeRecent($query, $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
