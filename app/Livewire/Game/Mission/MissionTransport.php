<?php

namespace App\Livewire\Game\Mission;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetShip;
use App\Models\Planet\PlanetMission;
use App\Models\Planet\PlanetResource;
use App\Models\Template\TemplatePlanet;
use App\Services\PrivateMessageService;
use App\Services\UserCustomizationService;
use App\Models\Resource\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\LogsUserActions;

#[Layout('components.layouts.game')]
class MissionTransport extends Component
{
    use LogsUserActions;
    
    public $planetId;
    public $planet;
    
    public $targetPlanet = null;
    public $targetGalaxy;
    public $targetSystem;
    public $targetPosition;
    
    // Sélection des vaisseaux
    public $selectedShips = [];
    public $totalShipsSelected = 0;
    public $availableShips = [];
    public $totalCapacity = 0;
    public $usedCapacity = 0;
    
    // Ressources pour le transport
    public $resourcesForTransport = [];
    public $showMissionSummary = false;
    public $missionDuration = 0;
    public $fuelConsumption = 0;
    
    public function mount($targetPlanetId)
    {
        $this->planet = auth()->user()->getActualPlanet();
        $this->planetId = $this->planet->id;
        
        $this->targetPlanet = Planet::with(['templatePlanet','user'])->findOrFail($targetPlanetId);
        $this->targetGalaxy = $this->targetPlanet->templatePlanet->galaxy;
        $this->targetSystem = $this->targetPlanet->templatePlanet->system;
        $this->targetPosition = $this->targetPlanet->templatePlanet->position;
        
        // Vérifier que la planète cible n'est pas la même que la planète de départ
        if ($this->targetPlanet->id === $this->planetId) {
           $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous ne pouvez pas transporter des ressources vers la même planète de départ.'
            ]);
            return redirect()->route('game.mission.index');
        }
        
        if ($this->targetPlanet->user_id !== Auth::id()) {
            $otherUser = $this->targetPlanet->user;
            $isAlly = $otherUser ? $this->isAllyWith($otherUser) : false;
            if (!$isAlly) {
                $this->dispatch('swal:error', [
                    'title' => 'Erreur',
                    'text' => 'Transport autorisé uniquement vers vos planètes ou celles de vos alliés (pacte ou membre d\'alliance).'
                ]);
                return redirect()->route('game.mission.index');
            }
        }
        
        $this->loadPlanetData();
        $this->loadAvailableShips();
        
        // Initialiser les ressources pour le transport
        $resources = $this->planet->resources;
        foreach ($resources as $resource) {
            if ($resource->resource->name != 'energy') {
                $this->resourcesForTransport[$resource->resource_id] = 0;
            }
        }
    }

    /**
     * Déterminer si l'utilisateur cible est allié (même alliance ou pacte accepté)
     */
    protected function isAllyWith($otherUser): bool
    {
        if (!$otherUser) return false;
        // Même alliance
        if (Auth::user()->alliance_id && $otherUser->alliance_id && Auth::user()->alliance_id === $otherUser->alliance_id) {
            return true;
        }
        // Pacte accepté
        $relation = \App\Models\User\UserRelation::findBetween(Auth::id(), $otherUser->id);
        return $relation && $relation->status === \App\Models\User\UserRelation::STATUS_ACCEPTED;
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
    }
    
    public function loadAvailableShips()
    {
        // Récupérer tous les vaisseaux de transport disponibles sur la planète
        $planetShips = PlanetShip::with(['ship'])
            ->where('planet_id', $this->planetId)
            ->where('quantity', '>', 0)
            ->whereHas('ship', function($query) {
                $query->where('name', 'transporteur_delta');
            })
            ->get();
        
        $this->availableShips = [];
        $service = app(UserCustomizationService::class);
        $user = Auth::user();

        foreach ($planetShips as $planetShip) {
            $resolved = $service->resolveBuild($user, $planetShip->ship);
            $this->availableShips[] = [
                'id' => $planetShip->id,
                'name' => $resolved['name'] ?? $planetShip->ship->name_display,
                'image' => $planetShip->ship->icon,
                'icon_url' => $resolved['icon_url'] ?? null,
                'attack' => $planetShip->ship->attack_power,
                'defense' => $planetShip->ship->defense_power,
                'speed' => $planetShip->ship->speed,
                'capacity' => $planetShip->getTotalCargoCapacity(),
                'quantity' => $planetShip->quantity,
                'fuel_consumption' => $planetShip->ship->fuel_consumption
            ];
        }
    }
    
    public function updateShipSelection()
    {
        $this->totalShipsSelected = 0;
        $this->totalCapacity = 0;
        
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
                $this->totalCapacity += $quantity * $ship['capacity'];
            }
        }
        
        // Calculer la capacité utilisée
        $this->calculateUsedCapacity();
        
        // Calculer la durée de la mission et la consommation de carburant
        $this->calculateMissionDetails();
    }

    public function updatedSelectedShips(): void
    {
        // Recalculer immédiatement lorsque l’entangle met à jour selectedShips
        $this->totalShipsSelected = 0;
        $this->totalCapacity = 0;

        foreach ($this->selectedShips as $id => $quantity) {
            if ($quantity < 0) {
                $this->selectedShips[$id] = 0;
                $quantity = 0;
            }
            $ship = collect($this->availableShips)->firstWhere('id', $id);
            if ($ship) {
                if ($quantity > $ship['quantity']) {
                    $this->selectedShips[$id] = $ship['quantity'];
                    $quantity = $ship['quantity'];
                }
                $this->totalShipsSelected += (int) $quantity;
                $this->totalCapacity += (int) $quantity * (int) $ship['capacity'];
            }
        }

        $this->calculateUsedCapacity();
        $this->calculateMissionDetails();
    }
    
    public function calculateUsedCapacity()
    {
        $this->usedCapacity = 0;
        foreach ($this->resourcesForTransport as $amount) {
            $this->usedCapacity += $amount;
        }
    }
    
    public function updateResourceAmount($resourceId, $amount)
    {
        // Vérifier que le montant est valide
        $amount = max(0, intval($amount));
        
        // Vérifier que le montant ne dépasse pas les ressources disponibles
        $availableResource = $this->getAvailableResourceAmount($resourceId);
        $amount = min($amount, $availableResource);
        
        // Mettre à jour la valeur
        $this->resourcesForTransport[$resourceId] = $amount;
        
        // Recalculer la capacité utilisée
        $this->calculateUsedCapacity();
    }
    
    public function getAvailableResourceAmount($resourceId)
    {
        $planetResource = $this->planet->resources->firstWhere('resource_id', $resourceId);
        return $planetResource ? $planetResource->current_amount : 0;
    }
    
    public function setMaxResource($resourceId)
    {
        $availableResource = $this->getAvailableResourceAmount($resourceId);
        $remainingCapacity = $this->totalCapacity - ($this->usedCapacity - ($this->resourcesForTransport[$resourceId] ?? 0));
        
        $maxAmount = min($availableResource, $remainingCapacity);
        $this->resourcesForTransport[$resourceId] = $maxAmount;
        
        $this->calculateUsedCapacity();
    }
    
    public function setClearResource($resourceId)
    {
        $this->resourcesForTransport[$resourceId] = 0;
        $this->calculateUsedCapacity();
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
    
    public function calculateMissionDetails()
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

            // Calculer la consommation de carburant (aller-retour) avec IDs de template
            $totalFuelConsumption = PlanetMission::calculateFuelConsumption(
                $selectedByTemplate,
                $this->planet->templatePlanet->system,
                $this->targetPlanet->templatePlanet->system
            );
            
            // Utiliser la méthode calculateMissionDuration du modèle PlanetMission
            $durationInMinutes = PlanetMission::calculateMissionDuration(
                $this->planet->templatePlanet->system,
                $this->targetPlanet->templatePlanet->system,
                $slowestSpeed,
                Auth::id()
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
                'text' => 'Vous devez sélectionner au moins un vaisseau de transport.'
            ]);
            return;
        }
        
        if ($this->usedCapacity <= 0) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous devez sélectionner au moins une ressource à transporter.'
            ]);
            return;
        }
        
        if ($this->usedCapacity > $this->totalCapacity) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'La capacité de transport est dépassée. Réduisez la quantité de ressources.'
            ]);
            return;
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
        
        $this->showMissionSummary = true;
    }
    
    public function backToSelection()
    {
        $this->showMissionSummary = false;
    }
    
    public function launchMission()
    {
        // Vérifier que des vaisseaux sont sélectionnés
        if ($this->totalShipsSelected <= 0) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous devez sélectionner au moins un vaisseau pour lancer une mission de transport.'
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

        // Vérifier que des ressources sont sélectionnées
        $totalResources = 0;
        foreach ($this->resourcesForTransport as $resourceId => $amount) {
            $totalResources += $amount;
        }
        
        if ($totalResources <= 0) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous devez sélectionner des ressources à transporter!'
            ]);
            return;
        }
        
        // Vérifier que la capacité n'est pas dépassée
        if ($this->usedCapacity > $this->totalCapacity) {
            $this->dispatch('swal:error', [
                'title' => 'Capacité dépassée',
                'text' => 'La quantité sélectionnée dépasse la capacité de transport disponible!'
            ]);
            return;
        }
        
        // Vérifier que les ressources sont toujours disponibles
        foreach ($this->resourcesForTransport as $resourceId => $amount) {
            if ($amount > 0) {
                $planetResource = PlanetResource::where('planet_id', $this->planet->id)
                    ->where('resource_id', $resourceId)
                    ->first();
                
                if (!$planetResource || $planetResource->current_amount < $amount) {
                    $this->dispatch('swal:error', [
                        'title' => 'Ressources insuffisantes',
                        'text' => 'Les ressources sélectionnées ne sont plus disponibles en quantité suffisante!'
                    ]);
                    return;
                }
            }
        }
        
        // Déduire le deutérium pour le carburant
        $deuteriumResource = PlanetResource::where('planet_id', $this->planet->id)
            ->whereHas('resource', function($query) {
                $query->where('name', 'deuterium');
            })
            ->first();
        
        if (!$deuteriumResource || $deuteriumResource->current_amount < $this->fuelConsumption) {
            $this->dispatch('swal:error', [
                'title' => 'Carburant insuffisant',
                'text' => 'Vous n\'avez plus assez de deutérium pour ce voyage!'
            ]);
            return;
        }
        
        $deuteriumResource->decrement('current_amount', $this->fuelConsumption);
        
        // Déduire les ressources transportées de la planète source
        foreach ($this->resourcesForTransport as $resourceId => $amount) {
            if ($amount > 0) {
                $planetResource = PlanetResource::where('planet_id', $this->planet->id)
                    ->where('resource_id', $resourceId)
                    ->first();
                
                if ($planetResource) {
                    $planetResource->decrement('current_amount', $amount);
                }
            }
        }
        
        // Récupérer les informations sur les vaisseaux utilisés pour la mission
        $transportShip = PlanetShip::where('planet_id', $this->planet->id)
            ->whereHas('ship', function($query) {
                $query->where('name', 'transporteur_delta');
            })
            ->first();
            
        // Calculer le nombre de vaisseaux nécessaires en fonction de la quantité de ressources à transporter
        $cargoCapacityPerShip = $transportShip->getTotalCargoCapacity();
        $shipsNeeded = ceil($totalResources / $cargoCapacityPerShip);
        
        // S'assurer qu'au moins un vaisseau est utilisé
        $shipsNeeded = max(1, $shipsNeeded);
        
        // Vérifier que les vaisseaux sont toujours disponibles
        if (!$transportShip || $transportShip->quantity < $shipsNeeded) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous n\'avez plus assez de vaisseaux pour cette mission!'
            ]);
            return;
        }
        
        // Préparer les données des vaisseaux pour la mission
        $missionShips = [
            $transportShip->ship_id => [
                'quantity' => $shipsNeeded,
                'name' => $transportShip->ship->name,
                'speed' => $transportShip->ship->speed,
                'cargo_capacity' => $transportShip->getTotalCargoCapacity()
            ]
        ];
        
        // Retirer les vaisseaux de la planète
        $transportShip->decrement('quantity', $shipsNeeded);
        
        // Créer la mission de transport
        $mission = PlanetMission::create([
            'user_id' => auth()->id(),
            'from_planet_id' => $this->planet->id,
            'to_planet_id' => $this->targetPlanet->id,
            'to_galaxy' => $this->targetPlanet->templatePlanet->galaxy,
            'to_system' => $this->targetPlanet->templatePlanet->system,
            'to_position' => $this->targetPlanet->templatePlanet->position,
            'mission_type' => 'transport',
            'ships' => $missionShips,
            'resources' => $this->resourcesForTransport,
            'departure_time' => Carbon::now(),
            'arrival_time' => Carbon::now()->addSeconds($this->missionDuration),
            'status' => 'traveling'
        ]);
        
        // Créer un message de départ de mission
        $messageService = new PrivateMessageService();
        $messageService->createMissionDepartureMessage($mission);
        
        // Logger la mission de transport
        $this->logMissionLaunched(
            'transport',
            $this->planet->id,
            $this->targetPlanet->id,
            [
                'from_planet' => $this->planet->name,
                'to_planet' => $this->targetPlanet->name,
                'coordinates' => "{$this->targetGalaxy}:{$this->targetSystem}:{$this->targetPosition}",
                'ships_sent' => $shipsNeeded,
                'resources_transported' => $totalResources,
                'fuel_consumed' => $this->fuelConsumption,
                'mission_duration' => $this->missionDuration
            ]
        );
        
        $this->dispatch('swal:success', [
            'title' => 'Mission lancée',
            'text' => "Mission de transport lancée."
        ]);
        
        return redirect()->route('game.mission.index');
    }
    
    public function render()
    {
        return view('livewire.game.mission.transport', [
            'planetId' => $this->planetId,
            'showSummary' => $this->showMissionSummary,
            'availableShips' => $this->availableShips,
            'selectedShips' => $this->selectedShips,
            'totalSelectedShips' => $this->totalShipsSelected,
            'missionDuration' => $this->missionDuration,
            'resourcesForTransport' => $this->resourcesForTransport,
            'availableResources' => $this->planet->resources,
            'targetGalaxy' => $this->targetGalaxy,
            'targetSystem' => $this->targetSystem,
            'targetPosition' => $this->targetPosition,
            'fuelConsumption' => $this->fuelConsumption
        ]);
    }
}