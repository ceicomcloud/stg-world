<?php

namespace App\Livewire\Admin\Template;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Template\TemplateBadge;
use App\Models\User\UserBadge;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Traits\LogsUserActions;

#[Layout('components.layouts.admin')]
class Badges extends Component
{
    use WithPagination, LogsUserActions;
    
    // Propriétés pour la pagination et le filtrage
    public $search = '';
    public $perPage = 15;
    public $sortField = 'id';
    public $sortDirection = 'asc';
    public $filterType = '';
    public $filterRarity = '';
    public $filterActive = '';
    
    // Propriétés pour l'onglet actif
    public $activeTab = 'list';
    
    // Propriétés pour le badge sélectionné
    public $selectedBadge = null;
    public $selectedBadgeId = null;
    
    // Propriétés pour le formulaire de badge
    public $badgeForm = [
        'name' => '',
        'description' => '',
        'icon' => '',
        'type' => '',
        'requirement_type' => '',
        'requirement_value' => 0,
        'rarity' => '',
        'points_reward' => 0,
        'is_active' => true
    ];
    
    // Types de badges disponibles
    public $badgeTypes = [
        TemplateBadge::TYPE_LEVEL => 'Niveau',
        TemplateBadge::TYPE_EXPERIENCE => 'Expérience',
        TemplateBadge::TYPE_RESEARCH => 'Recherche',
        TemplateBadge::TYPE_ACHIEVEMENT => 'Accomplissement',
        TemplateBadge::TYPE_SPECIAL => 'Spécial'
    ];
    
    // Types de conditions disponibles
    public $requirementTypes = [
        TemplateBadge::REQUIREMENT_REACH_LEVEL => 'Atteindre un niveau',
        TemplateBadge::REQUIREMENT_TOTAL_EXPERIENCE => 'Expérience totale',
        TemplateBadge::REQUIREMENT_RESEARCH_POINTS => 'Points de recherche',
        TemplateBadge::REQUIREMENT_CUSTOM => 'Personnalisé'
    ];
    
    // Raretés de badges disponibles
    public $badgeRarities = [
        TemplateBadge::RARITY_COMMON => 'Commun',
        TemplateBadge::RARITY_UNCOMMON => 'Peu commun',
        TemplateBadge::RARITY_RARE => 'Rare',
        TemplateBadge::RARITY_EPIC => 'Épique',
        TemplateBadge::RARITY_LEGENDARY => 'Légendaire'
    ];
    
    // Règles de validation pour le formulaire de badge
    protected $rules = [
        'badgeForm.name' => 'required|string|max:255',
        'badgeForm.description' => 'required|string',
        'badgeForm.icon' => 'required|string',
        'badgeForm.type' => 'required|string',
        'badgeForm.requirement_type' => 'required|string',
        'badgeForm.requirement_value' => 'required|integer|min:0',
        'badgeForm.rarity' => 'required|string',
        'badgeForm.points_reward' => 'required|integer|min:0',
        'badgeForm.is_active' => 'boolean'
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
    
    public function updatingFilterRarity()
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
        
        if ($tab === 'list') {
            $this->selectedBadge = null;
            $this->selectedBadgeId = null;
        }
    }
    
    /**
     * Sélectionner un badge pour l'édition
     */
    public function selectBadge($id)
    {
        $this->selectedBadgeId = $id;
        $this->selectedBadge = TemplateBadge::find($id);
        
        if ($this->selectedBadge) {
            $this->badgeForm = [
                'name' => $this->selectedBadge->name,
                'description' => $this->selectedBadge->description,
                'icon' => $this->selectedBadge->icon,
                'type' => $this->selectedBadge->type,
                'requirement_type' => $this->selectedBadge->requirement_type,
                'requirement_value' => $this->selectedBadge->requirement_value,
                'rarity' => $this->selectedBadge->rarity,
                'points_reward' => $this->selectedBadge->points_reward,
                'is_active' => $this->selectedBadge->is_active
            ];
            
            $this->setActiveTab('edit');
        }
    }
    
    /**
     * Créer un nouveau badge
     */
    public function createBadge()
    {
        $this->validate();
        
        $badge = TemplateBadge::create([
            'name' => $this->badgeForm['name'],
            'description' => $this->badgeForm['description'],
            'icon' => $this->badgeForm['icon'],
            'type' => $this->badgeForm['type'],
            'requirement_type' => $this->badgeForm['requirement_type'],
            'requirement_value' => $this->badgeForm['requirement_value'],
            'rarity' => $this->badgeForm['rarity'],
            'points_reward' => $this->badgeForm['points_reward'],
            'is_active' => $this->badgeForm['is_active']
        ]);
        
        // Ajouter un log pour la création de badge
        $this->logAction(
            'Création de badge',
            'admin',
            'Création du badge "' . $badge->name . '"',
            [
                'badge_id' => $badge->id,
                'badge_name' => $badge->name,
                'badge_type' => $badge->type,
                'badge_rarity' => $badge->rarity,
                'points_reward' => $badge->points_reward,
                'is_active' => $badge->is_active
            ]
        );
        
        $this->resetBadgeForm();
        $this->setActiveTab('list');
        $this->dispatch('admin-toast', type: 'success', message: 'Badge créé avec succès');
    }
    
