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
            <span class="breadcrumb-current">{{ $forum->name ?? 'Forum' }}</span>
        </nav>

        {{-- Forum Header --}}
        <div class="topics-header">
            <div class="forum-info">
                @if($forum->icon)
                    <i class="{{ $forum->icon }} forum-icon"></i>
                @endif
                <div>
                    <h2 class="forum-title">{{ $forum->name }}</h2>
                    @if($forum->description)
                        <p class="forum-description">{{ $forum->description }}</p>
                    @endif
                </div>
            </div>
            
            @if(!$forum->is_locked || auth()->user()->hasModeratorRights() || auth()->user()->hasAdminRights())
                <button wire:click="toggleNewTopicForm" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouveau Sujet
                </button>
            @endif
        </div>

        {{-- Affichage des sous-forums --}}
        @if($forum->children && $forum->children->count() > 0)
            <div class="subforums-section mb-2">
                <h4 class="subforums-title">
                    <i class="fas fa-folder-open"></i> Sous-forums
                </h4>
                <div class="subforums-grid">
                    @foreach($forum->children as $subforum)
                        <a href="{{ route('game.forum.topics', ['categoryId' => $categoryId, 'forumId' => $subforum->slug]) }}" class="subforum-card">
                            <div class="subforum-header">
                                @if($subforum->icon)
                                    <i class="{{ $subforum->icon }} subforum-icon"></i>
                                @else
                                    <i class="fas fa-comments subforum-icon"></i>
                                @endif
                                <div class="subforum-info">
                                    <h5 class="subforum-name">
                                        {{ $subforum->name }}
                                        @if($subforum->is_locked)
                                            <i class="fas fa-lock text-warning"></i>
                                        @endif
                                    </h5>
                                    @if($subforum->description)
                                        <p class="subforum-description">{{ $subforum->description }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="subforum-stats">
                                <span class="subforum-stat">
                                    <i class="fas fa-comments"></i> {{ $subforum->topics_count() }}
                                </span>
                                <span class="subforum-stat">
                                    <i class="fas fa-comment"></i> {{ $subforum->posts_count() }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- New Topic Form --}}
        @if($showNewTopicForm)
            <div class="new-topic-form">
                <div class="form-header">
                    <h4><i class="fas fa-plus-circle"></i> Créer un Nouveau Sujet</h4>
                    <button wire:click="toggleNewTopicForm" class="btn-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form wire:submit.prevent="createTopic">
                    <div class="form-group">
                        <label for="topicTitle"><i class="fas fa-heading"></i> Titre du sujet</label>
                        <input type="text" id="topicTitle" wire:model="newTopicTitle" class="form-control" placeholder="Entrez le titre du sujet...">
                        @error('newTopicTitle') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="topicContent"><i class="fas fa-edit"></i> Contenu</label>
                        <x-input.tinymce wire:model.live="newTopicContent" placeholder="Rédigez votre message..."></x-input.tinymce>
                        @error('newTopicContent') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Créer le Sujet
                        </button>
                        <button type="button" wire:click="toggleNewTopicForm" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Topics List --}}
        <div class="topics-list">
            @if(Auth::check() && Auth::user()->hasAdmin())
                <div class="bulk-actions-header">
                    <div class="select-all-container">
                        <input type="checkbox" id="selectAll" wire:model.live="selectAll" wire:change="toggleSelectAll">
                        <label for="selectAll">Tout sélectionner</label>
                    </div>
                    
                    @if($showBulkActions)
                        <div class="bulk-actions">
                            <button type="button" class="btn btn-warning" wire:click="showMoveTopicsModal">
                                <i class="fas fa-arrows-alt"></i> Déplacer ({{ count($selectedTopics) }})
                            </button>
                            <button type="button" class="btn btn-danger" wire:click="showDeleteTopicsModal">
                                <i class="fas fa-trash"></i> Supprimer ({{ count($selectedTopics) }})
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            @forelse($topics as $topic)
                <div class="topic-item {{ $topic->is_pinned ? 'pinned' : '' }} {{ $topic->is_locked ? 'locked' : '' }}">
                    @if(Auth::check() && Auth::user()->hasAdmin())
                        <div class="topic-checkbox">
                            <input type="checkbox" 
                                id="topic_{{ $topic->id }}" 
                                value="{{ $topic->id }}" 
                                wire:change="toggleTopicSelection({{ $topic->id }})"
                                @if(in_array($topic->id, $selectedTopics)) checked @endif>
                        </div>
                    @endif
                    <div class="topic-content">
                        <div class="topic-header">
                            <h4 class="topic-title">
                                <a href="{{ route('game.forum.topic', ['categoryId' => $categoryId, 'forumId' => $forumId, 'topicId' => $topic->slug]) }}">
                                    {{ $topic->title }}
                                </a>
                            </h4>
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
                        
                        <div class="topic-meta">
                            <span class="topic-author">
                                <i class="fas fa-user"></i> {{ $topic->user->name }}
                            </span>
                            <span class="topic-date">
                                <i class="fas fa-calendar"></i> {{ $topic->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="topic-stats">
                        <div class="topic-stat">
                            <span class="topic-stat-number">{{ $topic->posts_count() }}</span>
                            <span class="topic-stat-label">Réponses</span>
                        </div>
                        <div class="topic-stat">
                            <span class="topic-stat-number">{{ $topic->views_count }}</span>
                            <span class="topic-stat-label">Vues</span>
                        </div>
                    </div>
                    
                    @if($topic->lastPost)
                        <div class="topic-last-post">
                            <div class="topic-last-post-date">{{ $topic->last_post_at->format('d/m/Y H:i') }}</div>
                            <div class="topic-last-post-user">par {{ $topic->lastPost->user->name }}</div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3 class="empty-title">Aucun sujet</h3>
                    <p class="empty-description">Il n'y a pas encore de sujets dans ce forum.</p>
                    @if(!$forum->is_locked)
                        <button wire:click="toggleNewTopicForm" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Créer le premier sujet
                        </button>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($topics->hasPages())
            <div class="pagination-wrapper">
                {{ $topics->links() }}
            </div>
        @endif

        {{-- Move Topics Modal --}}
        @if($showMoveModal)
            <div class="modal-overlay" wire:click="cancelMove">
                <div class="modal-content" wire:click.stop>
                    <div class="modal-header">
                        <h3 class="modal-title">Déplacer les topics sélectionnés</h3>
                        <button type="button" class="btn-close" wire:click="cancelMove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Vous êtes sur le point de déplacer {{ count($selectedTopics) }} topic(s) vers un autre forum.
                        </p>
                        
                        <div class="form-group">
                            <label for="targetForum">Forum de destination :</label>
                            <select id="targetForum" wire:model="targetForumId" class="form-control">
                                <option value="">Sélectionnez un forum</option>
                                @foreach($availableForums as $availableForum)
                                    <option value="{{ $availableForum->id }}">
                                        {{ $availableForum->category->name }} > {{ $availableForum->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelMove">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button type="button" class="btn btn-warning" wire:click="moveSelectedTopics">
                            <i class="fas fa-arrows-alt"></i> Déplacer
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Delete Topics Modal --}}
        @if($showDeleteModal)
            <div class="modal-overlay" wire:click="cancelDelete">
                <div class="modal-content" wire:click.stop>
                    <div class="modal-header">
                        <h3 class="modal-title">Supprimer les topics sélectionnés</h3>
                        <button type="button" class="btn-close" wire:click="cancelDelete">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Attention !</strong> Vous êtes sur le point de supprimer définitivement {{ count($selectedTopics) }} topic(s) et tous leurs messages.
                            Cette action est irréversible.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDelete">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deleteSelectedTopics">
                            <i class="fas fa-trash"></i> Supprimer définitivement
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>