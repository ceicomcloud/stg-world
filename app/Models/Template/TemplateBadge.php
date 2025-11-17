<?php

namespace App\Models\Template;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TemplateBadge extends Model
{
    use HasFactory;

    protected $table = 'badges';

    protected $fillable = [
        'name',
        'description',
        'icon',
        'type',
        'requirement_type',
        'requirement_value',
        'rarity',
        'points_reward',
        'is_active'
    ];

    protected $casts = [
        'requirement_value' => 'integer',
        'points_reward' => 'integer',
        'is_active' => 'boolean'
    ];

    // Badge types
    const TYPE_LEVEL = 'niveau';
    const TYPE_EXPERIENCE = 'expérience';
    const TYPE_RESEARCH = 'recherche';
    const TYPE_ACHIEVEMENT = 'accomplissement';
    const TYPE_SPECIAL = 'spécial';

    // Badge rarities
    const RARITY_COMMON = 'commun';
    const RARITY_UNCOMMON = 'peu commun';
    const RARITY_RARE = 'rare';
    const RARITY_EPIC = 'épique';
    const RARITY_LEGENDARY = 'légendaire';

    // Requirement types
    const REQUIREMENT_REACH_LEVEL = 'reach_level';
    const REQUIREMENT_TOTAL_EXPERIENCE = 'total_experience';
    const REQUIREMENT_RESEARCH_POINTS = 'research_points';
    const REQUIREMENT_CUSTOM = 'custom';

    /**
     * Users who have earned this badge
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges', 'badge_id', 'user_id')
                    ->withTimestamps()
                    ->withPivot('earned_at');
    }

    /**
     * Check if a user meets the requirements for this badge
     */
    public function checkRequirement(User $user): bool
    {
        if (!$this->is_active) {
            return false;
        }

        switch ($this->requirement_type) {
            case self::REQUIREMENT_REACH_LEVEL:
                return $user->getLevel() >= $this->requirement_value;

            case self::REQUIREMENT_TOTAL_EXPERIENCE:
                $totalXp = $user->getTotalExperienceForLevel($user->getLevel()) + $user->getCurrentExperience();
                return $totalXp >= $this->requirement_value;

            case self::REQUIREMENT_RESEARCH_POINTS:
                return $user->getResearchPoints() >= $this->requirement_value;

            case self::REQUIREMENT_CUSTOM:
                // For custom requirements, you can extend this method
                return $this->checkCustomRequirement($user);

            default:
                return false;
        }
    }

    /**
     * Override this method for custom badge requirements
     */
    protected function checkCustomRequirement(User $user): bool
    {
        // Override in specific badge implementations or use events
        return false;
    }

    /**
     * Get badge color based on rarity
     */
    public function getRarityColor(): string
    {
        return match($this->rarity) {
            self::RARITY_COMMON => '#9CA3AF',      // Gray
            self::RARITY_UNCOMMON => '#10B981',    // Green
            self::RARITY_RARE => '#3B82F6',        // Blue
            self::RARITY_EPIC => '#8B5CF6',        // Purple
            self::RARITY_LEGENDARY => '#F59E0B',   // Orange/Gold
            default => '#6B7280'                   // Default gray
        };
    }

    /**
     * Get all available badge types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_LEVEL => 'Niveau',
            self::TYPE_EXPERIENCE => 'Expérience',
            self::TYPE_RESEARCH => 'Recherche',
            self::TYPE_ACHIEVEMENT => 'Accomplissement',
            self::TYPE_SPECIAL => 'Spécial'
        ];
    }

    /**
     * Get all available rarities
     */
    public static function getRarities(): array
    {
        return [
            self::RARITY_COMMON => 'Commun',
            self::RARITY_UNCOMMON => 'Peu commun',
            self::RARITY_RARE => 'Rare',
            self::RARITY_EPIC => 'Épique',
            self::RARITY_LEGENDARY => 'Légendaire'
        ];
    }

    /**
     * Get all requirement types
     */
    public static function getRequirementTypes(): array
    {
        return [
            self::REQUIREMENT_REACH_LEVEL => 'Atteindre le niveau',
            self::REQUIREMENT_TOTAL_EXPERIENCE => 'Expérience totale',
            self::REQUIREMENT_RESEARCH_POINTS => 'Points de recherche',
            self::REQUIREMENT_CUSTOM => 'Personnalisé'
        ];
    }

    /**
     * Create default level badges
     */
    public static function createDefaultLevelBadges(): void
    {
        $levelBadges = [
            ['level' => 5, 'name' => 'Novice', 'rarity' => self::RARITY_COMMON],
            ['level' => 10, 'name' => 'Apprenti', 'rarity' => self::RARITY_COMMON],
            ['level' => 25, 'name' => 'Explorateur', 'rarity' => self::RARITY_UNCOMMON],
            ['level' => 50, 'name' => 'Vétéran', 'rarity' => self::RARITY_RARE],
            ['level' => 75, 'name' => 'Expert', 'rarity' => self::RARITY_EPIC],
            ['level' => 100, 'name' => 'Maître', 'rarity' => self::RARITY_LEGENDARY],
            ['level' => 150, 'name' => 'Légende', 'rarity' => self::RARITY_LEGENDARY],
            ['level' => 200, 'name' => 'Immortel', 'rarity' => self::RARITY_LEGENDARY]
        ];

        foreach ($levelBadges as $badge) {
            self::updateOrCreate(
                [
                    'type' => self::TYPE_LEVEL,
                    'requirement_type' => self::REQUIREMENT_REACH_LEVEL,
                    'requirement_value' => $badge['level']
                ],
                [
                    'name' => $badge['name'],
                    'description' => "Atteindre le niveau {$badge['level']}",
                    'icon' => 'level-' . $badge['level'],
                    'rarity' => $badge['rarity'],
                    'points_reward' => $badge['level'] * 10,
                    'is_active' => true
                ]
            );
        }
    }
}