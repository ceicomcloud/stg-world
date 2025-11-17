<?php

namespace App\Livewire\Game\Mission;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetUnit;
use App\Models\Planet\PlanetMission;
use App\Models\Planet\PlanetResource;
use App\Services\PrivateMessageService;
use App\Services\AttackService;
use App\Services\EngagementBandService;
use App\Traits\LogsUserActions;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Server\ServerConfig;
use App\Services\UserCustomizationService;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use App\Models\Planet\PlanetEquip;

#[Layout('components.layouts.game')]
class MissionEarth extends Component
{
    use LogsUserActions;
    public $planetId;
    public $planet;
    public $availableUnits = [];
    
    // Informations sur la planète cible
    public $targetPlanet = null;
    public $targetGalaxy;
    public $targetSystem;
    public $targetPosition;
    
    
    // Sélection des unités
    public $selectedUnits = [];
    public $totalUnitsSelected = 0;
    public $totalCargoCapacity = 0;
    
    // Informations de mission
    public $missionDuration = 0;
    public $showMissionSummary = false;
    public $attackInProgress = false;

    // Équipes pré-configurées
    public $equipTeams = [];
    public $selectedTeamId = null;
    
    public function mount($targetPlanetId)
    {
        $this->planet = auth()->user()->getActualPlanet();
        $this->planetId = $this->planet->id;

        $this->targetPlanet = Planet::with('templatePlanet')->findOrFail($targetPlanetId);
        $this->targetGalaxy = $this->targetPlanet->templatePlanet->galaxy;
        $this->targetSystem = $this->targetPlanet->templatePlanet->system;
        $this->targetPosition = $this->targetPlanet->templatePlanet->position;
        
        // Interdire si la Porte des étoiles de la planète cible est verrouillée
        if ($this->targetPlanet->isStargateLocked()) {
            $this->dispatch('swal:error', [
                'title' => 'Porte des étoiles',
                'text' => "Cette planète a sa Porte des étoiles verrouillée. Les attaques terrestres sont interdites."
            ]);
            
            $this->logAction(
                'attack_blocked_by_stargate_lock',
                'mission',
                "Tentative d'attaque terrestre bloquée par Porte des étoiles verrouillée",
                [
                    'from_planet_id' => $this->planet->id,
                    'target_planet_id' => $this->targetPlanet->id,
                    'target_coordinates' => "[{$this->targetGalaxy}:{$this->targetSystem}:{$this->targetPosition}]",
                    'attack_type' => 'earth'
                ],
                $this->planet->id,
                $this->targetPlanet->user_id
            );
            
            return redirect()->route('game.mission.index');
        }

        if (!$this->validateEnemyOnly()) {
            return redirect()->route('game.mission.index');
        }

        // Interdire les attaques terrestres si la Porte des étoiles est active sur la planète cible
        if ($this->targetPlanet->stargate_active) {
            $this->dispatch('swal:error', [
                'title' => 'Porte des étoiles',
                'text' => "Cette planète a sa Porte des étoiles active. Les attaques terrestres sont interdites."
            ]);
            
            $this->logAction(
                'attack_blocked_by_stargate',
                'mission',
                "Tentative d'attaque terrestre bloquée par Porte des étoiles",
                [
                    'from_planet_id' => $this->planet->id,
                    'target_planet_id' => $this->targetPlanet->id,
                    'target_coordinates' => "[{$this->targetGalaxy}:{$this->targetSystem}:{$this->targetPosition}]",
                    'attack_type' => 'earth'
                ],
                $this->planet->id,
                $this->targetPlanet->user_id
            );
            
            return redirect()->route('game.mission.index');
        }

        // Vérifier si la planète cible est protégée par un bouclier planétaire
        if ($this->targetPlanet->isShieldProtectionActive()) {
            $this->dispatch('swal:error', [
                'title' => 'Protection planétaire',
                'text' => 'Cette planète est actuellement protégée par un bouclier planétaire. Impossible de l\'attaquer.'
            ]);
            
            // Enregistrer la tentative d'attaque bloquée dans les logs
            $this->logAction(
                'attack_blocked_by_shield',
                'mission',
                'Tentative d\'attaque terrestre bloquée par un bouclier planétaire',
                [
                    'from_planet_id' => $this->planet->id,
                    'target_planet_id' => $this->targetPlanet->id,
                    'target_coordinates' => "[{$this->targetGalaxy}:{$this->targetSystem}:{$this->targetPosition}]",
                    'attack_type' => 'earth'
                ],
                $this->planet->id,
                $this->targetPlanet->user_id
            );
            
            return redirect()->route('game.mission.index');
        }
        
        // Vérifier si la Porte des étoiles de la planète de départ est verrouillée
        if ($this->planet->isStargateLocked()) {
            $this->dispatch('swal:error', [
                'title' => 'Porte des étoiles',
                'text' => 'Votre Porte des étoiles est verrouillée. Impossible de lancer des attaques terrestres.'
            ]);
            return redirect()->route('game.mission.index');
        }

        // Vérifier si la planète de départ est protégée par un bouclier planétaire
        if ($this->planet->isShieldProtectionActive()) {
            $this->dispatch('swal:error', [
                'title' => 'Protection planétaire',
                'text' => 'Votre planète est actuellement protégée par un bouclier planétaire. Impossible de lancer des attaques.'
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
        
        
        // Charger les unités disponibles pour l'attaque
        $this->loadAvailableUnits();

        // Charger les équipes actives de la planète (catégorie terrestre)
        $this->loadEquipTeams();
        
        // Initialiser le tableau des unités sélectionnées
        foreach ($this->availableUnits as $unit) {
            $this->selectedUnits[$unit['id']] = 0;
        }
    }

    protected function loadEquipTeams(): void
    {
        $teams = PlanetEquip::active()
            ->byPlanet($this->planetId)
            ->earth()
            ->orderBy('team_index')
            ->get();

        $this->equipTeams = $teams->map(function ($t) {
            return [
                'id' => $t->id,
                'label' => $t->label,
                'team_index' => $t->team_index,
                'payload_units' => (array) (($t->payload['units'] ?? [])),
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
        foreach ($this->availableUnits as $unit) {
            $this->selectedUnits[$unit['id']] = 0;
        }

        $payload = (array) ($team['payload_units'] ?? []);

        // Appliquer les quantités en respectant la disponibilité sur la planète
        foreach ($payload as $templateUnitId => $qty) {
            $available = collect($this->availableUnits)->firstWhere('id', (int) $templateUnitId);
            if ($available) {
                $max = (int) ($available['quantity'] ?? 0);
                $this->selectedUnits[(int) $templateUnitId] = min((int) $qty, $max);
            }
        }

        // Mettre à jour les totaux
        $this->updateUnitSelection();

        $this->dispatch('toast:success', [
            'title' => 'Équipe appliquée',
            'text' => "La sélection a été remplie selon l'équipe"
        ]);
    }
    
    public function loadAvailableUnits()
    {
        // Récupérer toutes les unités de combat disponibles sur la planète
        $planetUnits = PlanetUnit::with(['unit'])
            ->where('planet_id', $this->planetId)
            ->where('quantity', '>', 0)
            ->get();
        
        $this->availableUnits = [];
        $svc = new UserCustomizationService();
        $user = FacadesAuth::user();
        
        foreach ($planetUnits as $planetUnit) {
            $resolved = $svc->resolveBuild($user, $planetUnit->unit);
            $this->availableUnits[] = [
                'id' => $planetUnit->unit->id,
                'name' => $resolved['name'],
                'description' => $planetUnit->unit->description,
                'attack' => $planetUnit->unit->attack_power,
                'defense' => $planetUnit->unit->defense_power,
                'speed' => $planetUnit->unit->speed,
                'cargo_capacity' => $planetUnit->getTotalCargoCapacity(),
                'quantity' => $planetUnit->quantity,
                'image' => $planetUnit->unit->icon,
                'icon_url' => $resolved['icon_url'],
            ];
        }
    }
    
    public function updateUnitSelection()
    {
        $this->totalUnitsSelected = 0;
        $this->totalCargoCapacity = 0;
        
        foreach ($this->selectedUnits as $unitId => $quantity) {
            // Vérifier que la quantité n'est pas négative
            if ($quantity < 0) {
                $this->selectedUnits[$unitId] = 0;
                $quantity = 0;
            }
            
            // Vérifier que la quantité ne dépasse pas le disponible
            $availableUnit = collect($this->availableUnits)->firstWhere('id', $unitId);
            if ($availableUnit && $quantity > $availableUnit['quantity']) {
                $this->selectedUnits[$unitId] = $availableUnit['quantity'];
                $quantity = $availableUnit['quantity'];
            }
            
            $this->totalUnitsSelected += $quantity;
            
            // Calculer la capacité de transport totale
            if ($availableUnit && $quantity > 0) {
                $cargoCapacity = $availableUnit['cargo_capacity'] ?? 0;
                $this->totalCargoCapacity += $cargoCapacity * $quantity;
            }
        }
        
        // Calculer la durée de la mission si des unités sont sélectionnées
        if ($this->totalUnitsSelected > 0) {
            $this->calculateMissionDuration();
        } else {
            $this->missionDuration = 0;
            $this->showMissionSummary = false;
        }
    }
    
    public function setMaxUnits($unitId)
    {
        $availableUnit = collect($this->availableUnits)->firstWhere('id', $unitId);
        if ($availableUnit) {
            $this->selectedUnits[$unitId] = $availableUnit['quantity'];
            $this->updateUnitSelection();
        }
    }
    
    public function setClearUnits($unitId)
    {
        $this->selectedUnits[$unitId] = 0;
        $this->updateUnitSelection();
    }
    
    public function calculateMissionDuration()
    {
        // Trouver l'unité la plus lente parmi les sélectionnées
        $slowestSpeed = PHP_INT_MAX;
        
        foreach ($this->selectedUnits as $unitId => $quantity) {
            if ($quantity > 0) {
                $unit = collect($this->availableUnits)->firstWhere('id', $unitId);
                if ($unit && $unit['speed'] < $slowestSpeed) {
                    $slowestSpeed = $unit['speed'];
                }
            }
        }
        
        // Si aucune unité n'est sélectionnée, utiliser une vitesse par défaut
        if ($slowestSpeed === PHP_INT_MAX) {
            $slowestSpeed = 50; // Vitesse par défaut pour les unités terrestres (plus lentes que les vaisseaux)
        }
        
        // Calculer la durée de la mission
        $this->missionDuration = PlanetMission::calculateMissionDuration(
            $this->planet->templatePlanet->system,
            $this->targetSystem,
            $slowestSpeed,
            auth()->id()
        );
    }
    
    public function showSummary()
    {
        if ($this->totalUnitsSelected <= 0) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous devez sélectionner au moins une unité pour lancer une attaque terrestre.'
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
        $check = $svc->checkTargetAllowed(auth()->user(), $this->targetPlanet?->user, 'earth_attack');
        if (($check['enabled'] ?? false) && !($check['allowed'] ?? true)) {
            $this->dispatch('swal:error', [
                'title' => 'Cible hors bande',
                'text' => "Vous pouvez attaquer des cibles entre " . number_format($check['min']) . " et " . number_format($check['max']) . " " . $check['label'] . ". Cible: " . number_format($check['target_points'] ?? 0) . "."
            ]);
            return;
        }
        
        $this->showMissionSummary = true;
    }
    
    public function launchAttack()
    {
        if ($this->totalUnitsSelected <= 0) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous devez sélectionner au moins une unité pour lancer une attaque terrestre.'
            ]);
            return;
        }

        // Vérifier la limitation fort/faible (bande d'engagement) avant le lancement
        // Exception: si la cible est le bot (user_id = 1), ignorer la bande
        if (!($this->targetPlanet && (int) $this->targetPlanet->user_id === 1)) {
            $svc = app(EngagementBandService::class);
            $check = $svc->checkTargetAllowed(auth()->user(), $this->targetPlanet?->user, 'earth_attack');
            if (($check['enabled'] ?? false) && !($check['allowed'] ?? true)) {
                $this->dispatch('swal:error', [
                    'title' => 'Cible hors bande',
                    'text' => "Attaque terrestre interdite: bande autorisée " . number_format($check['min']) . "–" . number_format($check['max']) . " " . $check['label'] . ". Cible: " . number_format($check['target_points'] ?? 0) . "."
                ]);
                return;
            }
        }

        // Bloquer si trêve active pour les attaques terrestres
        if (ServerConfig::get('truce_enabled') && ServerConfig::get('truce_block_earth_attack')) {
            $message = ServerConfig::get('truce_message') ?: "Trêve active: les attaques terrestres sont temporairement désactivées.";

            $this->dispatch('swal:error', [
                'title' => 'Trêve active',
                'text' => $message
            ]);

            // Journaliser la tentative bloquée
            $this->logAction(
                'attack_blocked_by_truce',
                'mission',
                "Tentative d'attaque terrestre bloquée par trêve",
                [
                    'from_planet_id' => $this->planet->id,
                    'target_planet_id' => $this->targetPlanet?->id,
                    'target_coordinates' => "[{$this->targetGalaxy}:{$this->targetSystem}:{$this->targetPosition}]",
                    'attack_type' => 'earth'
                ],
                $this->planet->id,
                $this->targetPlanet?->user_id
            );

            return redirect()->route('game.mission.index');
        }
        
        // Marquer l'attaque comme en cours
        $this->attackInProgress = true;
        
        // Vérifier si les coordonnées sont valides
        if ($this->targetGalaxy < 1 || $this->targetSystem < 1 || $this->targetPosition < 1) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Coordonnées invalides.'
            ]);
            return;
        }
                
        if (!$this->targetPlanet) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Aucune planète trouvée à ces coordonnées.'
            ]);
            return;
        }
        
        // Vérifier la limite d'attaques quotidiennes
        $attackService = new AttackService(new PrivateMessageService());
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
        
        // Préparer les données des unités pour l'attaque
        $attackerUnits = [];
        foreach ($this->selectedUnits as $unitId => $quantity) {
            if ($quantity > 0) {
                $planetUnit = PlanetUnit::where('planet_id', $this->planetId)
                    ->where('unit_id', $unitId)
                    ->first();
                    
                if ($planetUnit && $planetUnit->quantity >= $quantity) {
                    $attackerUnits[$unitId] = $quantity;
                    
                    // Retirer les unités de la planète attaquante
                    $planetUnit->decrement('quantity', $quantity);
                }
            }
        }
        
        try {
            // Exécuter le combat avec notre AttackService
            $combatResult = $attackService->executeGroundCombat(
                $this->planetId,
                $this->targetPlanet->id,
                $attackerUnits
            );
            
            // Remettre les unités survivantes sur la planète attaquante
            if (!empty($combatResult['surviving_attacker_units'])) {
                foreach ($combatResult['surviving_attacker_units'] as $unitId => $quantity) {
                    $planetUnit = PlanetUnit::where('planet_id', $this->planetId)
                        ->where('unit_id', $unitId)
                        ->first();
                        
                    if ($planetUnit) {
                        $planetUnit->increment('quantity', $quantity);
                    } else {
                        PlanetUnit::create([
                            'planet_id' => $this->planetId,
                            'unit_id' => $unitId,
                            'quantity' => $quantity
                        ]);
                    }
                }
            }
            
            // Réinitialiser les sélections
            $this->selectedUnits = [];
            foreach ($this->availableUnits as $unit) {
                $this->selectedUnits[$unit['id']] = 0;
            }
            $this->totalUnitsSelected = 0;
            $this->totalCargoCapacity = 0;
            $this->showMissionSummary = false;
            $this->attackInProgress = false;
            
            // Recharger les unités disponibles
            $this->loadAvailableUnits();
            
            // Message de succès
            $winnerText = $combatResult['winner'] === 'attacker' ? 'Victoire !' : 
                         ($combatResult['winner'] === 'defender' ? 'Défaite...' : 'Match nul');
                         
            $this->dispatch('swal:success', [
                'title' => 'Combat terminé',
                'text' => $winnerText . ' Le rapport de combat a été envoyé dans votre messagerie.'
            ]);
            
        } catch (\Exception $e) {
            // En cas d'erreur, remettre les unités sur la planète
            foreach ($attackerUnits as $unitId => $quantity) {
                $planetUnit = PlanetUnit::where('planet_id', $this->planetId)
                    ->where('unit_id', $unitId)
                    ->first();
                    
                if ($planetUnit) {
                    $planetUnit->increment('quantity', $quantity);
                }
            }
            
            // Réinitialiser l'état d'attaque
            $this->attackInProgress = false;
            
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Une erreur est survenue lors du combat: ' . $e->getMessage()
            ]);
        }
    }
    
    public function cancelMission()
    {
        $this->showMissionSummary = false;
    }
    
    public function render()
    {
        return view('livewire.game.mission.earth');
    }
}