<?php

namespace App\Livewire\Game;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Services\UserPointsService;
use App\Services\EventService;
use App\Models\User\UserStatEvent;
use App\Services\EngagementBandService;
use App\Models\Server\ServerConfig;
use App\Models\User;
use App\Support\Device;
use Illuminate\Support\Carbon;

#[Layout('components.layouts.game')]
class Ranking extends Component
{
    public $user;
    public $rankings = [];
    public $activeCategory = 'total';
    public $currentPage = 1;
    public $perPage = 50;
    public $totalPages = 1;
    public $userRanking = null;
    public $searchQuery = '';
    public $compareUserId = null;
    public $compareUserData = null;
    public $paginationStart = 1;
    public $paginationEnd = 1;
    public $userChangeIndicator = null;
    
    // Rôles à exclure du classement
    protected array $excludedRoles = ['bot', 'admin', 'owner'];
    
    protected $userPointsService;
    protected $bandService;
    protected $eventService;

    // Bandeau Fort/Faible (valeurs exposées au Blade)
    public $spyEnabled = false;
    public $spyPct = 0.3;
    public $spySrc = 'total_points';
    public $spyLabel = 'points totaux';
    public $spyExampleBase = null;
    public $spyMin = null;
    public $spyMax = null;

    public $atkEnabled = false;
    public $atkPct = 0.3;
    public $atkSrc = 'total_points';
    public $atkLabel = 'points totaux';
    public $atkExampleBase = null;
    public $atkMin = null;
    public $atkMax = null;

    // Infos d'événement pour l'onglet Événement
    public $eventActive = false;
    public $eventTypeLabel = null;
    public $eventDurationDays = null;
    public $rewardTypeLabel = null;
    public $baseReward = 0;
    public $pointsMultiplier = 0.0;
    public $sampleRewards = [];
    public $eventUserPoints = null;
    public $eventUserReward = null;
    public $eventUserRewardText = null;
    
    public function boot(UserPointsService $userPointsService, EngagementBandService $bandService, EventService $eventService)
    {
        $this->userPointsService = $userPointsService;
        $this->bandService = $bandService;
        $this->eventService = $eventService;
    }
    
    public function mount()
    {
        $this->user = Auth::user();
        $this->loadRankings();
        $this->loadUserRanking();
        $this->loadBandConfig();
        $this->loadEventInfo();
        $this->updatePaginationWindow();
    }
    
