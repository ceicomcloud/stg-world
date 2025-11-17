<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Faction;
use Illuminate\Support\Str;

#[Layout('components.layouts.admin')]
class Factions extends Component
{
    use WithPagination;
    
    // Propriétés pour la pagination et le filtrage
    public $search = '';
    public $perPage = 15;
    public $sortField = 'sort_order';
    public $sortDirection = 'asc';
    public $filterActive = '';
    
    // Propriétés pour l'onglet actif
    public $activeTab = 'list';
    
    // Propriétés pour la faction sélectionnée
    public $selectedFaction = null;
    public $selectedFactionId = null;
    
    // Propriétés pour le formulaire d'édition
    public $factionForm = [
        'name' => '',
        'slug' => '',
        'icon' => '',
        'banner' => '',
        'color_code' => '#3b82f6',
        'description' => '',
        'bonuses' => [
            'resource_production' => 0,
            'building_cost' => 0,
            'technology_cost' => 0,
            'ship_speed' => 0,
            'attack_power' => 0,
            'defense_power' => 0,
            'ship_capacity' => 0,
            'building_speed' => 0
        ],
        'is_active' => true,
        'sort_order' => 0
    ];
    
    /**
     * Réinitialiser la pagination lors de la recherche
     */
    public function updatingSearch()
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
        if ($tab === 'list') {
            $this->selectedFaction = null;
            $this->selectedFactionId = null;
            $this->resetFactionForm();
        } elseif ($tab === 'create') {
            $this->selectedFaction = null;
            $this->selectedFactionId = null;
            $this->resetFactionForm();
            // Définir l'ordre de tri par défaut pour une nouvelle faction
            $this->factionForm['sort_order'] = Faction::max('sort_order') + 1;
        }
    }
    
    /**
     * Réinitialiser le formulaire de faction
     */
    public function resetFactionForm()
    {
        $this->factionForm = [
            'name' => '',
            'slug' => '',
            'icon' => '',
            'banner' => '',
            'color_code' => '#3b82f6',
            'description' => '',
            'bonuses' => [
                'resource_production' => 0,
                'building_cost' => 0,
                'technology_cost' => 0,
                'ship_speed' => 0,
                'attack_power' => 0,
                'defense_power' => 0,
                'ship_capacity' => 0,
                'building_speed' => 0
            ],
            'is_active' => true,
            'sort_order' => 0
        ];
    }
    
    /**
     * Générer un slug à partir du nom
     */
    public function generateSlug()
    {
        $this->factionForm['slug'] = Str::slug($this->factionForm['name']);
    }
    
    /**
     * Sélectionner une faction pour voir/éditer ses détails
     */
    public function selectFaction($factionId)
    {
        $this->selectedFactionId = $factionId;
        $this->selectedFaction = Faction::find($factionId);
        $this->activeTab = 'edit';
        
        // Remplir le formulaire avec les données de la faction
        $this->factionForm = [
            'name' => $this->selectedFaction->name,
            'slug' => $this->selectedFaction->slug,
            'icon' => $this->selectedFaction->icon,
            'banner' => $this->selectedFaction->banner,
            'color_code' => $this->selectedFaction->color_code,
            'description' => $this->selectedFaction->description,
            'bonuses' => $this->selectedFaction->bonuses,
            'is_active' => $this->selectedFaction->is_active,
            'sort_order' => $this->selectedFaction->sort_order
        ];
    }
    
    /**
     * Créer une nouvelle faction
     */
    public function createFaction()
    {
        // Validation des données
        $this->validate([
            'factionForm.name' => 'required|string|max:255|unique:factions,name',
            'factionForm.slug' => 'required|string|max:255|unique:factions,slug',
            'factionForm.icon' => 'nullable|string|max:255',
            'factionForm.banner' => 'nullable|string|max:255',
            'factionForm.color_code' => 'required|string|max:20',
            'factionForm.description' => 'nullable|string',
            'factionForm.bonuses.resource_production' => 'numeric',
            'factionForm.bonuses.building_cost' => 'numeric',
            'factionForm.bonuses.technology_cost' => 'numeric',
            'factionForm.bonuses.ship_speed' => 'numeric',
            'factionForm.bonuses.attack_power' => 'numeric',
            'factionForm.bonuses.defense_power' => 'numeric',
            'factionForm.bonuses.ship_capacity' => 'numeric',
            'factionForm.bonuses.building_speed' => 'numeric',
            'factionForm.is_active' => 'boolean',
            'factionForm.sort_order' => 'integer'
        ]);
        
        // Création de la faction
        Faction::create([
            'name' => $this->factionForm['name'],
            'slug' => $this->factionForm['slug'],
            'icon' => $this->factionForm['icon'],
            'banner' => $this->factionForm['banner'],
            'color_code' => $this->factionForm['color_code'],
            'description' => $this->factionForm['description'],
            'bonuses' => $this->factionForm['bonuses'],
            'is_active' => $this->factionForm['is_active'],
            'sort_order' => $this->factionForm['sort_order']
        ]);
        
        // Notification et redirection
        $this->dispatch('admin:toast:success', ['message' => 'Faction créée avec succès']);
        $this->setActiveTab('list');
    }
    
    /**
     * Mettre à jour une faction existante
     */
    public function updateFaction()
    {
        // Validation des données
        $this->validate([
            'factionForm.name' => 'required|string|max:255|unique:factions,name,' . $this->selectedFactionId,
            'factionForm.slug' => 'required|string|max:255|unique:factions,slug,' . $this->selectedFactionId,
            'factionForm.icon' => 'nullable|string|max:255',
            'factionForm.banner' => 'nullable|string|max:255',
            'factionForm.color_code' => 'required|string|max:20',
            'factionForm.description' => 'nullable|string',
            'factionForm.bonuses.resource_production' => 'numeric',
            'factionForm.bonuses.building_cost' => 'numeric',
            'factionForm.bonuses.technology_cost' => 'numeric',
            'factionForm.bonuses.ship_speed' => 'numeric',
            'factionForm.bonuses.attack_power' => 'numeric',
            'factionForm.bonuses.defense_power' => 'numeric',
            'factionForm.bonuses.ship_capacity' => 'numeric',
            'factionForm.bonuses.building_speed' => 'numeric',
            'factionForm.is_active' => 'boolean',
            'factionForm.sort_order' => 'integer'
        ]);
        
        // Mise à jour de la faction
        $faction = Faction::find($this->selectedFactionId);
        $faction->update([
            'name' => $this->factionForm['name'],
            'slug' => $this->factionForm['slug'],
            'icon' => $this->factionForm['icon'],
            'banner' => $this->factionForm['banner'],
            'color_code' => $this->factionForm['color_code'],
            'description' => $this->factionForm['description'],
            'bonuses' => $this->factionForm['bonuses'],
            'is_active' => $this->factionForm['is_active'],
            'sort_order' => $this->factionForm['sort_order']
        ]);
        
        // Notification et redirection
        $this->dispatch('admin:toast:success', ['message' => 'Faction mise à jour avec succès']);
        $this->setActiveTab('list');
    }
    
    /**
     * Obtenir la liste des factions pour l'affichage
     */
    public function getFactions()
    {
        $query = Faction::query();
        
        // Appliquer la recherche
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }
        
        // Appliquer le filtre actif/inactif
        if ($this->filterActive !== '') {
            $query->where('is_active', $this->filterActive === 'active');
        }
        
        // Appliquer le tri
        $query->orderBy($this->sortField, $this->sortDirection);
        
        // Pagination
        return $query->paginate($this->perPage);
    }
    
    /**
     * Obtenir le nombre d'utilisateurs par faction
     */
    public function getUserCountByFaction()
    {
        $factions = Faction::withCount('users')->get();
        return $factions->pluck('users_count', 'id');
    }
    
    /**
     * Rendu du composant
     */
    public function render()
    {
        return view('livewire.admin.factions', [
            'factions' => $this->getFactions(),
            'userCounts' => $this->getUserCountByFaction()
        ]);
    }
}