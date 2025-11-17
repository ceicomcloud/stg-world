<?php

namespace App\Livewire\Game;

use App\Models\Forum\ForumCategory;
use App\Models\Forum\Forum as ForumModel;
use App\Models\Forum\ForumTopic as ForumTopicModel;
use App\Models\Forum\ForumPost;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Traits\LogsUserActions;

#[Layout('components.layouts.game')]
class ForumTopic extends Component
{
    use WithPagination, LogsUserActions;

    public $categoryId;
    public $forumId;
    public $category;
    public $forum;
    public $newTopicTitle = '';
    public $newTopicContent = '';
    public $showNewTopicForm = false;
    
    // Bulk management
    public $selectedTopics = [];
    public $selectAll = false;
    public $showBulkActions = false;
    public $showMoveModal = false;
    public $showDeleteModal = false;
    public $targetForumId = null;
    public $availableForums = [];

    protected $rules = [
        'newTopicTitle' => 'required|string|min:3|max:255',
        'newTopicContent' => 'required|string|min:10',
    ];

    public function mount($categoryId, $forumId)
    {
        $this->categoryId = $categoryId;
        $this->forumId = $forumId;
        
        $this->category = ForumCategory::where('slug', $categoryId)->firstOrFail();
        $this->forum = ForumModel::with(['children' => function($query) {
            $query->active()->ordered();
        }])->where('slug', $forumId)->firstOrFail();
    }

