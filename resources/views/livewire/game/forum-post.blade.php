<div page="forum">
    <div class="forum-container">
        {{-- Breadcrumb Navigation --}}
        <nav class="breadcrumb-nav mb-4">
            <a href="{{ route('game.forum') }}" class="breadcrumb-item">
                <i class="fas fa-home"></i> Forum
            </a>
            <span class="breadcrumb-separator">></span>
            <a href="{{ route('game.forum') }}" class="breadcrumb-item">
                {{ $category->name ?? 'Catégorie' }}
            </a>
            <span class="breadcrumb-separator">></span>
            <a href="{{ route('game.forum.topics', ['categoryId' => $categoryId, 'forumId' => $forumId]) }}" class="breadcrumb-item">
                {{ $forum->name ?? 'Forum' }}
            </a>
            <span class="breadcrumb-separator">></span>
            <span class="breadcrumb-current">{{ $topic->title ?? 'Topic' }}</span>
        </nav>

        {{-- Topic Header --}}
        <div class="topic-header mb-4">
            <div class="topic-info">
                <h2 class="topic-title">
                    @php($authorAvatar = $this->getUserAvatarUrl($topic->user_id, 32))
                    @if(!empty($authorAvatar))
                        <img src="{{ $authorAvatar }}" alt="Avatar de {{ $topic->user->name }}" style="width:24px; height:24px; border-radius:50%; object-fit:cover; vertical-align:middle; margin-right:8px;" />
                    @endif
                    {{ $topic->title }}
                </h2>
                <div class="topic-meta">
                    <span class="topic-author">
                        <i class="fas fa-user"></i> Créé par {{ $topic->user->name }}
                    </span>
                    <span class="topic-date">
                        <i class="fas fa-calendar"></i> {{ $topic->created_at->format('d/m/Y H:i') }}
                    </span>
                </div>
                
                <div class="topic-badges">
                    @if($topic->is_pinned)
                        <span class="badge badge-pinned">
                            <i class="fas fa-thumbtack"></i> Épinglé
                        </span>
                    @endif
                    @if($topic->is_locked)
                        <span class="badge badge-locked">
                            <i class="fas fa-lock"></i> Verrouillé
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="topic-actions">
                @if(!$topic->is_locked || auth()->user()->hasModeratorRights() || auth()->user()->hasAdminRights())
                    <button wire:click="toggleReplyForm" class="btn btn-primary">
                        <i class="fas fa-reply"></i> Répondre
                    </button>
                @endif
                
                @auth
                    @if(auth()->user()->hasAdmin())
                        <div class="admin-actions">
                            <button wire:click="showCloseTopicModalOpen" class="btn btn-warning">
                                @if($topic->is_locked)
                                    <i class="fas fa-unlock"></i> Ouvrir
                                @else
                                    <i class="fas fa-lock"></i> Fermer
                                @endif
                            </button>
                            <button wire:click="showDeleteTopicModalOpen" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </div>
                    @endif
                @endauth
            </div>
        </div>

        {{-- Reply Form --}}
        @if($showReplyForm)
            <div class="reply-form">
                <div class="form-header">
                    <h4>
                        <i class="fas fa-reply"></i> Répondre au sujet
                    </h4>
                    <button wire:click="toggleReplyForm" class="btn-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form wire:submit.prevent="createPost">
                    <div class="form-group">
                        <label for="postContent">
                            <i class="fas fa-edit"></i> Votre réponse
                        </label>
                        <x-input.tinymce wire:model.live="newPostContent" placeholder="Votre réponse..."></x-input.tinymce>
                        @error('newPostContent') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Publier la Réponse
                        </button>
                        <button type="button" wire:click="toggleReplyForm" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Posts List --}}
        <div class="posts-list">
            @forelse($posts as $index => $post)
                <div class="post-item {{ $index === 0 ? 'first-post' : '' }}">
                    <div class="post-sidebar">
                        <div class="user-info">
                            <div class="user-avatar">
                                @php($avatar = $this->getUserAvatarUrl($post->user_id, 64))
                                @if(!empty($avatar))
                                    <img src="{{ $avatar }}" alt="{{ $post->user->name }}" />
                                @else
                                    <div class="avatar-placeholder">
                                        {{ strtoupper(substr($post->user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="user-name">{{ $post->user->name }}</div>
                            <div class="user-role">
                                <i class="{{ $post->user->getRoleIcon() }}"></i>
                                {{ $post->user->getRoleName() }}
                            </div>
                            <div class="user-stats">
                                <small>Messages: {{ $post->user->posts_count() ?? 0 }}</small>
                                <br />
                                <small>Sujets: {{ $post->user->posts_count() ?? 0 }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="post-content">
                        <div class="post-header">
                            <div class="post-number">#{{ ($posts->currentPage() - 1) * $posts->perPage() + $index + 1 }}</div>
                            <div class="post-date">{{ $post->created_at->format('d/m/Y H:i') }}</div>
                            @if($post->updated_at != $post->created_at)
                                <div class="post-edited">
                                    <i class="fas fa-edit"></i> Modifié le {{ $post->updated_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>
                        
                        @if($editingPostId === $post->id)
                            {{-- Edit Form --}}
                            <div class="post-edit-form">
                                <div class="form-group mb-3">
                                    <label for="editPostContent" class="form-label">Modifier le message</label>
                                    <x-input.tinymce wire:model="editPostContent" placeholder="Rédigez votre message..."></x-input.tinymce>
                                    @error('editPostContent') <span class="form-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="edit-actions">
                                    <button wire:click="updatePost" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Sauvegarder
                                    </button>
                                    <button wire:click="cancelEdit" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Annuler
                                    </button>
                                </div>
                            </div>
                        @else
                            {{-- Post Content --}}
                            <div class="post-body">
                                {!! $this->parseQuotes($post->content) !!}
                                @if($post->updated_at > $post->created_at)
                                    <div class="post-edited-info">
                                        <small class="text-muted">
                                            <i class="fas fa-edit"></i> Modifié le {{ $post->updated_at->format('d/m/Y H:i') }}
                                        </small>
                                    </div>
                                @endif
                            </div>
                            
                            {{-- Post Actions --}}
                            <div class="post-actions">
                                @if(auth()->id() === $post->user_id || auth()->user()->hasAdmin())
                                    <button wire:click="startEditPost({{ $post->id }})" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                @endif
                                @if(auth()->check() && auth()->user()->hasAdmin())
                                    <button wire:click="showDeletePostModalOpen({{ $post->id }})" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                @endif
                                <button wire:click="quotePost({{ $post->id }})" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-quote-left"></i> Citer
                                </button>
                                @auth
                                    <button wire:click="startReportPost({{ $post->id }})" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-flag"></i> Signaler
                                    </button>
                                @endauth
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3 class="empty-title">Aucun message</h3>
                    <p class="empty-description">Il n'y a pas encore de messages dans ce sujet.</p>
                    @if(!$topic->is_locked)
                        <button wire:click="toggleReplyForm" class="btn btn-outline-primary">
                            <i class="fas fa-plus"></i> Soyez le premier à répondre
                        </button>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($posts->hasPages())
            <div class="pagination-wrapper">
                {{ $posts->links() }}
            </div>
        @endif

        {{-- Quick Reply --}}
        @if(!$topic->is_locked && !$showReplyForm)
            <div class="quick-reply mt-4">
                <button wire:click="toggleReplyForm" class="btn btn-primary btn-block">
                    <i class="fas fa-reply"></i> Réponse Rapide
                </button>
            </div>
        @endif

        {{-- Report Modal --}}
        @if($showReportModal)
            <div class="modal-overlay" wire:click="cancelReport">
                <div class="modal-content" wire:click.stop>
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-flag text-warning"></i> Signaler un message
                        </h5>
                        <button wire:click="cancelReport" class="btn-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="reportReason" class="form-label">Raison du signalement *</label>
                            <select wire:model="reportReason" id="reportReason" class="form-control">
                                <option value="">Sélectionnez une raison</option>
                                <option value="spam">Spam</option>
                                <option value="inappropriate">Contenu inapproprié</option>
                                <option value="harassment">Harcèlement</option>
                                <option value="off-topic">Hors sujet</option>
                                <option value="duplicate">Contenu dupliqué</option>
                                <option value="other">Autre</option>
                            </select>
                            @error('reportReason') 
                                <div class="text-danger mt-1">{{ $message }}</div> 
                            @enderror
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="reportDescription" class="form-label">Description (optionnel)</label>
                            <textarea 
                                wire:model="reportDescription" 
                                id="reportDescription" 
                                class="form-control" 
                                rows="4" 
                                placeholder="Décrivez le problème en détail..."
                                maxlength="500"
                            ></textarea>
                            @error('reportDescription') 
                                <div class="text-danger mt-1">{{ $message }}</div> 
                            @enderror
                            <small>Maximum 500 caractères</small>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button wire:click="submitReport" class="btn btn-warning">
                            <i class="fas fa-flag"></i> Signaler
                        </button>
                        <button wire:click="cancelReport" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Delete Topic Modal --}}
        @if($showDeleteTopicModal)
            <div class="modal-overlay" wire:click="cancelDeleteTopic">
                <div class="modal-content" wire:click.stop>
                    <div class="modal-header">
                        <h3 class="modal-title">Supprimer le topic</h3>
                        <button wire:click="cancelDeleteTopic" class="btn-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Attention !</strong> Cette action est irréversible.
                        </div>
                        <p>Êtes-vous sûr de vouloir supprimer ce topic et tous ses messages ?</p>
                        <p><strong>Topic :</strong> {{ $topic->title }}</p>
                    </div>
                    
                    <div class="modal-footer">
                        <button wire:click="cancelDeleteTopic" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button wire:click="deleteTopic" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Supprimer définitivement
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Close/Open Topic Modal --}}
        @if($showCloseTopicModal)
            <div class="modal-overlay" wire:click="cancelCloseTopic">
                <div class="modal-content" wire:click.stop>
                    <div class="modal-header">
                        <h3 class="modal-title">
                            @if($topic->is_locked)
                                Ouvrir le topic
                            @else
                                Fermer le topic
                            @endif
                        </h3>
                        <button wire:click="cancelCloseTopic" class="btn-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="modal-body">
                        <p>
                            @if($topic->is_locked)
                                Êtes-vous sûr de vouloir ouvrir ce topic ? Les utilisateurs pourront à nouveau y répondre.
                            @else
                                Êtes-vous sûr de vouloir fermer ce topic ? Les utilisateurs ne pourront plus y répondre.
                            @endif
                        </p>
                        <p><strong>Topic :</strong> {{ $topic->title }}</p>
                    </div>
                    
                    <div class="modal-footer">
                        <button wire:click="cancelCloseTopic" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button wire:click="toggleTopicLock" class="btn btn-warning">
                            @if($topic->is_locked)
                                <i class="fas fa-unlock"></i> Ouvrir
                            @else
                                <i class="fas fa-lock"></i> Fermer
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Delete Post Modal --}}
        @if($showDeletePostModal)
            <div class="modal-overlay" wire:click="cancelDeletePost">
                <div class="modal-content" wire:click.stop>
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-trash text-danger"></i> Supprimer le message
                        </h5>
                        <button wire:click="cancelDeletePost" class="btn-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="modal-body">
                        <p class="text-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Attention !</strong> Cette action est irréversible.
                        </p>
                        <p>Êtes-vous sûr de vouloir supprimer ce message ?</p>
                        <p><strong>Note :</strong> Le premier message d'un topic ne peut pas être supprimé. Pour supprimer le premier message, vous devez supprimer le topic entier.</p>
                    </div>
                    
                    <div class="modal-footer">
                        <button wire:click="cancelDeletePost" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button wire:click="deletePost" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
