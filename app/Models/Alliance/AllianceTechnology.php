<?php

namespace App\Models\Alliance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllianceTechnology extends Model
{
    use HasFactory;

    protected $fillable = [
        'alliance_id',
        'technology_type',
        'level',
        'max_level'
    ];

    /**
     * Types de technologies disponibles
     */
    public const TYPE_MEMBERS = 'members';
    public const TYPE_BANK = 'bank';

    /**
     * Niveau maximum par défaut
     */
    public const MAX_LEVEL = 15;

    /**
     * Relation avec l'alliance
     */
    public function alliance(): BelongsTo
    {
        return $this->belongsTo(Alliance::class);
    }

    /**
     * Calculer le coût en deuterium pour passer au niveau suivant
     */
    public function getUpgradeCost(): int
    {
        if ($this->level >= $this->max_level) {
            return 0;
        }

        $nextLevel = $this->level + 1;
        
        // Coût de base selon le type de technologie
        $baseCost = match($this->technology_type) {
            self::TYPE_MEMBERS => 1000,
            self::TYPE_BANK => 1500,
            default => 1000
        };

        // Coût exponentiel : coût_base * (niveau^2)
        return $baseCost * ($nextLevel * $nextLevel);
    }

    /**
     * Obtenir le bonus fourni par cette technologie
     */
    public function getBonus(): int
    {
        return match($this->technology_type) {
            self::TYPE_MEMBERS => $this->level * 5, // +5 membres par niveau
            self::TYPE_BANK => $this->level * 10000, // +10,000 deuterium de stockage par niveau
            default => 0
        };
    }

    /**
     * Obtenir la description de la technologie
     */
    public function getDescription(): string
    {
        return match($this->technology_type) {
            self::TYPE_MEMBERS => 'Augmente la capacité maximale de membres de l\'alliance',
            self::TYPE_BANK => 'Augmente la capacité de stockage de deuterium de la banque',
            default => 'Technologie inconnue'
        };
    }

    /**
     * Obtenir le nom formaté de la technologie
     */
    public function getName(): string
    {
        return match($this->technology_type) {
            self::TYPE_MEMBERS => 'Expansion des Membres',
            self::TYPE_BANK => 'Stockage Avancé',
            default => 'Technologie Inconnue'
        };
    }

    /**
     * Vérifier si la technologie peut être améliorée
     */
    public function canUpgrade(): bool
    {
        return $this->level < $this->max_level;
    }

    /**
     * Améliorer la technologie d'un niveau
     */
    public function upgrade(): bool
    {
        if (!$this->canUpgrade()) {
            return false;
        }

        $this->increment('level');
        return true;
    }

    /**
     * Obtenir le bonus au niveau suivant
     */
    public function getNextLevelBonus(): int
    {
        if (!$this->canUpgrade()) {
            return 0;
        }

        $nextLevel = $this->level + 1;
        return match($this->technology_type) {
            self::TYPE_MEMBERS => $nextLevel * 5,
            self::TYPE_BANK => $nextLevel * 10000,
            default => 0
        };
    }
}