    /**
     * Mettre à jour un badge existant
     */
    public function updateBadge()
    {
        $this->validate();
        
        if ($this->selectedBadge) {
            // Sauvegarder les anciennes valeurs pour le log
            $oldValues = [
                'name' => $this->selectedBadge->name,
                'description' => $this->selectedBadge->description,
                'icon' => $this->selectedBadge->icon,
                'type' => $this->selectedBadge->type,
                'requirement_type' => $this->selectedBadge->requirement_type,
                'requirement_value' => $this->selectedBadge->requirement_value,
                'rarity' => $this->selectedBadge->rarity,
                'points_reward' => $this->selectedBadge->points_reward,
                'is_active' => $this->selectedBadge->is_active
            ];
            
            $this->selectedBadge->update([
                'name' => $this->badgeForm['name'],
                'description' => $this->badgeForm['description'],
                'icon' => $this->badgeForm['icon'],
                'type' => $this->badgeForm['type'],
                'requirement_type' => $this->badgeForm['requirement_type'],
                'requirement_value' => $this->badgeForm['requirement_value'],
                'rarity' => $this->badgeForm['rarity'],
                'points_reward' => $this->badgeForm['points_reward'],
                'is_active' => $this->badgeForm['is_active']
            ]);
            
            // Ajouter un log pour la mise à jour de badge
            $this->logAction(
                'Mise à jour de badge',
                'admin',
                'Mise à jour du badge "' . $this->selectedBadge->name . '"',
                [
                    'badge_id' => $this->selectedBadge->id,
                    'old_values' => $oldValues,
                    'new_values' => $this->badgeForm
                ]
            );
            
            $this->dispatch('admin-toast', type: 'success', message: 'Badge mis à jour avec succès');
        }
    }
    
    /**
     * Supprimer un badge
     */
    public function deleteBadge($id)
    {
        $badge = TemplateBadge::find($id);
        
        if ($badge) {
            // Vérifier si des utilisateurs possèdent ce badge
            $userCount = UserBadge::where('badge_id', $id)->count();
            
            if ($userCount > 0) {
                $this->dispatch('admin-toast', type: 'error', message: 'Impossible de supprimer ce badge car il est attribué à ' . $userCount . ' utilisateur(s)');
                return;
            }
            
            // Sauvegarder les informations du badge pour le log
            $badgeInfo = [
                'badge_id' => $badge->id,
                'badge_name' => $badge->name,
                'badge_type' => $badge->type,
                'badge_rarity' => $badge->rarity
            ];
            
            $badge->delete();
            
            // Ajouter un log pour la suppression de badge
            $this->logAction(
                'Suppression de badge',
                'admin',
                'Suppression du badge "' . $badgeInfo['badge_name'] . '"',
                $badgeInfo
            );
            
            $this->dispatch('admin-toast', type: 'success', message: 'Badge supprimé avec succès');
            
            if ($this->selectedBadgeId === $id) {
                $this->selectedBadge = null;
                $this->selectedBadgeId = null;
                $this->resetBadgeForm();
                $this->setActiveTab('list');
            }
        }
    }
    
    /**
     * Réinitialiser le formulaire de badge
     */
    public function resetBadgeForm()
    {
        $this->badgeForm = [
            'name' => '',
            'description' => '',
            'icon' => 'fa-award',
            'type' => '',
            'requirement_type' => '',
            'requirement_value' => 0,
            'rarity' => '',
            'points_reward' => 0,
            'is_active' => true
        ];
        
        $this->resetErrorBag();
    }
    
    /**
     * Obtenir le nombre d'utilisateurs par badge
     */
    public function getBadgeUserCount($badgeId)
    {
        return UserBadge::where('badge_id', $badgeId)->count();
    }
    
    /**
     * Obtenir les statistiques des badges
     */
    public function getBadgeStats()
    {
        $stats = [
            'total' => TemplateBadge::count(),
            'active' => TemplateBadge::where('is_active', true)->count(),
            'inactive' => TemplateBadge::where('is_active', false)->count(),
            'byType' => [],
            'byRarity' => []
        ];
        
        // Compter par type
        foreach ($this->badgeTypes as $type => $label) {
            $stats['byType'][$type] = TemplateBadge::where('type', $type)->count();
        }
        
        // Compter par rareté
        foreach ($this->badgeRarities as $rarity => $label) {
            $stats['byRarity'][$rarity] = TemplateBadge::where('rarity', $rarity)->count();
        }
        
        return $stats;
    }
    
    /**
     * Obtenir les badges les plus attribués
     */
    public function getMostAwardedBadges($limit = 5)
    {
        return DB::table('user_badges')
            ->select('badge_id', DB::raw('count(*) as count'))
            ->groupBy('badge_id')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $badge = TemplateBadge::find($item->badge_id);
                return [
                    'badge' => $badge,
                    'count' => $item->count
                ];
            });
    }
    
    /**
     * Obtenir les badges récemment attribués
     */
    public function getRecentlyAwardedBadges($limit = 5)
    {
        return UserBadge::with(['badge', 'user'])
            ->orderBy('earned_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Render the component
     */
    public function render()
    {
        $query = TemplateBadge::query();
        
        // Appliquer la recherche
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }
        
        // Appliquer les filtres
        if (!empty($this->filterType)) {
            $query->where('type', $this->filterType);
        }
        
        if (!empty($this->filterRarity)) {
            $query->where('rarity', $this->filterRarity);
        }
        
        if ($this->filterActive !== '') {
            $isActive = $this->filterActive === 'active';
            $query->where('is_active', $isActive);
        }
        
        // Appliquer le tri
        $query->orderBy($this->sortField, $this->sortDirection);
        
        // Paginer les résultats
        $badges = $query->paginate($this->perPage);
        
        // Obtenir les statistiques
        $badgeStats = $this->getBadgeStats();
        $mostAwardedBadges = $this->getMostAwardedBadges();
        $recentlyAwardedBadges = $this->getRecentlyAwardedBadges();
        
        return view('livewire.admin.template.badges', [
            'badges' => $badges,
            'badgeStats' => $badgeStats,
            'mostAwardedBadges' => $mostAwardedBadges,
            'recentlyAwardedBadges' => $recentlyAwardedBadges
        ]);
    }
}