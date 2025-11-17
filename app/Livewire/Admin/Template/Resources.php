<?php

namespace App\Livewire\Admin\Template;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Template\TemplateResource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Traits\LogsUserActions;

#[Layout('components.layouts.admin')]
class Resources extends Component
{
    use WithPagination, LogsUserActions;
    
    // Propriétés pour la pagination et le filtrage
    public $search = '';
    public $perPage = 15;
    public $sortField = 'sort_order';
    public $sortDirection = 'asc';
    public $filterActive = '';
    public $filterType = '';
    
    // Propriétés pour l'onglet actif
    public $activeTab = 'list';
    
    // Propriétés pour la ressource sélectionnée
    public $selectedResource = null;
    public $selectedResourceId = null;
    
    // Propriétés pour le formulaire de ressource
    public $resourceForm = [
        'name' => '',
        'display_name' => '',
        'description' => '',
        'icon' => '',
        'color' => '#ffffff',
        'type' => '',
        'base_production' => 0,
        'base_storage' => 0,
        'trade_rate' => 1,
        'sort_order' => 0,
        'is_tradeable' => true,
        'is_active' => true
    ];
    
    // Types de ressources disponibles
    public $resourceTypes = [
        TemplateResource::TYPE_METAL => 'Métal',
        TemplateResource::TYPE_CRYSTAL => 'Cristal',
        TemplateResource::TYPE_DEUTERIUM => 'Deutérium',
        'energy' => 'Énergie',
        'population' => 'Population',
        'special' => 'Spécial'
    ];
    
    // Règles de validation pour le formulaire de ressource
    protected $rules = [
        'resourceForm.name' => 'required|string|max:50',
        'resourceForm.display_name' => 'required|string|max:50',
        'resourceForm.description' => 'nullable|string',
        'resourceForm.icon' => 'nullable|string',
        'resourceForm.color' => 'required|string',
        'resourceForm.type' => 'required|string',
        'resourceForm.base_production' => 'required|numeric|min:0',
        'resourceForm.base_storage' => 'required|numeric|min:0',
        'resourceForm.trade_rate' => 'required|numeric|min:0',
        'resourceForm.sort_order' => 'required|integer|min:0',
        'resourceForm.is_tradeable' => 'boolean',
        'resourceForm.is_active' => 'boolean'
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
     * Définir l'onglet actif
     */
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        
        if ($tab === 'list') {
            $this->selectedResource = null;
            $this->selectedResourceId = null;
        }
        
        if ($tab === 'create') {
            $this->resetResourceForm();
        }
    }
    
    /**
     * Réinitialiser le formulaire de ressource
     */
    public function resetResourceForm()
    {
        $this->resourceForm = [
            'name' => '',
            'display_name' => '',
            'description' => '',
            'icon' => '',
            'color' => '#ffffff',
            'type' => '',
            'base_production' => 0,
            'base_storage' => 0,
            'trade_rate' => 1,
            'sort_order' => 0,
            'is_tradeable' => true,
            'is_active' => true
        ];
    }
    
    /**
     * Sélectionner une ressource pour l'édition
     */
    public function selectResource($id)
    {
        $this->selectedResourceId = $id;
        $this->selectedResource = TemplateResource::find($id);
        
        if ($this->selectedResource) {
            $this->resourceForm = [
                'name' => $this->selectedResource->name,
                'display_name' => $this->selectedResource->display_name,
                'description' => $this->selectedResource->description,
                'icon' => $this->selectedResource->icon,
                'color' => $this->selectedResource->color,
                'type' => $this->selectedResource->type,
                'base_production' => $this->selectedResource->base_production,
                'base_storage' => $this->selectedResource->base_storage,
                'trade_rate' => $this->selectedResource->trade_rate,
                'sort_order' => $this->selectedResource->sort_order,
                'is_tradeable' => $this->selectedResource->is_tradeable,
                'is_active' => $this->selectedResource->is_active
            ];
            
            $this->activeTab = 'edit';
        }
    }
    
    /**
     * Créer une nouvelle ressource
     */
    public function createResource()
    {
        $this->validate();
        
        $resource = TemplateResource::create($this->resourceForm);
        
        // Ajouter un log pour la création de ressource
        $this->logAction(
            'Création de ressource',
            'admin',
            'Création de la ressource "' . $resource->name . '"',
            [
                'resource_id' => $resource->id,
                'resource_name' => $resource->name,
                'resource_type' => $resource->type,
                'is_active' => $resource->is_active
            ]
        );
        
        $this->dispatch('admin:toast:success', ['message' => 'Ressource créée avec succès']);
        $this->resetResourceForm();
        $this->activeTab = 'list';
    }
    
    /**
     * Mettre à jour une ressource existante
     */
    public function updateResource()
    {
        $this->validate();
        
        if ($this->selectedResource) {
            // Sauvegarder les anciennes valeurs pour le log
            $oldValues = [
                'name' => $this->selectedResource->name,
                'display_name' => $this->selectedResource->display_name,
                'type' => $this->selectedResource->type,
                'base_production' => $this->selectedResource->base_production,
                'base_storage' => $this->selectedResource->base_storage,
                'trade_rate' => $this->selectedResource->trade_rate,
                'is_tradeable' => $this->selectedResource->is_tradeable,
                'is_active' => $this->selectedResource->is_active
            ];
            
            $this->selectedResource->update($this->resourceForm);
            
            // Ajouter un log pour la mise à jour de ressource
            $this->logAction(
                'Mise à jour de ressource',
                'admin',
                'Mise à jour de la ressource "' . $this->selectedResource->name . '"',
                [
                    'resource_id' => $this->selectedResource->id,
                    'old_values' => $oldValues,
                    'new_values' => $this->resourceForm
                ]
            );
            
            $this->dispatch('admin:toast:success', ['message' => 'Ressource mise à jour avec succès']);
            $this->activeTab = 'list';
        }
    }
    
    /**
     * Obtenir la liste des ressources avec pagination et filtres
     */
    public function getResources()
    {
        $query = TemplateResource::query();
        
        // Appliquer la recherche
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('display_name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }
        
        // Appliquer les filtres
        if ($this->filterActive === 'active') {
            $query->where('is_active', true);
        } elseif ($this->filterActive === 'inactive') {
            $query->where('is_active', false);
        }
        
        if (!empty($this->filterType)) {
            $query->where('type', $this->filterType);
        }
        
        // Appliquer le tri
        $query->orderBy($this->sortField, $this->sortDirection);
        
        return $query->paginate($this->perPage);
    }
    
    /**
     * Obtenir le nombre de planètes utilisant chaque ressource
     */
    public function getPlanetCountForResource($resourceId)
    {
        $resource = TemplateResource::find($resourceId);
        if ($resource) {
            return $resource->planetResources()->count();
        }
        return 0;
    }
    
    /**
     * Rendu du composant
     */
    public function render()
    {
        return view('livewire.admin.template.resources', [
            'resources' => $this->getResources(),
            'planetCounts' => collect($this->getResources()->items())->mapWithKeys(function ($resource) {
                return [$resource->id => $this->getPlanetCountForResource($resource->id)];
            })
        ]);
    }
}