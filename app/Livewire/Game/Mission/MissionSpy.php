<?php

namespace App\Livewire\Game\Mission;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetShip;
use App\Models\Planet\PlanetMission;
use App\Models\Planet\PlanetResource;
use App\Services\PrivateMessageService;
use App\Models\User\UserTechnology;
use App\Models\Template\TemplateBuild;
use App\Services\DailyQuestService;
use App\Services\UserCustomizationService;
use App\Services\EngagementBandService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Traits\LogsUserActions;
use App\Models\Server\ServerConfig;

#[Layout('components.layouts.game')]
class MissionSpy extends Component
{
    use LogsUserActions;
    public $planetId;
    public $planet;
    public $availableShips = [];
    
    public $targetPlanet = null;
    public $targetGalaxy;
    public $targetSystem;
    public $targetPosition;
    
    public $selectedShips = [];
    public $totalShipsSelected = 0;
    
    public $missionDuration = 0;
    public $fuelConsumption = 0;
    public $showMissionSummary = false;

    // Limitation par tranche (fort/faible) pour l'espionnage
    public $spyBandEnabled = false;
    public $spyBandPercentage = 0.0;
    public $spyBandSource = 'total_points';
    public $attackerPoints = null;
    public $targetPoints = null;
    public $bandMin = null;
    public $bandMax = null;
    public $isTargetAllowed = true;
    
