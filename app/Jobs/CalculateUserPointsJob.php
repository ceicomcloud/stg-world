<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\User\UserStat;
use App\Services\UserPointsService;
use App\Services\DailyQuestService;
use App\Models\Template\TemplateBuild;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CalculateUserPointsJob implements ShouldQueue
{
    use Queueable;

    private ?int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(?int $userId = null)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $userPointsService = app(UserPointsService::class);
            
            if ($this->userId) {
                // Calculate points for specific user
                $user = User::find($this->userId);
                if ($user) {
                    $this->calculateUserPoints($user);
                    // Update daily ranking for this specific user
                    $userPointsService->updateUserDailyRanking($this->userId);
                }
            } else {
                // Calculate points for all users
                User::chunk(100, function ($users) {
                    foreach ($users as $user) {
                        $this->calculateUserPoints($user);
                    }
                });
                
                // Update daily rankings for all users after calculating all points
                $userPointsService->updateDailyRankings();
            }
        } catch (\Exception $e) {
            Log::error('Error calculating user points: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate points for a specific user
     */
    private function calculateUserPoints(User $user): void
    {
        $buildingPoints = 0;
        $unitsPoints = 0;
        $defensePoints = 0;
        $shipPoints = 0;
        $technologyPoints = 0;

        // Calculate points for all user's planets
        foreach ($user->planets as $planet) {
            $buildingPoints += $this->calculateBuildingPoints($planet);
            $unitsPoints += $this->calculateUnitsPoints($planet);
            $defensePoints += $this->calculateDefensePoints($planet);
            $shipPoints += $this->calculateShipPoints($planet);
        }

        // Calculate technology points
        $technologyPoints = $this->calculateTechnologyPoints($user);

        // Update or create user stats and compute delta for daily quest
        $previousTotal = $user->userStat ? (int) $user->userStat->total_points : 0;
        $userStat = $user->userStat ?? new UserStat(['user_id' => $user->id]);
        $userStat->building_points = $buildingPoints;
        $userStat->units_points = $unitsPoints;
        $userStat->defense_points = $defensePoints;
        $userStat->ship_points = $shipPoints;
        $userStat->technology_points = $technologyPoints;
        $userStat->total_points = $buildingPoints + $unitsPoints + $defensePoints + $shipPoints + $technologyPoints;
        $userStat->save();

        // Incrémenter la quête quotidienne gain_points_{X} selon le delta de points
        $delta = max(0, (int) $userStat->total_points - $previousTotal);
        if ($delta > 0) {
            app(DailyQuestService::class)->incrementProgressByPrefix($user, 'gain_points_', $delta);
        }
    }

    /**
     * Calculate building points for a planet
     */
    private function calculateBuildingPoints($planet): int
    {
        $totalCost = 0;

        foreach ($planet->buildings as $building) {
            if ($building->level > 0) {
                $templateBuild = TemplateBuild::find($building->building_id);
                if ($templateBuild) {
                    // Calculate total cost for all levels (1 to current level)
                    for ($level = 1; $level <= $building->level; $level++) {
                        $levelCost = 0;
                        foreach ($templateBuild->costs as $cost) {
                            $levelCost += $cost->calculateCostForLevel($level);
                        }
                        $totalCost += $levelCost;
                    }
                }
            }
        }

        // 1000 resources = 1 point
        return intval($totalCost / 2000);
    }

    /**
     * Calculate units points for a planet
     */
    private function calculateUnitsPoints($planet): int
    {
        $totalCost = 0;

        foreach ($planet->units as $unit) {
            if ($unit->quantity > 0) {
                $templateBuild = TemplateBuild::find($unit->unit_id);
                if ($templateBuild) {
                    $unitCost = 0;
                    foreach ($templateBuild->costs as $cost) {
                        $unitCost += $cost->calculateCostForLevel(1); // Units have base cost
                    }
                    $totalCost += $unitCost * $unit->quantity;
                }
            }
        }

        // 1000 resources = 1 point
        return intval($totalCost / 2000);
    }

    /**
     * Calculate defense points for a planet
     */
    private function calculateDefensePoints($planet): int
    {
        $totalCost = 0;

        foreach ($planet->defenses as $defense) {
            if ($defense->quantity > 0) {
                $templateBuild = TemplateBuild::find($defense->defense_id);
                if ($templateBuild) {
                    $defenseCost = 0;
                    foreach ($templateBuild->costs as $cost) {
                        $defenseCost += $cost->calculateCostForLevel(1); // Defenses have base cost
                    }
                    $totalCost += $defenseCost * $defense->quantity;
                }
            }
        }

        // 1000 resources = 1 point
        return intval($totalCost / 2000);
    }

    /**
     * Calculate ship points for a planet
     */
    private function calculateShipPoints($planet): int
    {
        $totalCost = 0;

        foreach ($planet->ships as $ship) {
            if ($ship->quantity > 0) {
                $templateBuild = TemplateBuild::find($ship->ship_id);
                if ($templateBuild) {
                    $shipCost = 0;
                    foreach ($templateBuild->costs as $cost) {
                        $shipCost += $cost->calculateCostForLevel(1); // Ships have base cost
                    }
                    $totalCost += $shipCost * $ship->quantity;
                }
            }
        }

        // 1000 resources = 1 point
        return intval($totalCost / 2000);
    }

    /**
     * Calculate technology points for a user
     */
    private function calculateTechnologyPoints(User $user): int
    {
        $totalCost = 0;

        foreach ($user->technologies as $technology) {
            if ($technology->level > 0) {
                $templateBuild = TemplateBuild::find($technology->technology_id);
                if ($templateBuild) {
                    // Calculate total cost for all levels (1 to current level)
                    for ($level = 1; $level <= $technology->level; $level++) {
                        $levelCost = 0;
                        foreach ($templateBuild->costs as $cost) {
                            $levelCost += $cost->calculateCostForLevel($level);
                        }
                        $totalCost += $levelCost;
                    }
                }
            }
        }

        // 100 resources = 1 technology point
        return intval($totalCost / 1000);
    }
}
