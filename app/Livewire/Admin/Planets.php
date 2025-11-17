<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetBuilding;
use App\Models\Planet\PlanetUnit;
use App\Models\Planet\PlanetShip;
use App\Models\Planet\PlanetDefense;
use App\Models\Planet\PlanetMission;
use App\Models\Planet\PlanetResource;
use App\Models\Planet\PlanetBunker;
use App\Models\Template\TemplatePlanet;
use App\Models\Template\TemplateResource;
use App\Models\Template\TemplateBuild;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\LogsUserActions;

#[Layout('components.layouts.admin')]
class Planets extends Component
{
    use WithPagination, LogsUserActions;
    
    // Propriétés pour la pagination et le filtrage
    public $search = '';
    public $perPage = 15;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $filterOccupied = '';
    public $filterActive = '';
    public $filterType = '';
    
    // Propriétés pour l'onglet actif
    public $activeTab = 'list';
    
    // Propriétés pour l'affectation d'une planète
    public $assignPlanet = [
        'user_id' => '',
        'template_planet_id' => '',
        'name' => '',
        'is_main_planet' => false,
        'is_active' => true
    ];
    
    // Propriétés pour la planète sélectionnée
    public $selectedPlanet = null;
    public $selectedPlanetId = null;
    
    /**
     * Initialisation du composant
     */
    public function mount($id = null)
    {
        // Si un ID de planète est fourni, sélectionner cette planète
        if ($id) {
            $this->selectPlanet($id);
        }
    }
    
    // Propriétés pour les onglets de détails
    public $planetDetailTab = 'info';
    
    // Règles de validation pour l'affectation d'une planète
    protected $rules = [
        'assignPlanet.user_id' => 'required|exists:users,id',
        'assignPlanet.template_planet_id' => 'required|exists:template_planets,id',
        'assignPlanet.name' => 'required|string|min:3|max:50',
        'assignPlanet.is_main_planet' => 'boolean',
        'assignPlanet.is_active' => 'boolean'
    ];
    
    /**
     * Réinitialiser la pagination lors de la recherche
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    /**
     * Réinitialiser la pagination lors du changement de filtres
     */
    public function updatingFilterOccupied()
    {
        $this->resetPage();
    }
    
    public function updatingFilterActive()
    {
        $this->resetPage();
    }
    
    public function updatingFilterType()
    {
        $this->resetPage();
    }
    
