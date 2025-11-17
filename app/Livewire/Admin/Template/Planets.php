<?php

namespace App\Livewire\Admin\Template;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Template\TemplatePlanet;
use App\Models\Planet\Planet;
use Illuminate\Support\Facades\DB;
use App\Traits\LogsUserActions;

#[Layout('components.layouts.admin')]
class Planets extends Component
{
    use WithPagination, LogsUserActions;
    
    // Propriétés pour la pagination et le filtrage
    public $search = '';
    public $perPage = 15;
    public $sortField = 'id';
    public $sortDirection = 'asc';
    public $filterType = '';
    public $filterSize = '';
    public $filterOccupied = '';
    public $filterColonizable = '';
    public $filterActive = '';
    public $filterGalaxy = '';
    public $filterSystem = '';
    
    // Propriétés pour l'onglet actif
    public $activeTab = 'list';
    
    // Propriétés pour la planète sélectionnée
    public $selectedPlanet = null;
    public $selectedPlanetId = null;
    
    // Propriétés pour le formulaire de planète
    public $planetForm = [
        'name' => '',
        'galaxy' => 1,
        'system' => 1,
        'position' => 1,
        'type' => 'planet',
        'size' => 'medium',
        'diameter' => 10000,
        'min_temperature' => -20,
        'max_temperature' => 40,
        'fields' => 200,
        'metal_bonus' => 1.00,
        'crystal_bonus' => 1.00,
        'deuterium_bonus' => 1.00,
        'energy_bonus' => 1.00,
        'is_colonizable' => true,
        'is_occupied' => false,
        'is_available' => true,
        'is_active' => true
    ];
    
    // Types et tailles de planètes disponibles
    public $planetTypes = [
        'planet' => 'Planète',
        'moon' => 'Lune',
        'asteroid' => 'Astéroïde',
        'debris' => 'Débris'
    ];
    
    public $planetSizes = [
        'tiny' => 'Minuscule',
        'small' => 'Petite',
        'medium' => 'Moyenne',
        'large' => 'Grande',
        'huge' => 'Énorme'
    ];
    
    // Règles de validation pour le formulaire de planète
    protected $rules = [
        'planetForm.name' => 'required|string|max:255',
        'planetForm.galaxy' => 'required|integer|min:1',
        'planetForm.system' => 'required|integer|min:1',
        'planetForm.position' => 'required|integer|min:1',
        'planetForm.type' => 'required|string|in:planet,moon,asteroid,debris',
        'planetForm.size' => 'required|string|in:tiny,small,medium,large,huge',
        'planetForm.diameter' => 'required|integer|min:1000',
        'planetForm.min_temperature' => 'required|integer',
        'planetForm.max_temperature' => 'required|integer|gte:planetForm.min_temperature',
        'planetForm.fields' => 'required|integer|min:0',
        'planetForm.metal_bonus' => 'required|numeric|min:0',
        'planetForm.crystal_bonus' => 'required|numeric|min:0',
        'planetForm.deuterium_bonus' => 'required|numeric|min:0',
        'planetForm.energy_bonus' => 'required|numeric|min:0',
        'planetForm.is_colonizable' => 'boolean',
        'planetForm.is_occupied' => 'boolean',
        'planetForm.is_available' => 'boolean',
        'planetForm.is_active' => 'boolean'
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
    public function updatingFilterType()
    {
        $this->resetPage();
    }
    
    public function updatingFilterSize()
    {
        $this->resetPage();
    }
    
    public function updatingFilterOccupied()
    {
        $this->resetPage();
    }
    
    public function updatingFilterColonizable()
    {
        $this->resetPage();
    }
    
    public function updatingFilterActive()
    {
        $this->resetPage();
    }
    
    public function updatingFilterGalaxy()
    {
        $this->resetPage();
    }
    
    public function updatingFilterSystem()
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
        if ($tab === 'create') {
            $this->resetPlanetForm();
        } elseif ($tab === 'list') {
            $this->selectedPlanet = null;
            $this->selectedPlanetId = null;
        }
    }
    