    public function loadRankings()
    {
        $offset = ($this->currentPage - 1) * $this->perPage;
        
        // Si une recherche est en cours, filtrer les utilisateurs par nom
        if (!empty($this->searchQuery)) {
            $this->searchUsers();
            return;
        }
        
        switch ($this->activeCategory) {
            case 'event':
                // Classement événement: basé sur UserStatEvent selon le type actif
                $this->rankings = $this->eventService->getTopUsersByActiveEvent($this->perPage, $offset);
                break;
            case 'buildings':
                $this->rankings = $this->userPointsService->getTopUsersByBuildingPoints($this->perPage, $offset);
                break;
            case 'units':
                $this->rankings = $this->userPointsService->getTopUsersByUnitsPoints($this->perPage, $offset);
                break;
            case 'defense':
                $this->rankings = $this->userPointsService->getTopUsersByDefensePoints($this->perPage, $offset);
                break;
            case 'ships':
                $this->rankings = $this->userPointsService->getTopUsersByShipPoints($this->perPage, $offset);
                break;
            case 'technology':
                $this->rankings = $this->userPointsService->getTopUsersByTechnologyPoints($this->perPage, $offset);
                break;
            case 'earth_attack':
                $this->rankings = $this->userPointsService->getTopUsersByEarthAttackPoints($this->perPage, $offset);
                break;
            case 'earth_defense':
                $this->rankings = $this->userPointsService->getTopUsersByEarthDefensePoints($this->perPage, $offset);
                break;
            case 'spatial_attack':
                $this->rankings = $this->userPointsService->getTopUsersBySpatialAttackPoints($this->perPage, $offset);
                break;
            case 'spatial_defense':
                $this->rankings = $this->userPointsService->getTopUsersBySpatialDefensePoints($this->perPage, $offset);
                break;
            case 'earth_attack_count':
                $this->rankings = $this->userPointsService->getTopUsersByEarthAttackCount($this->perPage, $offset);
                break;
            case 'earth_defense_count':
                $this->rankings = $this->userPointsService->getTopUsersByEarthDefenseCount($this->perPage, $offset);
                break;
            case 'spatial_attack_count':
                $this->rankings = $this->userPointsService->getTopUsersBySpatialAttackCount($this->perPage, $offset);
                break;
            case 'spatial_defense_count':
                $this->rankings = $this->userPointsService->getTopUsersBySpatialDefenseCount($this->perPage, $offset);
                break;
            case 'earth_loser_count':
                $this->rankings = $this->userPointsService->getTopUsersByEarthLoserCount($this->perPage, $offset);
                break;
            case 'spatial_loser_count':
                $this->rankings = $this->userPointsService->getTopUsersBySpatialLoserCount($this->perPage, $offset);
                break;
            default:
                $this->rankings = $this->userPointsService->getTopUsersByTotalPoints($this->perPage, $offset);
                break;
        }
        // Exclure les rôles indésirables et l'utilisateur bot (id=1)
        $this->rankings = collect($this->rankings)->filter(function($user) {
            $role = strtolower((string) ($user->role ?? ''));
            $isExcludedRole = in_array($role, $this->excludedRoles, true);
            $isBotId = isset($user->id) && ((int) $user->id === 1);
            return !$isExcludedRole && !$isBotId;
        })->values();

        // Calculer le nombre total de pages (en excluant le bot)
        if ($this->activeCategory === 'event') {
            $event = $this->eventService->getActiveEvent();
            if ($event) {
                $column = $this->eventService->mapTypeToColumn($event['type']);
                if ($column) {
                    $roles = $this->excludedRoles;
                    $totalUsers = UserStatEvent::whereHas('user', function($q) use ($roles) {
                            $q->where('id', '!=', 1)
                              ->whereNotIn('role', $roles);
                        })
                        ->where($column, '>', 0)
                        ->count();
                } else {
                    $totalUsers = 0;
                }
            } else {
                $totalUsers = 0;
            }
        } else {
            $roles = $this->excludedRoles;
            $totalUsers = User::where('id', '!=', 1)
                ->whereNotIn('role', $roles)
                ->count();
        }
        $this->totalPages = ceil($totalUsers / $this->perPage);
        $this->updatePaginationWindow();
        
        // Ajouter le rang à chaque utilisateur
        foreach ($this->rankings as $index => $ranking) {
            $ranking->rank = $offset + $index + 1;
        }

        // Annoter statut allié / ennemi pour le style de ligne
        foreach ($this->rankings as $ranking) {
            // Charger les relations manquantes pour disposer des stats et de l'alliance
            if (method_exists($ranking, 'loadMissing')) {
                $relations = ['userStat', 'alliance'];
                if ($this->activeCategory === 'event') {
                    $relations[] = 'userStatEvent';
                }
                $ranking->loadMissing($relations);
            }
            $ranking->isAllied = $this->isAllyWith($ranking);
            $ranking->isEnemy = $this->isEnemyWith($ranking);
            $ranking->bandClass = $this->computeBandClassForUser($ranking);
            // Indicateur d'évolution non pertinent pour l'événement (score spécifique)
            $ranking->changeIndicator = $this->activeCategory === 'event' ? null : $this->getRankingChangeIndicator($ranking);
        }
    }
    