    /**
     * Trier les résultats
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    /**
     * Changer d'onglet principal
     */
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        
        // Réinitialiser les données si nécessaire
        if ($tab === 'assign') {
            $this->resetAssignPlanet();
        } elseif ($tab === 'list') {
            $this->selectedPlanet = null;
            $this->selectedPlanetId = null;
            $this->planetDetailTab = 'info';
        }
    }
    
    /**
     * Changer d'onglet de détails de la planète
     */
    public function setPlanetDetailTab($tab)
    {
        $this->planetDetailTab = $tab;
    }
    
    /**
     * Réinitialiser le formulaire d'affectation de planète
     */
    public function resetAssignPlanet()
    {
        $this->assignPlanet = [
            'user_id' => '',
            'template_planet_id' => '',
            'name' => '',
            'is_main_planet' => false,
            'is_active' => true
        ];
        $this->resetErrorBag();
    }
    
    /**
     * Affecter une planète à un utilisateur
     */
    public function assignPlanetToUser()
    {
        $this->validate();
        
        // Vérifier si la planète est déjà occupée
        $templatePlanet = TemplatePlanet::find($this->assignPlanet['template_planet_id']);
        if ($templatePlanet->is_occupied) {
            $this->dispatch('admin:toast:error', ['message' => 'Cette planète est déjà occupée']);
            return;
        }
        
        // Vérifier si l'utilisateur a déjà une planète principale si on essaie d'en assigner une nouvelle
        if ($this->assignPlanet['is_main_planet']) {
            $hasMainPlanet = Planet::where('user_id', $this->assignPlanet['user_id'])
                ->where('is_main_planet', true)
                ->exists();
            
            if ($hasMainPlanet) {
                $this->dispatch('admin:toast:error', ['message' => 'Cet utilisateur a déjà une planète principale']);
                return;
            }
        }
        
        // Créer la planète
        $planet = new Planet();
        $planet->user_id = $this->assignPlanet['user_id'];
        $planet->template_planet_id = $this->assignPlanet['template_planet_id'];
        $planet->name = $this->assignPlanet['name'];
        $planet->is_main_planet = $this->assignPlanet['is_main_planet'];
        $planet->is_active = $this->assignPlanet['is_active'];
        $planet->used_fields = 0;
        $planet->last_update = now();
        $planet->save();
        
        // Marquer la planète template comme occupée
        $templatePlanet->is_occupied = true;
        $templatePlanet->save();
        
        // Initialiser les ressources de la planète
        $this->initializePlanetResources($planet);
        
        // Initialiser les bâtiments de la planète
        $this->initializePlanetBuildings($planet);
        
        // Ajouter un log pour l'affectation de planète
        $user = User::find($this->assignPlanet['user_id']);
        $this->logAction(
            'Affectation de planète',
            'admin',
            'Affectation de la planète "' . $planet->name . '" à l\'utilisateur "' . $user->name . '"',
            [
                'planet_id' => $planet->id,
                'user_id' => $user->id,
                'template_planet_id' => $templatePlanet->id,
                'coordinates' => $templatePlanet->galaxy . ':' . $templatePlanet->system . ':' . $templatePlanet->position,
                'is_main_planet' => $planet->is_main_planet
            ]
        );
        
        // Rediriger vers la liste avec un message de succès
        $this->dispatch('admin:toast:success', ['message' => 'Planète affectée avec succès']);
        $this->setActiveTab('list');
    }
    
    /**
     * Initialiser les ressources de la planète
     */
    private function initializePlanetResources($planet)
    {
        $resources = TemplateResource::all();
        
        foreach ($resources as $resource) {
            PlanetResource::create([
                'planet_id' => $planet->id,
                'resource_id' => $resource->id,
                'current_amount' => $resource->starting_amount,
                'production_rate' => $resource->base_production,
                'last_update' => now(),
                'is_active' => true
            ]);
        }
    }
    
    /**
     * Initialiser les bâtiments de la planète
     */
    private function initializePlanetBuildings($planet)
    {
        $buildings = TemplateBuild::where('type', 'building')->get();
        
        foreach ($buildings as $building) {
            PlanetBuilding::create([
                'planet_id' => $planet->id,
                'building_id' => $building->id,
                'level' => 0,
                'is_active' => true
            ]);
        }
    }
    
    /**
     * Sélectionner une planète pour voir ses détails
     */
    public function selectPlanet($planetId)
    {
        $this->selectedPlanetId = $planetId;
        $this->selectedPlanet = Planet::with(['user', 'templatePlanet', 'resources.resource', 'buildings.building', 'units.unit', 'ships.ship', 'defenses.defense', 'bunkers'])->find($planetId);
        $this->planetDetailTab = 'info';
        $this->activeTab = 'detail';
    }
    
    /**
     * Obtenir les bâtiments de la planète sélectionnée
     */
    public function getPlanetBuildings()
    {
        if (!$this->selectedPlanetId) {
            return [];
        }
        
        return PlanetBuilding::where('planet_id', $this->selectedPlanetId)
            ->with('building')
            ->get();
    }
    
    /**
     * Obtenir les unités de la planète sélectionnée
     */
    public function getPlanetUnits()
    {
        if (!$this->selectedPlanetId) {
            return [];
        }
        
        return PlanetUnit::where('planet_id', $this->selectedPlanetId)
            ->with('unit')
            ->get();
    }
    
    /**
     * Obtenir les vaisseaux de la planète sélectionnée
     */
    public function getPlanetShips()
    {
        if (!$this->selectedPlanetId) {
            return [];
        }
        
        return PlanetShip::where('planet_id', $this->selectedPlanetId)
            ->with('ship')
            ->get();
    }
    
    /**
     * Obtenir les défenses de la planète sélectionnée
     */
    public function getPlanetDefenses()
    {
        if (!$this->selectedPlanetId) {
            return [];
        }
        
        return PlanetDefense::where('planet_id', $this->selectedPlanetId)
            ->with('defense')
            ->get();
    }
    
    /**
     * Obtenir les ressources de la planète sélectionnée
     */
    public function getPlanetResources()
    {
        if (!$this->selectedPlanetId) {
            return [];
        }
        
        return PlanetResource::where('planet_id', $this->selectedPlanetId)
            ->with('resource')
            ->get();
    }
    
    /**
     * Obtenir les ressources du bunker de la planète sélectionnée
     */
    public function getPlanetBunkers()
    {
        if (!$this->selectedPlanetId) {
            return [];
        }
        
        return PlanetBunker::where('planet_id', $this->selectedPlanetId)
            ->with('resource')
            ->get();
    }
    
    /**
     * Obtenir les missions de la planète sélectionnée
     */
    public function getPlanetMissions()
    {
        if (!$this->selectedPlanetId) {
            return [];
        }
        
        return PlanetMission::where('from_planet_id', $this->selectedPlanetId)
            ->orWhere('to_planet_id', $this->selectedPlanetId)
            ->with(['fromPlanet', 'toPlanet', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }
    
    /**
     * Obtenir la liste des utilisateurs pour le formulaire
     */
    public function getUsers()
    {
        return User::where('is_active', true)
            ->where('role', '!=', 'bot')
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Obtenir la liste des planètes templates disponibles
     */
    public function getAvailableTemplatePlanets()
    {
        return TemplatePlanet::where('is_available', true)
            ->where('is_colonizable', true)
            ->where('is_occupied', false)
            ->orderBy('galaxy')
            ->orderBy('system')
            ->orderBy('position')
            ->get();
    }
    
    /**
     * Obtenir la liste des planètes filtrée
     */
    public function getPlanets()
    {
        $query = Planet::query()
            ->with(['user', 'templatePlanet'])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($query) {
                            $query->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->filterOccupied !== '', function ($query) {
                if ($this->filterOccupied === 'occupied') {
                    $query->whereNotNull('user_id');
                } else {
                    $query->whereNull('user_id');
                }
            })
            ->when($this->filterActive !== '', function ($query) {
                $query->where('is_active', $this->filterActive === 'active');
            })
            ->when($this->filterType !== '', function ($query) {
                $query->whereHas('templatePlanet', function ($query) {
                    $query->where('type', $this->filterType);
                });
            });
        
        return $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }
    
    /**
     * Propriétés pour l'ajout/retrait de ressources, unités, vaisseaux et défenses
     */
    public $resourceForm = [
        'resource_id' => '',
        'amount' => 0
    ];
    
    public $unitForm = [
        'unit_id' => '',
        'quantity' => 0
    ];
    
    public $shipForm = [
        'ship_id' => '',
        'quantity' => 0
    ];
    
    public $defenseForm = [
        'defense_id' => '',
        'quantity' => 0
    ];

    public $buildingForm = [
        'building_id' => '',
        'levels' => 1
    ];
    
    /**
     * Règles de validation pour les formulaires d'ajout/retrait
     */
    protected function getResourceFormRules()
    {
        return [
            'resourceForm.resource_id' => 'required|exists:template_resources,id',
            'resourceForm.amount' => 'required|integer'
        ];
    }
    
    protected function getUnitFormRules()
    {
        return [
            'unitForm.unit_id' => 'required|exists:template_builds,id',
            'unitForm.quantity' => 'required|integer|min:1'
        ];
    }
    
    protected function getShipFormRules()
    {
        return [
            'shipForm.ship_id' => 'required|exists:template_builds,id',
            'shipForm.quantity' => 'required|integer|min:1'
        ];
    }
    
    protected function getDefenseFormRules()
    {
        return [
            'defenseForm.defense_id' => 'required|exists:template_builds,id',
            'defenseForm.quantity' => 'required|integer|min:1'
        ];
    }

    protected function getBuildingFormRules()
    {
        return [
            'buildingForm.building_id' => 'required|exists:template_builds,id',
            'buildingForm.levels' => 'required|integer|min:1'
        ];
    }

    /**
     * Listes des templates disponibles pour sélection
     */
    public function getAvailableUnits()
    {
        return TemplateBuild::where('type', 'unit')->orderBy('id')->get();
    }

    public function getAvailableShips()
    {
        return TemplateBuild::where('type', 'ship')->orderBy('id')->get();
    }

    public function getAvailableDefenses()
    {
        return TemplateBuild::where('type', 'defense')->orderBy('id')->get();
    }

    public function getAvailableBuildings()
    {
        return TemplateBuild::where('type', 'building')->orderBy('id')->get();
    }

    /**
     * Ajouter des niveaux de bâtiment à une planète
     */
    public function addBuildingLevels()
    {
        $this->validate($this->getBuildingFormRules());

        if (!$this->selectedPlanetId) {
            $this->dispatch('admin:toast:error', ['message' => 'Aucune planète sélectionnée']);
            return;
        }

        $planetBuilding = PlanetBuilding::where('planet_id', $this->selectedPlanetId)
            ->where('building_id', $this->buildingForm['building_id'])
            ->first();

        if (!$planetBuilding) {
            // Créer le bâtiment si manquant
            $planetBuilding = PlanetBuilding::create([
                'planet_id' => $this->selectedPlanetId,
                'building_id' => $this->buildingForm['building_id'],
                'level' => 0,
                'is_active' => true
            ]);
        }

        $levels = (int) $this->buildingForm['levels'];
        if ($levels <= 0) {
            $this->dispatch('admin:toast:error', ['message' => 'Le nombre de niveaux doit être supérieur à 0']);
            return;
        }

        try {
            $planetBuilding->update([
                'level' => $planetBuilding->level + $levels
            ]);

            $buildingName = $planetBuilding->build->label ?? $planetBuilding->build->name;
            $this->logAction(
                'Ajout de niveaux de bâtiment',
                'admin',
                'Ajout de ' . $levels . ' niveau(x) à ' . $buildingName . ' sur la planète "' . $this->selectedPlanet->name . '"',
                [
                    'planet_id' => $this->selectedPlanetId,
                    'building_id' => $this->buildingForm['building_id'],
                    'building_name' => $buildingName,
                    'levels' => $levels
                ]
            );

            $this->dispatch('admin:toast:success', ['message' => 'Bâtiment amélioré avec succès']);
        } catch (\Exception $e) {
            $this->dispatch('admin:toast:error', ['message' => $e->getMessage()]);
        }

        $this->buildingForm['levels'] = 1;
    }

    /**
     * Retirer des niveaux de bâtiment d'une planète
     */
    public function removeBuildingLevels()
    {
        $this->validate($this->getBuildingFormRules());

        if (!$this->selectedPlanetId) {
            $this->dispatch('admin:toast:error', ['message' => 'Aucune planète sélectionnée']);
            return;
        }

        $planetBuilding = PlanetBuilding::where('planet_id', $this->selectedPlanetId)
            ->where('building_id', $this->buildingForm['building_id'])
            ->first();

        if (!$planetBuilding) {
            $this->dispatch('admin:toast:error', ['message' => 'Bâtiment non trouvé sur cette planète']);
            return;
        }

        $levels = (int) $this->buildingForm['levels'];
        if ($levels <= 0) {
            $this->dispatch('admin:toast:error', ['message' => 'Le nombre de niveaux doit être supérieur à 0']);
            return;
        }

        $newLevel = max(0, $planetBuilding->level - $levels);

        try {
            $planetBuilding->update([
                'level' => $newLevel
            ]);

            $buildingName = $planetBuilding->build->label ?? $planetBuilding->build->name;
            $this->logAction(
                'Retrait de niveaux de bâtiment',
                'admin',
                'Retrait de ' . $levels . ' niveau(x) de ' . $buildingName . ' sur la planète "' . $this->selectedPlanet->name . '"',
                [
                    'planet_id' => $this->selectedPlanetId,
                    'building_id' => $this->buildingForm['building_id'],
                    'building_name' => $buildingName,
                    'levels' => $levels
                ]
            );

            $this->dispatch('admin:toast:success', ['message' => 'Bâtiment rétrogradé avec succès']);
        } catch (\Exception $e) {
            $this->dispatch('admin:toast:error', ['message' => $e->getMessage()]);
        }

        $this->buildingForm['levels'] = 1;
    }
    
    /**
     * Ajouter des ressources à une planète
     */
    public function addResources()
    {
        $this->validate($this->getResourceFormRules());
        
        if (!$this->selectedPlanetId) {
            $this->dispatch('admin:toast:error', ['message' => 'Aucune planète sélectionnée']);
            return;
        }
        
        $planetResource = PlanetResource::where('planet_id', $this->selectedPlanetId)
            ->where('resource_id', $this->resourceForm['resource_id'])
            ->first();

        // Créer l'entrée si elle n'existe pas
        if (!$planetResource) {
            $template = \App\Models\Template\TemplateResource::find($this->resourceForm['resource_id']);
            $planetResource = PlanetResource::create([
                'planet_id' => $this->selectedPlanetId,
                'resource_id' => $this->resourceForm['resource_id'],
                'current_amount' => 0,
                'production_rate' => $template?->base_production ?? 0,
                'last_update' => now(),
                'is_active' => true
            ]);
        }
        
        $amount = (int) $this->resourceForm['amount'];
        if ($amount <= 0) {
            $this->dispatch('admin:toast:error', ['message' => 'La quantité doit être supérieure à 0']);
            return;
        }
        
        $planetResource->addResources($amount);
        
        // Ajouter un log pour l'ajout de ressources
        $resourceName = $planetResource->resource->name;
        $this->logAction(
            'Ajout de ressources',
            'admin',
            'Ajout de ' . $amount . ' ' . $resourceName . ' à la planète "' . $this->selectedPlanet->name . '"',
            [
                'planet_id' => $this->selectedPlanetId,
                'resource_id' => $this->resourceForm['resource_id'],
                'resource_name' => $resourceName,
                'amount' => $amount
            ]
        );
        
        $this->dispatch('admin:toast:success', ['message' => 'Ressources ajoutées avec succès']);
        $this->resourceForm['amount'] = 0;
    }
    
    /**
     * Retirer des ressources d'une planète
     */
    public function removeResources()
    {
        $this->validate($this->getResourceFormRules());
        
        if (!$this->selectedPlanetId) {
            $this->dispatch('admin:toast:error', ['message' => 'Aucune planète sélectionnée']);
            return;
        }
        
        $planetResource = PlanetResource::where('planet_id', $this->selectedPlanetId)
            ->where('resource_id', $this->resourceForm['resource_id'])
            ->first();
        
        if (!$planetResource) {
            $this->dispatch('admin:toast:error', ['message' => 'Ressource non trouvée sur cette planète']);
            return;
        }
        
        $amount = (int) $this->resourceForm['amount'];
        if ($amount <= 0) {
            $this->dispatch('admin:toast:error', ['message' => 'La quantité doit être supérieure à 0']);
            return;
        }
        
        $success = $planetResource->removeResources($amount);
        
        if ($success) {
            // Ajouter un log pour le retrait de ressources
            $resourceName = $planetResource->resource->name;
            $this->logAction(
                'Retrait de ressources',
                'admin',
                'Retrait de ' . $amount . ' ' . $resourceName . ' de la planète "' . $this->selectedPlanet->name . '"',
                [
                    'planet_id' => $this->selectedPlanetId,
                    'resource_id' => $this->resourceForm['resource_id'],
                    'resource_name' => $resourceName,
                    'amount' => $amount
                ]
            );
            
            $this->dispatch('admin:toast:success', ['message' => 'Ressources retirées avec succès']);
        } else {
            $this->dispatch('admin:toast:error', ['message' => 'Quantité insuffisante de ressources']);
        }
        
        $this->resourceForm['amount'] = 0;
    }
    
    /**
     * Ajouter des unités à une planète
     */
    public function addUnits()
    {
        $this->validate($this->getUnitFormRules());
        
        if (!$this->selectedPlanetId) {
            $this->dispatch('admin:toast:error', ['message' => 'Aucune planète sélectionnée']);
            return;
        }
        
        $planetUnit = PlanetUnit::where('planet_id', $this->selectedPlanetId)
            ->where('unit_id', $this->unitForm['unit_id'])
            ->first();

        // Créer l'entrée si elle n'existe pas
        if (!$planetUnit) {
            $planetUnit = PlanetUnit::create([
                'planet_id' => $this->selectedPlanetId,
                'unit_id' => $this->unitForm['unit_id'],
                'quantity' => 0
            ]);
        }
        
        $quantity = (int) $this->unitForm['quantity'];
        if ($quantity <= 0) {
            $this->dispatch('admin:toast:error', ['message' => 'La quantité doit être supérieure à 0']);
            return;
        }
        
        $planetUnit->addUnits($quantity);
        
        // Ajouter un log pour l'ajout d'unités
        $unitName = $planetUnit->unit->name;
        $this->logAction(
            'Ajout d\'unités',
            'admin',
            'Ajout de ' . $quantity . ' ' . $unitName . ' à la planète "' . $this->selectedPlanet->name . '"',
            [
                'planet_id' => $this->selectedPlanetId,
                'unit_id' => $this->unitForm['unit_id'],
                'unit_name' => $unitName,
                'quantity' => $quantity
            ]
        );
        
        $this->dispatch('admin:toast:success', ['message' => 'Unités ajoutées avec succès']);
        $this->unitForm['quantity'] = 0;
    }
    
    /**
     * Retirer des unités d'une planète
     */
    public function removeUnits()
    {
        $this->validate($this->getUnitFormRules());
        
        if (!$this->selectedPlanetId) {
            $this->dispatch('admin:toast:error', ['message' => 'Aucune planète sélectionnée']);
            return;
        }
        
        $planetUnit = PlanetUnit::where('planet_id', $this->selectedPlanetId)
            ->where('unit_id', $this->unitForm['unit_id'])
            ->first();
        
        if (!$planetUnit) {
            $this->dispatch('admin:toast:error', ['message' => 'Unité non trouvée sur cette planète']);
            return;
        }
        
        $quantity = (int) $this->unitForm['quantity'];
        if ($quantity <= 0) {
            $this->dispatch('admin:toast:error', ['message' => 'La quantité doit être supérieure à 0']);
            return;
        }
        
        $success = $planetUnit->removeUnits($quantity);
        
        if ($success) {
            // Ajouter un log pour le retrait d'unités
            $unitName = $planetUnit->unit->name;
            $this->logAction(
                'Retrait d\'unités',
                'admin',
                'Retrait de ' . $quantity . ' ' . $unitName . ' de la planète "' . $this->selectedPlanet->name . '"',
                [
                    'planet_id' => $this->selectedPlanetId,
                    'unit_id' => $this->unitForm['unit_id'],
                    'unit_name' => $unitName,
                    'quantity' => $quantity
                ]
            );
            
            $this->dispatch('admin:toast:success', ['message' => 'Unités retirées avec succès']);
        } else {
            $this->dispatch('admin:toast:error', ['message' => 'Quantité insuffisante d\'unités']);
        }
        
        $this->unitForm['quantity'] = 0;
    }
    
    /**
     * Ajouter des vaisseaux à une planète
     */
    public function addShips()
    {
        $this->validate($this->getShipFormRules());
        
        if (!$this->selectedPlanetId) {
            $this->dispatch('admin:toast:error', ['message' => 'Aucune planète sélectionnée']);
            return;
        }
        
        $planetShip = PlanetShip::where('planet_id', $this->selectedPlanetId)
            ->where('ship_id', $this->shipForm['ship_id'])
            ->first();

        // Créer l'entrée si elle n'existe pas
        if (!$planetShip) {
            $planetShip = PlanetShip::create([
                'planet_id' => $this->selectedPlanetId,
                'ship_id' => $this->shipForm['ship_id'],
                'quantity' => 0
            ]);
        }
        
        $quantity = (int) $this->shipForm['quantity'];
        if ($quantity <= 0) {
            $this->dispatch('admin:toast:error', ['message' => 'La quantité doit être supérieure à 0']);
            return;
        }
        
        $planetShip->addShips($quantity);
        
        // Ajouter un log pour l'ajout de vaisseaux
        $shipName = $planetShip->ship->name;
        $this->logAction(
            'Ajout de vaisseaux',
            'admin',
            'Ajout de ' . $quantity . ' ' . $shipName . ' à la planète "' . $this->selectedPlanet->name . '"',
            [
                'planet_id' => $this->selectedPlanetId,
                'ship_id' => $this->shipForm['ship_id'],
                'ship_name' => $shipName,
                'quantity' => $quantity
            ]
        );
        
        $this->dispatch('admin:toast:success', ['message' => 'Vaisseaux ajoutés avec succès']);
        $this->shipForm['quantity'] = 0;
    }
    
    /**
     * Retirer des vaisseaux d'une planète
     */
    public function removeShips()
    {
        $this->validate($this->getShipFormRules());
        
        if (!$this->selectedPlanetId) {
            $this->dispatch('admin:toast:error', ['message' => 'Aucune planète sélectionnée']);
            return;
        }
        
        $planetShip = PlanetShip::where('planet_id', $this->selectedPlanetId)
            ->where('ship_id', $this->shipForm['ship_id'])
            ->first();
        
        if (!$planetShip) {
            $this->dispatch('admin:toast:error', ['message' => 'Vaisseau non trouvé sur cette planète']);
            return;
        }
        
        $quantity = (int) $this->shipForm['quantity'];
        if ($quantity <= 0) {
            $this->dispatch('admin:toast:error', ['message' => 'La quantité doit être supérieure à 0']);
            return;
        }
        
        $success = $planetShip->removeShips($quantity);
        
        if ($success) {
            // Ajouter un log pour le retrait de vaisseaux
            $shipName = $planetShip->ship->name;
            $this->logAction(
                'Retrait de vaisseaux',
                'admin',
                'Retrait de ' . $quantity . ' ' . $shipName . ' de la planète "' . $this->selectedPlanet->name . '"',
                [
                    'planet_id' => $this->selectedPlanetId,
                    'ship_id' => $this->shipForm['ship_id'],
                    'ship_name' => $shipName,
                    'quantity' => $quantity
                ]
            );
            
            $this->dispatch('admin:toast:success', ['message' => 'Vaisseaux retirés avec succès']);
        } else {
            $this->dispatch('admin:toast:error', ['message' => 'Quantité insuffisante de vaisseaux']);
        }
        
        $this->shipForm['quantity'] = 0;
    }
    
    /**
     * Ajouter des défenses à une planète
     */
    public function addDefenses()
    {
        $this->validate($this->getDefenseFormRules());
        
        if (!$this->selectedPlanetId) {
            $this->dispatch('admin:toast:error', ['message' => 'Aucune planète sélectionnée']);
            return;
        }
        
        $planetDefense = PlanetDefense::where('planet_id', $this->selectedPlanetId)
            ->where('defense_id', $this->defenseForm['defense_id'])
            ->first();

        // Créer l'entrée si elle n'existe pas
        if (!$planetDefense) {
            $planetDefense = PlanetDefense::create([
                'planet_id' => $this->selectedPlanetId,
                'defense_id' => $this->defenseForm['defense_id'],
                'quantity' => 0
            ]);
        }
        
        $quantity = (int) $this->defenseForm['quantity'];
        if ($quantity <= 0) {
            $this->dispatch('admin:toast:error', ['message' => 'La quantité doit être supérieure à 0']);
            return;
        }
        
        $planetDefense->addDefenses($quantity);
        
        // Ajouter un log pour l'ajout de défenses
        $defenseName = $planetDefense->defense->name;
        $this->logAction(
            'Ajout de défenses',
            'admin',
            'Ajout de ' . $quantity . ' ' . $defenseName . ' à la planète "' . $this->selectedPlanet->name . '"',
            [
                'planet_id' => $this->selectedPlanetId,
                'defense_id' => $this->defenseForm['defense_id'],
                'defense_name' => $defenseName,
                'quantity' => $quantity
            ]
        );
        
        $this->dispatch('admin:toast:success', ['message' => 'Défenses ajoutées avec succès']);
        $this->defenseForm['quantity'] = 0;
    }
    
    /**
     * Retirer des défenses d'une planète
     */
    public function removeDefenses()
    {
        $this->validate($this->getDefenseFormRules());
        
        if (!$this->selectedPlanetId) {
            $this->dispatch('admin:toast:error', ['message' => 'Aucune planète sélectionnée']);
            return;
        }
        
        $planetDefense = PlanetDefense::where('planet_id', $this->selectedPlanetId)
            ->where('defense_id', $this->defenseForm['defense_id'])
            ->first();
        
        if (!$planetDefense) {
            $this->dispatch('admin:toast:error', ['message' => 'Défense non trouvée sur cette planète']);
            return;
        }
        
        $quantity = (int) $this->defenseForm['quantity'];
        if ($quantity <= 0) {
            $this->dispatch('admin:toast:error', ['message' => 'La quantité doit être supérieure à 0']);
            return;
        }
        
        $success = $planetDefense->removeDefenses($quantity);
        
        if ($success) {
            // Ajouter un log pour le retrait de défenses
            $defenseName = $planetDefense->defense->name;
            $this->logAction(
                'Retrait de défenses',
                'admin',
                'Retrait de ' . $quantity . ' ' . $defenseName . ' de la planète "' . $this->selectedPlanet->name . '"',
                [
                    'planet_id' => $this->selectedPlanetId,
                    'defense_id' => $this->defenseForm['defense_id'],
                    'defense_name' => $defenseName,
                    'quantity' => $quantity
                ]
            );
            
            $this->dispatch('admin:toast:success', ['message' => 'Défenses retirées avec succès']);
        } else {
            $this->dispatch('admin:toast:error', ['message' => 'Quantité insuffisante de défenses']);
        }
        
        $this->defenseForm['quantity'] = 0;
    }
    
    /**
     * Rendu du composant
     */
    public function render()
    {
        return view('livewire.admin.planets', [
            'planets' => $this->getPlanets(),
            'users' => $this->getUsers(),
            'availableTemplatePlanets' => $this->getAvailableTemplatePlanets(),
            'planetBuildings' => $this->getPlanetBuildings(),
            'planetUnits' => $this->getPlanetUnits(),
            'planetShips' => $this->getPlanetShips(),
            'planetDefenses' => $this->getPlanetDefenses(),
            'planetResources' => $this->getPlanetResources(),
            'planetBunkers' => $this->getPlanetBunkers(),
            'planetMissions' => $this->getPlanetMissions(),
            'availableUnits' => $this->getAvailableUnits(),
            'availableShips' => $this->getAvailableShips(),
            'availableDefenses' => $this->getAvailableDefenses(),
            'availableBuildings' => $this->getAvailableBuildings(),
        ])->title('Gestion des planètes');
    }
}