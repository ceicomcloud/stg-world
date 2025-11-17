<?php

namespace App\Livewire\Game\Mission;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetShip;
use App\Models\Planet\PlanetMission;
use App\Models\Planet\PlanetResource;
use App\Models\Template\TemplatePlanet;
use App\Models\User\UserTechnology;
use App\Models\Building\TemplateBuild;
use App\Services\PrivateMessageService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Traits\LogsUserActions;

#[Layout('components.layouts.game')]
class MissionColonize extends Component
{
    use LogsUserActions;
    public $planetId;
    public $planet;
    public $availableShips = [];
    
    // Informations sur la planète cible
    public $targetPlanetTemplate = null;
    public $targetPlanet = null;
    public $templateId;
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
    public $userPlanetCount = 0;
    public $maxPlanets = 9;
    public $canContinue = false;
    
    public function mount($templateId)
    {
        $this->planet = auth()->user()->getActualPlanet();
        $this->planetId = $this->planet->id;
        
        $this->targetPlanetTemplate = TemplatePlanet::findOrFail($templateId);
        
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
        
        // La vérification du nombre de planètes est maintenant faite dans loadTargetPlanetData()
        
        // Charger les informations de la planète cible
        $this->loadTargetPlanetData();
        
        // Charger les vaisseaux disponibles pour la colonisation
        $this->loadAvailableShips();
        
        // Initialiser le tableau des vaisseaux sélectionnés
        foreach ($this->availableShips as $ship) {
            $this->selectedShips[$ship['id']] = 0;
        }
    }
    
