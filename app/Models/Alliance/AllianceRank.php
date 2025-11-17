<?php

namespace App\Models\Alliance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AllianceRank extends Model
{
    use HasFactory;

    protected $fillable = [
        'alliance_id',
        'name',
        'level',
        'permissions'
    ];

    protected $casts = [
        'permissions' => 'array',
        'level' => 'integer'
    ];

    /**
     * Permissions disponibles
     */
    public const PERMISSIONS = [
        'manage_members' => 'Gérer les membres',
        'manage_ranks' => 'Gérer les rangs',
        'manage_bank' => 'Gérer la banque',
        'manage_wars' => 'Gérer les guerres',
        'manage_applications' => 'Gérer les candidatures',
        'view_internal_description' => 'Voir la description interne',
        'edit_alliance_info' => 'Modifier les informations de l\'alliance',
        'kick_members' => 'Exclure des membres',
        'promote_members' => 'Promouvoir des membres',
        'demote_members' => 'Rétrograder des membres',
        'manage_alliance' => 'Gérer l\'alliance (technologies)'
    ];

    /**
     * Niveaux de rang
     */
    public const LEVELS = [
        1 => 'Membre',
        2 => 'Officier',
        3 => 'Vice-Leader',
        4 => 'Leader'
    ];

    /**
     * Relation avec l'alliance
     */
    public function alliance(): BelongsTo
    {
        return $this->belongsTo(Alliance::class);
    }

    /**
     * Relation avec les membres ayant ce rang
     */
    public function members(): HasMany
    {
        return $this->hasMany(AllianceMember::class, 'rank_id');
    }

    /**
     * Vérifier si le rang a une permission spécifique
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Ajouter une permission
     */
    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }
    }

    /**
     * Retirer une permission
     */
    public function removePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, fn($p) => $p !== $permission);
        $this->permissions = array_values($permissions);
        $this->save();
    }

    /**
     * Obtenir le nom du niveau
     */
    public function getLevelNameAttribute(): string
    {
        return self::LEVELS[$this->level] ?? 'Inconnu';
    }

    /**
     * Vérifier si c'est un rang de leader
     */
    public function isLeaderRank(): bool
    {
        return $this->level === 4;
    }

    /**
     * Obtenir les permissions formatées
     */
    public function getFormattedPermissions(): array
    {
        $formatted = [];
        foreach ($this->permissions ?? [] as $permission) {
            $formatted[$permission] = self::PERMISSIONS[$permission] ?? $permission;
        }
        return $formatted;
    }
}