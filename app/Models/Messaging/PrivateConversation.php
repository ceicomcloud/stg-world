<?php

namespace App\Models\Messaging;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class PrivateConversation extends Model
{
    protected $fillable = [
        'title',
        'type',
        'created_by',
        'last_message_at',
        'is_active'
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants', 'conversation_id', 'user_id')
                ->withPivot(['joined_at', 'last_read_at', 'is_active'])
                ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(PrivateMessage::class, 'conversation_id');
    }

    public function lastMessage()
    {
        return $this->hasOne(PrivateMessage::class, 'conversation_id')->latest();
    }

    public function canReply(): bool
    {
        return !in_array($this->type, ['system', 'attack', 'spy', 'colonize', 'return', 'send', 'extract', 'bassement']);
    }

    public function addParticipant(User $user): void
    {
        if (!$this->participants()->where('user_id', $user->id)->exists()) {
            $this->participants()->attach($user->id, [
                'joined_at' => Carbon::now(),
                'is_active' => true
            ]);
        }
    }

    public function removeParticipant(User $user): void
    {
        $this->participants()->updateExistingPivot($user->id, ['is_active' => false]);
    }

    public function updateLastMessageTime(): void
    {
        $this->update(['last_message_at' => Carbon::now()]);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('conversation_participants.is_active', true);
        });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'player' => 'Joueur',
            'attack' => 'Attaque',
            'alliance' => 'Alliance',
            'spy' => 'Espionnage',
            'colonize' => 'Colonisation',
            'return' => 'Retour',
            'send' => 'Envoi',
            'system' => 'Système',
            'extract' => 'Extraction',
            'explore' => 'Exploration',
            'basement' => "Basement",
            default => ucfirst($this->type)
        };
    }
}
