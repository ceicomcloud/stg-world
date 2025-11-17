<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Server\ServerNews;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\LogsUserActions;

#[Layout('components.layouts.admin')]
class News extends Component
{
    use WithPagination, LogsUserActions;
    
    // Propriétés pour la pagination et le filtrage
    public $search = '';
    public $perPage = 15;
    public $sortField = 'published_at';
    public $sortDirection = 'desc';
    public $filterCategory = '';
    public $filterPriority = '';
    public $filterStatus = '';
    
    // Propriétés pour l'onglet actif
    public $activeTab = 'list';
    
    // Propriétés pour l'actualité sélectionnée
    public $selectedNews = null;
    public $selectedNewsId = null;
    
    // Propriétés pour le formulaire d'actualité
    public $newsForm = [
        'title' => '',
        'content' => '',
        'excerpt' => '',
        'category' => '',
        'priority' => '',
        'is_published' => false,
        'is_pinned' => false,
        'published_at' => null,
        'expires_at' => null,
        'image_url' => '',
        'external_url' => '',
        'tags' => [],
        'is_active' => true
    ];
    
    // Catégories d'actualités disponibles
    public $newsCategories = [
        ServerNews::CATEGORY_GENERAL => 'Général',
        ServerNews::CATEGORY_UPDATE => 'Mise à jour',
        ServerNews::CATEGORY_MAINTENANCE => 'Maintenance',
        ServerNews::CATEGORY_EVENT => 'Événement',
        ServerNews::CATEGORY_ANNOUNCEMENT => 'Annonce',
        ServerNews::CATEGORY_PATCH => 'Patch',
        ServerNews::CATEGORY_COMPETITION => 'Compétition',
        ServerNews::CATEGORY_COMMUNITY => 'Communauté'
    ];
    
    // Priorités d'actualités disponibles
    public $newsPriorities = [
        ServerNews::PRIORITY_LOW => 'Basse',
        ServerNews::PRIORITY_NORMAL => 'Normale',
        ServerNews::PRIORITY_HIGH => 'Haute',
        ServerNews::PRIORITY_URGENT => 'Urgente'
    ];
    