    /**
     * Réinitialiser le formulaire de planète
     */
    public function resetPlanetForm()
    {
        $this->planetForm = [
            'name' => '',
            'galaxy' => 1,
            'system' => 1,
            'position' => 1,
            'type' => 'planet',
            'size' => 'medium',
            'diameter' => 10000,
            'min_temperature' => -20,
            'max_temperature' => 40,
            'fields' => 200,
            'metal_bonus' => 1.00,
            'crystal_bonus' => 1.00,
            'deuterium_bonus' => 1.00,
            'energy_bonus' => 1.00,
            'is_colonizable' => true,
            'is_occupied' => false,
            'is_available' => true,
            'is_active' => true
        ];
        $this->resetErrorBag();
    }
    
    /**
     * Sélectionner une planète pour l'édition
     */
    public function selectPlanet($id)
    {
        $this->selectedPlanetId = $id;
        $this->selectedPlanet = TemplatePlanet::find($id);
        
        if ($this->selectedPlanet) {
            $this->planetForm = [
                'name' => $this->selectedPlanet->name,
                'galaxy' => $this->selectedPlanet->galaxy,
                'system' => $this->selectedPlanet->system,
                'position' => $this->selectedPlanet->position,
                'type' => $this->selectedPlanet->type,
                'size' => $this->selectedPlanet->size,
                'diameter' => $this->selectedPlanet->diameter,
                'min_temperature' => $this->selectedPlanet->min_temperature,
                'max_temperature' => $this->selectedPlanet->max_temperature,
                'fields' => $this->selectedPlanet->fields,
                'metal_bonus' => $this->selectedPlanet->metal_bonus,
                'crystal_bonus' => $this->selectedPlanet->crystal_bonus,
                'deuterium_bonus' => $this->selectedPlanet->deuterium_bonus,
                'energy_bonus' => $this->selectedPlanet->energy_bonus,
                'is_colonizable' => $this->selectedPlanet->is_colonizable,
                'is_occupied' => $this->selectedPlanet->is_occupied,
                'is_available' => $this->selectedPlanet->is_available,
                'is_active' => $this->selectedPlanet->is_active
            ];
            $this->activeTab = 'edit';
        }
    }
    
    /**
     * Créer une nouvelle planète
     */
    public function createPlanet()
    {
        $this->validate();
        
        // Vérifier si les coordonnées sont déjà utilisées
        $exists = TemplatePlanet::where('galaxy', $this->planetForm['galaxy'])
            ->where('system', $this->planetForm['system'])
            ->where('position', $this->planetForm['position'])
            ->exists();
        
        if ($exists) {
            $this->dispatch('admin:toast:error', ['message' => 'Ces coordonnées sont déjà utilisées']);
            return;
        }
        
        // Créer la planète
        $planet = new TemplatePlanet();
        $planet->name = $this->planetForm['name'];
        $planet->galaxy = $this->planetForm['galaxy'];
        $planet->system = $this->planetForm['system'];
        $planet->position = $this->planetForm['position'];
        $planet->type = $this->planetForm['type'];
        $planet->size = $this->planetForm['size'];
        $planet->diameter = $this->planetForm['diameter'];
        $planet->min_temperature = $this->planetForm['min_temperature'];
        $planet->max_temperature = $this->planetForm['max_temperature'];
        $planet->fields = $this->planetForm['fields'];
        $planet->metal_bonus = $this->planetForm['metal_bonus'];
        $planet->crystal_bonus = $this->planetForm['crystal_bonus'];
        $planet->deuterium_bonus = $this->planetForm['deuterium_bonus'];
        $planet->energy_bonus = $this->planetForm['energy_bonus'];
        $planet->is_colonizable = $this->planetForm['is_colonizable'];
        $planet->is_occupied = $this->planetForm['is_occupied'];
        $planet->is_available = $this->planetForm['is_available'];
        $planet->is_active = $this->planetForm['is_active'];
        $planet->save();
        
        // Log de l'action
        $this->logAction(
            'create',
            'admin_template_planet',
            'Création d\'une planète modèle',
            [
                'planet_id' => $planet->id,
                'name' => $planet->name,
                'coordinates' => "{$planet->galaxy}:{$planet->system}:{$planet->position}",
                'type' => $planet->type,
                'size' => $planet->size,
                'is_active' => $planet->is_active
            ]
        );
        
        $this->dispatch('admin:toast:success', ['message' => 'Planète créée avec succès']);
        $this->resetPlanetForm();
        $this->setActiveTab('list');
    }
    
