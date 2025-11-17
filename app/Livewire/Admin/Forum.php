<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Forum\ForumReport;
use App\Models\Forum\ForumPost;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Traits\LogsUserActions;

#[Layout('components.layouts.admin')]
class Forum extends Component
{
    use WithPagination, LogsUserActions;
    
    // Propriétés pour la pagination et le filtrage
    public $search = '';
    public $perPage = 15;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $statusFilter = 'pending';
    
    // Onglet actif
    public $activeTab = 'reports';
    
    // Propriétés pour l'affichage du post signalé
    public $selectedReport = null;
    public $selectedPost = null;
    public $adminNotes = '';
    
    /**
     * Règles de validation
     */
    protected function rules()
    {
        return [
            'adminNotes' => 'nullable|string|max:500',
        ];
    }
    
    /**
     * Réinitialiser la pagination lorsque la recherche change
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }
    
    /**
     * Réinitialiser la pagination lorsque le filtre de statut change
     */
    public function updatedStatusFilter()
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
        
        if ($tab !== 'view_report') {
            $this->selectedReport = null;
            $this->selectedPost = null;
            $this->adminNotes = '';
        }
        
        $this->resetPage();
    }
    
    /**
     * Afficher les détails d'un signalement
     */
    public function viewReport($reportId)
    {
        $this->selectedReport = ForumReport::with(['post.topic.forum', 'reportedBy', 'post.user'])
            ->findOrFail($reportId);
        $this->selectedPost = $this->selectedReport->post;
        $this->adminNotes = $this->selectedReport->admin_notes;
        $this->activeTab = 'view_report';
    }
    
    /**
     * Marquer un signalement comme résolu
     */
    public function resolveReport()
    {
        if (!$this->selectedReport) {
            return;
        }
        
        $this->validate();
        
        $this->selectedReport->markAsReviewed(Auth::user(), 'resolved', $this->adminNotes);
        
        // Log de l'action
        $this->logAction(
            'forum_report_resolved',
            'forum',
            "Signalement #{$this->selectedReport->id} marqué comme résolu",
            [
                'report_id' => $this->selectedReport->id,
                'post_id' => $this->selectedReport->post_id,
                'reported_by' => $this->selectedReport->reported_by,
                'reason' => $this->selectedReport->reason,
                'admin_notes' => $this->adminNotes
            ]
        );
        
        $this->dispatch('admin:toast:success', ['message' => 'Le signalement a été marqué comme résolu.']);
        
        $this->setActiveTab('reports');
    }
    
    /**
     * Marquer un signalement comme rejeté
     */
    public function dismissReport()
    {
        if (!$this->selectedReport) {
            return;
        }
        
        $this->validate();
        
        $this->selectedReport->markAsReviewed(Auth::user(), 'dismissed', $this->adminNotes);
        
        // Log de l'action
        $this->logAction(
            'forum_report_dismissed',
            'forum',
            "Signalement #{$this->selectedReport->id} rejeté",
            [
                'report_id' => $this->selectedReport->id,
                'post_id' => $this->selectedReport->post_id,
                'reported_by' => $this->selectedReport->reported_by,
                'reason' => $this->selectedReport->reason,
                'admin_notes' => $this->adminNotes
            ]
        );
        
        $this->dispatch('admin:toast:success', ['message' => 'Le signalement a été rejeté.']);
        
        $this->setActiveTab('reports');
    }
    
    /**
     * Supprimer un post signalé
     */
    public function deletePost()
    {
        if (!$this->selectedReport || !$this->selectedPost) {
            return;
        }
        
        $postId = $this->selectedPost->id;
        $topicId = $this->selectedPost->topic_id;
        $postContent = $this->selectedPost->content;
        $postUser = $this->selectedPost->user_id;
        
        // Supprimer le post
        $this->selectedPost->delete();
        
        // Marquer le signalement comme résolu
        $this->selectedReport->markAsReviewed(Auth::user(), 'resolved', 
            $this->adminNotes ? $this->adminNotes . "\n[Post supprimé]" : "Post supprimé");
        
        // Log de l'action
        $this->logAction(
            'forum_post_deleted',
            'forum',
            "Post #{$postId} supprimé suite à un signalement",
            [
                'report_id' => $this->selectedReport->id,
                'post_id' => $postId,
                'topic_id' => $topicId,
                'user_id' => $postUser,
                'content' => $postContent,
                'reason' => $this->selectedReport->reason,
                'admin_notes' => $this->adminNotes
            ]
        );
        
        $this->dispatch('admin:toast:success', ['message' => 'Le post a été supprimé et le signalement a été résolu.']);
        
        $this->setActiveTab('reports');
    }
    
    /**
     * Obtenir les statistiques des signalements
     */
    public function getReportStats()
    {
        return [
            'total' => ForumReport::count(),
            'pending' => ForumReport::where('status', 'pending')->count(),
            'resolved' => ForumReport::where('status', 'resolved')->count(),
            'dismissed' => ForumReport::where('status', 'dismissed')->count(),
        ];
    }
    
    /**
     * Obtenir les signalements filtrés et triés
     */
    public function getReports()
    {
        $query = ForumReport::with(['post.topic.forum', 'reportedBy', 'reviewedBy'])
            ->when($this->statusFilter !== 'all', function ($query) {
                return $query->where('status', $this->statusFilter);
            })
            ->when($this->search, function ($query) {
                return $query->whereHas('post', function ($q) {
                    $q->where('content', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('reportedBy', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })
                ->orWhere('reason', 'like', '%' . $this->search . '%')
                ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection);
        
        return $query->paginate($this->perPage);
    }
    
    /**
     * Rendu du composant
     */
    public function render()
    {
        return view('livewire.admin.forum', [
            'reports' => $this->getReports(),
            'reportStats' => $this->getReportStats(),
        ]);
    }
}