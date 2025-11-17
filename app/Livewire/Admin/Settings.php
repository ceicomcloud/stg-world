<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Server\ServerConfig;
use Illuminate\Support\Facades\DB;
use App\Traits\LogsUserActions;

#[Layout('components.layouts.admin')]
class Settings extends Component
{
    use WithPagination, LogsUserActions;
    
    // Propriétés pour la pagination et le filtrage
    public $search = '';
    public $perPage = 15;
    public $sortField = 'key';
    public $sortDirection = 'asc';
    public $filterCategory = '';
    public $filterType = '';
    public $filterActive = '';
    
    // Propriétés pour l'onglet actif
    public $activeTab = 'list';
    
    // Propriétés pour le paramètre sélectionné
    public $selectedConfig = null;
    public $selectedConfigId = null;
    
    // Propriétés pour le formulaire de paramètre
    public $configForm = [
        'key' => '',
        'value' => '',
        'type' => '',
        'description' => '',
        'category' => '',
        'is_active' => true
    ];
    
    // Types de paramètres disponibles
    public $configTypes = [
        ServerConfig::TYPE_INTEGER => 'Entier',
        ServerConfig::TYPE_FLOAT => 'Décimal',
        ServerConfig::TYPE_STRING => 'Texte',
        ServerConfig::TYPE_BOOLEAN => 'Booléen',
        ServerConfig::TYPE_JSON => 'JSON'
    ];
    
    // Catégories de paramètres disponibles
    public $configCategories = [
        ServerConfig::CATEGORY_GENERAL => 'Général',
        ServerConfig::CATEGORY_PRODUCTION => 'Production',
        ServerConfig::CATEGORY_STORAGE => 'Stockage',
        ServerConfig::CATEGORY_RESEARCH => 'Recherche',
        ServerConfig::CATEGORY_BUILDING => 'Bâtiments',
        ServerConfig::CATEGORY_COMBAT => 'Combat',
        ServerConfig::CATEGORY_FLEET => 'Flotte',
        ServerConfig::CATEGORY_PLANET => 'Planètes',
        ServerConfig::CATEGORY_USER => 'Utilisateurs',
        ServerConfig::CATEGORY_ECONOMY => 'Économie'
    ];
    
    // Règles de validation pour le formulaire de paramètre
    protected $rules = [
        'configForm.key' => 'required|string|max:255',
        'configForm.value' => 'required|string',
        'configForm.type' => 'required|string',
        'configForm.description' => 'nullable|string',
        'configForm.category' => 'required|string',
        'configForm.is_active' => 'boolean'
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
    public function updatingFilterCategory()
    {
        $this->resetPage();
    }
    
    public function updatingFilterType()
    {
        $this->resetPage();
    }
    
    public function updatingFilterActive()
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
        
        // Si on revient à la liste, on réinitialise le paramètre sélectionné
        if ($tab === 'list') {
            $this->selectedConfig = null;
            $this->selectedConfigId = null;
        }
    }
    
    /**
     * Sélectionner un paramètre pour l'édition
     */
    public function selectConfig($id)
    {
        $this->selectedConfigId = $id;
        $this->selectedConfig = ServerConfig::find($id);
        
        if ($this->selectedConfig) {
            $this->configForm = [
                'key' => $this->selectedConfig->key,
                'value' => $this->selectedConfig->getRawOriginal('value'),
                'type' => $this->selectedConfig->type,
                'description' => $this->selectedConfig->description,
                'category' => $this->selectedConfig->category,
                'is_active' => $this->selectedConfig->is_active
            ];
            
            $this->setActiveTab('edit');
        }
    }
    
    /**
     * Créer un nouveau paramètre
     */
    public function createConfig()
    {
        $this->validate();
        
        // Vérifier si la clé existe déjà
        $existingConfig = ServerConfig::where('key', $this->configForm['key'])->first();
        if ($existingConfig) {
            $this->addError('configForm.key', 'Cette clé existe déjà.');
            return;
        }
        
        $config = new ServerConfig();
        $config->key = $this->configForm['key'];
        $config->value = $this->configForm['value'];
        $config->type = $this->configForm['type'];
        $config->description = $this->configForm['description'];
        $config->category = $this->configForm['category'];
        $config->is_active = $this->configForm['is_active'];
        $config->save();
        
        // Journaliser l'action de création de paramètre
        $this->logAction(
            'config_created',
            'settings',
            'Création du paramètre: {key}',
            [
                'key' => $config->key,
                'value' => $config->value,
                'type' => $config->type,
                'category' => $config->category
            ]
        );
        
        $this->resetConfigForm();
        $this->setActiveTab('list');
        
        $this->dispatch('admin-toast', [
            'message' => 'Paramètre créé avec succès.',
            'type' => 'success'
        ]);
    }
    