    /**
     * Mettre à jour une planète existante
     */
    public function updatePlanet()
    {
        $this->validate();
        
        // Vérifier si les coordonnées sont déjà utilisées par une autre planète
        $exists = TemplatePlanet::where('galaxy', $this->planetForm['galaxy'])
            ->where('system', $this->planetForm['system'])
            ->where('position', $this->planetForm['position'])
            ->where('id', '!=', $this->selectedPlanetId)
            ->exists();
        
        if ($exists) {
            $this->dispatch('admin:toast:error', ['message' => 'Ces coordonnées sont déjà utilisées']);
            return;
        }
        
        // Mettre à jour la planète
        $planet = TemplatePlanet::find($this->selectedPlanetId);
        if (!$planet) {
            $this->dispatch('admin:toast:error', ['message' => 'Planète non trouvée']);
            return;
        }
        
        // Sauvegarder les anciennes valeurs pour le log
        $oldValues = [
            'name' => $planet->name,
            'coordinates' => "{$planet->galaxy}:{$planet->system}:{$planet->position}",
            'type' => $planet->type,
            'size' => $planet->size,
            'is_active' => $planet->is_active
        ];
        
        $planet->name = $this->planetForm['name'];
        $planet->galaxy = $this->planetForm['galaxy'];
        $planet->system = $this->planetForm['system'];
        $planet->position = $this->planetForm['position'];
        $planet->type = $this->planetForm['type'];
        $planet->size = $this->planetForm['size'];
        $planet->diameter = $this->planetForm['diameter'];
        $planet->min_temperature = $this->planetForm['min_temperature'];
        $planet->max_temperature = $this->planetForm['max_temperature'];
        $planet->fields = $this->planetForm['fields'];
        $planet->metal_bonus = $this->planetForm['metal_bonus'];
        $planet->crystal_bonus = $this->planetForm['crystal_bonus'];
        $planet->deuterium_bonus = $this->planetForm['deuterium_bonus'];
        $planet->energy_bonus = $this->planetForm['energy_bonus'];
        $planet->is_colonizable = $this->planetForm['is_colonizable'];
        $planet->is_occupied = $this->planetForm['is_occupied'];
        $planet->is_available = $this->planetForm['is_available'];
        $planet->is_active = $this->planetForm['is_active'];
        $planet->save();
        
        // Log de l'action
        $this->logAction(
            'update',
            'admin_template_planet',
            'Mise à jour d\'une planète modèle',
            [
                'planet_id' => $planet->id,
                'old_values' => $oldValues,
                'new_values' => [
                    'name' => $planet->name,
                    'coordinates' => "{$planet->galaxy}:{$planet->system}:{$planet->position}",
                    'type' => $planet->type,
                    'size' => $planet->size,
                    'is_active' => $planet->is_active
                ]
            ]
        );
        
        $this->dispatch('admin:toast:success', ['message' => 'Planète mise à jour avec succès']);
        $this->selectedPlanet = $planet;
    }
    
    /**
     * Supprimer une planète
     */
    public function deletePlanet($id)
    {
        // Vérifier si la planète est utilisée par des planètes de joueurs
        $usedByPlanets = Planet::where('template_planet_id', $id)->exists();
        
        if ($usedByPlanets) {
            $this->dispatch('admin:toast:error', ['message' => 'Cette planète est utilisée par des joueurs et ne peut pas être supprimée']);
            return;
        }
        
        // Supprimer la planète
        $planet = TemplatePlanet::find($id);
        if ($planet) {
            // Sauvegarder les informations pour le log
            $planetInfo = [
                'planet_id' => $planet->id,
                'name' => $planet->name,
                'coordinates' => "{$planet->galaxy}:{$planet->system}:{$planet->position}",
                'type' => $planet->type,
                'size' => $planet->size
            ];
            
            $planet->delete();
            
            // Log de l'action
            $this->logAction(
                'delete',
                'admin_template_planet',
                'Suppression d\'une planète modèle',
                $planetInfo
            );
            
            $this->dispatch('admin:toast:success', ['message' => 'Planète supprimée avec succès']);
            
            if ($this->selectedPlanetId === $id) {
                $this->selectedPlanet = null;
                $this->selectedPlanetId = null;
                $this->setActiveTab('list');
            }
        }
    }
    
