<?php

namespace App\Livewire\Game\Mission;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetShip;
use App\Models\Planet\PlanetMission;
use App\Models\Template\TemplateBuild;
use App\Models\Template\TemplatePlanet;
use App\Services\PrivateMessageService;
use App\Services\UserCustomizationService;
use App\Services\DailyQuestService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

#[Layout('components.layouts.game')]
class MissionExtract extends Component
{
    public $planetId;
    public $planet;
    public $availableShips = [];
    public $transporteurDeltaShips = [];
    
    // Informations sur la planète cible
    public $targetPlanetTemplate = null;
    public $targetPlanet;
    public $targetGalaxy;
    public $targetSystem;
    public $targetPosition;
    
    // Sélection des vaisseaux
    public $selectedShips = [];
    public $totalShipsSelected = 0;
    public $totalCapacity = 0;
    
    // Informations de mission
    public $fuelConsumption = 0;
    public $travelDurationMinutes = 0;
    public $showMissionSummary = false;
    
    public function mount($templateId)
    {
        $this->planet = auth()->user()->getActualPlanet();
        $this->planetId = $this->planet->id;
        
        $this->targetPlanetTemplate = TemplatePlanet::findOrFail($templateId);
        $this->targetGalaxy = $this->targetPlanetTemplate->galaxy;
        $this->targetSystem = $this->targetPlanetTemplate->system;
        $this->targetPosition = $this->targetPlanetTemplate->position;

        $this->targetPlanet = Planet::where('template_planet_id', $templateId)->first();
        
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
        
        // Charger les vaisseaux transporteur delta disponibles
        $this->loadTransporteurDeltaShips();
        
        // Initialiser le tableau des vaisseaux sélectionnés
        foreach ($this->transporteurDeltaShips as $ship) {
            $this->selectedShips[$ship['id']] = 0;
        }
    }
    
    public function loadTransporteurDeltaShips()
    {
        // Récupérer uniquement les transporteurs delta
        $planetShips = PlanetShip::with(['ship'])
            ->where('planet_id', $this->planetId)
            ->where('quantity', '>', 0)
            ->whereHas('ship', function($query) {
                $query->where('name', 'transporteur_delta');
            })
            ->get();
        
        $this->transporteurDeltaShips = [];
        $service = app(UserCustomizationService::class);
        $user = Auth::user();

        foreach ($planetShips as $planetShip) {
            $resolved = $service->resolveBuild($user, $planetShip->ship);
            $this->transporteurDeltaShips[] = [
                'id' => $planetShip->id,
                'name' => $resolved['name'] ?? $planetShip->ship->label,
                'description' => $planetShip->ship->description,
                'capacity' => $planetShip->getTotalCargoCapacity(),
                'speed' => $planetShip->ship->speed,
                'quantity' => $planetShip->quantity,
                'image' => $planetShip->ship->icon,
                'icon_url' => $resolved['icon_url'] ?? null,
            ];
        }
    }
    
    public function updateShipSelection()
    {
        $this->totalShipsSelected = 0;
        $this->totalCapacity = 0;
        
        foreach ($this->selectedShips as $shipId => $quantity) {
            if ($quantity < 0) {
                $this->selectedShips[$shipId] = 0;
                $quantity = 0;
            }
            
            $availableShip = collect($this->transporteurDeltaShips)->firstWhere('id', $shipId);
            if ($availableShip && $quantity > $availableShip['quantity']) {
                $this->selectedShips[$shipId] = $availableShip['quantity'];
                $quantity = $availableShip['quantity'];
            }
            
            if ($availableShip && $quantity > 0) {
                $this->totalShipsSelected += $quantity;
                $this->totalCapacity += $availableShip['capacity'] * $quantity;
            }
        }
        
        // Calculer les paramètres de mission (consommation) si des vaisseaux sont sélectionnés
        if ($this->totalShipsSelected > 0 && $this->targetGalaxy && $this->targetSystem && $this->targetPosition) {
            $this->calculateMissionDuration();
        } else {
            $this->fuelConsumption = 0;
            $this->showMissionSummary = false;
        }
    }
    
    public function setMaxShips($shipId)
    {
        $availableShip = collect($this->transporteurDeltaShips)->firstWhere('id', $shipId);
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
    
    public function calculateMissionDuration()
    {
        if ($this->totalShipsSelected > 0 && $this->targetGalaxy && $this->targetSystem && $this->targetPosition) {
            $slowestSpeed = PlanetMission::calculateSpeed($this->selectedShips, $this->transporteurDeltaShips);
            
            if ($slowestSpeed > 0) {
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
                    $this->targetSystem
                );
                $this->fuelConsumption = $totalFuelConsumption;
                
                // Calculer la durée du voyage (aller) en minutes
                $this->travelDurationMinutes = PlanetMission::calculateMissionDuration(
                    $this->planet->templatePlanet->system,
                    $this->targetSystem,
                    $slowestSpeed,
                    auth()->id()
                );
            }
        } else {
            $this->fuelConsumption = 0;
            $this->travelDurationMinutes = 0;
        }
    }
    
