<?php

namespace App\Livewire\Game;

use App\Models\Forum\ForumCategory;
use App\Models\Forum\Forum as ForumModel;
use App\Models\Forum\ForumTopic as ForumTopicModel;
use App\Models\Forum\ForumPost as ForumPostModel;
use App\Models\Forum\ForumReport;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Traits\LogsUserActions;

#[Layout('components.layouts.game')]
class ForumPost extends Component
{
    use WithPagination, LogsUserActions;

    public $categoryId;
    public $forumId;
    public $topicId;
    public $category;
    public $forum;
    public $topic;
    public $newPostContent = '';
    public $showReplyForm = false;
    
    // Edit functionality
    public $editingPostId = null;
    public $editPostContent = '';
    
    // Quote functionality
    public $quotedPostId = null;
    public $quotedContent = '';
    
    // Report functionality
    public $reportingPostId = null;
    public $reportReason = '';
    public $reportDescription = '';
    public $showReportModal = false;
    
    // Admin functionality
    public $showDeleteTopicModal = false;
    public $showCloseTopicModal = false;
    public $showDeletePostModal = false;
    public $deletingPostId = null;


    public function mount($categoryId, $forumId, $topicId)
    {
        $this->categoryId = $categoryId;
        $this->forumId = $forumId;
        $this->topicId = $topicId;

        $this->category = ForumCategory::where('slug', $categoryId)->firstOrFail();
        $this->forum = ForumModel::where('slug', $forumId)->firstOrFail();
        $this->topic = ForumTopicModel::where('slug', $topicId)->firstOrFail();
    }

