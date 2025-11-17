<?php

namespace App\Livewire\Game\Modal;

use App\Models\Alliance\Alliance;
use App\Models\Alliance\AllianceMember;
use App\Models\Alliance\AllianceWar;
use Illuminate\Support\Facades\Auth;
use LivewireUI\Modal\ModalComponent;

class AllianceInfo extends ModalComponent
{
    public $allianceId;
    public $allianceName;
    public $allianceData = [];
    public $isUserMember = false;
    
    public function mount($allianceId = null)
    {
        $this->allianceId = $allianceId;
        
        if ($this->allianceId) {
            $this->loadAllianceData();
        }
    }

    public function loadAllianceData()
    {
        // Récupérer l'alliance avec ses relations
        $alliance = Alliance::with([
            'leader',
            'members.user',
            'members.rank',
            'ranks',
            'attackerWars' => function($query) {
                $query->where('status', 'active');
            },
            'defenderWars' => function($query) {
                $query->where('status', 'active');
            }
        ])->find($this->allianceId);

        if (!$alliance) {
            $this->allianceName = 'Alliance inconnue';
            return;
        }

        $this->allianceName = $alliance->name;
        
        // Calculer les statistiques de l'alliance
        $totalMembers = $alliance->members->count();
        $totalPoints = $alliance->members->sum(function($member) {
            return $member->user->userStat->total_points ?? 0;
        });
        
        $averagePoints = $totalMembers > 0 ? round($totalPoints / $totalMembers) : 0;
        
        // Récupérer les guerres actives
        $activeWars = $alliance->attackerWars->merge($alliance->defenderWars);
        
        // Vérifier si l'utilisateur actuel est membre de cette alliance
        $currentUser = Auth::user();
        $this->isUserMember = $currentUser && $currentUser->alliance_id === $alliance->id;
        
        // Préparer les données de l'alliance
        $this->allianceData = [
            'name' => $alliance->name,
            'tag' => $alliance->tag,
            'logo_url' => $alliance->logo_url,
            'description' => $alliance->description,
            'external_description' => $alliance->external_description,
            'created_at' => $alliance->created_at,
            'leader' => [
                'name' => $alliance->leader->name,
                'id' => $alliance->leader->id
            ],
            'members_count' => $totalMembers,
            'max_members' => $alliance->getMaxMembers(), // Utilise la méthode qui calcule avec les technologies
            'total_points' => $totalPoints,
            'average_points' => $averagePoints,
            'recruitment_open' => $alliance->recruitment_open,
            'active_wars_count' => $activeWars->count(),
            'ranks_count' => $alliance->ranks->count(),
            'top_members' => $alliance->members
                ->sortByDesc(function($member) {
                    return $member->user->userStat->total_points ?? 0;
                })
                ->take(5)
                ->map(function($member) {
                    return [
                        'name' => $member->user->name,
                        'rank_name' => $member->rank->name ?? 'Aucun rang',
                        'points' => $member->user->userStat->total_points ?? 0
                    ];
                })
                ->values()
                ->toArray()
        ];
    }
    
    public function getAllianceIcon()
    {
        // Icône basée sur le nombre de membres
        $memberCount = $this->allianceData['members_count'] ?? 0;
        
        if ($memberCount >= 50) {
            return 'crown';
        } elseif ($memberCount >= 25) {
            return 'shield-alt';
        } elseif ($memberCount >= 10) {
            return 'users';
        } else {
            return 'user-friends';
        }
    }
    
    public function getAllianceColor()
    {
        // Couleur basée sur le nombre de membres
        $memberCount = $this->allianceData['members_count'] ?? 0;
        
        if ($memberCount >= 50) {
            return 'gold';
        } elseif ($memberCount >= 25) {
            return 'silver';
        } elseif ($memberCount >= 10) {
            return 'bronze';
        } else {
            return 'regular';
        }
    }
    
    public function getRecruitmentStatus()
    {
        return $this->allianceData['recruitment_open'] ?? false ? 'Ouvert' : 'Fermé';
    }
    
    public function getRecruitmentIcon()
    {
        return $this->allianceData['recruitment_open'] ?? false ? 'door-open' : 'door-closed';
    }
    
    public function getRecruitmentColor()
    {
        return $this->allianceData['recruitment_open'] ?? false ? 'success' : 'danger';
    }

    public function render()
    {
        return view('livewire.game.modal.alliance-info');
    }
}