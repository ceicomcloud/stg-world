<div page="forum">
    <div class="forum-container">
        {{-- Forum Header --}}
        @if($currentView === 'categories')
            <div class="forum-header">
                <h1 class="forum-title">
                    <i class="fas fa-comments"></i>
                    Forum Communautaire {{ config('app.name') }}
                </h1>
                <p class="forum-subtitle">Échangez, partagez et discutez avec la communauté</p>
            </div>
        @endif

        {{-- Breadcrumb Navigation --}}
        <nav class="breadcrumb-nav mb-4">
            <button wire:click="showCategories" class="breadcrumb-item {{ $currentView === 'categories' ? 'active' : '' }}">
                <i class="fas fa-home"></i> Forum Principal
            </button>
            
            @if($selectedCategory)
                <span class="breadcrumb-separator">></span>
                <span class="breadcrumb-current">{{ $selectedCategory->name }}</span>
            @endif
        </nav>

        {{-- Vue des Catégories --}}
        @if($currentView === 'categories')
            <div class="forum-categories">
                
                @forelse($categories as $category)
                    <div class="category-card">
                        <div class="category-header" style="border-left: 4px solid {{ $category->color ?? '#007bff' }}">
                            <div class="category-info">
                                @if($category->icon)
                                    <i class="{{ $category->icon }} category-icon"></i>
                                @endif
                                <div>
                                    <h3 class="category-name">{{ $category->name }}</h3>
                                    @if($category->description)
                                        <p class="category-description">{{ $category->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        @if($category->forums->count() > 0)
                            <div class="forums-list">
                                @foreach($category->forums as $forum)
                                    <a href="{{ route('game.forum.topics', ['categoryId' => $category->slug, 'forumId' => $forum->slug]) }}" class="forum-item">
                                        <div class="forum-info">
                                            @if($forum->icon)
                                                <i class="{{ $forum->icon }} forum-icon"></i>
                                            @endif
                                            <div>
                                                <h4 class="forum-name">
                                                    {{ $forum->name }}
                                                    @if($forum->is_locked)
                                                        <i class="fas fa-lock text-warning"></i>
                                                    @endif
                                                </h4>
                                                @if($forum->description)
                                                    <p class="forum-description">{{ $forum->description }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="forum-stats">
                                            <div class="stat">
                                                <span class="stat-number">{{ $forum->topics_count() }}</span>
                                                <span class="stat-label">Sujets</span>
                                            </div>
                                            <div class="stat">
                                                <span class="stat-number">{{ $forum->posts_count() }}</span>
                                                <span class="stat-label">Messages</span>
                                            </div>
                                        </div>
                                        @if($forum->lastPost)
                                            <div class="forum-last-post">
                                                <div class="last-post-info">
                                                    <div class="last-post-date">{{ $forum->last_post_at->diffForHumans() }}</div>
                                                    <div class="last-post-user">par {{ $forum->lastPost->user->name }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="no-forums">
                                <p>Aucun forum dans cette catégorie pour le moment.</p>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="no-categories">
                        <p>Aucune catégorie de forum disponible.</p>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- Forums View --}}
        @if($currentView === 'forums')
            <div class="forum-list">
                <h2 class="forum-title">
                    <i class="fas fa-folder"></i> {{ $selectedCategory->name }}
                </h2>
                
                @forelse($forums as $forum)
                    <div class="forum-group">
                        <a href="{{ route('game.forum.topics', ['categoryId' => $selectedCategory->slug, 'forumId' => $forum->slug]) }}" class="forum-item">
                            <div class="forum-info">
                                @if($forum->icon)
                                    <i class="{{ $forum->icon }} forum-icon"></i>
                                @endif
                                <div>
                                    <h3 class="forum-name">
                                        {{ $forum->name }}
                                        @if($forum->is_locked)
                                            <i class="fas fa-lock text-warning"></i>
                                        @endif
                                        @if($forum->children->count() > 0)
                                            <span class="subforum-count">({{ $forum->children->count() }} sous-forums)</span>
                                        @endif
                                    </h3>
                                    @if($forum->description)
                                        <p class="forum-description">{{ $forum->description }}</p>
                                    @endif
                                    
                                    {{-- Affichage des sous-forums --}}
                                    @if($forum->children->count() > 0)
                                        <div class="subforums">
                                            <span class="subforums-label">Sous-forums :</span>
                                            @foreach($forum->children as $index => $subforum)
                                                <a href="{{ route('game.forum.topics', ['categoryId' => $selectedCategory->id, 'forumId' => $subforum->id]) }}" class="subforum-link">
                                                    {{ $subforum->name }}
                                                    @if($subforum->is_locked)
                                                        <i class="fas fa-lock text-warning"></i>
                                                    @endif
                                                </a>@if(!$loop->last), @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="forum-stats">
                                <div class="stat">
                                    <span class="stat-number">{{ $forum->topics_count() }}</span>
                                    <span class="stat-label">Sujets</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-number">{{ $forum->posts_count() }}</span>
                                    <span class="stat-label">Messages</span>
                                </div>
                            </div>
                            
                            @if($forum->lastPost)
                                <div class="forum-last-post">
                                    <div class="last-post-info">
                                        <div class="last-post-date">{{ $forum->last_post_at->diffForHumans() }}</div>
                                        <div class="last-post-user">par {{ $forum->lastPost->user->name }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="forum-last-post">
                                    <div class="last-post-info">
                                        <div class="last-post-date">Aucun message</div>
                                        <div class="last-post-user">-</div>
                                    </div>
                                </div>
                            @endif
                        </a>
                    </div>
                @empty
                    <div class="no-forums">
                        <p>Aucun forum dans cette catégorie.</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</div>