    /**
     * Mettre à jour un paramètre existant
     */
    public function updateConfig()
    {
        $this->validate();
        
        // Vérifier si la clé existe déjà pour un autre paramètre
        $existingConfig = ServerConfig::where('key', $this->configForm['key'])
            ->where('id', '!=', $this->selectedConfigId)
            ->first();
            
        if ($existingConfig) {
            $this->addError('configForm.key', 'Cette clé existe déjà.');
            return;
        }
        
        $config = ServerConfig::find($this->selectedConfigId);
        if ($config) {
            // Sauvegarder les anciennes valeurs pour le log
            $oldValues = [
                'key' => $config->key,
                'value' => $config->getRawOriginal('value'),
                'type' => $config->type,
                'category' => $config->category,
                'is_active' => $config->is_active
            ];
            
            $config->key = $this->configForm['key'];
            $config->value = $this->configForm['value'];
            $config->type = $this->configForm['type'];
            $config->description = $this->configForm['description'];
            $config->category = $this->configForm['category'];
            $config->is_active = $this->configForm['is_active'];
            $config->save();
            
            // Journaliser l'action de mise à jour de paramètre
            $this->logSettingsChanged([
                'key' => $config->key,
                'old_value' => $oldValues['value'],
                'new_value' => $config->value,
                'old_type' => $oldValues['type'],
                'new_type' => $config->type,
                'old_category' => $oldValues['category'],
                'new_category' => $config->category,
                'old_is_active' => $oldValues['is_active'],
                'new_is_active' => $config->is_active
            ]);
            
            $this->selectedConfig = $config;
            
            $this->dispatch('admin-toast', [
                'message' => 'Paramètre mis à jour avec succès.',
                'type' => 'success'
            ]);
        }
    }
    
    /**
     * Supprimer un paramètre
     */
    public function deleteConfig()
    {
        $config = ServerConfig::find($this->selectedConfigId);
        if ($config) {
            // Sauvegarder les informations pour le log
            $configInfo = [
                'key' => $config->key,
                'value' => $config->getRawOriginal('value'),
                'type' => $config->type,
                'category' => $config->category
            ];
            
            $config->delete();
            
            // Journaliser l'action de suppression de paramètre
            $this->logAction(
                'config_deleted',
                'settings',
                'Suppression du paramètre: {key}',
                $configInfo
            );
            
            $this->resetConfigForm();
            $this->setActiveTab('list');
            
            $this->dispatch('admin-toast', [
                'message' => 'Paramètre supprimé avec succès.',
                'type' => 'success'
            ]);
        }
    }
    
    /**
     * Réinitialiser le formulaire de paramètre
     */
    public function resetConfigForm()
    {
        $this->configForm = [
            'key' => '',
            'value' => '',
            'type' => ServerConfig::TYPE_STRING,
            'description' => '',
            'category' => ServerConfig::CATEGORY_GENERAL,
            'is_active' => true
        ];
        
        $this->selectedConfig = null;
        $this->selectedConfigId = null;
        $this->resetErrorBag();
    }
    
    /**
     * Obtenir les statistiques des paramètres
     */
    public function getConfigStats()
    {
        $stats = [
            'total' => ServerConfig::count(),
            'active' => ServerConfig::where('is_active', true)->count(),
            'inactive' => ServerConfig::where('is_active', false)->count(),
            'byCategory' => []
        ];
        
        // Compter par catégorie
        $categoryStats = ServerConfig::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get()
            ->pluck('count', 'category')
            ->toArray();
            
        foreach ($this->configCategories as $key => $label) {
            $stats['byCategory'][$key] = $categoryStats[$key] ?? 0;
        }
        
        return $stats;
    }
    
    /**
     * Rendre la vue
     */
    public function render()
    {
        // Construire la requête de base
        $query = ServerConfig::query();
        
        // Appliquer la recherche
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('key', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }
        
        // Appliquer les filtres
        if (!empty($this->filterCategory)) {
            $query->where('category', $this->filterCategory);
        }
        
        if (!empty($this->filterType)) {
            $query->where('type', $this->filterType);
        }
        
        if ($this->filterActive === 'active') {
            $query->where('is_active', true);
        } elseif ($this->filterActive === 'inactive') {
            $query->where('is_active', false);
        }
        
        // Appliquer le tri
        $query->orderBy($this->sortField, $this->sortDirection);
        
        // Paginer les résultats
        $configs = $query->paginate($this->perPage);
        
        // Obtenir les statistiques
        $configStats = $this->getConfigStats();
        
        return view('livewire.admin.settings', [
            'configs' => $configs,
            'configStats' => $configStats
        ]);
    }
}