    // Règles de validation pour le formulaire d'actualité
    protected $rules = [
        'newsForm.title' => 'required|string|max:255',
        'newsForm.content' => 'required|string',
        'newsForm.excerpt' => 'nullable|string',
        'newsForm.category' => 'required|string',
        'newsForm.priority' => 'required|string',
        'newsForm.is_published' => 'boolean',
        'newsForm.is_pinned' => 'boolean',
        'newsForm.published_at' => 'nullable|date',
        'newsForm.expires_at' => 'nullable|date|after_or_equal:newsForm.published_at',
        'newsForm.image_url' => 'nullable|string|max:255',
        'newsForm.external_url' => 'nullable|string|max:255',
        'newsForm.tags' => 'nullable|array',
        'newsForm.is_active' => 'boolean'
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
    
    public function updatingFilterPriority()
    {
        $this->resetPage();
    }
    
    public function updatingFilterStatus()
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
            $this->sortDirection = 'desc';
        }
    }
    
    /**
     * Définir l'onglet actif
     */
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        
        // Si on revient à la liste, on réinitialise l'actualité sélectionnée
        if ($tab === 'list') {
            $this->selectedNews = null;
            $this->selectedNewsId = null;
        }
    }
    
    /**
     * Sélectionner une actualité pour l'édition
     */
    public function selectNews($id)
    {
        $this->selectedNewsId = $id;
        $this->selectedNews = ServerNews::find($id);
        
        if ($this->selectedNews) {
            $this->newsForm = [
                'title' => $this->selectedNews->title,
                'content' => $this->selectedNews->content,
                'excerpt' => $this->selectedNews->excerpt,
                'category' => $this->selectedNews->category,
                'priority' => $this->selectedNews->priority,
                'is_published' => $this->selectedNews->is_published,
                'is_pinned' => $this->selectedNews->is_pinned,
                'published_at' => $this->selectedNews->published_at ? $this->selectedNews->published_at->format('Y-m-d\TH:i') : null,
                'expires_at' => $this->selectedNews->expires_at ? $this->selectedNews->expires_at->format('Y-m-d\TH:i') : null,
                'image_url' => $this->selectedNews->image_url,
                'external_url' => $this->selectedNews->external_url,
                'tags' => $this->selectedNews->tags ?? [],
                'is_active' => $this->selectedNews->is_active
            ];
            
            $this->setActiveTab('edit');
        }
    }
    
    /**
     * Créer une nouvelle actualité
     */
    public function createNews()
    {
        $this->validate();
        
        $news = new ServerNews();
        $news->title = $this->newsForm['title'];
        $news->content = $this->newsForm['content'];
        $news->excerpt = $this->newsForm['excerpt'];
        $news->author_id = Auth::id();
        $news->category = $this->newsForm['category'];
        $news->priority = $this->newsForm['priority'];
        $news->is_published = $this->newsForm['is_published'];
        $news->is_pinned = $this->newsForm['is_pinned'];
        $news->published_at = $this->newsForm['published_at'] ? Carbon::parse($this->newsForm['published_at']) : ($this->newsForm['is_published'] ? now() : null);
        $news->expires_at = $this->newsForm['expires_at'] ? Carbon::parse($this->newsForm['expires_at']) : null;
        $news->image_url = $this->newsForm['image_url'];
        $news->external_url = $this->newsForm['external_url'];
        $news->tags = $this->newsForm['tags'];
        $news->is_active = $this->newsForm['is_active'];
        $news->save();
        
        // Ajouter un log pour la création d'actualité
        $this->logAction(
            'Création d\'actualité',
            'admin',
            'Création de l\'actualité "' . $news->title . '"',
            [
                'news_id' => $news->id,
                'category' => $news->category,
                'priority' => $news->priority,
                'is_published' => $news->is_published
            ]
        );
        
        $this->resetNewsForm();
        $this->setActiveTab('list');
        
        $this->dispatch('admin-toast', [
            'message' => 'Actualité créée avec succès.',
            'type' => 'success'
        ]);
    }
    
    /**
     * Mettre à jour une actualité existante
     */
    public function updateNews()
    {
        $this->validate();
        
        $news = ServerNews::find($this->selectedNewsId);
        if ($news) {
            // Sauvegarder les anciennes valeurs pour le log
            $oldValues = [
                'title' => $news->title,
                'category' => $news->category,
                'priority' => $news->priority,
                'is_published' => $news->is_published,
                'is_pinned' => $news->is_pinned
            ];
            
            $news->title = $this->newsForm['title'];
            $news->content = $this->newsForm['content'];
            $news->excerpt = $this->newsForm['excerpt'];
            $news->category = $this->newsForm['category'];
            $news->priority = $this->newsForm['priority'];
            $news->is_published = $this->newsForm['is_published'];
            $news->is_pinned = $this->newsForm['is_pinned'];
            $news->published_at = $this->newsForm['published_at'] ? Carbon::parse($this->newsForm['published_at']) : ($this->newsForm['is_published'] ? now() : null);
            $news->expires_at = $this->newsForm['expires_at'] ? Carbon::parse($this->newsForm['expires_at']) : null;
            $news->image_url = $this->newsForm['image_url'];
            $news->external_url = $this->newsForm['external_url'];
            $news->tags = $this->newsForm['tags'];
            $news->is_active = $this->newsForm['is_active'];
            $news->save();
            
            // Ajouter un log pour la mise à jour d'actualité
            $this->logAction(
                'Modification d\'actualité',
                'admin',
                'Modification de l\'actualité "' . $news->title . '"',
                [
                    'news_id' => $news->id,
                    'old_values' => $oldValues,
                    'new_values' => [
                        'title' => $news->title,
                        'category' => $news->category,
                        'priority' => $news->priority,
                        'is_published' => $news->is_published,
                        'is_pinned' => $news->is_pinned
                    ]
                ]
            );
            
            $this->selectedNews = $news;
            
            $this->dispatch('admin-toast', [
                'message' => 'Actualité mise à jour avec succès.',
                'type' => 'success'
            ]);
        }
    }
    
    /**
     * Supprimer une actualité
     */
    public function deleteNews()
    {
        $news = ServerNews::find($this->selectedNewsId);
        if ($news) {
            // Sauvegarder les informations pour le log
            $newsInfo = [
                'id' => $news->id,
                'title' => $news->title,
                'category' => $news->category,
                'priority' => $news->priority
            ];
            
            $news->delete();
            
            // Ajouter un log pour la suppression d'actualité
            $this->logAction(
                'Suppression d\'actualité',
                'admin',
                'Suppression de l\'actualité "' . $newsInfo['title'] . '"',
                $newsInfo
            );
            
            $this->resetNewsForm();
            $this->setActiveTab('list');
            
            $this->dispatch('admin-toast', [
                'message' => 'Actualité supprimée avec succès.',
                'type' => 'success'
            ]);
        }
    }
    
    /**
     * Réinitialiser le formulaire d'actualité
     */
    public function resetNewsForm()
    {
        $this->newsForm = [
            'title' => '',
            'content' => '',
            'excerpt' => '',
            'category' => ServerNews::CATEGORY_GENERAL,
            'priority' => ServerNews::PRIORITY_NORMAL,
            'is_published' => false,
            'is_pinned' => false,
            'published_at' => null,
            'expires_at' => null,
            'image_url' => '',
            'external_url' => '',
            'tags' => [],
            'is_active' => true
        ];
        
        $this->selectedNews = null;
        $this->selectedNewsId = null;
        $this->resetErrorBag();
    }
    
    /**
     * Ajouter un tag
     */
    public function addTag($tag)
    {
        if (!empty($tag) && !in_array($tag, $this->newsForm['tags'])) {
            $this->newsForm['tags'][] = $tag;
        }
    }
    
    /**
     * Supprimer un tag
     */
    public function removeTag($index)
    {
        if (isset($this->newsForm['tags'][$index])) {
            unset($this->newsForm['tags'][$index]);
            $this->newsForm['tags'] = array_values($this->newsForm['tags']);
        }
    }
    
    /**
     * Publier immédiatement une actualité
     */
    public function publishNow()
    {
        $this->newsForm['is_published'] = true;
        $this->newsForm['published_at'] = now()->format('Y-m-d\TH:i');
    }
    
    /**
     * Obtenir les statistiques des actualités
     */
    public function getNewsStats()
    {
        return ServerNews::getStatistics();
    }
    
    /**
     * Rendre la vue
     */
    public function render()
    {
        // Construire la requête de base
        $query = ServerNews::query();
        
        // Appliquer la recherche
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('content', 'like', '%' . $this->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $this->search . '%');
            });
        }
        
        // Appliquer les filtres
        if (!empty($this->filterCategory)) {
            $query->where('category', $this->filterCategory);
        }
        
        if (!empty($this->filterPriority)) {
            $query->where('priority', $this->filterPriority);
        }
        
        if ($this->filterStatus === 'published') {
            $query->published();
        } elseif ($this->filterStatus === 'draft') {
            $query->draft();
        } elseif ($this->filterStatus === 'scheduled') {
            $query->scheduled();
        } elseif ($this->filterStatus === 'expired') {
            $query->expired();
        } elseif ($this->filterStatus === 'active') {
            $query->active();
        } elseif ($this->filterStatus === 'inactive') {
            $query->where('is_active', false);
        }
        
        // Appliquer le tri
        $query->orderBy($this->sortField, $this->sortDirection);
        
        // Paginer les résultats
        $news = $query->paginate($this->perPage);
        
        // Obtenir les statistiques
        $newsStats = $this->getNewsStats();
        
        // Obtenir la liste des auteurs
        $authors = User::whereIn('id', ServerNews::select('author_id')->distinct()->pluck('author_id'))
            ->pluck('name', 'id')
            ->toArray();
        
        return view('livewire.admin.news', [
            'news' => $news,
            'newsStats' => $newsStats,
            'authors' => $authors
        ]);
    }
}