    /**
     * Rechercher des utilisateurs par nom
     */
    public function updatedsearchQuery()
    {
        if (empty($this->searchQuery)) {
            $this->resetSearch();
            return;
        }
        
        // Rechercher les utilisateurs dont le nom contient la requête
        $roles = $this->excludedRoles;
        $this->rankings = User::with(['userStat', 'alliance'])
            ->whereHas('userStat')
            ->where('id', '!=', 1)
            ->whereNotIn('role', $roles)
            ->where('name', 'like', '%' . $this->searchQuery . '%')
            ->take($this->perPage)
            ->get();
            
        // Calculer le rang pour chaque utilisateur trouvé
        foreach ($this->rankings as $index => $user) {
            $userRanking = $this->userPointsService->getUserRanking($user->id, $this->activeCategory);
            $user->rank = $userRanking['rank'] ?? 0;
        }
        
        // Trier les résultats par rang
        $this->rankings = $this->rankings->sortBy('rank')->values();
        
        // Mettre à jour la pagination
        $this->totalPages = 1;
        $this->currentPage = 1;
    }
    
    /**
     * Réinitialiser la recherche
     */
    public function resetSearch()
    {
        $this->searchQuery = '';
        $this->currentPage = 1;
        $this->loadRankings();
    }
    
    public function loadUserRanking()
    {
        if (!$this->user) return;

        if ($this->activeCategory === 'event') {
            $event = $this->eventService->getActiveEvent();
            if (!$event) {
                $this->userRanking = null;
                $this->userChangeIndicator = null;
                return;
            }
            $column = $this->eventService->mapTypeToColumn($event['type']);
            if (!$column) {
                $this->userRanking = null;
                $this->userChangeIndicator = null;
                return;
            }
            // Obtenir le score et le rang de l'utilisateur pour l'événement
            $entry = UserStatEvent::where('user_id', $this->user->id)->first();
            $score = $entry ? (int) ($entry->{$column} ?? 0) : 0;
            if ($score <= 0) {
                $this->userRanking = ['rank' => 0, 'points' => 0];
            } else {
                $higher = UserStatEvent::where($column, '>', $score)->count();
                $this->userRanking = ['rank' => $higher + 1, 'points' => $score];
            }
            $this->userChangeIndicator = null;
            return;
        }

        $this->userRanking = $this->userPointsService->getUserRanking($this->user->id, $this->activeCategory);
        $this->userChangeIndicator = $this->getRankingChangeIndicator($this->user);
    }
    
    public function switchCategory($category)
    {
        $this->activeCategory = $category;
        $this->currentPage = 1;
        $this->loadRankings();
        $this->loadUserRanking();
        // La configuration de bande dépend de la source (server config), pas de l'onglet.
        // On la recharge quand même pour mettre à jour les bornes si les points de l'utilisateur ont changé.
        $this->loadBandConfig();
        $this->loadEventInfo();
    }
    
    public function goToPage($page)
    {
        if ($page >= 1 && $page <= $this->totalPages) {
            $this->currentPage = $page;
            $this->loadRankings();
            $this->updatePaginationWindow();
        }
    }
    