    public function validateTarget()
    {
        if (!$this->targetGalaxy || !$this->targetSystem || !$this->targetPosition) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Veuillez saisir des coordonnées valides.'
            ]);
            return false;
        }

        // Vérifier la distance maximale de 10 systèmes et même galaxie
        $sourceGalaxy = $this->planet->templatePlanet->galaxy ?? null;
        $sourceSystem = $this->planet->templatePlanet->system ?? null;
        if ($sourceGalaxy === null || $sourceSystem === null) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Impossible de déterminer les coordonnées de votre planète de départ.'
            ]);
            return false;
        }
        if ($this->targetGalaxy !== $sourceGalaxy) {
            $this->dispatch('swal:error', [
                'title' => 'Distance trop grande',
                'text' => 'L\'extraction doit se faire dans la même galaxie et à ≤ 10 systèmes.'
            ]);
            return false;
        }
        $systemDiff = abs($this->targetSystem - $sourceSystem);
        if ($systemDiff > 10) {
            $this->dispatch('swal:error', [
                'title' => 'Distance trop grande',
                'text' => 'L\'extraction doit se faire à une distance maximale de 10 systèmes.'
            ]);
            return false;
        }
        
        // Vérifier que la planète cible n'est pas colonisée        
        if ($this->targetPlanet) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Cette planète est déjà colonisée. L\'extraction n\'est possible que sur des planètes non colonisées.'
            ]);
            return false;
        }
        
        if (!$this->targetPlanetTemplate) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Aucune planète n\'existe à ces coordonnées.'
            ]);
            return false;
        }
        
        return true;
    }
    
    public function showSummary()
    {
        if (!$this->validateTarget()) {
            return;
        }
        
        if ($this->totalShipsSelected <= 0) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous devez sélectionner au moins un Transporteur Delta pour lancer une mission d\'extraction.'
            ]);
            return;
        }
        
        $this->calculateMissionDuration();
        $this->showMissionSummary = true;
    }
    
    public function launchMission()
    {
        try {
            if (!$this->validateTarget()) {
                return;
            }
            
            if ($this->totalShipsSelected <= 0) {
                throw new \Exception('Aucun Transporteur Delta sélectionné.');
            }
            
            // Vérifier le plafond des flottes en vol selon le niveau du Centre de Commandement
            $allowedFlying = \App\Models\Planet\PlanetMission::getAllowedFlyingFleetsForPlanet($this->planetId);
            $currentFlying = \App\Models\Planet\PlanetMission::countUserFlyingMissions(auth()->id());
            if ($currentFlying >= $allowedFlying) {
                $ccLevel = \App\Models\Planet\PlanetMission::getCommandCenterLevelForPlanet($this->planetId);
                $this->dispatch('swal:error', [
                    'title' => 'Trop de flottes en vol',
                    'text' => "Limite actuelle: {$allowedFlying} flottes en vol (Centre de Commandement niveau {$ccLevel})."
                ]);
                return;
            }

            // Vérifier si le joueur a assez de deutérium
            $deuteriumResource = $this->planet->resources()->where('resource_id', 3)->first(); // 3 = deutérium
            if (!$deuteriumResource || $deuteriumResource->current_amount < $this->fuelConsumption) {
                throw new \Exception('Pas assez de deutérium pour cette mission. Requis: ' . number_format($this->fuelConsumption));
            }
            
            // Consommer le deutérium
            $deuteriumResource->decrement('current_amount', $this->fuelConsumption);
            
            // Préparer les données des vaisseaux avec informations détaillées
            $missionShips = [];
            foreach ($this->selectedShips as $shipId => $quantity) {
                if ($quantity > 0) {
                    $planetShip = PlanetShip::find($shipId);
                    if ($planetShip) {
                        $missionShips[$planetShip->ship_id] = [
                            'type' => 'ship',
                            'quantity' => $quantity,
                            'name' => $planetShip->ship->name,
                            'speed' => $planetShip->ship->speed,
                            'attack_power' => $planetShip->ship->attack_power,
                            'defense_power' => $planetShip->ship->defense_power
                        ];
                    }
                }
            }
            
            // Retirer les vaisseaux de la planète d'origine
            foreach ($this->selectedShips as $shipId => $quantity) {
                if ($quantity > 0) {
                    $planetShip = PlanetShip::find($shipId);
                    if ($planetShip && $planetShip->quantity >= $quantity) {
                        $planetShip->decrement('quantity', $quantity);
                    }
                }
            }
            
            $slowestSpeed = PlanetMission::calculateSpeed($this->selectedShips, $this->transporteurDeltaShips);
            $travelDuration = PlanetMission::calculateMissionDuration(
                $this->planet->templatePlanet->system,
                $this->targetSystem,
                $slowestSpeed,
                auth()->id()
            );
            
            // Créer la mission d'extraction
            $mission = PlanetMission::create([
                'user_id' => auth()->id(),
                'from_planet_id' => $this->planetId,
                'to_planet_id' => null, // Pas de planète cible car non colonisée
                'to_galaxy' => $this->targetGalaxy,
                'to_system' => $this->targetSystem,
                'to_position' => $this->targetPosition,
                'mission_type' => 'extract',
                'ships' => $missionShips,
                'resources' => [],
                'departure_time' => Carbon::now(),
                'arrival_time' => Carbon::now()->addMinutes($travelDuration),
                'return_time' => null, // Sera calculé lors du traitement
                'status' => 'traveling',
                'result' => [
                    'total_capacity' => (int) $this->totalCapacity,
                ]
            ]);

            // Incrémenter la quête quotidienne pour mission d'extraction
            $user = Auth::user();
            if ($user) {
                app(DailyQuestService::class)->incrementProgress($user, 'mission_extract');
            }
            
            $this->dispatch('swal:success', [
                'title' => 'Mission lancée !',
                'text' => 'La mission d\'extraction a été lancée avec succès.'
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
        return view('livewire.game.mission.mission-extract');
    }
}