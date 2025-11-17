<?php

namespace App\Models\Player;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Planet\Planet;
use Carbon\Carbon;

class PlayerAttackLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'access_key',
        'attacker_user_id',
        'defender_user_id',
        'attacker_planet_id',
        'defender_planet_id',
        'attack_type',
        'attacker_units',
        'report_data',
        'combat_result',
        'attacker_won',
        'points_gained',
        'resources_pillaged',
        'attacked_at',
    ];

    protected $casts = [
        'attacker_units' => 'array',
        'report_data' => 'array',
        'combat_result' => 'array',
        'resources_pillaged' => 'array',
        'attacker_won' => 'boolean',
        'attacked_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function attackerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attacker_user_id');
    }

    public function defenderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'defender_user_id');
    }

    public function attackerPlanet(): BelongsTo
    {
        return $this->belongsTo(Planet::class, 'attacker_planet_id');
    }

    public function defenderPlanet(): BelongsTo
    {
        return $this->belongsTo(Planet::class, 'defender_planet_id');
    }

    /**
     * Vérifier si un joueur peut attaquer un autre joueur aujourd'hui
     * 
     * @param int $attackerUserId
     * @param int $defenderUserId
     * @param int $maxAttacksPerDay
     * @return bool
     */
    public static function canAttackPlayer(int $attackerUserId, int $defenderUserId, int $maxAttacksPerDay = 5): bool
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        
        $attacksToday = self::where('attacker_user_id', $attackerUserId)
            ->where('defender_user_id', $defenderUserId)
            ->whereBetween('attacked_at', [$today, $tomorrow])
            ->count();
            
        return $attacksToday < $maxAttacksPerDay;
    }

    /**
     * Obtenir le nombre d'attaques restantes pour aujourd'hui
     * 
     * @param int $attackerUserId
     * @param int $defenderUserId
     * @param int $maxAttacksPerDay
     * @return int
     */
    public static function getRemainingAttacks(int $attackerUserId, int $defenderUserId, int $maxAttacksPerDay = 5): int
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        
        $attacksToday = self::where('attacker_user_id', $attackerUserId)
            ->where('defender_user_id', $defenderUserId)
            ->whereBetween('attacked_at', [$today, $tomorrow])
            ->count();
            
        return max(0, $maxAttacksPerDay - $attacksToday);
    }

    /**
     * Enregistrer une nouvelle attaque
     * 
     * @param array $attackData
     * @return self
     */
    public static function logAttack(array $attackData): self
    {
        // Génère une clé d'accès unique si absente
        if (empty($attackData['access_key'])) {
            $attackData['access_key'] = \Illuminate\Support\Str::uuid()->toString();
        }

        return self::create([
            'access_key' => $attackData['access_key'],
            'attacker_user_id' => $attackData['attacker_user_id'],
            'defender_user_id' => $attackData['defender_user_id'],
            'attacker_planet_id' => $attackData['attacker_planet_id'],
            'defender_planet_id' => $attackData['defender_planet_id'],
            'attack_type' => $attackData['attack_type'],
            'attacker_units' => $attackData['attacker_units'] ?? [],
            'report_data' => $attackData['report_data'] ?? null,
            'combat_result' => $attackData['combat_result'] ?? [],
            'attacker_won' => $attackData['attacker_won'] ?? false,
            'points_gained' => $attackData['points_gained'] ?? 0,
            'resources_pillaged' => $attackData['resources_pillaged'] ?? [],
            'attacked_at' => $attackData['attacked_at'] ?? now(),
        ]);
    }

    /**
     * Obtenir l'historique des attaques d'un joueur contre un autre
     * 
     * @param int $attackerUserId
     * @param int $defenderUserId
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAttackHistory(int $attackerUserId, int $defenderUserId, int $days = 7)
    {
        $startDate = Carbon::now()->subDays($days);
        
        return self::where('attacker_user_id', $attackerUserId)
            ->where('defender_user_id', $defenderUserId)
            ->where('attacked_at', '>=', $startDate)
            ->orderBy('attacked_at', 'desc')
            ->get();
    }

    /**
     * Obtenir les types d'attaques disponibles
     * 
     * @return array
     */
    public static function getAttackTypes(): array
    {
        return [
            'raid' => 'Raid',
            'attack' => 'Attaque',
            'spy' => 'Espionnage',
            'colonize' => 'Colonisation',
            'transport' => 'Transport',
        ];
    }
}