    public function toggleNewTopicForm()
    {
        $isStaff = Auth::check() && (Auth::user()->hasModeratorRights() || Auth::user()->hasAdminRights());
        if ($this->forum && $this->forum->is_locked && !$isStaff) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Ce forum est verrouillé.'
            ]);
            return;
        }

        $this->showNewTopicForm = !$this->showNewTopicForm;
        $this->newTopicTitle = '';
        $this->newTopicContent = '';
    }

    public function createTopic()
    {
        if (!Auth::check()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous devez être connecté pour créer des sujets.'
            ]);
            return;
        }

        $isStaff = Auth::user()->hasModeratorRights() || Auth::user()->hasAdminRights();
        if ($this->forum && $this->forum->is_locked && !$isStaff) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Ce forum est verrouillé.'
            ]);
            return;
        }

        $this->validate([
            'newTopicTitle' => 'required|string|min:3|max:255',
            'newTopicContent' => ['required', 'string', 'min:10', function($attribute, $value, $fail) {
                $text = trim(strip_tags($value));
                if ($text === '') {
                    $fail('Le contenu ne peut pas être vide.');
                }
            }],
        ]);

        $topic = ForumTopicModel::create([
            'forum_id' => $this->forum->id,
            'user_id' => Auth::id(),
            'title' => $this->newTopicTitle,
            'is_pinned' => false,
            'is_locked' => false,
        ]);

        $post = ForumPost::create([
            'topic_id' => $topic->id,
            'user_id' => Auth::id(),
            'content' => $this->newTopicContent,
        ]);

        // Update topic counters
        $topic->update([
            'last_post_id' => $post->id,
            'last_post_user_id' => Auth::id(),
            'last_post_at' => now(),
        ]);

        // Update forum counters
        $this->forum->update([
            'last_post_id' => $post->id,
            'last_post_at' => now(),
        ]);

        $this->showNewTopicForm = false;
        $this->newTopicTitle = '';
        $this->newTopicContent = '';

        // Logger la création du sujet
        $this->logAction(
            'topic_created',
            'forum',
            'Création d\'un nouveau sujet dans le forum',
            [
                'topic_title' => $topic->title,
                'forum_name' => $this->forum->name,
                'category_name' => $this->category->name,
                'content_length' => strlen($this->newTopicContent)
            ]
        );

        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => 'Sujet créé avec succès !'
        ]);
    }

    // Bulk management methods
    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $topics = ForumTopicModel::where('forum_id', $this->forum->id)->pluck('id')->toArray();
            $this->selectedTopics = $topics;
        } else {
            $this->selectedTopics = [];
        }
        $this->updateBulkActionsVisibility();
    }

    public function toggleTopicSelection($topicId)
    {
        if (in_array($topicId, $this->selectedTopics)) {
            $this->selectedTopics = array_diff($this->selectedTopics, [$topicId]);
        } else {
            $this->selectedTopics[] = $topicId;
        }
        
        $totalTopics = ForumTopicModel::where('forum_id', $this->forum->id)->count();
        $this->selectAll = count($this->selectedTopics) === $totalTopics;
        $this->updateBulkActionsVisibility();
    }

    public function updateBulkActionsVisibility()
    {
        $this->showBulkActions = count($this->selectedTopics) > 0;
    }

    public function showMoveTopicsModal()
    {
        if (!Auth::check() || !Auth::user()->hasAdmin()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas les permissions nécessaires.'
            ]);
            return;
        }

        if (empty($this->selectedTopics)) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Veuillez sélectionner au moins un topic.'
            ]);
            return;
        }

        // Load available forums (excluding current forum)
        $this->availableForums = ForumModel::where('id', '!=', $this->forum->id)
            ->with('category')
            ->get();
        
        $this->showMoveModal = true;
    }

    public function cancelMove()
    {
        $this->showMoveModal = false;
        $this->targetForumId = null;
    }

    public function moveSelectedTopics()
    {
        if (!Auth::check() || !Auth::user()->hasAdmin()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas les permissions nécessaires.'
            ]);
            return;
        }

        if (!$this->targetForumId) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Veuillez sélectionner un forum de destination.'
            ]);
            return;
        }

        $targetForum = ForumModel::find($this->targetForumId);
        if (!$targetForum) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Forum de destination introuvable.'
            ]);
            return;
        }

        $topics = ForumTopicModel::whereIn('id', $this->selectedTopics)->get();
        $movedCount = 0;

        foreach ($topics as $topic) {
            // Update topic forum
            $topic->update(['forum_id' => $this->targetForumId]);
                        
            $movedCount++;
        }        

        // Reset selection
        $this->selectedTopics = [];
        $this->selectAll = false;
        $this->showBulkActions = false;
        $this->showMoveModal = false;
        $this->targetForumId = null;

        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => "{$movedCount} topic(s) déplacé(s) vers {$targetForum->name}."
        ]);
    }

    public function showDeleteTopicsModal()
    {
        if (!Auth::check() || !Auth::user()->hasAdmin()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas les permissions nécessaires.'
            ]);
            return;
        }

        if (empty($this->selectedTopics)) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Veuillez sélectionner au moins un topic.'
            ]);
            return;
        }

        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
    }

    public function deleteSelectedTopics()
    {
        if (!Auth::check() || !Auth::user()->hasAdmin()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas les permissions nécessaires.'
            ]);
            return;
        }

        $topics = ForumTopicModel::whereIn('id', $this->selectedTopics)->get();
        $deletedCount = 0;
        $totalPosts = 0;

        foreach ($topics as $topic) {
            $totalPosts += $topic->posts_count();
            
            // Delete all posts in the topic
            ForumPost::where('topic_id', $topic->id)->delete();
            
            // Delete the topic
            $topic->delete();
            
            $deletedCount++;
        }

        // Reset selection
        $this->selectedTopics = [];
        $this->selectAll = false;
        $this->showBulkActions = false;
        $this->showDeleteModal = false;

        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => "{$deletedCount} topic(s) supprimé(s) avec succès."
        ]);
    }

    public function render()
    {
        $topics = ForumTopicModel::where('forum_id', $this->forum->id)
            ->with(['user', 'lastPost.user'])
            ->orderBy('is_pinned', 'desc')
            ->orderBy('last_post_at', 'desc')
            ->paginate(20);

        return view('livewire.game.forum-topic', [
            'topics' => $topics
        ]);
    }
}