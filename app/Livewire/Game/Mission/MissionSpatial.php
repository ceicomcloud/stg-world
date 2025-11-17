<?php

namespace App\Livewire\Game\Mission;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetShip;
use App\Models\Planet\PlanetMission;
use App\Models\Planet\PlanetResource;
use App\Services\PrivateMessageService;
use App\Services\DailyQuestService;
use App\Services\EngagementBandService;
use App\Traits\LogsUserActions;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Server\ServerConfig;
use App\Services\UserCustomizationService;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use App\Models\Planet\PlanetEquip;

#[Layout('components.layouts.game')]
class MissionSpatial extends Component
{
    public $planetId;
    public $planet;
    public $availableShips = [];
    
    // Informations sur la planète cible
    public $targetPlanet = null;
    public $targetGalaxy;
    public $targetSystem;
    public $targetPosition;
    
    // Sélection des vaisseaux
    public $selectedShips = [];
    public $totalShipsSelected = 0;
    
    // Informations de mission
    public $missionDuration = 0;
    public $fuelConsumption = 0;
    public $showMissionSummary = false;
    
    // Équipes pré-configurées
    public $equipTeams = [];
    public $selectedTeamId = null;
    
    public function mount($targetPlanetId)
    {
        $this->planet = auth()->user()->getActualPlanet();
        $this->planetId = $this->planet->id;

        // Vérifier si la planète d'origine est protégée
        if ($this->planet->isShieldProtectionActive()) {
            $this->dispatch('swal:error', [
                'title' => 'Protection active',
                'text' => 'Votre planète est sous protection planétaire. Vous ne pouvez pas lancer d\'attaques.'
            ]);
            return redirect()->route('game.mission.index');
        }

        if (!$this->validateEnemyOnly()) {
            return redirect()->route('game.mission.index');
        }

        $this->targetPlanet = Planet::with('templatePlanet')->findOrFail($targetPlanetId);
        $this->targetGalaxy = $this->targetPlanet->templatePlanet->galaxy;
        $this->targetSystem = $this->targetPlanet->templatePlanet->system;
        $this->targetPosition = $this->targetPlanet->templatePlanet->position;
        
        // Vérifier si la planète cible est protégée
        if ($this->targetPlanet->isShieldProtectionActive()) {
            $this->dispatch('swal:error', [
                'title' => 'Protection active',
                'text' => 'Cette planète est sous protection planétaire. Vous ne pouvez pas l\'attaquer.'
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
                
        // Charger les vaisseaux disponibles pour l'attaque
        $this->loadAvailableShips();
        
        // Initialiser le tableau des vaisseaux sélectionnés
        foreach ($this->availableShips as $ship) {
            $this->selectedShips[$ship['id']] = 0;
        }

        // Charger les équipes actives de la planète (catégorie spatiale)
        $this->loadEquipTeams();
    }

    /**
     * Recharge les informations de la planète cible depuis la base,
     * et synchronise ses coordonnées.
     */
    public function loadTargetPlanetData(): void
    {
        if (!$this->targetPlanet) {
            return;
        }

        $planet = Planet::with(['templatePlanet', 'user'])->find($this->targetPlanet->id);

        if (!$planet) {
            // Si la planète n'existe plus, on nettoie les informations cibles
            $this->targetPlanet = null;
            $this->targetGalaxy = null;
            $this->targetSystem = null;
            $this->targetPosition = null;
            return;
        }

        $this->targetPlanet = $planet;

        if ($planet->templatePlanet) {
            $this->targetGalaxy = $planet->templatePlanet->galaxy;
            $this->targetSystem = $planet->templatePlanet->system;
            $this->targetPosition = $planet->templatePlanet->position;
        }
    }
    
    public function updateShipSelection()
    {
        $this->totalShipsSelected = 0;
        
        foreach ($this->selectedShips as $shipId => $quantity) {
            // Vérifier que la quantité n'est pas négative
            if ($quantity < 0) {
                $this->selectedShips[$shipId] = 0;
                $quantity = 0;
            }
            
            // Vérifier que la quantité ne dépasse pas le disponible
            $availableShip = collect($this->availableShips)->firstWhere('id', $shipId);
            if ($availableShip && $quantity > $availableShip['quantity']) {
                $this->selectedShips[$shipId] = $availableShip['quantity'];
                $quantity = $availableShip['quantity'];
            }
            
            $this->totalShipsSelected += $quantity;
        }
        
        // Calculer la durée de la mission si des vaisseaux sont sélectionnés
        if ($this->totalShipsSelected > 0) {
            $this->calculateMissionDuration();
        } else {
            $this->missionDuration = 0;
            $this->showMissionSummary = false;
        }
    }
    
    public function setMaxShips($shipId)
    {
        $availableShip = collect($this->availableShips)->firstWhere('id', $shipId);
        if ($availableShip) {
            $this->selectedShips[$shipId] = $availableShip['quantity'];
            $this->updateShipSelection();
        }
    }

    protected function loadAvailableShips()
    {
        $planetShips = PlanetShip::with('ship')
            ->where('planet_id', $this->planetId)
            ->where('quantity', '>', 0)
            ->get();

        $svc = new UserCustomizationService();
        $user = FacadesAuth::user();

        $this->availableShips = [];
        foreach ($planetShips as $ps) {
            $resolved = $svc->resolveBuild($user, $ps->ship);
            $this->availableShips[] = [
                'id' => $ps->id,
                'template_id' => $ps->ship_id,
                'name' => $resolved['name'],
                'image' => $ps->ship->icon,
                'icon_url' => $resolved['icon_url'],
                'quantity' => $ps->quantity,
                'attack' => $ps->ship->attack_power,
                'defense' => $ps->ship->defense_power,
                'speed' => $ps->ship->speed,
            ];
        }
    }

    protected function loadEquipTeams(): void
    {
        $teams = PlanetEquip::active()
            ->byPlanet($this->planetId)
            ->spatial()
            ->orderBy('team_index')
            ->get();

        $this->equipTeams = $teams->map(function ($t) {
            return [
                'id' => $t->id,
                'label' => $t->label,
                'team_index' => $t->team_index,
                'payload_ships' => (array) (($t->payload['ships'] ?? [])),
            ];
        })->toArray();
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

    public function applySelectedTeam(): void
    {
        if (!$this->selectedTeamId) {
            $this->dispatch('toast:info', [
                'title' => 'Équipe',
                'text' => "Sélectionnez une équipe à appliquer"
            ]);
            return;
        }

        $this->applyEquipTeam((int) $this->selectedTeamId);
    }

    public function applyEquipTeam(int $teamId): void
    {
        $team = collect($this->equipTeams)->firstWhere('id', $teamId);
        if (!$team) {
            $this->dispatch('toast:error', [
                'title' => 'Équipe introuvable',
                'text' => "L'équipe sélectionnée n'existe pas ou n'est pas active."
            ]);
            return;
        }

        // Réinitialiser la sélection
        foreach ($this->availableShips as $ship) {
            $this->selectedShips[$ship['id']] = 0;
        }

        $payload = (array) ($team['payload_ships'] ?? []);

        // Appliquer les quantités en respectant la disponibilité sur la planète
        foreach ($payload as $templateShipId => $qty) {
            $available = collect($this->availableShips)->firstWhere('template_id', (int) $templateShipId);
            if ($available) {
                $max = (int) ($available['quantity'] ?? 0);
                $this->selectedShips[(int) $available['id']] = min((int) $qty, $max);
            }
        }

        // Mettre à jour les totaux
        $this->updateShipSelection();

        $this->dispatch('toast:success', [
            'title' => 'Équipe appliquée',
            'text' => "La sélection a été remplie selon l'équipe"
        ]);
    }
    
    public function setClearShips($shipId)
    {
        $this->selectedShips[$shipId] = 0;
        $this->updateShipSelection();
    }
    
    public function calculateSpeed()
    {
        return PlanetMission::calculateSpeed($this->selectedShips, $this->availableShips);
    }
    
    public function calculateFuelConsumption()
    {
        if ($this->totalShipsSelected > 0 && $this->targetPlanet) {
            return PlanetMission::calculateFuelConsumption(
                $this->selectedShips,
                $this->planet->templatePlanet->system,
                $this->targetPlanet->templatePlanet->system,
                true // Aller-retour pour les missions d'attaque
            );
        }
        return 0;
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
            
            // Calculer la consommation de carburant avec la méthode centralisée
            $totalFuelConsumption = PlanetMission::calculateFuelConsumption(
                $selectedByTemplate,
                $this->planet->templatePlanet->system,
                $this->targetPlanet->templatePlanet->system,
                true // Aller-retour pour les missions d'attaque
            );
            
            // Utiliser la méthode calculateMissionDuration du modèle PlanetMission
            $durationInMinutes = PlanetMission::calculateMissionDuration(
                $this->planet->templatePlanet->system,
                $this->targetPlanet->templatePlanet->system,
                $slowestSpeed,
                auth()->id()
            );
            
            // Convertir les minutes en secondes pour la compatibilité avec le code existant
            $this->missionDuration = $durationInMinutes * 60;
            $this->fuelConsumption = $totalFuelConsumption;
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
                'text' => 'Vous devez sélectionner au moins un vaisseau pour lancer une attaque.'
            ]);
            return;
        }
        
        // Autoriser toute attaque si la cible est le bot (user_id = 1)
        if ($this->targetPlanet && (int) $this->targetPlanet->user_id === 1) {
            $this->calculateMissionDuration();
            $this->showMissionSummary = true;
            return;
        }

        // Ennemis seulement
        if (!$this->validateEnemyOnly()) {
            return;
        }

        // Vérifier la limitation fort/faible avant l'aperçu
        $svc = app(EngagementBandService::class);
        $check = $svc->checkTargetAllowed(auth()->user(), $this->targetPlanet?->user, 'spatial_attack');
        if (($check['enabled'] ?? false) && !($check['allowed'] ?? true)) {
            $this->dispatch('swal:error', [
                'title' => 'Cible hors bande',
                'text' => "Vous pouvez attaquer des cibles entre " . number_format($check['min']) . " et " . number_format($check['max']) . " " . $check['label'] . ". Cible: " . number_format($check['target_points'] ?? 0) . "."
            ]);
            return;
        }

        $this->calculateMissionDuration();
        $this->showMissionSummary = true;
    }
    
    public function launchAttack()
    {
        if ($this->totalShipsSelected <= 0) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous devez sélectionner au moins un vaisseau pour lancer une attaque.'
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

        // Bloquer si trêve active pour les attaques spatiales
        if (ServerConfig::get('truce_enabled') && ServerConfig::get('truce_block_spatial_attack')) {
            $message = ServerConfig::get('truce_message') ?: "Trêve active: les attaques spatiales sont temporairement désactivées.";

            $this->dispatch('swal:error', [
                'title' => 'Trêve active',
                'text' => $message
            ]);

            // Journaliser la tentative bloquée
            if (method_exists($this, 'logAction')) {
                $this->logAction(
                    'attack_blocked_by_truce',
                    'mission',
                    "Tentative d'attaque spatiale bloquée par trêve",
                    [
                        'from_planet_id' => $this->planet->id,
                        'target_planet_id' => $this->targetPlanet?->id,
                        'target_coordinates' => "[{$this->targetGalaxy}:{$this->targetSystem}:{$this->targetPosition}]",
                        'attack_type' => 'spatial'
                    ],
                    $this->planet->id,
                    $this->targetPlanet?->user_id
                );
            }

            return;
        }
        
        // Vérifier si les coordonnées sont valides
        if ($this->planet->templatePlanet->galaxy < 1 || $this->planet->templatePlanet->system < 1 || $this->planet->templatePlanet->position < 1) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Coordonnées invalides.'
            ]);
            return;
        }
        
        // Recharger les informations de la planète cible pour s'assurer qu'elles sont à jour
        $this->loadTargetPlanetData();
        
        if (!$this->targetPlanet) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Aucune planète trouvée à ces coordonnées.'
            ]);
            return;
        }
        
        // Vérifier la limite d'attaques quotidiennes
        $attackService = new \App\Services\AttackService(new \App\Services\PrivateMessageService());
        $attackCheck = $attackService->canAttackPlayer(auth()->id(), $this->targetPlanet->user_id);
        
        if (!$attackCheck['can_attack']) {
            $this->dispatch('swal:error', [
                'title' => 'Limite d\'attaques atteinte',
                'text' => $attackCheck['message']
            ]);
            return;
        }
        
        // Informer le joueur du nombre d'attaques restantes
        if ($attackCheck['remaining_attacks'] !== null && $attackCheck['remaining_attacks'] <= 3) {
            $this->dispatch('swal:info', [
                'title' => 'Information',
                'text' => $attackCheck['message']
            ]);
        }
        
        // Vérifier la limitation fort/faible juste avant le lancement
        // Exception: si la cible est le bot (user_id = 1), ignorer la bande
        if (!($this->targetPlanet && (int) $this->targetPlanet->user_id === 1)) {
            $svc = app(EngagementBandService::class);
            $check = $svc->checkTargetAllowed(auth()->user(), $this->targetPlanet?->user, 'spatial_attack');
            if (($check['enabled'] ?? false) && !($check['allowed'] ?? true)) {
                $this->dispatch('swal:error', [
                    'title' => 'Cible hors bande',
                    'text' => "Attaque spatiale interdite: bande autorisée " . number_format($check['min']) . "–" . number_format($check['max']) . " " . $check['label'] . ". Cible: " . number_format($check['target_points'] ?? 0) . "."
                ]);
                return;
            }
        }
        
        // Vérifier si on a assez de deutérium pour le voyage
        $deuteriumResource = $this->planet->resources()
            ->whereHas('resource', function($query) {
                $query->where('name', 'deuterium');
            })
            ->first();
        
        if (!$deuteriumResource || $deuteriumResource->current_amount < $this->fuelConsumption) {
            $this->dispatch('swal:error', [
                'title' => 'Carburant insuffisant',
                'text' => "Vous avez besoin de {$this->fuelConsumption} unités de deutérium pour ce voyage!"
            ]);
            return;
        }
        
        // Préparer les données des vaisseaux pour la mission
        // Format attendu par AttackService: [template_ship_id => quantity]
        $ships = [];
        foreach ($this->selectedShips as $planetShipId => $quantity) {
            if ($quantity > 0) {
                $planetShip = PlanetShip::find($planetShipId);
                if ($planetShip && $planetShip->quantity >= $quantity) {
                    // Agréger par identifiant de template du vaisseau
                    $ships[$planetShip->ship_id] = ($ships[$planetShip->ship_id] ?? 0) + $quantity;

                    // Retirer les vaisseaux de la planète
                    $planetShip->decrement('quantity', $quantity);
                }
            }
        }
        
        // Déduire le deutérium pour le carburant
        $deuteriumResource->decrement('current_amount', $this->fuelConsumption);
        
        // Créer la mission
        $mission = PlanetMission::create([
            'user_id' => auth()->id(),
            'from_planet_id' => $this->planetId,
            'to_planet_id' => $this->targetPlanet->id,
            'to_galaxy' => $this->targetGalaxy,
            'to_system' => $this->targetSystem,
            'to_position' => $this->targetPosition,
            'mission_type' => 'attack',
            'ships' => $ships,
            'resources' => [],
            'departure_time' => Carbon::now(),
            'arrival_time' => Carbon::now()->addSeconds($this->missionDuration),
            'status' => 'traveling'
        ]);

        // Incrémenter la quête quotidienne pour mission d'attaque
        $user = Auth::user();
        if ($user) {
            app(DailyQuestService::class)->incrementProgress($user, 'mission_attack');
        }
        
        // Créer un message de départ de mission
        $messageService = new PrivateMessageService();
        $messageService->createMissionDepartureMessage($mission);
        
        // Réinitialiser les sélections
        foreach ($this->selectedShips as $shipId => $quantity) {
            $this->selectedShips[$shipId] = 0;
        }
        $this->totalShipsSelected = 0;
        $this->showMissionSummary = false;
        
        // Recharger les vaisseaux disponibles
        $this->loadAvailableShips();
        
        $this->dispatch('swal:success', [
            'title' => 'Mission lancée',
            'text' => "Mission d'attaque lancée !"
        ]);
    }
    
    public function cancelMission()
    {
        $this->showMissionSummary = false;
    }
    
    public function render()
    {
        return view('livewire.game.mission.spatial');
    }
}