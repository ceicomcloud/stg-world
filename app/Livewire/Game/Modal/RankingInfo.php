<?php

namespace App\Livewire\Game\Modal;

use App\Models\User;
use App\Models\User\UserStat;
use App\Models\User\UserRelation;
use Illuminate\Support\Facades\Auth;
use App\Services\PrivateMessageService;
use App\Services\UserPointsService;
use LivewireUI\Modal\ModalComponent;

class RankingInfo extends ModalComponent
{
    public $userId;
    public $userName;
    public $userRank;
    public $userData = [];
    protected $userStat;
    protected $userPointsService;
    public $relationStatus = 'none'; // none | pending | accepted
    public $canRequestPact = true;
    
    public function boot(UserPointsService $userPointsService)
    {
        $this->userPointsService = $userPointsService;
    }

    public function mount($userId = null)
    {
        $this->userId = $userId;
        
        if ($this->userId) {
            $this->loadUserData();
        }
    }

    public function loadUserData()
    {
        // Récupérer l'utilisateur
        $user = User::with('userStat')->find($this->userId);

        if (!$user) {
            $this->userName = 'Utilisateur inconnu';
            $this->userRank = 0;
            return;
        }

        $this->userName = $user->name;
        $this->userStat = $user->userStat;
        
        // Récupérer le rang de l'utilisateur
        $userRanking = $this->userPointsService->getUserRanking($this->userId, 'total');
        $this->userRank = $userRanking['rank'] ?? 0;
        
        // Déterminer si le joueur masque le détail de ses points
        $hideBreakdown = (bool) ($user->hide_points_breakdown ?? false);

        // Préparer les données de l'utilisateur
        $this->userData = [
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'rank' => $this->userRank,
            'total_points' => $this->userStat->total_points ?? 0,
            'building_points' => $hideBreakdown ? 0 : ($this->userStat->building_points ?? 0),
            'units_points' => $hideBreakdown ? 0 : ($this->userStat->units_points ?? 0),
            'defense_points' => $hideBreakdown ? 0 : ($this->userStat->defense_points ?? 0),
            'ship_points' => $hideBreakdown ? 0 : ($this->userStat->ship_points ?? 0),
            'technology_points' => $hideBreakdown ? 0 : ($this->userStat->technology_points ?? 0),
            'points_hidden' => $hideBreakdown,
            'earth_attack' => $this->userStat->earth_attack ?? 0,
            'earth_defense' => $this->userStat->earth_defense ?? 0,
            'spatial_attack' => $this->userStat->spatial_attack ?? 0,
            'spatial_defense' => $this->userStat->spatial_defense ?? 0,
            'earth_attack_count' => $this->userStat->earth_attack_count ?? 0,
            'earth_defense_count' => $this->userStat->earth_defense_count ?? 0,
            'spatial_attack_count' => $this->userStat->spatial_attack_count ?? 0,
            'spatial_defense_count' => $this->userStat->spatial_defense_count ?? 0,
            'earth_loser_count' => $this->userStat->earth_loser_count ?? 0,
            'spatial_loser_count' => $this->userStat->spatial_loser_count ?? 0,
            'planets_count' => $user->planets()->count(),
            'avatar_url' => $user->getUserAvatarUrl(150)
        ];

        // Ajouter un badge VIP si actif et visible
        if ($user->vip_active && ($user->vip_badge_enabled ?? true)) {
            $this->userData['badges'][] = [
                'name' => 'VIP',
                'description' => 'Membre VIP',
                'icon' => 'crown',
                'color' => '#ffd700',
                'earned_at' => $user->vip_until ? \Carbon\Carbon::parse($user->vip_until) : \Carbon\Carbon::now()
            ];
        }

        // Mettre à jour le statut de relation (pacte)
        $this->updateRelationStatus();
    }

    // Avatar géré via User::getUserAvatarUrl()

    protected function updateRelationStatus(): void
    {
        $currentUserId = Auth::id();
        if (!$currentUserId || !$this->userId || $this->userId == $currentUserId) {
            $this->relationStatus = 'none';
            $this->canRequestPact = false;
            return;
        }

        $existing = UserRelation::between($currentUserId, $this->userId)->first();
        if (!$existing) {
            $this->relationStatus = 'none';
            $this->canRequestPact = true;
            return;
        }

        $this->relationStatus = $existing->status;
        $this->canRequestPact = $existing->status === UserRelation::STATUS_REJECTED;
    }
    
    public function getRankIcon()
    {
        if ($this->userRank <= 3) {
            return match($this->userRank) {
                1 => 'crown',
                2 => 'medal',
                3 => 'award',
                default => 'trophy'
            };
        }
        return 'user';
    }
    
    public function getRankColor()
    {
        if ($this->userRank <= 3) {
            return match($this->userRank) {
                1 => 'gold',
                2 => 'silver',
                3 => 'bronze',
                default => 'regular'
            };
        }
        return 'regular';
    }
    
    public function getPointsPercentage($points, $totalPoints)
    {
        if ($totalPoints == 0) return 0;
        return round(($points / $totalPoints) * 100, 1);
    }

    public function render()
    {
        return view('livewire.game.modal.ranking-info');
    }

    public function sendPactRequest(PrivateMessageService $pm)
    {
        $currentUserId = Auth::id();
        if (!$this->userId || $this->userId == $currentUserId) {
            return;
        }

        // Vérifier existence d'une relation dans les deux sens
        $existing = UserRelation::between($currentUserId, $this->userId)->first();
        if ($existing) {
            if ($existing->status === UserRelation::STATUS_ACCEPTED) {
                $this->dispatch('toast:error', [
                    'title' => 'Déjà en pacte',
                    'text' => 'Vous avez déjà un pacte actif avec ce joueur.'
                ]);
                return;
            }
            if ($existing->status === UserRelation::STATUS_PENDING) {
                $this->dispatch('toast:error', [
                    'title' => 'Demande en attente',
                    'text' => 'Une demande de pacte existe déjà entre vous.'
                ]);
                return;
            }
        }

        // Créer la demande
        $relation = UserRelation::create([
            'requester_id' => $currentUserId,
            'receiver_id' => $this->userId,
            'status' => UserRelation::STATUS_PENDING
        ]);

        // Envoyer un MP au destinataire
        $receiver = User::find($this->userId);
        $requester = User::find($currentUserId);
        if ($receiver && $requester) {
            $pm->createSystemNotification(
                $receiver,
                'Demande de pacte',
                "🤝 Vous avez reçu une demande de pacte de <strong>{$requester->name}</strong>. Rendez-vous dans \"Relations\" pour l'accepter ou la refuser."
            );
        }

        $this->dispatch('toast:success', [
            'title' => 'Demande envoyée',
            'text' => 'Votre demande de pacte a été envoyée.'
        ]);
    }
}