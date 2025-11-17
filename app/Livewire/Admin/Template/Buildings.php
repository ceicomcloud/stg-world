<?php

namespace App\Livewire\Admin\Template;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Template\TemplateBuild;
use App\Models\Template\TemplateBuildCost;
use App\Models\Template\TemplateBuildRequired;
use App\Models\Template\TemplateBuildAdvantage;
use App\Models\Template\TemplateBuildDisadvantage;
use App\Models\Template\TemplateResource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

#[Layout('components.layouts.admin')]
class Buildings extends Component
{
    use WithPagination;
    
    // Propriétés pour la pagination et le filtrage
    public $search = '';
    public $perPage = 15;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $filterActive = '';
    public $filterType = '';
    public $filterCategory = '';
    
    // Propriétés pour l'onglet actif
    public $activeTab = 'list';
    
    // Propriétés pour le bâtiment sélectionné
    public $selectedBuild = null;
    public $selectedBuildId = null;
    
    // Propriétés pour le formulaire de bâtiment
    public $buildForm = [
        'uuid' => '',
        'name' => '',
        'label' => '',
        'description' => '',
        'type' => '',
        'category' => '',
        'icon' => '',
        'max_level' => 1,
        'base_build_time' => 0,
        'is_active' => true
    ];
    
    // Propriétés pour les coûts, prérequis, avantages et désavantages
    public $costs = [];
    public $requirements = [];
    public $advantages = [];
    public $disadvantages = [];
    
    // Propriétés pour les formulaires d'ajout
    public $newCost = [
        'resource_id' => '',
        'base_cost' => 0,
        'cost_multiplier' => 1.5,
        'level' => 1
    ];
    
    public $newRequirement = [
        'required_build_id' => '',
        'required_level' => 1,
        'is_active' => true
    ];
    
    public $newAdvantage = [
        'resource_id' => null,
        'advantage_type' => '',
        'target_type' => '',
        'base_value' => 0,
        'value_per_level' => 0,
        'calculation_type' => 'additive',
        'is_percentage' => false,
        'is_active' => true
    ];
    
    public $newDisadvantage = [
        'resource_id' => null,
        'disadvantage_type' => '',
        'target_type' => '',
        'base_value' => 0,
        'value_per_level' => 0,
        'calculation_type' => 'additive',
        'is_percentage' => false,
        'is_active' => true
    ];
    
    // Types et catégories disponibles
    public $buildTypes = [
        TemplateBuild::TYPE_BUILDING => 'Bâtiment',
        TemplateBuild::TYPE_RESEARCH => 'Recherche',
        TemplateBuild::TYPE_UNIT => 'Unité',
        TemplateBuild::TYPE_DEFENSE => 'Défense',
        TemplateBuild::TYPE_SHIP => 'Vaisseau'
    ];
    
    public $buildCategories = [
        TemplateBuild::CATEGORY_RESOURCE => 'Ressource',
        TemplateBuild::CATEGORY_FACILITY => 'Installation',
        TemplateBuild::CATEGORY_MILITARY => 'Militaire',
        TemplateBuild::CATEGORY_RESEARCH => 'Recherche',
        TemplateBuild::CATEGORY_SHIPYARD => 'Chantier spatial'
    ];
    
    public $advantageTypes = [];
    public $disadvantageTypes = [];
    public $targetTypes = [];
    public $calculationTypes = [];
    
