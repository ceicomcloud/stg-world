<?php

namespace App\Models\Alliance;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllianceMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'alliance_id',
        'user_id',
        'rank_id',
        'joined_at',
        'contributed_deuterium'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'contributed_deuterium' => 'integer'
    ];

    /**
     * Relation avec l'alliance
     */
    public function alliance(): BelongsTo
    {
        return $this->belongsTo(Alliance::class);
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le rang
     */
    public function rank(): BelongsTo
    {
        return $this->belongsTo(AllianceRank::class, 'rank_id');
    }

    /**
     * Ajouter une contribution en deuterium
     */
    public function addContribution(int $amount): void
    {
        $this->increment('contributed_deuterium', $amount);
    }

    /**
     * Vérifier si le membre a une permission spécifique
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->rank) {
            return false;
        }

        $permissions = $this->rank->permissions;
        return in_array($permission, $permissions);
    }

    /**
     * Vérifier si le membre est le leader
     */
    public function isLeader(): bool
    {
        return $this->alliance->leader_id === $this->user_id;
    }

    /**
     * Obtenir le niveau du rang
     */
    public function getRankLevel(): int
    {
        return $this->rank ? $this->rank->level : 1;
    }
}