    /**
     * Générer le nom de la planète en fonction de sa position
     */
    public function generatePlanetName()
    {
        $prefixes = [
            'Alpha', 'Beta', 'Gamma', 'Delta', 'Epsilon', 'Zeta', 'Eta', 'Theta',
            'Nova', 'Stellar', 'Cosmic', 'Nebula', 'Orion', 'Vega', 'Sirius', 'Rigel',
            'Proxima', 'Centauri', 'Andromeda', 'Cassiopeia', 'Perseus', 'Draco'
        ];
        
        $suffixes = [
            'Prime', 'Major', 'Minor', 'Secundus', 'Tertius', 'Quartus',
            'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'
        ];
        
        $prefix = $prefixes[array_rand($prefixes)];
        $suffix = $suffixes[($this->planetForm['position'] - 1) % count($suffixes)];
        
        $this->planetForm['name'] = "{$prefix} {$suffix}";
    }
    
    /**
     * Calculer les propriétés de la planète en fonction de sa taille et de sa position
     */
    public function calculatePlanetProperties()
    {
        // Diamètres basés sur la taille
        $diameters = [
            'tiny' => rand(4000, 6000),
            'small' => rand(6000, 8000),
            'medium' => rand(8000, 12000),
            'large' => rand(12000, 16000),
            'huge' => rand(16000, 20000)
        ];
        
        // Champs de construction basés sur la taille
        $fields = [
            'tiny' => rand(80, 120),
            'small' => rand(120, 180),
            'medium' => rand(180, 250),
            'large' => rand(250, 320),
            'huge' => rand(320, 400)
        ];
        
        // Température basée sur la position dans le système (plus proche du soleil = plus chaud)
        $baseTemp = 50 - ($this->planetForm['position'] * 15); // Position 1 = ~35°C, Position 10 = ~-100°C
        $tempVariation = rand(-20, 20);
        $minTemp = $baseTemp + $tempVariation - 30;
        $maxTemp = $baseTemp + $tempVariation + 30;
        
        // Bonus de ressources basés sur le type et la position
        $metalBonus = 1.0;
        $crystalBonus = 1.0;
        $deuteriumBonus = 1.0;
        $energyBonus = 1.0;
        
        if ($this->planetForm['type'] === 'planet') {
            // Les planètes proches du soleil ont plus de métal et d'énergie
            if ($this->planetForm['position'] <= 3) {
                $metalBonus = rand(110, 130) / 100;
                $energyBonus = rand(105, 120) / 100;
            }
            // Les planètes moyennes ont plus de cristal
            elseif ($this->planetForm['position'] >= 4 && $this->planetForm['position'] <= 7) {
                $crystalBonus = rand(110, 130) / 100;
            }
            // Les planètes éloignées ont plus de deutérium
            else {
                $deuteriumBonus = rand(110, 150) / 100;
            }
        }
        
        // Ajustements pour les astéroïdes et débris
        if ($this->planetForm['type'] === 'asteroid') {
            $fields[$this->planetForm['size']] = (int)($fields[$this->planetForm['size']] * 0.3); // Moins de champs
            $metalBonus *= 1.5; // Plus de métal
        } elseif ($this->planetForm['type'] === 'debris') {
            $fields[$this->planetForm['size']] = 0; // Pas de champs constructibles
            $metalBonus *= 0.5;
            $crystalBonus *= 0.5;
        }
        
        // Mettre à jour le formulaire avec les valeurs calculées
        $this->planetForm['diameter'] = $diameters[$this->planetForm['size']];
        $this->planetForm['min_temperature'] = $minTemp;
        $this->planetForm['max_temperature'] = $maxTemp;
        $this->planetForm['fields'] = $fields[$this->planetForm['size']];
        $this->planetForm['metal_bonus'] = round($metalBonus, 2);
        $this->planetForm['crystal_bonus'] = round($crystalBonus, 2);
        $this->planetForm['deuterium_bonus'] = round($deuteriumBonus, 2);
        $this->planetForm['energy_bonus'] = round($energyBonus, 2);
    }
    
