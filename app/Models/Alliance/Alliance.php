<?php

namespace App\Models\Alliance;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Alliance extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tag',
        'external_description',
        'internal_description',
        'logo',
        'leader_id',
        'max_members',
        'open_recruitment',
        'deuterium_bank'
    ];

    protected $casts = [
        'open_recruitment' => 'boolean',
        'deuterium_bank' => 'integer',
        'max_members' => 'integer'
    ];

    /**
     * Relation avec le leader de l'alliance
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * Relation avec les membres de l'alliance
     */
    public function members(): HasMany
    {
        return $this->hasMany(AllianceMember::class);
    }

    /**
     * Relation avec les rangs de l'alliance
     */
    public function ranks(): HasMany
    {
        return $this->hasMany(AllianceRank::class);
    }

    /**
     * Relation avec les candidatures
     */
    public function applications(): HasMany
    {
        return $this->hasMany(AllianceApplication::class);
    }

    /**
     * Relation avec les guerres où cette alliance est attaquante
     */
    public function attackerWars(): HasMany
    {
        return $this->hasMany(AllianceWar::class, 'attacker_alliance_id');
    }

    /**
     * Relation avec les guerres où cette alliance est défenseur
     */
    public function defenderWars(): HasMany
    {
        return $this->hasMany(AllianceWar::class, 'defender_alliance_id');
    }

    /**
     * Relation avec les technologies de l'alliance
     */
    public function technologies(): HasMany
    {
        return $this->hasMany(AllianceTechnology::class);
    }

    /**
     * Obtenir tous les utilisateurs membres de l'alliance
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'alliance_members')
                    ->withPivot(['rank_id', 'joined_at', 'contributed_deuterium'])
                    ->withTimestamps();
    }

    /**
     * Vérifier si l'alliance peut accepter de nouveaux membres
     */
    public function canAcceptNewMembers(): bool
    {
        return $this->members()->count() < $this->max_members;
    }

    /**
     * Obtenir le nombre de membres actuels
     */
    public function getMemberCountAttribute(): int
    {
        return $this->members()->count();
    }

    /**
     * Obtenir l'URL du logo
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        // Si le logo est stocké directement sous public/, retourner via asset()
        $publicFile = public_path($this->logo);
        if (file_exists($publicFile)) {
            return asset($this->logo);
        }

        // Sinon, tenter via le disque 'public' (storage/app/public -> public/storage)
        if (Storage::disk('public')->exists($this->logo)) {
            return Storage::disk('public')->url($this->logo);
        }

        return null;
    }

    /**
     * Ajouter du deuterium à la banque
     */
    public function addToDeuteriumBank(int $amount): void
    {
        $this->increment('deuterium_bank', $amount);
    }

    /**
     * Retirer du deuterium de la banque
     */
    public function withdrawFromDeuteriumBank(int $amount): bool
    {
        if ($this->deuterium_bank >= $amount) {
            $this->decrement('deuterium_bank', $amount);
            return true;
        }
        return false;
    }

    /**
     * Obtenir les candidatures en attente
     */
    public function pendingApplications()
    {
        return $this->applications()->where('status', 'pending')->with('user');
    }

    /**
     * Obtenir une technologie spécifique
     */
    public function getTechnology(string $type): ?AllianceTechnology
    {
        return $this->technologies()->where('technology_type', $type)->first();
    }

    /**
     * Initialiser les technologies de base pour l'alliance
     */
    public function initializeTechnologies(): void
    {
        $technologies = [
            AllianceTechnology::TYPE_MEMBERS,
            AllianceTechnology::TYPE_BANK
        ];

        foreach ($technologies as $type) {
            $this->technologies()->firstOrCreate([
                'technology_type' => $type
            ], [
                'level' => 0,
                'max_level' => AllianceTechnology::MAX_LEVEL
            ]);
        }
    }

    /**
     * Obtenir la capacité maximale de membres
     */
    public function getMaxMembers(): int
    {
        $baseMemberLimit = 5; // Limite de base
        $membersTech = $this->getTechnology(AllianceTechnology::TYPE_MEMBERS);
        
        return $baseMemberLimit + ($membersTech ? $membersTech->getBonus() : 0);
    }

    /**
     * Obtenir la capacité maximale de stockage de deuterium
     */
    public function getMaxDeuteriumStorage(): int
    {
        $baseStorage = 5000; // Stockage de base
        $bankTech = $this->getTechnology(AllianceTechnology::TYPE_BANK);
        
        return $baseStorage + ($bankTech ? $bankTech->getBonus() : 0);
    }

    /**
     * Obtenir les guerres actives
     */
    public function activeWars()
    {
        return $this->attackerWars()->where('status', 'active')
                    ->union($this->defenderWars()->where('status', 'active'));
    }
}