<?php

namespace App\Livewire\Game\Mission;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetShip;
use App\Models\Planet\PlanetUnit;
use App\Models\Planet\PlanetMission;
use App\Models\Template\TemplateBuild;
use App\Services\PrivateMessageService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\UserCustomizationService;
use Illuminate\Support\Facades\Auth as FacadesAuth;

#[Layout('components.layouts.game')]
class MissionBasement extends Component
{
    public $planetId;
    public $planet;
    public $availableUnits = [];
    public $availableShips = [];
    
    // Informations sur la planète cible
    public $targetPlanetId = null;
    public $targetPlanet = null;
    public $targetGalaxy;
    public $targetSystem;
    public $targetPosition;
    
    // Sélection des unités et vaisseaux
    public $selectedUnits = [];
    public $selectedShips = [];
    public $totalUnitsSelected = 0;
    public $totalShipsSelected = 0;
    
    // Informations de mission
    public $showMissionSummary = false;
    public $missionDuration = 0;
    public $fuelConsumption = 0;
    
    public function mount($targetPlanetId)
    {
        $this->planet = auth()->user()->getActualPlanet();
        $this->planetId = $this->planet->id;
        
        // Récupérer la planète de destination directement
        $this->targetPlanet = Planet::with('templatePlanet')->findOrFail($targetPlanetId);
        $this->targetPlanetId = $targetPlanetId;
        $this->targetGalaxy = $this->targetPlanet->templatePlanet->galaxy;
        $this->targetSystem = $this->targetPlanet->templatePlanet->system;
        $this->targetPosition = $this->targetPlanet->templatePlanet->position;
        
        // Vérifier que la planète appartient bien à l'utilisateur
        if ($this->targetPlanet->user_id !== auth()->id()) {
            abort(403, 'Cette planète ne vous appartient pas.');
        }
        
        // Vérifier que la planète cible n'est pas la même que la planète de départ
        if ($this->targetPlanet->id === $this->planetId) {
           $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous ne pouvez pas transférer des unités vers la même planète de départ.'
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
        
        // Charger les unités disponibles
        $this->loadAvailableUnits();
        
        // Charger les vaisseaux disponibles
        $this->loadAvailableShips();
        
        // Initialiser les tableaux de sélection
        foreach ($this->availableUnits as $unit) {
            $this->selectedUnits[$unit['id']] = 0;
        }
        
        foreach ($this->availableShips as $ship) {
            $this->selectedShips[$ship['id']] = 0;
        }
    }
    

    
    public function loadAvailableUnits()
    {
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
                'id' => $planetUnit->id,
                'name' => $resolved['name'],
                'description' => $planetUnit->unit->description,
                'quantity' => $planetUnit->quantity,
                'image' => $planetUnit->unit->icon,
                'icon_url' => $resolved['icon_url'],
                'speed' => $planetUnit->unit->speed ?? 1
            ];
        }
    }
    
    public function loadAvailableShips()
    {
        $planetShips = PlanetShip::with(['ship'])
            ->where('planet_id', $this->planetId)
            ->where('quantity', '>', 0)
            ->get();
        
        $this->availableShips = [];
        $svc = new UserCustomizationService();
        $user = FacadesAuth::user();
        
        foreach ($planetShips as $planetShip) {
            $resolved = $svc->resolveBuild($user, $planetShip->ship);
            $this->availableShips[] = [
                'id' => $planetShip->id,
                'name' => $resolved['name'],
                'description' => $planetShip->ship->description,
                'quantity' => $planetShip->quantity,
                'image' => $planetShip->ship->icon,
                'icon_url' => $resolved['icon_url'],
                'speed' => $planetShip->ship->speed ?? 1
            ];
        }
    }
    
    public function updateUnitSelection()
    {
        $this->totalUnitsSelected = 0;
        
        foreach ($this->selectedUnits as $unitId => $quantity) {
            if ($quantity < 0) {
                $this->selectedUnits[$unitId] = 0;
                $quantity = 0;
            }
            
            $availableUnit = collect($this->availableUnits)->firstWhere('id', $unitId);
            if ($availableUnit && $quantity > $availableUnit['quantity']) {
                $this->selectedUnits[$unitId] = $availableUnit['quantity'];
                $quantity = $availableUnit['quantity'];
            }
            
            $this->totalUnitsSelected += $quantity;
        }
        
        // Recalculer la durée de mission
        $this->calculateMissionDetails();
    }
    
    public function updateShipSelection()
    {
        $this->totalShipsSelected = 0;
        
        foreach ($this->selectedShips as $shipId => $quantity) {
            if ($quantity < 0) {
                $this->selectedShips[$shipId] = 0;
                $quantity = 0;
            }
            
            $availableShip = collect($this->availableShips)->firstWhere('id', $shipId);
            if ($availableShip && $quantity > $availableShip['quantity']) {
                $this->selectedShips[$shipId] = $availableShip['quantity'];
                $quantity = $availableShip['quantity'];
            }
            
            $this->totalShipsSelected += $quantity;
        }
        
        // Recalculer la durée de mission
        $this->calculateMissionDetails();
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
    
    public function setMaxShips($shipId)
    {
        $availableShip = collect($this->availableShips)->firstWhere('id', $shipId);
        if ($availableShip) {
            $this->selectedShips[$shipId] = $availableShip['quantity'];
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
        if (($this->totalUnitsSelected > 0 || $this->totalShipsSelected > 0) && $this->targetPlanet) {
            // Calculer la vitesse la plus lente parmi les unités et vaisseaux sélectionnés
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
        if (!$this->targetPlanetId) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous devez sélectionner une planète de destination.'
            ]);
            return;
        }
        
        if ($this->totalUnitsSelected <= 0 && $this->totalShipsSelected <= 0) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous devez sélectionner au moins une unité ou un vaisseau à transférer.'
            ]);
            return;
        }
        
        // Calculer la durée de la mission et la consommation de carburant
        $this->calculateMissionDetails();
        
        // La planète cible est déjà chargée dans mount()
        $this->showMissionSummary = true;
    }
    
    public function launchMission()
    {
        try {
            if (!$this->targetPlanetId) {
                throw new \Exception('Planète de destination non sélectionnée.');
            }
            
            if ($this->totalUnitsSelected <= 0 && $this->totalShipsSelected <= 0) {
                throw new \Exception('Aucune unité ou vaisseau sélectionné.');
            }
            
            // Préparer les données des unités et vaisseaux
            $missionShips = [];
            
            // Ajouter les unités sélectionnées
            foreach ($this->selectedUnits as $unitId => $quantity) {
                if ($quantity > 0) {
                    $planetUnit = PlanetUnit::with('unit')->find($unitId);
                    if ($planetUnit) {
                        $missionShips[$planetUnit->unit_id] = [
                            'type' => 'unit',
                            'quantity' => $quantity,
                            'name' => $planetUnit->unit->label ?? 'Unité inconnue',
                            'speed' => $planetUnit->unit->speed ?? 0,
                            'attack_power' => $planetUnit->unit->attack_power ?? 0,
                            'defense_power' => $planetUnit->unit->defense_power ?? 0
                        ];
                    }
                }
            }
            
            // Ajouter les vaisseaux sélectionnés
            foreach ($this->selectedShips as $shipId => $quantity) {
                if ($quantity > 0) {
                    $planetShip = PlanetShip::with('ship')->find($shipId);
                    if ($planetShip) {
                        $missionShips[$planetShip->ship_id] = [
                            'type' => 'ship',
                            'quantity' => $quantity,
                            'name' => $planetShip->ship->label ?? 'Vaisseau inconnu',
                            'speed' => $planetShip->ship->speed ?? 0,
                            'attack_power' => $planetShip->ship->attack_power ?? 0,
                            'defense_power' => $planetShip->ship->defense_power ?? 0
                        ];
                    }
                }
            }
            
            // Retirer les unités et vaisseaux de la planète d'origine
            foreach ($this->selectedUnits as $unitId => $quantity) {
                if ($quantity > 0) {
                    $planetUnit = PlanetUnit::find($unitId);
                    if ($planetUnit && $planetUnit->quantity >= $quantity) {
                        $planetUnit->decrement('quantity', $quantity);
                    }
                }
            }
            
            foreach ($this->selectedShips as $shipId => $quantity) {
                if ($quantity > 0) {
                    $planetShip = PlanetShip::find($shipId);
                    if ($planetShip && $planetShip->quantity >= $quantity) {
                        $planetShip->decrement('quantity', $quantity);
                    }
                }
            }
            
            // Vérifier et déduire le deutérium pour le carburant
            if ($this->fuelConsumption > 0) {
                $deuteriumResource = $this->planet->resources->firstWhere('resource.name', 'deuterium');
                
                if (!$deuteriumResource || $deuteriumResource->current_amount < $this->fuelConsumption) {
                    $this->dispatch('swal:error', [
                        'title' => 'Carburant insuffisant',
                        'text' => 'Vous n\'avez pas assez de deutérium pour ce voyage!'
                    ]);
                    return;
                }
                
                $deuteriumResource->decrement('current_amount', $this->fuelConsumption);
            }
            
            // Créer la mission basement avec délai
            $mission = PlanetMission::create([
                'user_id' => auth()->id(),
                'from_planet_id' => $this->planetId,
                'to_planet_id' => $this->targetPlanetId,
                'to_galaxy' => $this->targetGalaxy,
                'to_system' => $this->targetSystem,
                'to_position' => $this->targetPosition,
                'mission_type' => 'basement',
                'ships' => $missionShips,
                'resources' => [],
                'departure_time' => Carbon::now(),
                'arrival_time' => Carbon::now()->addSeconds($this->missionDuration),
                'return_time' => null,
                'status' => 'traveling'
            ]);
            
            // Créer un message de départ de mission
            $messageService = new PrivateMessageService();
            $messageService->createMissionDepartureMessage($mission);
            
            $this->dispatch('swal:success', [
                'title' => 'Mission lancée !',
                'text' => 'La mission de transfert a été lancée avec succès.'
            ]);
            
            return redirect()->route('game.mission.index');
            
        } catch (\Exception $e) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Erreur lors du lancement de la mission: ' . $e->getMessage()
            ]);
        }
    }
    
    public function render()
    {
        return view('livewire.game.mission.mission-basement', [
            'planetId' => $this->planetId,
            'showSummary' => $this->showMissionSummary,
            'availableUnits' => $this->availableUnits,
            'availableShips' => $this->availableShips,
            'selectedUnits' => $this->selectedUnits,
            'selectedShips' => $this->selectedShips,
            'totalSelectedUnits' => $this->totalUnitsSelected,
            'totalSelectedShips' => $this->totalShipsSelected,
            'missionDuration' => $this->missionDuration,
            'targetGalaxy' => $this->targetGalaxy,
            'targetSystem' => $this->targetSystem,
            'targetPosition' => $this->targetPosition,
            'fuelConsumption' => $this->fuelConsumption
        ]);
    }
}