    // Règles de validation pour le formulaire de bâtiment
    protected $rules = [
        'buildForm.name' => 'required|string|max:50',
        'buildForm.label' => 'required|string|max:50',
        'buildForm.description' => 'nullable|string',
        'buildForm.type' => 'required|string',
        'buildForm.category' => 'required|string',
        'buildForm.icon' => 'nullable|string',
        'buildForm.max_level' => 'required|integer|min:1',
        'buildForm.base_build_time' => 'required|integer|min:0',
        'buildForm.is_active' => 'boolean',
        
        'newCost.resource_id' => 'required|integer|exists:template_resources,id',
        'newCost.base_cost' => 'required|numeric|min:0',
        'newCost.cost_multiplier' => 'required|numeric|min:1',
        'newCost.level' => 'required|integer|min:1',
        
        'newRequirement.required_build_id' => 'required|integer|exists:template_builds,id',
        'newRequirement.required_level' => 'required|integer|min:1',
        'newRequirement.is_active' => 'boolean',
        
        'newAdvantage.resource_id' => 'nullable|integer|exists:template_resources,id',
        'newAdvantage.advantage_type' => 'required|string',
        'newAdvantage.target_type' => 'required|string',
        'newAdvantage.base_value' => 'required|numeric|min:0',
        'newAdvantage.value_per_level' => 'required|numeric|min:0',
        'newAdvantage.calculation_type' => 'required|string',
        'newAdvantage.is_percentage' => 'boolean',
        'newAdvantage.is_active' => 'boolean',
        
        'newDisadvantage.resource_id' => 'nullable|integer|exists:template_resources,id',
        'newDisadvantage.disadvantage_type' => 'required|string',
        'newDisadvantage.target_type' => 'required|string',
        'newDisadvantage.base_value' => 'required|numeric|min:0',
        'newDisadvantage.value_per_level' => 'required|numeric|min:0',
        'newDisadvantage.calculation_type' => 'required|string',
        'newDisadvantage.is_percentage' => 'boolean',
        'newDisadvantage.is_active' => 'boolean'
    ];
    
    /**
     * Initialiser les propriétés du composant
     */
    public function mount()
    {
        // Initialiser les types d'avantages
        $this->advantageTypes = [
            TemplateBuildAdvantage::TYPE_PRODUCTION_BOOST => 'Boost de production',
            TemplateBuildAdvantage::TYPE_STORAGE_BONUS => 'Bonus de stockage',
            TemplateBuildAdvantage::TYPE_ENERGY_PRODUCTION => 'Production d\'énergie',
            TemplateBuildAdvantage::TYPE_RESEARCH_SPEED => 'Vitesse de recherche',
            TemplateBuildAdvantage::TYPE_BUILD_SPEED => 'Vitesse de construction',
            TemplateBuildAdvantage::TYPE_DEFENSE_BOOST => 'Boost défensif',
            TemplateBuildAdvantage::TYPE_ATTACK_BOOST => 'Boost offensif',
            TemplateBuildAdvantage::TYPE_CAPACITY_INCREASE => 'Augmentation de capacité',
            TemplateBuildAdvantage::TYPE_SHIELD_BONUS => 'Bonus de bouclier',
            TemplateBuildAdvantage::TYPE_SPEED_BONUS => 'Bonus de vitesse',
            TemplateBuildAdvantage::TYPE_BUNKER_BOOST => 'Boost de bunker',
            TemplateBuildAdvantage::TYPE_GLOBAL_EFFICIENCY => 'Efficacité globale'
        ];
        
        // Initialiser les types de désavantages
        $this->disadvantageTypes = [
            TemplateBuildDisadvantage::TYPE_ENERGY_CONSUMPTION => 'Consommation d\'énergie',
            TemplateBuildDisadvantage::TYPE_MAINTENANCE_COST => 'Coût de maintenance',
            TemplateBuildDisadvantage::TYPE_PRODUCTION_PENALTY => 'Pénalité de production',
            TemplateBuildDisadvantage::TYPE_STORAGE_PENALTY => 'Pénalité de stockage',
            TemplateBuildDisadvantage::TYPE_RESEARCH_PENALTY => 'Pénalité de recherche',
            TemplateBuildDisadvantage::TYPE_BUILD_PENALTY => 'Pénalité de construction',
            TemplateBuildDisadvantage::TYPE_DEFENSE_PENALTY => 'Pénalité défensive',
            TemplateBuildDisadvantage::TYPE_ATTACK_PENALTY => 'Pénalité offensive',
            TemplateBuildDisadvantage::TYPE_SPEED_PENALTY => 'Pénalité de vitesse',
            TemplateBuildDisadvantage::TYPE_RESOURCE_CONSUMPTION => 'Consommation de ressources'
        ];
        
        // Initialiser les types de cibles
        $this->targetTypes = [
            TemplateBuildAdvantage::TARGET_RESOURCE => 'Ressource',
            TemplateBuildAdvantage::TARGET_BUILD => 'Bâtiment',
            TemplateBuildAdvantage::TARGET_RESEARCH => 'Recherche',
            TemplateBuildAdvantage::TARGET_TECHNOLOGY => 'Technologie',
            TemplateBuildAdvantage::TARGET_UNIT => 'Unité',
            TemplateBuildAdvantage::TARGET_DEFENSE => 'Défense',
            TemplateBuildAdvantage::TARGET_SHIP => 'Vaisseau',
            TemplateBuildAdvantage::TARGET_PLANET => 'Planète',
            TemplateBuildAdvantage::TARGET_GLOBAL => 'Global'
        ];
        
        // Initialiser les types de calcul
        $this->calculationTypes = [
            TemplateBuildAdvantage::CALC_ADDITIVE => 'Additif',
            TemplateBuildAdvantage::CALC_MULTIPLICATIVE => 'Multiplicatif',
            TemplateBuildAdvantage::CALC_EXPONENTIAL => 'Exponentiel'
        ];
        
        // Générer un UUID pour un nouveau bâtiment
        $this->buildForm['uuid'] = (string) Str::uuid();
    }
    
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
    