    /**
     * Obtenir les planètes pour l'affichage
     */
    public function getPlanetsProperty()
    {
        return TemplatePlanet::when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere(DB::raw("CONCAT(galaxy, ':', system, ':', position)"), 'like', '%' . $this->search . '%');
            })
            ->when($this->filterType, function ($query) {
                $query->where('type', $this->filterType);
            })
            ->when($this->filterSize, function ($query) {
                $query->where('size', $this->filterSize);
            })
            ->when($this->filterOccupied !== '', function ($query) {
                $query->where('is_occupied', $this->filterOccupied === 'occupied');
            })
            ->when($this->filterColonizable !== '', function ($query) {
                $query->where('is_colonizable', $this->filterColonizable === 'colonizable');
            })
            ->when($this->filterActive !== '', function ($query) {
                $query->where('is_active', $this->filterActive === 'active');
            })
            ->when($this->filterGalaxy, function ($query) {
                $query->where('galaxy', $this->filterGalaxy);
            })
            ->when($this->filterSystem, function ($query) {
                $query->where('system', $this->filterSystem);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }
    
    /**
     * Obtenir les galaxies disponibles pour le filtre
     */
    public function getGalaxiesProperty()
    {
        return TemplatePlanet::select('galaxy')->distinct()->orderBy('galaxy')->pluck('galaxy');
    }
    
    /**
     * Obtenir les systèmes disponibles pour le filtre
     */
    public function getSystemsProperty()
    {
        $query = TemplatePlanet::select('system')->distinct();
        
        if ($this->filterGalaxy) {
            $query->where('galaxy', $this->filterGalaxy);
        }
        
        return $query->orderBy('system')->pluck('system');
    }
    
    /**
     * Obtenir le nombre de planètes occupées
     */
    public function getOccupiedPlanetsCountProperty()
    {
        return TemplatePlanet::where('is_occupied', true)->count();
    }
    
    /**
     * Obtenir le nombre de planètes libres
     */
    public function getFreePlanetsCountProperty()
    {
        return TemplatePlanet::where('is_occupied', false)->count();
    }
    
    /**
     * Obtenir le nombre de planètes colonisables
     */
    public function getColonizablePlanetsCountProperty()
    {
        return TemplatePlanet::where('is_colonizable', true)->count();
    }
    
    /**
     * Obtenir le nombre de planètes par type
     */
    public function getPlanetTypeCountsProperty()
    {
        $counts = [];
        foreach ($this->planetTypes as $type => $label) {
            $counts[$type] = TemplatePlanet::where('type', $type)->count();
        }
        return $counts;
    }
    
    /**
     * Obtenir le nombre de planètes par taille
     */
    public function getPlanetSizeCountsProperty()
    {
        $counts = [];
        foreach ($this->planetSizes as $size => $label) {
            $counts[$size] = TemplatePlanet::where('size', $size)->count();
        }
        return $counts;
    }
    
    /**
     * Rendu du composant
     */
    public function render()
    {
        return view('livewire.admin.template.planets', [
            'planets' => $this->planets,
            'galaxies' => $this->galaxies,
            'systems' => $this->systems,
            'occupiedPlanetsCount' => $this->occupiedPlanetsCount,
            'freePlanetsCount' => $this->freePlanetsCount,
            'colonizablePlanetsCount' => $this->colonizablePlanetsCount,
            'planetTypeCounts' => $this->planetTypeCounts,
            'planetSizeCounts' => $this->planetSizeCounts
        ]);
    }
}