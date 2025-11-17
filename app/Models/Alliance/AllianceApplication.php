<?php

namespace App\Models\Alliance;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllianceApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'alliance_id',
        'user_id',
        'message',
        'status',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime'
    ];

    /**
     * Statuts disponibles
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Relation avec l'alliance
     */
    public function alliance(): BelongsTo
    {
        return $this->belongsTo(Alliance::class);
    }

    /**
     * Relation avec l'utilisateur candidat
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec l'utilisateur qui a examiné la candidature
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Accepter la candidature
     */
    public function accept(User $reviewer): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        if (!$this->alliance->canAcceptNewMembers()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now()
        ]);

        // Créer le membre de l'alliance
        AllianceMember::create([
            'alliance_id' => $this->alliance_id,
            'user_id' => $this->user_id,
            'joined_at' => now()
        ]);

        // Mettre à jour l'utilisateur
        $this->user->update(['alliance_id' => $this->alliance_id]);

        return true;
    }

    /**
     * Rejeter la candidature
     */
    public function reject(User $reviewer): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_REJECTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now()
        ]);

        return true;
    }

    /**
     * Vérifier si la candidature est en attente
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Vérifier si la candidature a été acceptée
     */
    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Vérifier si la candidature a été rejetée
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Obtenir le statut formaté
     */
    public function getFormattedStatusAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_ACCEPTED => 'Acceptée',
            self::STATUS_REJECTED => 'Rejetée',
            default => 'Inconnu'
        };
    }
}