<?php

namespace App\Livewire\Game;

use Livewire\Component;
use Livewire\Attributes\On; 
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetResource;
use App\Models\Template\TemplateResource;
use Illuminate\Support\Facades\Auth;
use App\Support\Device;

class Resource extends Component
{
    protected $listeners = [
        'resourcesUpdated' => 'loadPlanetResources'
    ];

    public $planet = null;
    public $availablePlanets = [];

    public $primaryResources = [];
    public $totalProduction = 0;
    public $totalEnergyConsumption = 0;
    public $totalEnergyRemaining = 0;
    public $researchPointsProduction = 0;

    public function mount()
    {
        $this->loadUserPlanets();
        
        // Utiliser la planète actuelle de l'utilisateur ou la première disponible
        $actualPlanet = Auth::user()->getActualPlanet();
        if ($actualPlanet) {
            $planetData = $this->availablePlanets->firstWhere('id', $actualPlanet->id);
            if ($planetData) {
                $this->selectPlanet($planetData);
                return;
            }
        }
        
        // Fallback sur la première planète disponible
        if ($this->availablePlanets->isNotEmpty()) {
            $this->selectPlanet($this->availablePlanets->first());
        }
    }

    public function loadUserPlanets()
    {
        // Utiliser la relation planets du modèle User
        $this->availablePlanets = Auth::user()->planets()
            ->select('id', 'name', 'description', 'is_main_planet')
            ->get();
    }

    public function selectPlanet($planetData)
    {
        // Convertir en array si c'est un modèle
        if (is_object($planetData)) {
            $planetData = $planetData->toArray();
        }
        
        $this->planet = $planetData;

        // Mettre à jour l'actual_planet_id de l'utilisateur
        Auth::user()->update(['actual_planet_id' => $this->planet['id']]);

        $this->loadPlanetResources();
        $this->calculateTotals();
    }

    public function loadPlanetResources()
    {
        if (!$this->planet) {
            return;
        }

        $planetResources = PlanetResource::where('planet_id', $this->planet['id'])
            ->with('resource')
            ->where('is_active', true)
            ->get();

        $this->primaryResources = [];

        foreach ($planetResources as $planetResource) {
            $currentProduction = $planetResource->getCurrentProductionPerHour();
            $storageCapacity = $planetResource->getStorageCapacity();
            $storageUsage = $planetResource->getStorageUsagePercentage();
            
            $resource = [
                'id' => $planetResource->id,
                'resource_id' => $planetResource->resource_id,
                'name' => $planetResource->resource->display_name,
                'icon' => $planetResource->resource->icon,
                'color' => $planetResource->resource->color ?? '#ffffff',
                'current_amount' => $planetResource->current_amount,
                'production_rate' => $currentProduction,
                'storage_capacity' => $storageCapacity,
                'storage_usage' => $storageUsage,
                'is_storage_full' => $planetResource->isStorageFull(),
                'available_storage' => $planetResource->getAvailableStorage(),
                'last_update' => $planetResource->last_update?->diffForHumans() ?? 'Jamais'
            ];

            // Déterminer si c'est une ressource primaire (métal, cristal, deutérium)
            $resourceName = strtolower($planetResource->resource->name);
            if (in_array($resourceName, ['metal', 'crystal', 'deuterium'])) {
                $this->primaryResources[] = $resource;
            }
        }
    }

    public function calculateTotals()
    {
        // Calculer la production totale des ressources primaires
        $this->totalProduction = collect($this->primaryResources)
            ->where('production_rate', '>', 0)
            ->sum('production_rate');

        $planetModel = Planet::find($this->planet['id']);

        // Calculer la consommation d'énergie (ressources avec production négative)
        if ($planetModel) {
            $this->totalEnergyConsumption = $planetModel->getEnergyConsumption();
        }

        // Calculer l'énergie restante
        if ($planetModel) {
            $this->totalEnergyRemaining = $planetModel->getNetEnergy();
        }

        // Calculer la production de research_points
        if ($planetModel) {
            $this->researchPointsProduction = $this->calculateResearchPointsProduction($planetModel);
        }
    }

    /**
     * Calculer la production de research_points basée sur le centre de recherche
     */
    private function calculateResearchPointsProduction(Planet $planet): float
    {
        // Obtenir le centre de recherche
        $researchCenter = $planet->buildings()
            ->where('is_active', true)
            ->whereHas('building', function($query) {
                $query->where('name', 'centre_recherche');
            })
            ->first();

        if (!$researchCenter || $researchCenter->level <= 0) {
            return 0;
        }

        // Production de base : 10 points par niveau par heure
        $baseProduction = $researchCenter->level * 10;

        // Appliquer les bonus des technologies et autres bâtiments
        $productionBonus = \App\Models\Template\TemplateBuildAdvantage::getResearchPointsProduction($planet->id);
        
        return $baseProduction + $productionBonus;
    }

    #[On('resource-refresh')]
    public function ResourceRefresh()
    {
        // Si c'est la planète actuellement sélectionnée, mettre à jour l'affichage
        if ($this->planet) {
            $this->loadPlanetResources();
            $this->calculateTotals();
            
            // Déclencher une notification toast pour informer l'utilisateur
            /*$this->dispatch('toast:success', [
                'title' => 'Ressources mises à jour',
                'text' => 'Les ressources de ' . $this->planet['name'] . ' ont été mises à jour automatiquement.'
            ]);*/
        }
    }

    public function render()
    {
        return view('livewire.game.resource');
    }
}