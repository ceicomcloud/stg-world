<?php

namespace App\Livewire\Game\Modal;

use App\Models\User;
use App\Services\UserPointsService;
use LivewireUI\Modal\ModalComponent;

class RankingCompare extends ModalComponent
{
    public $userId1;
    public $userId2;
    public $user1;
    public $user2;
    public $user1Stats;
    public $user2Stats;
    
    protected $userPointsService;
    
    public function boot(UserPointsService $userPointsService)
    {
        $this->userPointsService = $userPointsService;
    }
    
    public function mount($userId1, $userId2)
    {
        $this->userId1 = $userId1;
        $this->userId2 = $userId2;
        $this->loadUserData();
    }
    
    /**
     * Charger les données des deux utilisateurs
     */
    public function loadUserData()
    {
        // Charger les données du premier utilisateur
        $this->user1 = User::with('userStat')->find($this->userId1);
        if ($this->user1 && $this->user1->userStat) {
            $userRanking1 = $this->userPointsService->getUserRanking($this->userId1, 'total');
            $this->user1Stats = $this->prepareUserStats($this->user1, $userRanking1);
        }
        
        // Charger les données du deuxième utilisateur
        $this->user2 = User::with('userStat')->find($this->userId2);
        if ($this->user2 && $this->user2->userStat) {
            $userRanking2 = $this->userPointsService->getUserRanking($this->userId2, 'total');
            $this->user2Stats = $this->prepareUserStats($this->user2, $userRanking2);
        }
    }
    
    /**
     * Préparer les statistiques d'un utilisateur pour la comparaison
     */
    private function prepareUserStats($user, $userRanking)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'rank' => $userRanking['rank'] ?? 0,
            'total_points' => $user->userStat->total_points ?? 0,
            'building_points' => $user->userStat->building_points ?? 0,
            'units_points' => $user->userStat->units_points ?? 0,
            'defense_points' => $user->userStat->defense_points ?? 0,
            'ship_points' => $user->userStat->ship_points ?? 0,
            'technology_points' => $user->userStat->technology_points ?? 0,
            'earth_attack' => $user->userStat->earth_attack ?? 0,
            'earth_defense' => $user->userStat->earth_defense ?? 0,
            'spatial_attack' => $user->userStat->spatial_attack ?? 0,
            'spatial_defense' => $user->userStat->spatial_defense ?? 0,
            'earth_attack_count' => $user->userStat->earth_attack_count ?? 0,
            'earth_defense_count' => $user->userStat->earth_defense_count ?? 0,
            'spatial_attack_count' => $user->userStat->spatial_attack_count ?? 0,
            'spatial_defense_count' => $user->userStat->spatial_defense_count ?? 0,
            'earth_loser_count' => $user->userStat->earth_loser_count ?? 0,
            'spatial_loser_count' => $user->userStat->spatial_loser_count ?? 0,
            'planets_count' => $user->planets()->count(),
            'created_at' => $user->created_at,
        ];
    }
    
    /**
     * Obtenir la couleur du rang en fonction de la position
     */
    public function getRankColor($rank)
    {
        if ($rank === 1) {
            return 'gold';
        } elseif ($rank === 2) {
            return 'silver';
        } elseif ($rank === 3) {
            return 'bronze';
        } else {
            return 'regular';
        }
    }
    
    /**
     * Calculer le pourcentage d'une catégorie par rapport au total
     */
    public function calculatePercentage($categoryPoints, $totalPoints)
    {
        if ($totalPoints <= 0) {
            return 0;
        }
        
        return round(($categoryPoints / $totalPoints) * 100);
    }
    
    /**
     * Calculer la différence entre deux valeurs et déterminer si c'est positif ou négatif
     */
    public function calculateDifference($value1, $value2)
    {
        $difference = $value1 - $value2;
        return [
            'value' => abs($difference),
            'positive' => $difference > 0,
            'negative' => $difference < 0,
            'equal' => $difference === 0,
        ];
    }
    
    public function render()
    {
        return view('livewire.game.modal.ranking-compare');
    }
    
    /**
     * Définir les styles CSS à inclure pour ce composant
     */
    public function styles()
    {
        return [
            'css/ranking-compare.css',
        ];
    }
}