    public function loadTargetPlanetData()
    { 
        if (!$this->targetPlanetTemplate) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Template de planète non trouvé.'
            ]);
            return redirect()->route('game.mission.index');
        }
        
        // Initialiser les coordonnées de la cible
        $this->targetGalaxy = $this->targetPlanetTemplate->galaxy;
        $this->targetSystem = $this->targetPlanetTemplate->system;
        $this->targetPosition = $this->targetPlanetTemplate->position;
        
        // Vérifier si la planète cible existe et n'est pas déjà colonisée
        $this->targetPlanet = Planet::where('template_planet_id', $this->templateId)->first();
        
        // Vérifier le nombre de planètes de l'utilisateur
        $this->userPlanetCount = Planet::where('user_id', auth()->id())->count();
        $this->maxPlanets = \App\Models\Server\ServerConfig::where('key', 'max_planets_per_user')->first()->value ?? 9;
        
        // Déterminer si l'utilisateur peut continuer
        $this->canContinue = !$this->targetPlanet && $this->userPlanetCount < $this->maxPlanets;
    }
    
    public function loadAvailableShips()
    {
        // Récupérer tous les vaisseaux de colonisation disponibles sur la planète
        $planetShips = PlanetShip::with(['ship'])
            ->where('planet_id', $this->planetId)
            ->where('quantity', '>', 0)
            ->whereHas('ship', function($query) {
                $query->where('name', 'vaisseau_commandement');
            })
            ->get();
        
        $this->availableShips = [];
        
        foreach ($planetShips as $planetShip) {
            $this->availableShips[] = [
                'id' => $planetShip->id,
                'name' => $planetShip->ship->name_display,
                'image' => $planetShip->ship->icon,
                'attack' => $planetShip->ship->attack_power,
                'defense' => $planetShip->ship->defense_power,
                'speed' => $planetShip->ship->speed,
                'quantity' => $planetShip->quantity,
                'fuel_consumption' => $planetShip->ship->fuel_consumption ?? 0
            ];
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
        
        // Pour la colonisation, on ne peut envoyer qu'un seul vaisseau
        if ($this->totalShipsSelected > 1) {
            // Réinitialiser toutes les sélections
            foreach ($this->selectedShips as $id => $quantity) {
                $this->selectedShips[$id] = 0;
            }
            
            $this->totalShipsSelected = 0;
            
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous ne pouvez envoyer qu\'un seul vaisseau de colonisation à la fois.'
            ]);
        }
        
        // Calculer la durée de la mission
        $this->calculateMissionDuration();
    }
    
    public function setMaxShips($shipId)
    {
        // Pour la colonisation, on ne peut envoyer qu'un seul vaisseau
        foreach ($this->selectedShips as $id => $quantity) {
            $this->selectedShips[$id] = 0;
        }
        
        $this->selectedShips[$shipId] = 1;
        $this->updateShipSelection();
    }
    
    public function setClearShips($shipId)
    {
        $this->selectedShips[$shipId] = 0;
        $this->updateShipSelection();
    }
    
    public function calculateMissionDuration()
    {
        if ($this->totalShipsSelected > 0 && $this->targetPlanetTemplate) {
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

            // Calculer la consommation de carburant avec la méthode centralisée (aller simple pour colonisation)
            $totalFuelConsumption = PlanetMission::calculateFuelConsumption(
                $selectedByTemplate,
                $this->planet->templatePlanet->system,
                $this->targetPlanetTemplate->system,
                false // Aller simple pour la colonisation
            );
            
            // Utiliser la méthode calculateMissionDuration du modèle PlanetMission
            $durationInMinutes = PlanetMission::calculateMissionDuration(
                $this->planet->templatePlanet->system,
                $this->targetPlanetTemplate->system,
                $slowestSpeed,
                auth()->id()
            );
            
            // Convertir en secondes
            $this->missionDuration = $durationInMinutes * 60;
            $this->fuelConsumption = $totalFuelConsumption;
        } else {
            $this->missionDuration = 0;
            $this->fuelConsumption = 0;
        }
    }
    
    public function calculateDistance($galaxy1, $system1, $position1, $galaxy2, $system2, $position2)
    {
        if ($galaxy1 != $galaxy2) {
            return abs($galaxy1 - $galaxy2) * 20000;
        } elseif ($system1 != $system2) {
            return abs($system1 - $system2) * 5000;
        } else {
            return abs($position1 - $position2) * 1000;
        }
    }
    
    public function showSummary()
    {
        if ($this->totalShipsSelected <= 0) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous devez sélectionner un vaisseau de colonisation.'
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
        // Vérifier qu'au moins un vaisseau est sélectionné
        if ($this->totalShipsSelected <= 0) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous devez sélectionner au moins un vaisseau de colonisation.'
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
        
        // Vérifier le nombre maximum de planètes
        $maxPlanets = \App\Models\Server\ServerConfig::where('key', 'max_planets_per_user')->first()->value ?? 9;
        $userPlanetCount = Planet::where('user_id', auth()->id())->count();
        
        if ($userPlanetCount >= $maxPlanets) {
            $this->dispatch('swal:error', [
                'title' => 'Limite atteinte',
                'text' => "Vous avez atteint la limite de {$maxPlanets} planètes!"
            ]);
            return;
        }
        
        // Vérifier si l'utilisateur a des vaisseaux colonisateurs
        $colonyShip = PlanetShip::where('planet_id', $this->planet->id)
            ->whereHas('ship', function($query) {
                $query->where('name', 'vaisseau_commandement');
            })
            ->first();
        
        if (!$colonyShip || $colonyShip->quantity <= 0) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous n\'avez pas de Vaisseau de Colonisation disponible!'
            ]);
            return;
        }
        
        // Vérifier que la planète cible n'est pas déjà colonisée
        if ($this->templateId) {
            $existingPlanet = Planet::where('template_planet_id', $this->templateId)->first();
            
            if ($existingPlanet) {
                $this->dispatch('swal:error', [
                    'title' => 'Erreur',
                    'text' => 'Cette planète est déjà colonisée.'
                ]);
                return;
            }
        }
        
        // Vérifier le deutérium pour le carburant
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
        
        // Déduire le deutérium pour le carburant
        $deuteriumResource->decrement('current_amount', $this->fuelConsumption);
        
        // Préparer les données des vaisseaux pour la mission
        $missionShips = [
            $colonyShip->ship_id => [
                'quantity' => 1,
                'name' => $colonyShip->ship->name,
                'speed' => $colonyShip->ship->speed
            ]
        ];
        
        // Retirer le vaisseau de la planète
        $colonyShip->decrement('quantity', 1);
        
        // Créer la mission de colonisation
        $mission = PlanetMission::create([
            'user_id' => auth()->id(),
            'from_planet_id' => $this->planet->id,
            'to_planet_id' => null,
            'to_galaxy' => $this->targetPlanetTemplate->galaxy,
            'to_system' => $this->targetPlanetTemplate->system,
            'to_position' => $this->targetPlanetTemplate->position,
            'mission_type' => 'colonize',
            'ships' => $missionShips,
            'departure_time' => Carbon::now(),
            'arrival_time' => Carbon::now()->addSeconds($this->missionDuration),
            'status' => 'traveling'
        ]);
        
        // Créer un message de départ de mission
        $messageService = new PrivateMessageService();
        $messageService->createMissionDepartureMessage($mission);
        
        // Logger la mission de colonisation
        $this->logMissionLaunched(
            'colonize',
            $this->planet->id,
            null, // Pas de planète cible existante pour la colonisation
            [
                'target_coordinates' => "{$this->targetPlanetTemplate->galaxy}:{$this->targetPlanetTemplate->system}:{$this->targetPlanetTemplate->position}",
                'planet_type' => $this->targetPlanetTemplate->type,
                'ships_sent' => 1,
                'fuel_consumed' => $this->fuelConsumption,
                'mission_duration' => $this->missionDuration
            ]
        );
        
        $this->dispatch('swal:success', [
            'title' => 'Mission lancée',
            'text' => "Mission de colonisation lancée! Arrivée dans {$this->missionDuration} minutes."
        ]);
        
        return redirect()->route('game.mission.index');
    }
    
    public function render()
    {
        return view('livewire.game.mission.colonize', [
            'planetId' => $this->planetId,
            'templateId' => $this->templateId,
            'showSummary' => $this->showMissionSummary,
            'availableShips' => $this->availableShips,
            'selectedShips' => $this->selectedShips,
            'totalSelectedShips' => $this->totalShipsSelected,
            'missionDuration' => $this->missionDuration,
        ]);
    }
}