    public function previousPage()
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
            $this->loadRankings();
            $this->updatePaginationWindow();
        }
    }
    
    public function nextPage()
    {
        if ($this->currentPage < $this->totalPages) {
            $this->currentPage++;
            $this->loadRankings();
            $this->updatePaginationWindow();
        }
    }
    
    public function getPointsForCategory($userStat, $category)
    {
        switch ($category) {
            case 'event':
                // Appelé uniquement pour compatibilité mais non utilisé pour l'événement (utiliser getEventPointsForUser)
                return 0;
            case 'buildings':
                return number_format($userStat->building_points ?? 0);
            case 'units':
                return number_format($userStat->units_points ?? 0);
            case 'defense':
                return number_format($userStat->defense_points ?? 0);
            case 'ships':
                return number_format($userStat->ship_points ?? 0);
            case 'technology':
                return number_format($userStat->technology_points ?? 0);
            case 'earth_attack':
                return number_format($userStat->earth_attack ?? 0);
            case 'earth_defense':
                return number_format($userStat->earth_defense ?? 0);
            case 'spatial_attack':
                return number_format($userStat->spatial_attack ?? 0);
            case 'spatial_defense':
                return number_format($userStat->spatial_defense ?? 0);
            case 'earth_attack_count':
                return number_format($userStat->earth_attack_count ?? 0);
            case 'earth_defense_count':
                return number_format($userStat->earth_defense_count ?? 0);
            case 'spatial_attack_count':
                return number_format($userStat->spatial_attack_count ?? 0);
            case 'spatial_defense_count':
                return number_format($userStat->spatial_defense_count ?? 0);
            case 'earth_loser_count':
                return number_format($userStat->earth_loser_count ?? 0);
            case 'spatial_loser_count':
                return number_format($userStat->spatial_loser_count ?? 0);
            default:
                return number_format($userStat->total_points ?? 0);
        }
    }

    /**
     * Points pour l'utilisateur selon l'événement actif
     */
    public function getEventPointsForUser($user): string
    {
        if (!$user) return '0';
        $event = $this->eventService->getActiveEvent();
        if (!$event) return '0';
        $column = $this->eventService->mapTypeToColumn($event['type']);
        if (!$column) return '0';

        // Assurer la relation disponible
        if (!isset($user->userStatEvent)) {
            $user->loadMissing('userStatEvent');
        }
        $score = (int) ($user->userStatEvent->{$column} ?? 0);
        return number_format($score);
    }
    
    public function getCategoryLabel($category)
    {
        $labels = [
            'total' => 'Total',
            'event' => 'Événement',
            'buildings' => 'Bâtiments',
            'units' => 'Unités',
            'defense' => 'Défenses',
            'ships' => 'Vaisseaux',
            'technology' => 'Technologies',
            'earth_attack' => 'Attaque Terrestre',
            'earth_defense' => 'Défense Terrestre',
            'spatial_attack' => 'Attaque Spatiale',
            'spatial_defense' => 'Défense Spatiale',
            'earth_attack_count' => 'Victoires Attaque Terrestre',
            'earth_defense_count' => 'Victoires Défense Terrestre',
            'spatial_attack_count' => 'Victoires Attaque Spatiale',
            'spatial_defense_count' => 'Victoires Défense Spatiale',
            'earth_loser_count' => 'Défaites Terrestre',
            'spatial_loser_count' => 'Défaites Spatiale'
        ];
        
        return $labels[$category] ?? 'Total';
    }
    
    public function openUserProfile($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $userName = $user->name;
            $this->dispatch('openModal', component: 'game.modal.ranking-info', arguments: [
                'title' => 'Profil de ' . $userName,
                'userId' => $userId,
            ]);
        }
    }

    public function openAllianceProfile($allianceId)
    {
        $alliance = \App\Models\Alliance\Alliance::find($allianceId);
        if ($alliance) {
            $allianceName = $alliance->name . ' [' . $alliance->tag . ']';
            $this->dispatch('openModal', component: 'game.modal.alliance-info', arguments: [
                'title' => 'Alliance ' . $allianceName,
                'allianceId' => $allianceId,
            ]);
        }
    }

    /**
     * Get ranking change indicator for a user
     */
    public function getRankingChangeIndicator($user)
    {
        if (!$user->userStat) {
            return [
                'icon' => '',
                'class' => '',
                'text' => '',
                'change' => 0
            ];
        }
        
        return $this->userPointsService->getRankingChangeIndicator($user->userStat);
    }

    /**
     * Sélectionner un joueur pour la comparaison
     */
    public function selectUserForComparison($userId)
    {
        $this->compareUserId = $userId;
        $this->loadCompareUserData();
    }
    
    /**
     * Charger les données du joueur à comparer
     */
    public function loadCompareUserData()
    {
        if (!$this->compareUserId) {
            $this->compareUserData = null;
            return;
        }
        
        $user = User::with('userStat')->find($this->compareUserId);
        if (!$user || !$user->userStat) {
            $this->compareUserData = null;
            return;
        }
        
        // Récupérer le rang de l'utilisateur
        $userRanking = $this->userPointsService->getUserRanking($this->compareUserId, 'total');
        
        // Préparer les données de l'utilisateur pour la comparaison
        $this->compareUserData = [
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
        ];
    }
    
    /**
     * Annuler la comparaison
     */
    public function cancelComparison()
    {
        $this->compareUserId = null;
        $this->compareUserData = null;
    }
    
    /**
     * Comparer le joueur actuel avec un autre joueur dans une modal
     */
    public function compareUsers($userId1, $userId2)
    {
        $this->dispatch('openModal', component: 'game.modal.ranking-compare', arguments: [
            'title' => 'Comparaison de joueurs',
            'userId1' => $userId1,
            'userId2' => $userId2,
        ]);
    }
    
    public function render()
    {
        return view('livewire.game.ranking');
    }

    protected function updatePaginationWindow(): void
    {
        $this->paginationStart = max(1, $this->currentPage - 2);
        $this->paginationEnd = min($this->totalPages, $this->currentPage + 2);
    }

    /**
     * Déterminer si un utilisateur est allié
     */
    protected function isAllyWith($otherUser): bool
    {
        if (!$this->user || !$otherUser || $this->user->id === $otherUser->id) return false;
        // Même alliance
        if ($this->user->alliance_id && $otherUser->alliance_id && $this->user->alliance_id === $otherUser->alliance_id) {
            return true;
        }
        // Relation acceptée
        $relation = \App\Models\User\UserRelation::findBetween($this->user->id, $otherUser->id);
        return $relation && $relation->status === \App\Models\User\UserRelation::STATUS_ACCEPTED;
    }

    /**
     * Déterminer si un utilisateur est ennemi (guerre d'alliance active)
     */
    protected function isEnemyWith($otherUser): bool
    {
        if (!$this->user || !$otherUser || !$this->user->alliance_id || !$otherUser->alliance_id) return false;
        return \App\Models\Alliance\AllianceWar::where('status', \App\Models\Alliance\AllianceWar::STATUS_ACTIVE)
            ->where(function($q) use ($otherUser) {
                $q->where('attacker_alliance_id', $this->user->alliance_id)
                  ->where('defender_alliance_id', $otherUser->alliance_id);
            })
            ->orWhere(function($q) use ($otherUser) {
                $q->where('attacker_alliance_id', $otherUser->alliance_id)
                  ->where('defender_alliance_id', $this->user->alliance_id);
            })
            ->exists();
    }

    /**
     * Charger la configuration bande Fort/Faible et calculer les exemples basés sur l'utilisateur.
     */
    protected function loadBandConfig(): void
    {
        // Espionnage
        $this->spyEnabled = (bool) ServerConfig::get('spy_band_enabled');
        $this->spyPct = (float) ServerConfig::get('spy_band_percentage', 0.3);
        $this->spySrc = ServerConfig::get('spy_band_points_source', 'total_points');
        $this->spyLabel = $this->bandService->getSourceLabel($this->spySrc);
        $this->spyExampleBase = $this->spyEnabled && $this->user ? (int) $this->bandService->getUserPoints($this->user, $this->spySrc) : null;
        if (!is_null($this->spyExampleBase)) {
            $this->spyMin = (int) floor($this->spyExampleBase * (1 - $this->spyPct));
            $this->spyMax = (int) ceil($this->spyExampleBase * (1 + $this->spyPct));
        } else {
            $this->spyMin = $this->spyMax = null;
        }

        // Attaque
        $this->atkEnabled = (bool) ServerConfig::get('attack_band_enabled', $this->spyEnabled);
        $this->atkPct = (float) ServerConfig::get('attack_band_percentage', $this->spyPct);
        $this->atkSrc = ServerConfig::get('attack_band_points_source', $this->spySrc);
        $this->atkLabel = $this->bandService->getSourceLabel($this->atkSrc);
        $this->atkExampleBase = $this->atkEnabled && $this->user ? (int) $this->bandService->getUserPoints($this->user, $this->atkSrc) : null;
        if (!is_null($this->atkExampleBase)) {
            $this->atkMin = (int) floor($this->atkExampleBase * (1 - $this->atkPct));
            $this->atkMax = (int) ceil($this->atkExampleBase * (1 + $this->atkPct));
        } else {
            $this->atkMin = $this->atkMax = null;
        }
    }

    /**
     * Déterminer la classe de bande (fort/faible) pour un utilisateur au regard des limites d'attaque.
     */
    protected function computeBandClassForUser($targetUser): ?string
    {
        if (!$this->atkEnabled || is_null($this->atkMin) || is_null($this->atkMax)) return null;
        if (!$targetUser) return null;

        $targetPoints = (int) $this->bandService->getUserPoints($targetUser, $this->atkSrc);

        if ($targetPoints > $this->atkMax) {
            return 'band-strong'; // trop fort -> rouge
        }
        if ($targetPoints < $this->atkMin && !$this->isAllyWith($targetUser)) {
            return 'band-weak'; // trop faible et non allié -> vert
        }
        return null;
    }

    /**
     * Charger les informations de l'événement actif pour affichage.
     */
    protected function loadEventInfo(): void
    {
        $event = $this->eventService->getActiveEvent();
        $this->eventActive = (bool) $event;
        if (!$event) {
            $this->eventTypeLabel = null;
            $this->eventDurationDays = null;
            $this->rewardTypeLabel = null;
            $this->baseReward = 0;
            $this->pointsMultiplier = 0.0;
            $this->sampleRewards = [];
            return;
        }

        // Libellé du type
        $typeMap = [
            'attaque' => 'Attaques',
            'exploration' => 'Explorations',
            'extraction' => 'Extractions',
            'pillage' => 'Pillage',
            'construction' => 'Construction',
        ];
        $this->eventTypeLabel = $typeMap[$event['type']] ?? ucfirst((string) $event['type']);

        // Durée en jours (si dates présentes)
        $this->eventDurationDays = null;
        try {
            if (!empty($event['start_at']) && !empty($event['end_at'])) {
                $start = Carbon::parse($event['start_at']);
                $end = Carbon::parse($event['end_at']);
                $diff = $start->diffInDays($end);
                $this->eventDurationDays = max(1, (int) $diff);
            }
        } catch (\Throwable $e) {
            $this->eventDurationDays = null;
        }

        // Récompense et paramètres
        $this->rewardTypeLabel = ($event['reward_type'] ?? 'resource') === 'gold' ? 'Or' : 'Ressources';
        $this->baseReward = (int) ($event['base_reward'] ?? 0);
        $this->pointsMultiplier = (float) ($event['points_multiplier'] ?? 0.0);

        // Exemples de récompense (Top 10, valeurs typiques)
        $examples = [1000, 10000, 100000];
        $this->sampleRewards = collect($examples)->map(function($pts) {
            $reward = (int) ($this->baseReward + $pts * $this->pointsMultiplier);
            $unit = $this->rewardTypeLabel;
            return [
                'points' => $pts,
                'reward' => $reward,
                'reward_text' => number_format($reward) . ' ' . $unit,
            ];
        })->toArray();

        // Gain estimé de l'utilisateur en fonction de ses points actuels pour l'événement
        $this->eventUserPoints = null;
        $this->eventUserReward = null;
        $this->eventUserRewardText = null;
        $column = $this->eventService->mapTypeToColumn($event['type'] ?? null);
        if ($this->user && $column) {
            $entry = UserStatEvent::where('user_id', $this->user->id)->first();
            $points = $entry ? (int) ($entry->{$column} ?? 0) : 0;
            $this->eventUserPoints = $points;
            if ($points > 0) {
                $reward = (int) ($this->baseReward + $points * $this->pointsMultiplier);
                $this->eventUserReward = $reward;
                $this->eventUserRewardText = number_format($reward) . ' ' . $this->rewardTypeLabel;
            }
        }
    }
}