    public function mount($targetPlanetId)
    {
        $this->planet = auth()->user()->getActualPlanet();
        $this->planetId = $this->planet->id;
        
        $this->targetPlanet = Planet::with('templatePlanet')->findOrFail($targetPlanetId);
        $this->targetGalaxy = $this->targetPlanet->templatePlanet->galaxy;
        $this->targetSystem = $this->targetPlanet->templatePlanet->system;
        $this->targetPosition = $this->targetPlanet->templatePlanet->position;

        if (!$this->validateEnemyOnly()) {
            return redirect()->route('game.mission.index');
        }
        
        // Vérifier que la planète cible n'appartient pas au joueur actuel
        if ($this->targetPlanet->user_id === auth()->id()) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous ne pouvez pas espionner votre propre planète.'
            ]);
            return redirect()->route('game.mission.index');
        }
        
        // Vérifier que la planète cible n'appartient pas à un membre de l'alliance du joueur
        if ($this->targetPlanet->user && auth()->user()->alliance_id && 
            $this->targetPlanet->user->alliance_id === auth()->user()->alliance_id) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous ne pouvez pas espionner un membre de votre alliance.'
            ]);
            return redirect()->route('game.mission.index');
        }
        
        // Vérifier si la planète cible est protégée par un bouclier planétaire
        if ($this->targetPlanet->isShieldProtectionActive()) {
            $this->dispatch('swal:error', [
                'title' => 'Protection planétaire',
                'text' => 'Cette planète est actuellement protégée par un bouclier planétaire. Impossible de l\'espionner.'
            ]);
            
            // Enregistrer la tentative d'espionnage bloquée dans les logs
            $this->logAction(
                'spy_blocked_by_shield',
                'mission',
                'Tentative d\'espionnage bloquée par un bouclier planétaire',
                [
                    'from_planet_id' => $this->planet->id,
                    'target_planet_id' => $this->targetPlanet->id,
                    'target_coordinates' => "[{$this->targetGalaxy}:{$this->targetSystem}:{$this->targetPosition}]"
                ],
                $this->planet->id,
                $this->targetPlanet->user_id
            );
            
            return redirect()->route('game.mission.index');
        }
        
        // Vérifier si la planète de départ est protégée par un bouclier planétaire
        if ($this->planet->isShieldProtectionActive()) {
            $this->dispatch('swal:error', [
                'title' => 'Protection planétaire',
                'text' => 'Votre planète est actuellement protégée par un bouclier planétaire. Impossible de lancer des missions d\'espionnage.'
            ]);
            return redirect()->route('game.mission.index');
        }
        
        $this->loadPlanetData();
    }
    
    public function loadPlanetData()
    {
        if (!$this->planet || $this->planet->user_id !== auth()->id()) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Planète non trouvée ou vous n\'avez pas accès à cette planète.'
            ]);
            return redirect()->route('game.mission.index');
        }
                
        // Charger les vaisseaux disponibles pour l'espionnage
        $this->loadAvailableShips();
        
        // Initialiser le tableau des vaisseaux sélectionnés
        foreach ($this->availableShips as $ship) {
            $this->selectedShips[$ship['id']] = 0;
        }

        // Calculer la bande d'espionnage autorisée
        $this->computeSpyBandLimits();
    }
    
    public function loadAvailableShips()
    {
        // Récupérer tous les vaisseaux d'espionnage disponibles sur la planète
        $planetShips = PlanetShip::with(['ship'])
            ->where('planet_id', $this->planetId)
            ->where('quantity', '>', 0)
            ->whereHas('ship', function($query) {
                $query->where('name', 'scout_quantique');
            })
            ->get();
        
        $this->availableShips = [];
        $service = app(UserCustomizationService::class);
        $user = Auth::user();

        foreach ($planetShips as $planetShip) {
            $resolved = $service->resolveBuild($user, $planetShip->ship);
            $this->availableShips[] = [
                'id' => $planetShip->id,
                'name' => $resolved['name'] ?? $planetShip->ship->label,
                'image' => $planetShip->ship->icon,
                'icon_url' => $resolved['icon_url'] ?? null,
                'attack' => $planetShip->ship->attack_power,
                'defense' => $planetShip->ship->defense_power,
                'speed' => $planetShip->ship->speed,
                'quantity' => $planetShip->quantity
            ];
        }
    }

    protected function computeSpyBandLimits(): void
    {
        $service = app(EngagementBandService::class);
        $result = $service->checkTargetAllowed(auth()->user(), $this->targetPlanet?->user, 'spy');
        $this->spyBandEnabled = (bool) ($result['enabled'] ?? false);
        $this->spyBandPercentage = (float) ($result['percentage'] ?? 0.0);
        $this->spyBandSource = (string) ($result['source'] ?? 'total_points');
        $this->attackerPoints = $result['attacker_points'] ?? null;
        $this->targetPoints = $result['target_points'] ?? null;
        $this->bandMin = $result['min'] ?? null;
        $this->bandMax = $result['max'] ?? null;
        $this->isTargetAllowed = (bool) ($result['allowed'] ?? true);
    }

    protected function resolveUserPoints($user, string $source): ?int
    {
        if (!$user || !$user->userStat) {
            return null;
        }

        switch ($source) {
            case 'earth_attack':
                return (int) ($user->userStat->earth_attack ?? 0);
            case 'spatial_attack':
                return (int) ($user->userStat->spatial_attack ?? 0);
            case 'total_points':
            default:
                return (int) ($user->userStat->total_points ?? 0);
        }
    }
    
    public function updateShipSelection()
    {
        $this->totalShipsSelected = 0;
        
        foreach ($this->selectedShips as $id => $quantity) {
            // Vérifier que la quantité est valide
            if ($quantity < 0) {
                $this->selectedShips[$id] = 0;
                $quantity = 0;
            }
            
            // Trouver le vaisseau correspondant
            $ship = collect($this->availableShips)->firstWhere('id', $id);
            
            if ($ship) {
                // Vérifier que la quantité ne dépasse pas le maximum disponible
                if ($quantity > $ship['quantity']) {
                    $this->selectedShips[$id] = $ship['quantity'];
                    $quantity = $ship['quantity'];
                }
                
                $this->totalShipsSelected += $quantity;
            }
        }
        
        // Calculer la durée de la mission
        $this->calculateMissionDuration();
    }
    
    public function setMaxShips($shipId)
    {
        $ship = collect($this->availableShips)->firstWhere('id', $shipId);
        if ($ship) {
            $this->selectedShips[$shipId] = $ship['quantity'];
            $this->updateShipSelection();
        }
    }
    
    public function setClearShips($shipId)
    {
        $this->selectedShips[$shipId] = 0;
        $this->updateShipSelection();
    }
    
    public function calculateMissionDuration()
    {
        if ($this->totalShipsSelected > 0 && $this->targetPlanet) {
            // Calculer la vitesse la plus lente avec la méthode centralisée
            $slowestSpeed = PlanetMission::calculateSpeed($this->selectedShips, $this->availableShips);
            
            // Construire un mapping par ID de template pour le calcul du carburant
            $selectedByTemplate = [];
            foreach ($this->selectedShips as $planetShipId => $quantity) {
                if ($quantity > 0) {
                    $planetShip = PlanetShip::find($planetShipId);
                    if ($planetShip) {
                        $tplId = $planetShip->ship_id;
                        $selectedByTemplate[$tplId] = ($selectedByTemplate[$tplId] ?? 0) + (int) $quantity;
                    }
                }
            }
            
            // Calculer la consommation de carburant avec la méthode centralisée (aller-retour)
            $this->fuelConsumption = PlanetMission::calculateFuelConsumption(
                $selectedByTemplate,
                $this->planet->templatePlanet->system,
                $this->targetPlanet->templatePlanet->system,
                true
            );
            
            // Utiliser la méthode statique de PlanetMission pour calculer la durée
            $this->missionDuration = PlanetMission::calculateMissionDuration(
                $this->planet->templatePlanet->system,
                $this->targetPlanet->templatePlanet->system,
                $slowestSpeed,
                auth()->id()
            );
        } else {
            $this->missionDuration = 0;
            $this->fuelConsumption = 0;
        }
    }
    
    public function showSummary()
    {
        if ($this->totalShipsSelected <= 0) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous devez sélectionner au moins un vaisseau d\'espionnage.'
            ]);
            return;
        }

        // Autoriser toute attaque si la cible est le bot (user_id = 1)
        if ($this->targetPlanet && (int) $this->targetPlanet->user_id === 1) {
            $this->showMissionSummary = true;
            return;
        }

        // Ennemis seulement
        if (!$this->validateEnemyOnly()) {
            return;
        }
        
       // Vérifier la limitation fort/faible avant l'aperçu
        $svc = app(EngagementBandService::class);
        $check = $svc->checkTargetAllowed(auth()->user(), $this->targetPlanet?->user, 'spy');
        if (($check['enabled'] ?? false) && !($check['allowed'] ?? true)) {
            $this->dispatch('swal:error', [
                'title' => 'Cible hors bande',
                'text' => "Vous pouvez espionner des cibles entre " . number_format($check['min']) . " et " . number_format($check['max']) . " " . $check['label'] . ". Cible: " . number_format($check['target_points'] ?? 0) . "."
            ]);
            return;
        }
        
        $this->showMissionSummary = true;
    }
    
    public function backToSelection()
    {
        $this->showMissionSummary = false;
    }
    
    public function launchMission()
    {
        // Ennemis seulement
        if (!$this->validateEnemyOnly()) {
            return redirect()->route('game.mission.index');
        }
        // Bloquer si trêve active pour l'espionnage
        if (ServerConfig::get('truce_enabled') && ServerConfig::get('truce_block_spy')) {
            $message = ServerConfig::get('truce_message') ?: "Trêve active: les missions d'espionnage sont temporairement désactivées.";

            $this->dispatch('swal:error', [
                'title' => 'Trêve active',
                'text' => $message
            ]);

            // Journaliser la tentative bloquée
            $this->logAction(
                'spy_blocked_by_truce',
                'mission',
                "Tentative d'espionnage bloquée par trêve",
                [
                    'from_planet_id' => $this->planet->id,
                    'target_planet_id' => $this->targetPlanet?->id,
                    'target_coordinates' => "[{$this->targetGalaxy}:{$this->targetSystem}:{$this->targetPosition}]"
                ],
                $this->planet->id,
                $this->targetPlanet?->user_id
            );

            return redirect()->route('game.mission.index');
        }
        
        // Vérifier qu'au moins un vaisseau est sélectionné
        if ($this->totalShipsSelected <= 0) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous devez sélectionner au moins un vaisseau d\'espionnage.'
            ]);
            return;
        }

        // Vérifier le plafond des flottes en vol selon le niveau du Centre de Commandement
        $allowedFlying = \App\Models\Planet\PlanetMission::getAllowedFlyingFleetsForPlanet($this->planet->id);
        $currentFlying = \App\Models\Planet\PlanetMission::countUserFlyingMissions(auth()->id());
        if ($currentFlying >= $allowedFlying) {
            $ccLevel = \App\Models\Planet\PlanetMission::getCommandCenterLevelForPlanet($this->planet->id);
            $this->dispatch('swal:error', [
                'title' => 'Trop de flottes en vol',
                'text' => "Limite actuelle: {$allowedFlying} flottes en vol (Centre de Commandement niveau {$ccLevel})."
            ]);
            return;
        }
        
        // Récupérer le vaisseau scout
        $scoutShip = PlanetShip::where('planet_id', $this->planet->id)
            ->whereHas('ship', function($query) {
                $query->where('name', 'scout_quantique');
            })
            ->first();
        
        if (!$scoutShip || $scoutShip->quantity < $this->totalShipsSelected) {
            $this->dispatch('swal:error', [
                'title' => 'Mission espionnage',
                'text' => "Vous n'avez pas assez de Scouts Quantiques."
            ]);
            return;
        }
        
        // Vérifier le niveau de technologie d'espionnage
        $espionageTech = UserTechnology::where('user_id', auth()->id())
            ->whereHas('technology', function($query) {
                $query->where('name', 'espionnage_tactique');
            })
            ->first();
        
        $espionageLevel = $espionageTech ? $espionageTech->level : 0;
        
        if ($espionageLevel < 1) {
            $this->dispatch('swal:error', [
                'title' => 'Mission espionnage',
                'text' => "Vous devez avoir au moins le niveau 1 en technologie d'espionnage."
            ]);
            return;
        }
        
        // Préparer les données des vaisseaux pour la mission
        $missionShips = [
            $scoutShip->ship_id => [
                'quantity' => $this->totalShipsSelected,
                'name' => $scoutShip->ship->name,
                'speed' => $scoutShip->ship->speed,
                'attack_power' => $scoutShip->ship->attack_power,
                'defense_power' => $scoutShip->ship->defense_power
            ]
        ];
        
        // Retirer les vaisseaux de la planète
        $scoutShip->decrement('quantity', $this->totalShipsSelected);
        
        // Créer la mission d'espionnage
        $mission = PlanetMission::create([
            'user_id' => auth()->id(),
            'from_planet_id' => $this->planet->id,
            'to_planet_id' => $this->targetPlanet->id,
            'to_galaxy' => $this->targetPlanet->templatePlanet->galaxy,
            'to_system' => $this->targetPlanet->templatePlanet->system,
            'to_position' => $this->targetPlanet->templatePlanet->position,
            'mission_type' => 'spy',
            'ships' => $missionShips,
            'departure_time' => Carbon::now(),
            'arrival_time' => Carbon::now()->addSeconds($this->missionDuration),
            'status' => 'traveling'
        ]);

        // Incrémenter la quête quotidienne pour mission d'espionnage
        $user = Auth::user();
        if ($user) {
            app(DailyQuestService::class)->incrementProgress($user, 'mission_spy');
        }
        
        // Créer un message de départ de mission
        $messageService = new PrivateMessageService();
        $messageService->createMissionDepartureMessage($mission);
        
        // Logger la mission d'espionnage
        $this->logMissionLaunched(
            'spy',
            $this->planet->id,
            $this->targetPlanet->id,
            [
                'scout_ships' => $this->totalShipsSelected,
                'coordinates' => "{$this->targetGalaxy}:{$this->targetSystem}:{$this->targetPosition}",
                'fuel_consumed' => $this->fuelConsumption,
                'mission_duration' => $this->missionDuration
            ]
        );
        
        $this->dispatch('swal:success', [
            'title' => 'Mission lancée',
            'text' => "Mission d'espionnage lancée! Arrivée dans {$this->missionDuration} minutes."
        ]);
        
        return redirect()->route('game.mission.index');
    }

    // Helpers de validation: allié/ennemi et règle ennemis seulement
    protected function isAllyWith($otherUser): bool
    {
        if (!$otherUser) return false;
        if (Auth::user()->alliance_id && $otherUser->alliance_id && Auth::user()->alliance_id === $otherUser->alliance_id) {
            return true;
        }
        $relation = \App\Models\User\UserRelation::findBetween(Auth::id(), $otherUser->id);
        return $relation && $relation->status === \App\Models\User\UserRelation::STATUS_ACCEPTED;
    }

    protected function isEnemyWith($otherUser): bool
    {
        if (!Auth::user()->alliance_id || !$otherUser || !$otherUser->alliance_id) return false;
        return \App\Models\Alliance\AllianceWar::where('status', \App\Models\Alliance\AllianceWar::STATUS_ACTIVE)
            ->where(function($q) use ($otherUser) {
                $q->where('attacker_alliance_id', Auth::user()->alliance_id)
                  ->where('defender_alliance_id', $otherUser->alliance_id);
            })
            ->orWhere(function($q) use ($otherUser) {
                $q->where('attacker_alliance_id', $otherUser->alliance_id)
                  ->where('defender_alliance_id', Auth::user()->alliance_id);
            })
            ->exists();
    }

    protected function validateEnemyOnly(): bool
    {
        $targetUser = $this->targetPlanet?->user;
        if (!$targetUser) {
            return true;
        }
        if ($targetUser->id === Auth::id()) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous ne pouvez pas espionner votre propre planète.'
            ]);
            return false;
        }
        if ($this->isAllyWith($targetUser)) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous ne pouvez pas espionner un allié.'
            ]);
            return false;
        }
        
        return true;
    }
    
    public function render()
    {
        return view('livewire.game.mission.spy', [
            'planetId' => $this->planetId,
            'showSummary' => $this->showMissionSummary,
            'availableShips' => $this->availableShips,
            'selectedShips' => $this->selectedShips,
            'totalSelectedShips' => $this->totalShipsSelected,
            'missionDuration' => $this->missionDuration,
            // Infos bande espionnage pour l'UI
            'spyBandEnabled' => $this->spyBandEnabled,
            'spyBandPercentage' => $this->spyBandPercentage,
            'spyBandSource' => $this->spyBandSource,
            'attackerPoints' => $this->attackerPoints,
            'targetPoints' => $this->targetPoints,
            'bandMin' => $this->bandMin,
            'bandMax' => $this->bandMax,
            'isTargetAllowed' => $this->isTargetAllowed,
        ]);
    }
}