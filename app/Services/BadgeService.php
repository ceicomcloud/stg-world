<?php

namespace App\Services;

use App\Models\Template\TemplateBadge;
use App\Models\User;
use App\Models\User\UserBadge;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Services\DailyQuestService;

class BadgeService
{
    /**
     * Check and award all eligible badges for a user
     */
    public function checkAndAwardAllBadges(User $user): array
    {
        return $user->checkAndAwardBadges();
    }

    /**
     * Award a specific badge to a user
     */
    public function awardBadge(User $user, TemplateBadge $badge): bool
    {
        if ($user->hasBadge($badge)) {
            return false;
        }

        $user->awardBadge($badge);

        // Incrémenter la quête quotidienne d'obtention de badge
        app(DailyQuestService::class)->incrementProgress($user, 'obtain_badge');
        return true;
    }

    /**
     * Award a custom badge (for special events, admin actions, etc.)
     */
    public function awardCustomBadge(User $user, string $badgeName): bool
    {
        $badge = TemplateBadge::where('name', $badgeName)
                     ->where('is_active', true)
                     ->first();

        if (!$badge) {
            return false;
        }

        return $this->awardBadge($user, $badge);
    }

    /**
     * Get user's badge statistics
     */
    public function getUserBadgeStats(User $user): array
    {
        $badges = $user->badges;
        $totalBadges = TemplateBadge::where('is_active', true)->count();
        
        return [
            'total_earned' => $badges->count(),
            'total_available' => $totalBadges,
            'completion_percentage' => $totalBadges > 0 ? round(($badges->count() / $totalBadges) * 100, 2) : 0,
            'by_rarity' => $user->getBadgeCountByRarity(),
            'by_type' => $badges->groupBy('type')->map->count()->toArray(),
            'recent_badges' => $badges->take(5)->values(),
            'total_points_from_badges' => $badges->sum('points_reward')
        ];
    }

    /**
     * Get badges that a user is close to earning
     */
    public function getUpcomingBadges(User $user, int $limit = 5): Collection
    {
        $earnedBadgeIds = $user->badges()->pluck('badges.id')->toArray();
        
        return TemplateBadge::where('is_active', true)
                   ->whereNotIn('id', $earnedBadgeIds)
                   ->get()
                   ->filter(function ($badge) use ($user) {
                       return $this->getBadgeProgress($user, $badge) > 0;
                   })
                   ->sortByDesc(function ($badge) use ($user) {
                       return $this->getBadgeProgress($user, $badge);
                   })
                   ->take($limit)
                   ->values();
    }

    /**
     * Get progress towards a specific badge (0-100)
     */
    public function getBadgeProgress(User $user, TemplateBadge $badge): float
    {
        if ($user->hasBadge($badge)) {
            return 100.0;
        }

        $current = $this->getCurrentValueForBadge($user, $badge);
        $required = $badge->requirement_value;

        if ($required <= 0) {
            return 0.0;
        }

        return min(100.0, ($current / $required) * 100);
    }

    /**
     * Get current user value for badge requirement
     */
    private function getCurrentValueForBadge(User $user, TemplateBadge $badge): int
    {
        switch ($badge->requirement_type) {
            case TemplateBadge::REQUIREMENT_REACH_LEVEL:
                return $user->getLevel();

            case TemplateBadge::REQUIREMENT_TOTAL_EXPERIENCE:
                return $user->getTotalExperienceForLevel($user->getLevel()) + $user->getCurrentExperience();

            case TemplateBadge::REQUIREMENT_RESEARCH_POINTS:
                return $user->getResearchPoints();

            case TemplateBadge::REQUIREMENT_CUSTOM:
                // For custom badges, return 0 by default
                // Override this method or use events for custom logic
                return 0;

            default:
                return 0;
        }
    }