    public function updatingFilterCategory()
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
            $this->selectedBuild = null;
            $this->selectedBuildId = null;
            $this->resetBuildForm();
            $this->resetRelatedData();
        }
        
        if ($tab === 'create') {
            $this->resetBuildForm();
            $this->resetRelatedData();
            // Générer un nouvel UUID
            $this->buildForm['uuid'] = (string) Str::uuid();
        }
    }
    
    /**
     * Réinitialiser le formulaire de bâtiment
     */
    public function resetBuildForm()
    {
        $this->buildForm = [
            'uuid' => (string) Str::uuid(),
            'name' => '',
            'label' => '',
            'description' => '',
            'type' => '',
            'category' => '',
            'icon' => '',
            'max_level' => 1,
            'base_build_time' => 0,
            'is_active' => true
        ];
    }
    
    /**
     * Réinitialiser les données liées (coûts, prérequis, avantages, désavantages)
     */
    public function resetRelatedData()
    {
        $this->costs = [];
        $this->requirements = [];
        $this->advantages = [];
        $this->disadvantages = [];
        
        $this->newCost = [
            'resource_id' => '',
            'base_cost' => 0,
            'cost_multiplier' => 1.5,
            'level' => 1
        ];
        
        $this->newRequirement = [
            'required_build_id' => '',
            'required_level' => 1,
            'is_active' => true
        ];
        
        $this->newAdvantage = [
            'resource_id' => null,
            'advantage_type' => '',
            'target_type' => '',
            'base_value' => 0,
            'value_per_level' => 0,
            'calculation_type' => 'additive',
            'is_percentage' => false,
            'is_active' => true
        ];
        
        $this->newDisadvantage = [
            'resource_id' => null,
            'disadvantage_type' => '',
            'target_type' => '',
            'base_value' => 0,
            'value_per_level' => 0,
            'calculation_type' => 'additive',
            'is_percentage' => false,
            'is_active' => true
        ];
    }
    
    /**
     * Sélectionner un bâtiment pour l'édition
     */
    public function selectBuild($id)
    {
        $this->selectedBuildId = $id;
        $this->selectedBuild = TemplateBuild::find($id);
        
        if ($this->selectedBuild) {
            $this->buildForm = [
                'uuid' => $this->selectedBuild->uuid,
                'name' => $this->selectedBuild->name,
                'label' => $this->selectedBuild->label,
                'description' => $this->selectedBuild->description,
                'type' => $this->selectedBuild->type,
                'category' => $this->selectedBuild->category,
                'icon' => $this->selectedBuild->icon,
                'max_level' => $this->selectedBuild->max_level,
                'base_build_time' => $this->selectedBuild->base_build_time,
                'is_active' => $this->selectedBuild->is_active
            ];
            
            // Charger les coûts, prérequis, avantages et désavantages
            $this->loadRelatedData();
            
            $this->activeTab = 'edit';
        }
    }
    
    /**
     * Charger les données liées au bâtiment sélectionné
     */
    public function loadRelatedData()
    {
        if (!$this->selectedBuild) {
            return;
        }
        
        // Charger les coûts
        $this->costs = $this->selectedBuild->costs()->get()->toArray();
        
        // Charger les prérequis
        $this->requirements = $this->selectedBuild->requirements()->get()->toArray();
        
        // Charger les avantages
        $this->advantages = $this->selectedBuild->advantages()->get()->toArray();
        
        // Charger les désavantages
        $this->disadvantages = $this->selectedBuild->disadvantages()->get()->toArray();
    }
    
    /**
     * Créer un nouveau bâtiment
     */
    public function createBuild()
    {
        $this->validate([
            'buildForm.name' => 'required|string|max:50',
            'buildForm.label' => 'required|string|max:50',
            'buildForm.description' => 'nullable|string',
            'buildForm.type' => 'required|string',
            'buildForm.category' => 'required|string',
            'buildForm.icon' => 'nullable|string',
            'buildForm.max_level' => 'required|integer|min:1',
            'buildForm.base_build_time' => 'required|integer|min:0',
            'buildForm.is_active' => 'boolean'
        ]);
        
        $build = TemplateBuild::create($this->buildForm);
        
        $this->dispatch('admin:toast:success', ['message' => 'Bâtiment créé avec succès']);
        $this->selectedBuildId = $build->id;
        $this->selectedBuild = $build;
        $this->activeTab = 'edit';
    }
    
    /**
     * Mettre à jour un bâtiment existant
     */
    public function updateBuild()
    {
        $this->validate([
            'buildForm.name' => 'required|string|max:50',
            'buildForm.label' => 'required|string|max:50',
            'buildForm.description' => 'nullable|string',
            'buildForm.type' => 'required|string',
            'buildForm.category' => 'required|string',
            'buildForm.icon' => 'nullable|string',
            'buildForm.max_level' => 'required|integer|min:1',
            'buildForm.base_build_time' => 'required|integer|min:0',
            'buildForm.is_active' => 'boolean'
        ]);
        
        if ($this->selectedBuild) {
            $this->selectedBuild->update($this->buildForm);
            $this->dispatch('admin:toast:success', ['message' => 'Bâtiment mis à jour avec succès']);
        }
    }
    
    /**
     * Ajouter un coût au bâtiment
     */
    public function addCost()
    {
        $this->validate([
            'newCost.resource_id' => 'required|integer|exists:template_resources,id',
            'newCost.base_cost' => 'required|numeric|min:0',
            'newCost.cost_multiplier' => 'required|numeric|min:1',
            'newCost.level' => 'required|integer|min:1'
        ]);
        
        if ($this->selectedBuild) {
            $cost = $this->selectedBuild->costs()->create([
                'resource_id' => $this->newCost['resource_id'],
                'base_cost' => $this->newCost['base_cost'],
                'cost_multiplier' => $this->newCost['cost_multiplier'],
                'level' => $this->newCost['level']
            ]);
            
            // Recharger les coûts
            $this->costs = $this->selectedBuild->costs()->get()->toArray();
            
            // Réinitialiser le formulaire de coût
            $this->newCost = [
                'resource_id' => '',
                'base_cost' => 0,
                'cost_multiplier' => 1.5,
                'level' => 1
            ];
            
            $this->dispatch('admin:toast:success', ['message' => 'Coût ajouté avec succès']);
        }
    }
    
    /**
     * Supprimer un coût
     */
    public function deleteCost($costId)
    {
        $cost = TemplateBuildCost::find($costId);
        if ($cost && $cost->build_id == $this->selectedBuildId) {
            $cost->delete();
            
            // Recharger les coûts
            $this->costs = $this->selectedBuild->costs()->get()->toArray();
            
            $this->dispatch('admin:toast:success', ['message' => 'Coût supprimé avec succès']);
        }
    }
    
    /**
     * Ajouter un prérequis au bâtiment
     */
    public function addRequirement()
    {
        $this->validate([
            'newRequirement.required_build_id' => 'required|integer|exists:template_builds,id',
            'newRequirement.required_level' => 'required|integer|min:1',
            'newRequirement.is_active' => 'boolean'
        ]);
        
        if ($this->selectedBuild) {
            // Vérifier que le bâtiment requis n'est pas le bâtiment lui-même
            if ($this->newRequirement['required_build_id'] == $this->selectedBuildId) {
                $this->dispatch('admin:toast:error', ['message' => 'Un bâtiment ne peut pas être son propre prérequis']);
                return;
            }
            
            $requirement = $this->selectedBuild->requirements()->create([
                'required_build_id' => $this->newRequirement['required_build_id'],
                'required_level' => $this->newRequirement['required_level'],
                'is_active' => $this->newRequirement['is_active']
            ]);
            
            // Recharger les prérequis
            $this->requirements = $this->selectedBuild->requirements()->get()->toArray();
            
            // Réinitialiser le formulaire de prérequis
            $this->newRequirement = [
                'required_build_id' => '',
                'required_level' => 1,
                'is_active' => true
            ];
            
            $this->dispatch('admin:toast:success', ['message' => 'Prérequis ajouté avec succès']);
        }
    }
    
    /**
     * Supprimer un prérequis
     */
    public function deleteRequirement($requirementId)
    {
        $requirement = TemplateBuildRequired::find($requirementId);
        if ($requirement && $requirement->build_id == $this->selectedBuildId) {
            $requirement->delete();
            
            // Recharger les prérequis
            $this->requirements = $this->selectedBuild->requirements()->get()->toArray();
            
            $this->dispatch('admin:toast:success', ['message' => 'Prérequis supprimé avec succès']);
        }
    }
    
    /**
     * Ajouter un avantage au bâtiment
     */
    public function addAdvantage()
    {
        $this->validate([
            'newAdvantage.resource_id' => 'nullable|integer|exists:template_resources,id',
            'newAdvantage.advantage_type' => 'required|string',
            'newAdvantage.target_type' => 'required|string',
            'newAdvantage.base_value' => 'required|numeric|min:0',
            'newAdvantage.value_per_level' => 'required|numeric|min:0',
            'newAdvantage.calculation_type' => 'required|string',
            'newAdvantage.is_percentage' => 'boolean',
            'newAdvantage.is_active' => 'boolean'
        ]);
        
        if ($this->selectedBuild) {
            $advantage = $this->selectedBuild->advantages()->create([
                'resource_id' => $this->newAdvantage['resource_id'] ?: null,
                'advantage_type' => $this->newAdvantage['advantage_type'],
                'target_type' => $this->newAdvantage['target_type'],
                'base_value' => $this->newAdvantage['base_value'],
                'value_per_level' => $this->newAdvantage['value_per_level'],
                'calculation_type' => $this->newAdvantage['calculation_type'],
                'is_percentage' => $this->newAdvantage['is_percentage'],
                'is_active' => $this->newAdvantage['is_active']
            ]);
            
            // Recharger les avantages
            $this->advantages = $this->selectedBuild->advantages()->get()->toArray();
            
            // Réinitialiser le formulaire d'avantage
            $this->newAdvantage = [
                'resource_id' => null,
                'advantage_type' => '',
                'target_type' => '',
                'base_value' => 0,
                'value_per_level' => 0,
                'calculation_type' => 'additive',
                'is_percentage' => false,
                'is_active' => true
            ];
            
            $this->dispatch('admin:toast:success', ['message' => 'Avantage ajouté avec succès']);
        }
    }
    
    /**
     * Supprimer un avantage
     */
    public function deleteAdvantage($advantageId)
    {
        $advantage = TemplateBuildAdvantage::find($advantageId);
        if ($advantage && $advantage->build_id == $this->selectedBuildId) {
            $advantage->delete();
            
            // Recharger les avantages
            $this->advantages = $this->selectedBuild->advantages()->get()->toArray();
            
            $this->dispatch('admin:toast:success', ['message' => 'Avantage supprimé avec succès']);
        }
    }
    
    /**
     * Ajouter un désavantage au bâtiment
     */
    public function addDisadvantage()
    {
        $this->validate([
            'newDisadvantage.resource_id' => 'nullable|integer|exists:template_resources,id',
            'newDisadvantage.disadvantage_type' => 'required|string',
            'newDisadvantage.target_type' => 'required|string',
            'newDisadvantage.base_value' => 'required|numeric|min:0',
            'newDisadvantage.value_per_level' => 'required|numeric|min:0',
            'newDisadvantage.calculation_type' => 'required|string',
            'newDisadvantage.is_percentage' => 'boolean',
            'newDisadvantage.is_active' => 'boolean'
        ]);
        
        if ($this->selectedBuild) {
            $disadvantage = $this->selectedBuild->disadvantages()->create([
                'resource_id' => $this->newDisadvantage['resource_id'] ?: null,
                'disadvantage_type' => $this->newDisadvantage['disadvantage_type'],
                'target_type' => $this->newDisadvantage['target_type'],
                'base_value' => $this->newDisadvantage['base_value'],
                'value_per_level' => $this->newDisadvantage['value_per_level'],
                'calculation_type' => $this->newDisadvantage['calculation_type'],
                'is_percentage' => $this->newDisadvantage['is_percentage'],
                'is_active' => $this->newDisadvantage['is_active']
            ]);
            
            // Recharger les désavantages
            $this->disadvantages = $this->selectedBuild->disadvantages()->get()->toArray();
            
            // Réinitialiser le formulaire de désavantage
            $this->newDisadvantage = [
                'resource_id' => null,
                'disadvantage_type' => '',
                'target_type' => '',
                'base_value' => 0,
                'value_per_level' => 0,
                'calculation_type' => 'additive',
                'is_percentage' => false,
                'is_active' => true
            ];
            
            $this->dispatch('admin:toast:success', ['message' => 'Désavantage ajouté avec succès']);
        }
    }
    
    /**
     * Supprimer un désavantage
     */
    public function deleteDisadvantage($disadvantageId)
    {
        $disadvantage = TemplateBuildDisadvantage::find($disadvantageId);
        if ($disadvantage && $disadvantage->build_id == $this->selectedBuildId) {
            $disadvantage->delete();
            
            // Recharger les désavantages
            $this->disadvantages = $this->selectedBuild->disadvantages()->get()->toArray();
            
            $this->dispatch('admin:toast:success', ['message' => 'Désavantage supprimé avec succès']);
        }
    }
    
    /**
     * Obtenir la liste des bâtiments avec pagination et filtres
     */
    public function getBuilds()
    {
        $query = TemplateBuild::query();
        
        // Appliquer la recherche
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('label', 'like', '%' . $this->search . '%')
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
        
        if (!empty($this->filterCategory)) {
            $query->where('category', $this->filterCategory);
        }
        
        // Appliquer le tri
        $query->orderBy($this->sortField, $this->sortDirection);
        
        return $query->paginate($this->perPage);
    }
    
    /**
     * Obtenir la liste des ressources pour les sélecteurs
     */
    public function getResources()
    {
        return TemplateResource::where('is_active', true)->orderBy('sort_order')->get();
    }
    
    /**
     * Obtenir la liste des bâtiments pour les sélecteurs de prérequis
     */
    public function getAvailableBuilds()
    {
        return TemplateBuild::where('is_active', true)
            ->when($this->selectedBuildId, function ($query) {
                return $query->where('id', '!=', $this->selectedBuildId); // Exclure le bâtiment actuel
            })
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Obtenir le nombre de planètes utilisant chaque bâtiment
     */
    public function getPlanetCountForBuild($buildId)
    {
        $build = TemplateBuild::find($buildId);
        if (!$build) {
            return 0;
        }
        
        switch ($build->type) {
            case TemplateBuild::TYPE_BUILDING:
                return $build->planetBuildings()->count();
            case TemplateBuild::TYPE_UNIT:
                return $build->planetUnits()->count();
            case TemplateBuild::TYPE_DEFENSE:
                return $build->planetDefenses()->count();
            case TemplateBuild::TYPE_SHIP:
                return $build->planetShips()->count();
            case TemplateBuild::TYPE_RESEARCH:
                return $build->userTechnologies()->count();
            default:
                return 0;
        }
    }
    
    /**
     * Rendu du composant
     */
    public function render()
    {
        return view('livewire.admin.template.buildings', [
            'builds' => $this->getBuilds(),
            'resources' => $this->getResources(),
            'availableBuilds' => $this->getAvailableBuilds(),
            'planetCounts' => collect($this->getBuilds()->items())->mapWithKeys(function ($build) {
                return [$build->id => $this->getPlanetCountForBuild($build->id)];
            })
        ]);
    }
}