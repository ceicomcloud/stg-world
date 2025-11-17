<?php

namespace App\Services;

use App\Jobs\CalculateUserPointsJob;
use App\Models\User;
use App\Models\User\UserStat;
use Illuminate\Support\Facades\Queue;

class UserPointsService
{
    /**
     * Calculate points for a specific user
     */
    public function calculateUserPoints(int $userId, bool $useQueue = false): bool
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        if ($useQueue) {
            CalculateUserPointsJob::dispatch($userId);
        } else {
            $job = new CalculateUserPointsJob($userId);
            $job->handle();
        }

        return true;
    }

    /**
     * Calculate points for all users
     */
    public function calculateAllUsersPoints(bool $useQueue = false): void
    {
        if ($useQueue) {
            CalculateUserPointsJob::dispatch();
        } else {
            $job = new CalculateUserPointsJob();
            $job->handle();
        }
    }

    /**
     * Get user points breakdown
     */
    public function getUserPoints(int $userId): ?array
    {
        $user = User::find($userId);
        if (!$user || !$user->userStat) {
            return null;
        }

        return $user->userStat->getPointsBreakdown();
    }

    /**
     * Get top users by total points
     */
    public function getTopUsersByPoints(int $limit = 10): array
    {
        return UserStat::with('user')
            ->orderBy('total_points', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($userStat) {
                return [
                    'user_id' => $userStat->user_id,
                    'username' => $userStat->user->name,
                    'points' => $userStat->getPointsBreakdown()
                ];
            })
            ->toArray();
    }
    
    /**
     * Get top users by total points with pagination
     */
    public function getTopUsersByTotalPoints(int $limit = 50, int $offset = 0)
    {
        return User::with(['userStat', 'alliance'])
            ->whereHas('userStat')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy('user_stats.total_points', 'desc')
            ->select('users.*')
            ->skip($offset)
            ->take($limit)
            ->get();
    }
    
    /**
     * Get top users by building points with pagination
     */
    public function getTopUsersByBuildingPoints(int $limit = 50, int $offset = 0)
    {
        return User::with(['userStat', 'alliance'])
            ->whereHas('userStat')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy('user_stats.building_points', 'desc')
            ->select('users.*')
            ->skip($offset)
            ->take($limit)
            ->get();
    }
    
    /**
     * Get top users by units points with pagination
     */
    public function getTopUsersByUnitsPoints(int $limit = 50, int $offset = 0)
    {
        return User::with(['userStat', 'alliance'])
            ->whereHas('userStat')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy('user_stats.units_points', 'desc')
            ->select('users.*')
            ->skip($offset)
            ->take($limit)
            ->get();
    }
    
    /**
     * Get top users by defense points with pagination
     */
    public function getTopUsersByDefensePoints(int $limit = 50, int $offset = 0)
    {
        return User::with(['userStat', 'alliance'])
            ->whereHas('userStat')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy('user_stats.defense_points', 'desc')
            ->select('users.*')
            ->skip($offset)
            ->take($limit)
            ->get();
    }
    
    /**
     * Get top users by ship points with pagination
     */
    public function getTopUsersByShipPoints(int $limit = 50, int $offset = 0)
    {
        return User::with(['userStat', 'alliance'])
            ->whereHas('userStat')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy('user_stats.ship_points', 'desc')
            ->select('users.*')
            ->skip($offset)
            ->take($limit)
            ->get();
    }
    
    /**
     * Get top users by technology points with pagination
     */
    public function getTopUsersByTechnologyPoints(int $limit = 50, int $offset = 0)
    {
        return User::with(['userStat', 'alliance'])
            ->whereHas('userStat')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy('user_stats.technology_points', 'desc')
            ->select('users.*')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Get top users by earth attack points with pagination
     */
    public function getTopUsersByEarthAttackPoints(int $limit = 50, int $offset = 0)
    {
        return User::with(['userStat', 'alliance'])
            ->whereHas('userStat')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy('user_stats.earth_attack', 'desc')
            ->select('users.*')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Get top users by earth defense points with pagination
     */
    public function getTopUsersByEarthDefensePoints(int $limit = 50, int $offset = 0)
    {
        return User::with(['userStat', 'alliance'])
            ->whereHas('userStat')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy('user_stats.earth_defense', 'desc')
            ->select('users.*')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Get top users by spatial attack points with pagination
     */
    public function getTopUsersBySpatialAttackPoints(int $limit = 50, int $offset = 0)
    {
        return User::with(['userStat', 'alliance'])
            ->whereHas('userStat')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy('user_stats.spatial_attack', 'desc')
            ->select('users.*')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Get top users by spatial defense points with pagination
     */
    public function getTopUsersBySpatialDefensePoints(int $limit = 50, int $offset = 0)
    {
        return User::with(['userStat', 'alliance'])
            ->whereHas('userStat')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy('user_stats.spatial_defense', 'desc')
            ->select('users.*')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Get top users by earth attack count with pagination
     */
    public function getTopUsersByEarthAttackCount(int $limit = 50, int $offset = 0)
    {
        return User::with(['userStat', 'alliance'])
            ->whereHas('userStat')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy('user_stats.earth_attack_count', 'desc')
            ->select('users.*')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Get top users by earth defense count with pagination
     */
    public function getTopUsersByEarthDefenseCount(int $limit = 50, int $offset = 0)
    {
        return User::with(['userStat', 'alliance'])
            ->whereHas('userStat')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy('user_stats.earth_defense_count', 'desc')
            ->select('users.*')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Get top users by spatial attack count with pagination
     */
    public function getTopUsersBySpatialAttackCount(int $limit = 50, int $offset = 0)
    {
        return User::with(['userStat', 'alliance'])
            ->whereHas('userStat')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy('user_stats.spatial_attack_count', 'desc')
            ->select('users.*')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Get top users by spatial defense count with pagination
     */
    public function getTopUsersBySpatialDefenseCount(int $limit = 50, int $offset = 0)
    {
        return User::with(['userStat', 'alliance'])
            ->whereHas('userStat')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy('user_stats.spatial_defense_count', 'desc')
            ->select('users.*')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Get top users by earth loser count with pagination
     */
    public function getTopUsersByEarthLoserCount(int $limit = 50, int $offset = 0)
    {
        return User::with(['userStat', 'alliance'])
            ->whereHas('userStat')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy('user_stats.earth_loser_count', 'desc')
            ->select('users.*')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Get top users by spatial loser count with pagination
     */
    public function getTopUsersBySpatialLoserCount(int $limit = 50, int $offset = 0)
    {
        return User::with(['userStat', 'alliance'])
            ->whereHas('userStat')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderBy('user_stats.spatial_loser_count', 'desc')
            ->select('users.*')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Get top users by specific category
     */
    public function getTopUsersByCategory(string $category, int $limit = 10): array
    {
        $validCategories = ['building_points', 'units_points', 'defense_points', 'ship_points', 'technology_points'];
        
        if (!in_array($category, $validCategories)) {
            throw new \InvalidArgumentException("Invalid category: {$category}");
        }

        return UserStat::with('user')
            ->orderBy($category, 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($userStat) use ($category) {
                return [
                    'user_id' => $userStat->user_id,
                    'username' => $userStat->user->name,
                    'points' => $userStat->{$category},
                    'total_points' => $userStat->total_points
                ];
            })
            ->toArray();
    }

    /**
     * Get user ranking by total points or specific category
     */
    public function getUserRanking(int $userId, string $category = 'total'): ?array
    {
        $user = User::find($userId);
        if (!$user || !$user->userStat) {
            return null;
        }
        
        $columnMap = [
            'total' => 'total_points',
            'buildings' => 'building_points',
            'units' => 'units_points',
            'defense' => 'defense_points',
            'ships' => 'ship_points',
            'technology' => 'technology_points',
            'earth_attack' => 'earth_attack',
            'earth_defense' => 'earth_defense',
            'spatial_attack' => 'spatial_attack',
            'spatial_defense' => 'spatial_defense',
            'earth_attack_count' => 'earth_attack_count',
            'earth_defense_count' => 'earth_defense_count',
            'spatial_attack_count' => 'spatial_attack_count',
            'spatial_defense_count' => 'spatial_defense_count',
            'earth_loser_count' => 'earth_loser_count',
            'spatial_loser_count' => 'spatial_loser_count'
        ];
        
        $column = $columnMap[$category] ?? 'total_points';
        $userPoints = $user->userStat->{$column};
        $rank = UserStat::where($column, '>', $userPoints)->count() + 1;
        
        return [
            'rank' => $rank,
            'points' => $userPoints
        ];
    }

    /**
     * Initialize user stats if they don't exist
     */
    public function initializeUserStats(int $userId): UserStat
    {
        $user = User::find($userId);
        if (!$user) {
            throw new \InvalidArgumentException("User with ID {$userId} not found");
        }

        return UserStat::firstOrCreate(
            ['user_id' => $userId],
            [
                'total_points' => 0,
                'building_points' => 0,
                'units_points' => 0,
                'defense_points' => 0,
                'ship_points' => 0,
                'technology_points' => 0,
            ]
        );
    }

    /**
     * Update daily rankings for all users
     */
    public function updateDailyRankings(): void
    {
        $today = now()->toDateString();
        
        // Get all users with their current rankings
        $users = User::with('userStat')
            ->whereHas('userStat')
            ->get();
            
        foreach ($users as $user) {
            $this->updateUserDailyRanking($user->id, $today);
        }
    }

    /**
     * Update daily ranking for a specific user
     */
    public function updateUserDailyRanking(int $userId, ?string $date = null): void
    {
        $date = $date ?? now()->toDateString();
        $userStat = UserStat::where('user_id', $userId)->first();
        
        if (!$userStat) {
            return;
        }
        
        // Calculate current rank
        $currentRank = UserStat::where('total_points', '>', $userStat->total_points)->count() + 1;
        
        // Only update if it's a new day or first time
        if ($userStat->last_rank_update !== $date) {
            // Store previous rank before updating
            $previousRank = $userStat->current_rank;
            
            // Calculate rank change
            $rankChange = 0;
            if ($previousRank !== null) {
                $rankChange = $currentRank - $previousRank; // Positive = lost positions, Negative = gained positions
            }
            
            // Update the user stats
            $userStat->update([
                'previous_rank' => $previousRank,
                'current_rank' => $currentRank,
                'rank_change' => $rankChange,
                'last_rank_update' => $date
            ]);
        }
    }

    /**
     * Get ranking change indicator for display
     */
    public function getRankingChangeIndicator(UserStat $userStat): array
    {
        // Système de changement de rang désactivé temporairement
        return [
            'icon' => '',
            'class' => '',
            'text' => '',
            'change' => 0
        ];
    }
}