    /**
     * Get leaderboard for a specific badge type
     */
    public function getBadgeLeaderboard(string $type, int $limit = 10): Collection
    {
        return User::withCount(['badges' => function ($query) use ($type) {
                        $query->where('type', $type);
                    }])
                   ->orderBy('badges_count', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get global badge leaderboard
     */
    public function getGlobalBadgeLeaderboard(int $limit = 10): Collection
    {
        return User::withCount('badges')
                   ->orderBy('badges_count', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Create a custom badge
     */
    public function createCustomBadge(array $data): TemplateBadge
    {
        return TemplateBadge::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'icon' => $data['icon'] ?? null,
            'type' => $data['type'] ?? TemplateBadge::TYPE_SPECIAL,
            'requirement_type' => $data['requirement_type'] ?? TemplateBadge::REQUIREMENT_CUSTOM,
            'requirement_value' => $data['requirement_value'] ?? 1,
            'rarity' => $data['rarity'] ?? TemplateBadge::RARITY_COMMON,
            'points_reward' => $data['points_reward'] ?? 0,
            'is_active' => $data['is_active'] ?? true
        ]);
    }

    /**
     * Bulk award badges to multiple users
     */
    public function bulkAwardBadge(Collection $users, TemplateBadge $badge): int
    {
        $awarded = 0;
        
        foreach ($users as $user) {
            if ($this->awardBadge($user, $badge)) {
                $awarded++;
            }
        }
        
        return $awarded;
    }

    /**
     * Get badge recommendations for a user
     */
    public function getBadgeRecommendations(User $user, int $limit = 3): Collection
    {
        $earnedBadgeIds = $user->badges()->pluck('badges.id')->toArray();
        
        // Get badges that are achievable soon (>50% progress)
        return TemplateBadge::where('is_active', true)
                   ->whereNotIn('id', $earnedBadgeIds)
                   ->get()
                   ->filter(function ($badge) use ($user) {
                       $progress = $this->getBadgeProgress($user, $badge);
                       return $progress >= 50 && $progress < 100;
                   })
                   ->sortByDesc(function ($badge) use ($user) {
                       return $this->getBadgeProgress($user, $badge);
                   })
                   ->take($limit)
                   ->values();
    }

    /**
     * Auto-award badges to a user based on their current stats
     */
    public function autoAwardBadges(User $user): array
    {
        $newBadges = [];
        $availableBadges = TemplateBadge::where('is_active', true)->get();
        $earnedBadgeIds = $user->badges()->pluck('badges.id')->toArray();

        foreach ($availableBadges as $badge) {
            // Skip if already earned
            if (in_array($badge->id, $earnedBadgeIds)) {
                continue;
            }

            // Check if requirements are met
            if ($this->checkBadgeRequirement($user, $badge)) {
                $this->awardBadge($user, $badge);
                
                $newBadges[] = $badge;
            }
        }

        return $newBadges;
    }

    /**
     * Check if user meets badge requirements (including custom ones)
     */
    public function checkBadgeRequirement(User $user, TemplateBadge $badge): bool
    {
        if (!$badge->is_active) {
            return false;
        }

        switch ($badge->requirement_type) {
            case TemplateBadge::REQUIREMENT_REACH_LEVEL:
                return $user->getLevel() >= $badge->requirement_value;

            case TemplateBadge::REQUIREMENT_TOTAL_EXPERIENCE:
                $totalXp = $user->getTotalExperienceForLevel($user->getLevel()) + $user->getCurrentExperience();
                return $totalXp >= $badge->requirement_value;

            case TemplateBadge::REQUIREMENT_RESEARCH_POINTS:
                return $user->getResearchPoints() >= $badge->requirement_value;

            case TemplateBadge::REQUIREMENT_CUSTOM:
                return $this->checkCustomBadgeRequirement($user, $badge);

            default:
                return false;
        }
    }

    /**
     * Check custom badge requirements
     */
    private function checkCustomBadgeRequirement(User $user, TemplateBadge $badge): bool
    {
        // Vérifications personnalisées basées sur le nom du badge
        switch ($badge->name) {
            // Forum badges
            case 'Premier Message':
                return $user->forumPosts()->count() >= 1;
            
            case 'Bavard':
                return $user->forumPosts()->count() >= 10;
            
            case 'Communicateur':
                return $user->forumPosts()->count() >= 50;
            
            case 'Orateur':
                return $user->forumPosts()->count() >= 200;
            
            case 'Maître du Forum':
                return $user->forumPosts()->count() >= 1000;
            
            case 'Créateur de Sujet':
                return $user->forumTopics()->count() >= 1;
            
            case 'Initiateur':
                return $user->forumTopics()->count() >= 10;
            
            case 'Modérateur Citoyen':
                return $user->forumReports()->count() >= 5;
            
            // Building badges
            case 'Premier Bâtiment':
                return $user->planets()->with('buildings')->get()
                    ->flatMap->buildings->where('level', '>', 0)->count() >= 1;
            
            case 'Architecte Débutant':
                return $user->planets()->with('buildings')->get()
                    ->flatMap->buildings->sum('level') >= 10;
            
            case 'Constructeur':
                return $user->planets()->with('buildings')->get()
                    ->flatMap->buildings->sum('level') >= 50;
            
            case 'Maître Architecte':
                return $user->planets()->with('buildings')->get()
                    ->flatMap->buildings->sum('level') >= 200;
            
            case 'Empereur Bâtisseur':
                return $user->planets()->with('buildings')->get()
                    ->flatMap->buildings->sum('level') >= 1000;
            
            // Resource building specific badges
            case 'Industriel':
                return $user->planets()->with(['buildings' => function($query) {
                    $query->whereHas('build', function($q) {
                        $q->where('name', 'mine_fer');
                    })->where('level', '>=', 10);
                }])->get()->flatMap->buildings->count() >= 1;
            
            case 'Cristallier':
                return $user->planets()->with(['buildings' => function($query) {
                    $query->whereHas('build', function($q) {
                        $q->where('name', 'extracteur_cristal');
                    })->where('level', '>=', 10);
                }])->get()->flatMap->buildings->count() >= 1;
            
            case 'Raffineur':
                return $user->planets()->with(['buildings' => function($query) {
                    $query->whereHas('build', function($q) {
                        $q->where('name', 'raffinerie_deuterium');
                    })->where('level', '>=', 10);
                }])->get()->flatMap->buildings->count() >= 1;
            
            case 'Énergéticien':
                return $user->planets()->with(['buildings' => function($query) {
                    $query->whereHas('build', function($q) {
                        $q->where('name', 'centrale_solaire');
                    })->where('level', '>=', 15);
                }])->get()->flatMap->buildings->count() >= 1;
            
            // Defense badges
            case 'Défenseur':
                return $user->planets()->with('defenses')->get()
                    ->flatMap->defenses->where('quantity', '>', 0)->count() >= 1;
            
            case 'Forteresse':
                return $user->planets()->with('defenses')->get()
                    ->flatMap->defenses->sum('quantity') >= 100;
            
            // Ship badges
            case 'Amiral':
                return $user->planets()->with('ships')->get()
                    ->flatMap->ships->where('quantity', '>', 0)->count() >= 1;
            
            case 'Commandant de Flotte':
                return $user->planets()->with('ships')->get()
                    ->flatMap->ships->sum('quantity') >= 50;
            
            // Earth Attack badges
            case 'Guerrier Terrestre':
                return $user->userStat->earth_attack >= 100;
            
            case 'Conquérant Terrestre':
                return $user->userStat->earth_attack >= 1000;
            
            case 'Maître de Guerre Terrestre':
                return $user->userStat->earth_attack >= 10000;
            
            case 'Légende Terrestre':
                return $user->userStat->earth_attack >= 100000;
            
            case 'Empereur des Batailles Terrestres':
                return $user->userStat->earth_attack >= 1000000;
            
            // Earth Defense badges
            case 'Défenseur Terrestre':
                return $user->userStat->earth_defense >= 100;
            
            case 'Gardien Terrestre':
                return $user->userStat->earth_defense >= 1000;
            
            case 'Protecteur Terrestre':
                return $user->userStat->earth_defense >= 10000;
            
            case 'Bouclier Terrestre':
                return $user->userStat->earth_defense >= 100000;
            
            case 'Forteresse Terrestre':
                return $user->userStat->earth_defense >= 1000000;
            
            // Spatial Attack badges
            case 'Guerrier Spatial':
                return $user->userStat->spatial_attack >= 100;
            
            case 'Conquérant Spatial':
                return $user->userStat->spatial_attack >= 1000;
            
            case 'Maître de Guerre Spatial':
                return $user->userStat->spatial_attack >= 10000;
            
            case 'Légende Spatiale':
                return $user->userStat->spatial_attack >= 100000;
            
            case 'Empereur des Batailles Spatiales':
                return $user->userStat->spatial_attack >= 1000000;
            
            // Spatial Defense badges
            case 'Défenseur Spatial':
                return $user->userStat->spatial_defense >= 100;
            
            case 'Gardien Spatial':
                return $user->userStat->spatial_defense >= 1000;
            
            case 'Protecteur Spatial':
                return $user->userStat->spatial_defense >= 10000;
            
            case 'Bouclier Spatial':
                return $user->userStat->spatial_defense >= 100000;
            
            case 'Forteresse Spatiale':
                return $user->userStat->spatial_defense >= 1000000;
            
            // Combat Count badges
            case 'Vétéran Terrestre':
                return $user->userStat->earth_attack_count >= 10;
            
            case 'Stratège Terrestre':
                return $user->userStat->earth_attack_count >= 100;
            
            case 'Tacticien Terrestre':
                return $user->userStat->earth_attack_count >= 500;
            
            case 'Vétéran Spatial':
                return $user->userStat->spatial_attack_count >= 10;
            
            case 'Stratège Spatial':
                return $user->userStat->spatial_attack_count >= 100;
            
            case 'Tacticien Spatial':
                return $user->userStat->spatial_attack_count >= 500;
            
            case 'Survivant Terrestre':
                return $user->userStat->earth_defense_count >= 10;
            
            case 'Résistant Terrestre':
                return $user->userStat->earth_defense_count >= 50;
            
            case 'Survivant Spatial':
                return $user->userStat->spatial_defense_count >= 10;
            
            case 'Résistant Spatial':
                return $user->userStat->spatial_defense_count >= 50;
            
            default:
                return false;
        }
    }

    /**
     * Auto-award badges to all users
     */
    public function autoAwardBadgesToAllUsers(int $batchSize = 500, int $sleepMs = 0): array
    {
        $results = [];

        User::query()
            ->select(['id', 'name'])
            ->orderBy('id')
            ->chunkById($batchSize, function ($users) use (&$results, $sleepMs) {
                foreach ($users as $user) {
                    $newBadges = $this->autoAwardBadges($user);
                    if (!empty($newBadges)) {
                        $results[$user->id] = [
                            'user' => $user->name,
                            'badges' => collect($newBadges)->pluck('name')->toArray()
                        ];
                    }
                }
                if ($sleepMs > 0) {
                    usleep($sleepMs * 1000);
                }
            });
        
        return $results;
    }
}