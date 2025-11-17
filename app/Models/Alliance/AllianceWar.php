<?php

namespace App\Models\Alliance;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllianceWar extends Model
{
    use HasFactory;

    protected $fillable = [
        'attacker_alliance_id',
        'defender_alliance_id',
        'reason',
        'status',
        'started_at',
        'ended_at',
        'declared_by',
        'ended_by'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime'
    ];

    /**
     * Statuts disponibles
     */
    public const STATUS_DECLARED = 'declared';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ENDED = 'ended';

    /**
     * Relation avec l'alliance attaquante
     */
    public function attackerAlliance(): BelongsTo
    {
        return $this->belongsTo(Alliance::class, 'attacker_alliance_id');
    }

    /**
     * Relation avec l'alliance défenseur
     */
    public function defenderAlliance(): BelongsTo
    {
        return $this->belongsTo(Alliance::class, 'defender_alliance_id');
    }

    /**
     * Relation avec l'utilisateur qui a déclaré la guerre
     */
    public function declaredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'declared_by');
    }

    /**
     * Relation avec l'utilisateur qui a terminé la guerre
     */
    public function endedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ended_by');
    }

    /**
     * Démarrer la guerre
     */
    public function start(): bool
    {
        if ($this->status !== self::STATUS_DECLARED) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_ACTIVE,
            'started_at' => now()
        ]);

        return true;
    }

    /**
     * Terminer la guerre
     */
    public function end(User $endedBy): bool
    {
        if ($this->status === self::STATUS_ENDED) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_ENDED,
            'ended_at' => now(),
            'ended_by' => $endedBy->id
        ]);

        return true;
    }

    /**
     * Vérifier si la guerre est déclarée
     */
    public function isDeclared(): bool
    {
        return $this->status === self::STATUS_DECLARED;
    }

    /**
     * Vérifier si la guerre est active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Vérifier si la guerre est terminée
     */
    public function isEnded(): bool
    {
        return $this->status === self::STATUS_ENDED;
    }

    /**
     * Obtenir la durée de la guerre
     */
    public function getDuration(): ?int
    {
        if (!$this->started_at) {
            return null;
        }

        $endTime = $this->ended_at ?? now();
        return $this->started_at->diffInHours($endTime);
    }

    /**
     * Obtenir le statut formaté
     */
    public function getFormattedStatusAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DECLARED => 'Déclarée',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_ENDED => 'Terminée',
            default => 'Inconnu'
        };
    }

    /**
     * Vérifier si un utilisateur peut terminer cette guerre
     */
    public function canBeEndedBy(User $user): bool
    {
        // Seuls les leaders ou vice-leaders peuvent terminer une guerre
        $attackerMember = $this->attackerAlliance->members()->where('user_id', $user->id)->first();
        $defenderMember = $this->defenderAlliance->members()->where('user_id', $user->id)->first();

        if ($attackerMember && $attackerMember->hasPermission('manage_wars')) {
            return true;
        }

        if ($defenderMember && $defenderMember->hasPermission('manage_wars')) {
            return true;
        }

        return false;
    }
}