    public function toggleReplyForm()
    {
        if ($this->topic && $this->topic->is_locked) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Ce sujet est verrouillé.'
            ]);
            return;
        }

        $this->showReplyForm = !$this->showReplyForm;
        $this->newPostContent = '';
    }

    public function createPost()
    {
        if (!Auth::check()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous devez être connecté pour répondre.'
            ]);
            return;
        }

        if ($this->topic && $this->topic->is_locked) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Ce sujet est verrouillé.'
            ]);
            return;
        }

        $this->validate([
            'newPostContent' => ['required','string','min:3', function($attribute, $value, $fail) {
                $text = trim(strip_tags($value));
                if ($text === '') { $fail('Le contenu ne peut pas être vide.'); }
            }],
        ]);

        $post = ForumPostModel::create([
            'topic_id' => $this->topic->id,
            'user_id' => Auth::id(),
            'content' => $this->newPostContent,
        ]);

        // Update topic counters
        $this->topic->update([
            'last_post_id' => $post->id,
            'last_post_user_id' => Auth::id(),
            'last_post_at' => now(),
        ]);

        // Update forum counters
        $this->forum->update([
            'last_post_id' => $post->id,
            'last_post_at' => now(),
        ]);

        $this->showReplyForm = false;
        $this->newPostContent = '';

        // Logger la création du post
        $this->logAction(
            'post_created',
            'forum',
            'Création d\'une réponse dans le forum',
            [
                'topic_title' => $this->topic->title,
                'forum_name' => $this->forum->name,
                'category_name' => $this->category->name,
                'content_length' => strlen($this->newPostContent)
            ]
        );

        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => 'Réponse ajoutée avec succès !'
        ]);
    }

    // Edit functionality
    public function startEditPost($postId)
    {
        $post = ForumPostModel::findOrFail($postId);
        
        // Check if user can edit this post
        if (Auth::id() !== $post->user_id && !Auth::user()->isAdmin()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous ne pouvez pas modifier ce message.'
            ]);
            return;
        }
        
        $this->editingPostId = $postId;
        $this->editPostContent = (string) $post->content;
    }

    public function cancelEdit()
    {
        $this->editingPostId = null;
        $this->editPostContent = '';
    }

    public function updatePost()
    {
        if (!$this->editingPostId) {
            return;
        }

        $this->validate([
            'editPostContent' => ['required','string','min:3', function($attribute, $value, $fail) {
                $text = trim(strip_tags($value));
                if ($text === '') { $fail('Le contenu ne peut pas être vide.'); }
            }],
        ]);

        $post = ForumPostModel::findOrFail($this->editingPostId);
        
        // Check if user can edit this post
        if (Auth::id() !== $post->user_id && !Auth::user()->isAdmin()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous ne pouvez pas modifier ce message.'
            ]);
            return;
        }

        $post->update([
            'content' => $this->editPostContent,
            'updated_at' => now(),
        ]);

        $this->editingPostId = null;
        $this->editPostContent = '';

        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => 'Message modifié avec succès !'
        ]);
    }

    // Quote functionality
    public function quotePost($postId)
    {
        $post = ForumPostModel::with('user')->findOrFail($postId);
        
        $quotedText = "[quote={$post->user->name}]{$post->content}[/quote]\n\n";
        
        if ($this->showReplyForm) {
            $this->newPostContent = $quotedText . $this->newPostContent;
        } else {
            $this->newPostContent = $quotedText;
            $this->showReplyForm = true;
        }
        
        $this->dispatch('toast:info', [
            'title' => 'Citation ajoutée',
            'text' => 'Le message a été cité dans votre réponse.'
        ]);
    }

    // Report functionality
    public function startReportPost($postId)
    {
        $this->reportingPostId = $postId;
        $this->reportReason = '';
        $this->reportDescription = '';
        $this->showReportModal = true;
    }

    public function cancelReport()
    {
        $this->reportingPostId = null;
        $this->reportReason = '';
        $this->reportDescription = '';
        $this->showReportModal = false;
    }

    public function submitReport()
    {
        if (!Auth::check()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous devez être connecté pour signaler un message.'
            ]);
            return;
        }

        $this->validate([
            'reportReason' => 'required|string',
            'reportDescription' => 'nullable|string|max:500',
        ]);

        // Check if user already reported this post
        $existingReport = ForumReport::where('post_id', $this->reportingPostId)
            ->where('reported_by', Auth::id())
            ->first();

        if ($existingReport) {
            $this->dispatch('toast:warning', [
                'title' => 'Attention!',
                'text' => 'Vous avez déjà signalé ce message.'
            ]);
            $this->cancelReport();
            return;
        }

        ForumReport::create([
            'post_id' => $this->reportingPostId,
            'reported_by' => Auth::id(),
            'reason' => $this->reportReason,
            'description' => $this->reportDescription,
            'status' => 'pending',
        ]);

        $this->cancelReport();

        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => 'Message signalé avec succès. Les modérateurs examineront votre signalement.'
        ]);
    }

    // Admin functions
    public function showDeleteTopicModalOpen()
    {
        if (!Auth::check() || !Auth::user()->hasAdmin()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas les permissions nécessaires.'
            ]);
            return;
        }

        $this->showDeleteTopicModal = true;
    }

    public function cancelDeleteTopic()
    {
        $this->showDeleteTopicModal = false;
    }

    public function deleteTopic()
    {
        if (!Auth::check() || !Auth::user()->hasAdmin()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas les permissions nécessaires.'
            ]);
            return;
        }

        // Supprimer tous les posts du topic
        ForumPostModel::where('topic_id', $this->topic->id)->delete();
        
        // Supprimer le topic
        $this->topic->delete();
                
        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => 'Topic supprimé avec succès.'
        ]);
        
        // Rediriger vers le forum
        return redirect()->route('forum.show', [
            'categoryId' => $this->categoryId,
            'forumId' => $this->forumId
        ]);
    }

    public function showCloseTopicModalOpen()
    {
        if (!Auth::check() || !Auth::user()->hasAdmin()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas les permissions nécessaires.'
            ]);
            return;
        }

        $this->showCloseTopicModal = true;
    }

    public function cancelCloseTopic()
    {
        $this->showCloseTopicModal = false;
    }

    public function toggleTopicLock()
    {
        if (!Auth::check() || !Auth::user()->hasAdmin()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas les permissions nécessaires.'
            ]);
            return;
        }

        $this->topic->is_locked = !$this->topic->is_locked;
        $this->topic->save();
        
        $status = $this->topic->is_locked ? 'fermé' : 'ouvert';
        
        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => "Topic {$status} avec succès."
        ]);
        
        $this->showCloseTopicModal = false;
    }

    // Delete post functionality (Admin only)
    public function showDeletePostModalOpen($postId)
    {
        if (!Auth::check() || !Auth::user()->hasAdmin()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas les permissions nécessaires.'
            ]);
            return;
        }

        $this->deletingPostId = $postId;
        $this->showDeletePostModal = true;
    }

    public function cancelDeletePost()
    {
        $this->showDeletePostModal = false;
        $this->deletingPostId = null;
    }

    public function deletePost()
    {
        if (!Auth::check() || !Auth::user()->hasAdmin()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas les permissions nécessaires.'
            ]);
            return;
        }

        if (!$this->deletingPostId) {
            return;
        }

        $post = ForumPostModel::findOrFail($this->deletingPostId);
        
        // Vérifier que ce n'est pas le premier post du topic
        $firstPost = ForumPostModel::where('topic_id', $this->topic->id)
            ->orderBy('created_at', 'asc')
            ->first();
            
        if ($post->id === $firstPost->id) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Impossible de supprimer le premier message du topic. Supprimez le topic entier à la place.'
            ]);
            $this->cancelDeletePost();
            return;
        }
        
        // Supprimer le post
        $post->delete();
                
        // Mettre à jour le dernier post du topic si nécessaire
        if ($this->topic->last_post_id === $post->id) {
            $lastPost = ForumPostModel::where('topic_id', $this->topic->id)
                ->orderBy('created_at', 'desc')
                ->first();
                
            if ($lastPost) {
                $this->topic->update([
                    'last_post_id' => $lastPost->id,
                    'last_post_user_id' => $lastPost->user_id,
                    'last_post_at' => $lastPost->created_at,
                ]);
            }
        }
        
        // Mettre à jour le dernier post du forum si nécessaire
        if ($this->forum->last_post_id === $post->id) {
            $lastForumPost = ForumPostModel::whereHas('topic', function($query) {
                $query->where('forum_id', $this->forum->id);
            })->orderBy('created_at', 'desc')->first();
            
            if ($lastForumPost) {
                $this->forum->update([
                    'last_post_id' => $lastForumPost->id,
                    'last_post_at' => $lastForumPost->created_at,
                ]);
            }
        }
        
        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => 'Message supprimé avec succès.'
        ]);
        
        $this->cancelDeletePost();
    }

    /**
     * Transform [quote] tags into proper HTML blockquotes
     */
    public function parseQuotes($content)
    {
        // Pattern to match [quote=username]content[/quote]
        $pattern = '/\[quote=([^\]]+)\]([\s\S]*?)\[\/quote\]/i';
        
        $content = preg_replace_callback($pattern, function($matches) {
            $username = htmlspecialchars($matches[1]);
            $quotedContent = trim($matches[2]);
            
            return '<blockquote class="forum-quote">' .
                   '<div class="quote-header">' .
                   '<i class="fas fa-quote-left"></i> ' .
                   '<strong>' . $username . ' a écrit :</strong>' .
                   '</div>' .
                   '<div class="quote-content">' . nl2br(htmlspecialchars($quotedContent)) . '</div>' .
                   '</blockquote>';
        }, $content);
        
        // Pattern to match simple [quote]content[/quote] without username
        $simplePattern = '/\[quote\]([\s\S]*?)\[\/quote\]/i';
        
        $content = preg_replace_callback($simplePattern, function($matches) {
            $quotedContent = trim($matches[1]);
            
            return '<blockquote class="forum-quote">' .
                   '<div class="quote-header">' .
                   '<i class="fas fa-quote-left"></i> ' .
                   '<strong>Citation :</strong>' .
                   '</div>' .
                   '<div class="quote-content">' . nl2br(htmlspecialchars($quotedContent)) . '</div>' .
                   '</blockquote>';
        }, $content);
        
        return $content;
    }

    public function render()
    {
        $posts = ForumPostModel::where('topic_id', $this->topic->id)
            ->with(['user'])
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return view('livewire.game.forum-post', [
            'posts' => $posts
        ]);
    }

    /**
     * Récupère l'URL d'avatar pour un utilisateur (custom ou Gravatar)
     */
    public function getUserAvatarUrl(int $userId, int $size = 64): ?string
    {
        $user = \App\Models\User::find($userId);
        return $user ? $user->getUserAvatarUrl($size) : null;
    }
}