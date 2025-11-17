<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_points',
        'building_points',
        'units_points',
        'defense_points',
        'ship_points',
        'technology_points',
        'earth_attack',
        'earth_defense',
        'earth_attack_count',
        'earth_defense_count',
        'earth_loser_count',
        'spatial_attack',
        'spatial_defense',
        'spatial_attack_count',
        'spatial_defense_count',
        'spatial_loser_count',
        'pillage',
        'exploration_count',
        'extraction_count',
        'construction_spent',
        'current_rank',
        'previous_rank',
        'rank_change',
        'last_rank_update',
    ];

    protected $casts = [
        'total_points' => 'integer',
        'building_points' => 'integer',
        'units_points' => 'integer',
        'defense_points' => 'integer',
        'ship_points' => 'integer',
        'technology_points' => 'integer',
        'earth_attack' => 'integer',
        'earth_defense' => 'integer',
        'earth_attack_count' => 'integer',
        'earth_defense_count' => 'integer',
        'earth_loser_count' => 'integer',
        'spatial_attack' => 'integer',
        'spatial_defense' => 'integer',
        'spatial_attack_count' => 'integer',
        'spatial_defense_count' => 'integer',
        'spatial_loser_count' => 'integer',
        'pillage' => 'integer',
        'exploration_count' => 'integer',
        'extraction_count' => 'integer',
        'construction_spent' => 'integer',
        'current_rank' => 'integer',
        'previous_rank' => 'integer',
        'rank_change' => 'integer',
        'last_rank_update' => 'date',
    ];

    /**
     * Get the user this stat belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate and update total points
     */
    public function updateTotalPoints(): void
    {
        $this->total_points = $this->building_points + 
                             $this->units_points + 
                             $this->defense_points + 
                             $this->ship_points + 
                             $this->technology_points;
        $this->save();
    }

    /**
     * Get points breakdown as array
     */
    public function getPointsBreakdown(): array
    {
        return [
            'total' => $this->total_points,
            'building' => $this->building_points,
            'units' => $this->units_points,
            'defense' => $this->defense_points,
            'ship' => $this->ship_points,
            'technology' => $this->technology_points,
        ];
    }

    /**
     * Add earth attack stats
     */
    public function addEarthAttackStats(int $points): void
    {
        $this->earth_attack += $points;
        $this->earth_attack_count += 1;
        $this->save();
    }

    /**
     * Add earth defense stats
     */
    public function addEarthDefenseStats(int $points): void
    {
        $this->earth_defense += $points;
        $this->earth_defense_count += 1;
        $this->save();
    }

    /**
     * Add spatial attack stats
     */
    public function addSpatialAttackStats(int $points): void
    {
        $this->spatial_attack += $points;
        $this->spatial_attack_count += 1;
        $this->save();
    }

    /**
     * Add spatial defense stats
     */
    public function addSpatialDefenseStats(int $points): void
    {
        $this->spatial_defense += $points;
        $this->spatial_defense_count += 1;
        $this->save();
    }

    /**
     * Add earth loser count (when losing an earth attack)
     */
    public function addEarthLoserCount(): void
    {
        $this->earth_loser_count += 1;
        $this->save();
    }

    /**
     * Add spatial loser count (when losing a spatial attack)
     */
    public function addSpatialLoserCount(): void
    {
        $this->spatial_loser_count += 1;
        $this->save();
    }

    /**
     * Get combat stats breakdown
     */
    public function getCombatStatsBreakdown(): array
    {
        return [
            'earth' => [
                'attack_points' => $this->earth_attack,
                'defense_points' => $this->earth_defense,
                'attack_count' => $this->earth_attack_count,
                'defense_count' => $this->earth_defense_count,
                'loser_count' => $this->earth_loser_count,
            ],
            'spatial' => [
                'attack_points' => $this->spatial_attack,
                'defense_points' => $this->spatial_defense,
                'attack_count' => $this->spatial_attack_count,
                'defense_count' => $this->spatial_defense_count,
                'loser_count' => $this->spatial_loser_count,
            ],
        ];
    }
}