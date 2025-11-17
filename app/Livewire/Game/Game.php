<?php

namespace App\Livewire\Game;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetResource;
use App\Models\Planet\PlanetMission;
use App\Models\Other\Queue;
use App\Services\BadgeService;
use App\Support\Device;
use App\Services\GameDataService;
use App\Services\EventService;
use App\Models\User\UserDailyReward;
use App\Models\Template\TemplateResource;

#[Layout('components.layouts.game')]
class Game extends Component
{
    public $user;
    public $currentPlanet;
    public $planets;
    public $queues;
    public $missions;
    public $recentBadges;
    public $upcomingBadges;
    // Server event state
    public ?array $activeServerEvent = null;
    public bool $eventActive = false;
    // Modal de confirmation de rappel de mission
    public bool $showRecallModal = false;
    public ?int $missionToRecallId = null;
    // Daily Reward UI state
    public $dailyReward; // UserDailyReward instance or array
    public bool $dailyRewardClaimable = false; // show claim button if true
    public array $dailyRewardPreview = []; // amounts previewed per resource & gold for today
    public int $dailyRewardDay = 1; // 1..7
    public array $dailyRewardSchedule = []; // 1..7 preview amounts
    
    protected $listeners = [
        'queuesUpdated' => 'loadQueues',
        'missionsUpdated' => 'loadMissions',
    ];

    public function mount(BadgeService $badgeService, GameDataService $gameData, EventService $events)
    {
        $this->user = Auth::user();
        
        // Vérifier que l'utilisateur a une planète principale
        if (!$this->user->main_planet_id) {
            return redirect()->route('dashboard');
        }
        
        // Charger la planète actuelle en utilisant la relation
        $this->currentPlanet = $this->user->getActualPlanet();
        if ($this->currentPlanet) {
            $this->currentPlanet->load(['templatePlanet', 'resources']);
        }
            
        // Charger toutes les planètes du joueur via service (cache)
        $this->planets = $gameData->getUserPlanetsWithResources($this->user->id);
                    
        // Charger les queues en cours
        $this->loadQueues($gameData);
        
        // Charger les missions en cours
        $this->loadMissions($gameData);
        
        // Charger les badges récents et à venir
        $this->loadBadges($badgeService, $gameData);

        // Daily reward: init state
        $this->initDailyRewardState();

        // Server-wide event state
        $event = $events->getActiveEvent();
        $this->activeServerEvent = $event;
        $this->eventActive = !empty($event);
    }
    
    public function loadBadges(BadgeService $badgeService, GameDataService $gameData = null)
    {
        $gameData = $gameData ?? app(GameDataService::class);
        // Récupérer les badges récents (3 derniers) via service (cache)
        $this->recentBadges = $gameData->getRecentBadges($this->user->id, 3);
            
        // Récupérer les badges à venir (3 prochains)
        $this->upcomingBadges = $badgeService->getUpcomingBadges($this->user, 3);
    }

    /**
     * Initialiser et rafraîchir l'état des récompenses quotidiennes.
     */
    private function initDailyRewardState(): void
    {
        if (!$this->user || !$this->currentPlanet) {
            $this->dailyRewardClaimable = false;
            return;
        }

        $udr = UserDailyReward::firstOrCreate(
            ['user_id' => $this->user->id],
            ['current_streak' => 0, 'last_seen_at' => now(), 'last_claim_at' => null]
        );

        // Reset du streak si une journée entière a été manquée
        if ($udr->last_seen_at) {
            $daysSinceSeen = $udr->last_seen_at->startOfDay()->diffInDays(now()->startOfDay());
            if ($daysSinceSeen > 1) {
                $udr->current_streak = 0;
            }
        }
        $udr->last_seen_at = now();
        $udr->save();

        // Si déjà claim aujourd'hui, pas d'affichage
        if ($udr->last_claim_at && $udr->last_claim_at->isToday()) {
            $this->dailyRewardClaimable = false;
            $this->dailyRewardDay = min(max((int) $udr->current_streak, 1), 7);
            $this->dailyRewardPreview = [];
        } else {
            $this->dailyRewardDay = min(max(((int) $udr->current_streak) + 1, 1), 7);
            $this->dailyRewardClaimable = true;
            $this->dailyRewardPreview = $this->calculateDailyRewardForDay($this->dailyRewardDay, $this->currentPlanet);
        }

        $this->dailyReward = $udr;

        // Construire le planning 1..7 pour affichage
        $this->dailyRewardSchedule = [];
        for ($i = 1; $i <= 7; $i++) {
            $this->dailyRewardSchedule[$i] = $this->calculateDailyRewardForDay($i, $this->currentPlanet);
        }
    }

