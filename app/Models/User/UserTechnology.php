<?php

namespace App\Models\User;

use App\Models\Template\TemplateBuild;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTechnology extends Model
{
    use HasFactory;

    protected $table = 'user_technologies';

    protected $fillable = [
        'user_id',
        'technology_id',
        'level',
        'is_researching',
        'research_start_time',
        'research_end_time',
        'is_active'
    ];

    protected $casts = [
        'level' => 'integer',
        'is_researching' => 'boolean',
        'research_start_time' => 'datetime',
        'research_end_time' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Get the user this technology belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the template technology this is based on
     */
    public function technology(): BelongsTo
    {
        return $this->belongsTo(TemplateBuild::class, 'technology_id');
    }

    /**
     * Check if technology is currently being researched
     */
    public function isUnderResearch(): bool
    {
        return $this->is_researching && $this->research_end_time && $this->research_end_time->isFuture();
    }

    /**
     * Check if research is completed
     */
    public function isResearchCompleted(): bool
    {
        return $this->is_researching && $this->research_end_time && $this->research_end_time->isPast();
    }

    /**
     * Get remaining research time in seconds
     */
    public function getRemainingResearchTime(): int
    {
        if (!$this->isUnderResearch()) {
            return 0;
        }

        return max(0, $this->research_end_time->diffInSeconds(now()));
    }

    /**
     * Get research progress percentage
     */
    public function getResearchProgress(): float
    {
        if (!$this->is_researching || !$this->research_start_time || !$this->research_end_time) {
            return 0;
        }

        $totalTime = $this->research_end_time->diffInSeconds($this->research_start_time);
        $elapsedTime = now()->diffInSeconds($this->research_start_time);

        if ($totalTime <= 0) {
            return 100;
        }

        return min(100, ($elapsedTime / $totalTime) * 100);
    }

    /**
     * Start research upgrade
     */
    public function startResearch(int $researchTime): void
    {
        $this->update([
            'is_researching' => true,
            'research_start_time' => now(),
            'research_end_time' => now()->addSeconds($researchTime)
        ]);
    }

    /**
     * Complete research upgrade
     */
    public function completeResearch(): void
    {
        $this->update([
            'level' => $this->level + 1,
            'is_researching' => false,
            'research_start_time' => null,
            'research_end_time' => null
        ]);
    }

    /**
     * Cancel research
     */
    public function cancelResearch(): void
    {
        $this->update([
            'is_researching' => false,
            'research_start_time' => null,
            'research_end_time' => null
        ]);
    }

    /**
     * Get research cost for next level (uses research points)
     */
    public function getResearchCost(): int
    {
        $nextLevel = $this->level + 1;
        
        // Find research points cost from template
        $researchPointsCost = $this->technology->costs()
            ->whereHas('resource', function($query) {
                $query->where('name', 'research_points');
            })
            ->first();
            
        if ($researchPointsCost) {
            return $researchPointsCost->calculateCostForLevel($nextLevel);
        }
        
        return 0;
    }

    /**
     * Get research time for next level
     */
    public function getResearchTime(): int
    {
        $nextLevel = $this->level + 1;
        $baseTime = $this->technology->base_build_time;
        
        // Formula: base_time * (2 ^ (level - 1))
        $calculatedTime = (int) ($baseTime * pow(2, $nextLevel - 1));
        
        // Appliquer le bonus de faction pour la vitesse de recherche
        if ($this->user && $this->user->faction) {
            $technologySpeedBonus = $this->user->faction->getBonusBuildingSpeed(); // Utiliser le même bonus que pour les bâtiments
            if ($technologySpeedBonus > 0) { // Bonus positif = réduction de temps
                $calculatedTime = (int)($calculatedTime * (1 - $technologySpeedBonus / 100));
            }
        }
        
        return $calculatedTime;
    }

    /**
     * Check if technology can be researched
     */
    public function canResearch(): bool
    {
        // Check if already researching
        if ($this->is_researching) {
            return false;
        }

        // Check max level
        if ($this->technology->max_level > 0 && $this->level >= $this->technology->max_level) {
            return false;
        }

        // Check requirements
        foreach ($this->technology->requirements as $requirement) {
            if (!$requirement->isMetForUser($this->user_id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user has enough research points
     */
    public function hasEnoughResearchPoints(): bool
    {
        $cost = $this->getResearchCost();
        return $this->user->research_points >= $cost;
    }

    /**
     * Consume research points for research
     */
    public function consumeResearchPoints(): void
    {
        $cost = $this->getResearchCost();
        $this->user->decrement('research_points', $cost);
    }

    /**
     * Get technology bonus for specific type
     */
    public function getTechnologyBonus(string $bonusType): float
    {
        // This would calculate bonus based on technology level and type
        // For now, return a simple calculation
        switch ($bonusType) {
            case 'research_speed':
                return $this->level * 0.1; // 10% per level
            case 'production_boost':
                return $this->level * 0.05; // 5% per level
            case 'energy_efficiency':
                return $this->level * 0.02; // 2% per level
            default:
                return 0;
        }
    }

    /**
     * Get user's total research level (for ranking)
     */
    public static function getUserTotalResearchLevel($userId): int
    {
        return self::where('user_id', $userId)
            ->where('is_active', true)
            ->sum('level');
    }

    /**
     * Get user's research in specific category
     */
    public static function getUserResearchInCategory($userId, $category): int
    {
        return self::where('user_id', $userId)
            ->where('is_active', true)
            ->whereHas('technology', function($query) use ($category) {
                $query->where('category', $category);
            })
            ->sum('level');
    }

    /**
     * Check if user is currently researching anything
     */
    public static function isUserResearching($userId): bool
    {
        return self::where('user_id', $userId)
            ->where('is_researching', true)
            ->where('research_end_time', '>', now())
            ->exists();
    }

    /**
     * Get user's current research
     */
    public static function getUserCurrentResearch($userId): ?self
    {
        return self::where('user_id', $userId)
            ->where('is_researching', true)
            ->where('research_end_time', '>', now())
            ->first();
    }

    /**
     * Scope for active technologies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for technologies under research
     */
    public function scopeUnderResearch($query)
    {
        return $query->where('is_researching', true)
            ->where('research_end_time', '>', now());
    }

    /**
     * Scope for completed research
     */
    public function scopeResearchCompleted($query)
    {
        return $query->where('is_researching', true)
            ->where('research_end_time', '<=', now());
    }

    /**
     * Scope by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope by technology type
     */
    public function scopeByTechnologyType($query, $technologyId)
    {
        return $query->where('technology_id', $technologyId);
    }

    /**
     * Scope by minimum level
     */
    public function scopeMinLevel($query, $level)
    {
        return $query->where('level', '>=', $level);
    }

    /**
     * Scope by technology category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->whereHas('technology', function($q) use ($category) {
            $q->where('category', $category);
        });
    }
}