    /**
     * Calculer les montants de récompense pour le jour donné, adaptés à la production de la planète.
     * Retourne: [ 'metal' => int, 'crystal' => int, 'deuterium' => int, 'gold' => int ]
     */
    private function calculateDailyRewardForDay(int $day, \App\Models\Planet\Planet $planet): array
    {
        $day = max(1, min(7, $day));

        // Échelle de pourcentage basée sur le jour: 10%, 15%, 20%, 25%, 30%, 35%, 40% de la production journalière
        $percentByDay = [1 => 0.10, 2 => 0.15, 3 => 0.20, 4 => 0.25, 5 => 0.30, 6 => 0.35, 7 => 0.40];
        $pct = $percentByDay[$day] ?? 0.10;

        // Petite récompense d'or croissante: 3,4,5,6,7,8,10
        $goldByDay = [1 => 3, 2 => 4, 3 => 5, 4 => 6, 5 => 7, 6 => 8, 7 => 10];
        $gold = $goldByDay[$day] ?? 3;

        $resources = ['metal', 'crystal', 'deuterium'];
        $amounts = ['metal' => 0, 'crystal' => 0, 'deuterium' => 0];

        foreach ($planet->resources as $pr) {
            $rName = strtolower($pr->resource->name);
            if (in_array($rName, $resources, true)) {
                $perHour = (int) floor($pr->getCurrentProductionPerHour());
                $daily = $perHour * 24;
                $reward = (int) floor($daily * $pct);
                $amounts[$rName] = max(0, $reward);
            }
        }

        return array_merge($amounts, ['gold' => $gold]);
    }

    /**
     * Action: récupérer la récompense du jour.
     */
    public function claimDailyReward(): void
    {
        if (!$this->user || !$this->currentPlanet) {
            return;
        }

        $udr = UserDailyReward::where('user_id', $this->user->id)->first();
        if (!$udr) {
            $this->initDailyRewardState();
            $udr = $this->dailyReward instanceof UserDailyReward ? $this->dailyReward : null;
        }
        if (!$udr) {
            return;
        }

        // Si déjà pris aujourd'hui, ignorer
        if ($udr->last_claim_at && $udr->last_claim_at->isToday()) {
            return;
        }

        $day = min(max(((int) $udr->current_streak) + 1, 1), 7);
        $amounts = $this->calculateDailyRewardForDay($day, $this->currentPlanet);

        // Créditer les ressources sur la planète avec respect du stockage
        $credited = [];
        foreach ($this->currentPlanet->resources as $pr) {
            $rName = strtolower($pr->resource->name);
            if (isset($amounts[$rName]) && $amounts[$rName] > 0) {
                $added = (int) $pr->addResources((int) $amounts[$rName]);
                $credited[$rName] = $added;
            }
        }

        // Créditer l'or utilisateur (solde)
        $gold = (int) ($amounts['gold'] ?? 0);
        if ($gold > 0) {
            $this->user->gold_balance = (int) (($this->user->gold_balance ?? 0) + $gold);
            $this->user->save();
        }

        // Mettre à jour le streak (cycle après 7)
        $udr->current_streak = ($day >= 7) ? 0 : $day;
        $udr->last_claim_at = now();
        $udr->last_seen_at = now();
        $udr->save();

        $this->dailyReward = $udr;
        $this->dailyRewardClaimable = false;
        $this->dailyRewardPreview = [];
        $this->dailyRewardDay = max(1, (int) $udr->current_streak);

        // Recalculer le planning après claim (au cas où la prod ait bougé)
        $this->dailyRewardSchedule = [];
        for ($i = 1; $i <= 7; $i++) {
            $this->dailyRewardSchedule[$i] = $this->calculateDailyRewardForDay($i, $this->currentPlanet);
        }

        $this->dispatch('toast:success', [
            'title' => 'Récompense quotidienne',
            'text' => 'Vous avez reçu ' .
                (isset($credited['metal']) ? (number_format($credited['metal']).' métal, ') : '') .
                (isset($credited['crystal']) ? (number_format($credited['crystal']).' cristal, ') : '') .
                (isset($credited['deuterium']) ? (number_format($credited['deuterium']).' deutérium, ') : '') .
                (number_format($gold) . ' or'),
        ]);
    }
    
    public function loadQueues(GameDataService $gameData = null)
    {
        $gameData = $gameData ?? app(GameDataService::class);
        if ($this->currentPlanet) {
            // Regrouper et mettre en cache les files par type
            $this->queues = $gameData->getPlanetQueuesGrouped($this->currentPlanet->id);
        }
    }
    
    public function switchPlanet($planetId)
    {
        // Utiliser la relation planets pour vérifier que la planète appartient à l'utilisateur
        $planet = $this->user->planets()->where('id', $planetId)->first();
            
        if ($planet) {
            // Mettre à jour la planète actuelle de l'utilisateur
            $this->user->update(['actual_planet_id' => $planet->id]);
            
            // Recharger les données
            $this->currentPlanet = $planet->load(['templatePlanet', 'resources']);
            $this->loadQueues(app(GameDataService::class));
            $this->loadMissions(app(GameDataService::class));
        }
    }
    
    public function loadMissions(GameDataService $gameData = null)
    {
        $gameData = $gameData ?? app(GameDataService::class);
        // Charger toutes les missions de l'utilisateur via service (cache)
        $this->missions = $gameData->getUserActiveMissions($this->user->id);
    }

    public function forceMissionReturn($missionId)
    {
        $mission = PlanetMission::where('id', $missionId)
            ->where('user_id', $this->user->id)
            ->where('status', 'traveling')
            ->first();
            
        if ($mission) {
            // Récupérer la vitesse du vaisseau (utiliser la valeur par défaut si non disponible)
            $shipSpeed = 100; // Valeur par défaut
            
            // Si la mission contient des informations sur les vaisseaux, utiliser leur vitesse
            if (isset($mission->ships) && !empty($mission->ships)) {
                // Récupérer le premier vaisseau de la mission pour sa vitesse
                $shipId = array_key_first($mission->ships);
                $ship = \App\Models\Template\TemplateBuild::find($shipId);
                if ($ship) {
                    $shipSpeed = $ship->speed;
                }
            }
            
            // Calculer le temps de retour basé sur la durée originale et la vitesse du vaisseau
            $returnDuration = PlanetMission::calculateMissionDuration(
                $mission->fromPlanet->system,
                $mission->to_system,
                $shipSpeed,
                $this->user->id // Ajouter l'ID de l'utilisateur pour appliquer les bonus technologiques
            );
            
            $mission->update([
                'status' => 'returning',
                'return_time' => now()->addMinutes($returnDuration),
                'result' => ['forced_return' => true, 'message' => 'Mission annulée par le joueur']
            ]);
            
            // Invalider et recharger les missions via le service
            $gameData = app(GameDataService::class);
            $gameData->forgetUserMissions($this->user->id);
            $this->loadMissions($gameData);
            
            $this->dispatch('toast:success', [
                'title' => 'Succès!',
                'text' => 'Mission rappelée avec succès!'
            ]);
        }
    }

    /**
     * Ouvre la modale de confirmation pour le rappel d’une mission.
     */
    public function confirmMissionRecall(int $missionId): void
    {
        $this->missionToRecallId = $missionId;
        $this->showRecallModal = true;
    }

    /**
     * Confirme et exécute le rappel de mission, puis ferme la modale.
     */
    public function performMissionRecall(): void
    {
        if ($this->missionToRecallId) {
            $this->forceMissionReturn($this->missionToRecallId);
        }
        $this->dismissModals();
    }

    /**
     * Ferme les modales et réinitialise l’état.
     */
    public function dismissModals(): void
    {
        $this->showRecallModal = false;
        $this->missionToRecallId = null;
    }
    
    public function goToDashboard()
    {
        return redirect()->route('dashboard');
    }
    
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        
        return redirect()->route('login');
    }
    
    public function openMissionInfo($missionId)
    {
        // Vérifier que la mission appartient à l'utilisateur
        $mission = PlanetMission::where('id', $missionId)
            ->where('user_id', $this->user->id)
            ->first();
            
        if ($mission) {
            $this->dispatch('openModal', component: 'game.modal.mission-info', arguments: [
                'title' => 'Détails de la mission',
                'missionId' => $missionId,
            ]);
        }
    }
    
    public function render(BadgeService $badgeService)
    {
        // Garder un rendu léger: éviter les requêtes lourdes ici.
        // Les badges sont déjà chargés en mount et quand nécessaire.
        return view('livewire.game.game');
    }

    // Compléter le chargement des ressources de la planète actuelle si besoin
    public function loadResources(): void
    {
        if ($this->currentPlanet) {
            $this->currentPlanet->load('resources');
        }
    }
}