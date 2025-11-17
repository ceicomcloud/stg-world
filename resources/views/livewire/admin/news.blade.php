<div class="admin-news">
    <div class="admin-page-header">
        <h1>Gestion des actualités</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button {{ $activeTab === 'list' ? 'active' : '' }}" wire:click="setActiveTab('list')">
                Liste des actualités
            </button>
            <button class="admin-tab-button {{ $activeTab === 'create' ? 'active' : '' }}" wire:click="setActiveTab('create')">
                Créer une actualité
            </button>
            @if($selectedNews)
                <button class="admin-tab-button {{ $activeTab === 'edit' ? 'active' : '' }}" wire:click="setActiveTab('edit')">
                Modifier l'actualité
                </button>
            @endif
        </div>
    </div>

    <!-- Statistiques des actualités -->
    @if($activeTab === 'list')
    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-card-icon">
                <i class="fas fa-newspaper"></i>
            </div>
            <div class="admin-stat-card-content">
                <div class="admin-stat-value">{{ $newsStats['total'] }}</div>
                <div class="admin-stat-label">Actualités au total</div>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="admin-stat-card-content">
                <div class="admin-stat-value">{{ $newsStats['published'] }}</div>
                <div class="admin-stat-label">Actualités publiées</div>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-card-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="admin-stat-card-content">
                <div class="admin-stat-value">{{ $newsStats['scheduled'] }}</div>
                <div class="admin-stat-label">Actualités programmées</div>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-card-icon">
                <i class="fas fa-thumbtack"></i>
            </div>
            <div class="admin-stat-card-content">
                <div class="admin-stat-value">{{ $newsStats['pinned'] }}</div>
                <div class="admin-stat-label">Actualités épinglées</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Liste des actualités -->
    @if($activeTab === 'list')
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Liste des actualités</h2>
                <div class="admin-card-tools">
                    <div class="admin-search">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="admin-input">
                    </div>
                </div>
            </div>
            <div class="admin-card-body">
                <div class="admin-filters">
                    <div class="admin-filter">
                        <label for="filterCategory" class="admin-filter-label">Catégorie</label>
                        <select id="filterCategory" wire:model.live="filterCategory" class="admin-select">
                            <option value="">Toutes les catégories</option>
                            @foreach($newsCategories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="admin-filter">
                        <label for="filterPriority" class="admin-filter-label">Priorité</label>
                        <select id="filterPriority" wire:model.live="filterPriority" class="admin-select">
                            <option value="">Toutes les priorités</option>
                            @foreach($newsPriorities as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="admin-filter">
                        <label for="filterStatus" class="admin-filter-label">Statut</label>
                        <select id="filterStatus" wire:model.live="filterStatus" class="admin-select">
                            <option value="">Tous les statuts</option>
                            <option value="published">Publiées</option>
                            <option value="draft">Brouillons</option>
                            <option value="scheduled">Programmées</option>
                            <option value="expired">Expirées</option>
                            <option value="active">Actives</option>
                            <option value="inactive">Inactives</option>
                        </select>
                    </div>
                    <div class="admin-filter">
                        <label for="perPage" class="admin-filter-label">Par page</label>
                        <select id="perPage" wire:model.live="perPage" class="admin-select">
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <div class="admin-table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th wire:click="sortBy('title')" class="admin-table-sortable">
                                    Titre
                                    @if($sortField === 'title')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('category')" class="admin-table-sortable">
                                    Catégorie
                                    @if($sortField === 'category')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('priority')" class="admin-table-sortable">
                                    Priorité
                                    @if($sortField === 'priority')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('published_at')" class="admin-table-sortable">
                                    Date de publication
                                    @if($sortField === 'published_at')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($news as $item)
                                <tr>
                                    <td>
                                        <div class="admin-news-title">
                                            {{ $item->title }}
                                            @if($item->is_pinned)
                                                <i class="fas fa-thumbtack text-primary ml-2" title="Épinglée"></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="admin-badge admin-badge-primary">
                                            {{ $newsCategories[$item->category] ?? $item->category }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="admin-badge" style="background-color: {{ $item->getPriorityColor() }}">
                                            {{ $newsPriorities[$item->priority] ?? $item->priority }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->published_at)
                                            {{ $item->published_at->format('d/m/Y H:i') }}
                                        @else
                                            <span class="admin-badge admin-badge-secondary">Non publiée</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$item->is_active)
                                            <span class="admin-badge admin-badge-danger">Inactive</span>
                                        @elseif($item->isScheduled())
                                            <span class="admin-badge admin-badge-warning">Programmée</span>
                                        @elseif($item->isExpired())
                                            <span class="admin-badge admin-badge-danger">Expirée</span>
                                        @elseif($item->is_published)
                                            <span class="admin-badge admin-badge-success">Publiée</span>
                                        @else
                                            <span class="admin-badge admin-badge-secondary">Brouillon</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="admin-table-actions">
                                            <button class="admin-btn admin-btn-primary admin-btn-sm" wire:click="selectNews({{ $item->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="admin-table-empty">Aucune actualité trouvée</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="admin-pagination">
                    {{ $news->links() }}
                </div>
            </div>
        </div>
    @endif

    <!-- Formulaire de création d'actualité -->
    @if($activeTab === 'create')
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Créer une nouvelle actualité</h2>
            </div>
            <div class="admin-card-body">
                <form wire:submit.prevent="createNews" class="admin-form">
                    <div class="admin-form-group">
                        <label for="title" class="admin-form-label">Titre</label>
                        <input type="text" id="title" wire:model="newsForm.title" class="admin-form-input" placeholder="Titre de l'actualité">
                        @error('newsForm.title') <span class="admin-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <label for="category" class="admin-form-label">Catégorie</label>
                            <select id="category" wire:model="newsForm.category" class="admin-form-select">
                                @foreach($newsCategories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('newsForm.category') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="admin-form-group">
                            <label for="priority" class="admin-form-label">Priorité</label>
                            <select id="priority" wire:model="newsForm.priority" class="admin-form-select">
                                @foreach($newsPriorities as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('newsForm.priority') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <label for="content" class="admin-form-label">Contenu</label>
                        <textarea id="content" wire:model="newsForm.content" class="admin-form-textarea" rows="10" placeholder="Contenu de l'actualité"></textarea>
                        @error('newsForm.content') <span class="admin-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="admin-form-group">
                        <label for="excerpt" class="admin-form-label">Extrait (optionnel)</label>
                        <textarea id="excerpt" wire:model="newsForm.excerpt" class="admin-form-textarea" rows="3" placeholder="Extrait court de l'actualité"></textarea>
                        @error('newsForm.excerpt') <span class="admin-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <label for="image_url" class="admin-form-label">URL de l'image (optionnel)</label>
                            <input type="text" id="image_url" wire:model="newsForm.image_url" class="admin-form-input" placeholder="https://exemple.com/image.jpg">
                            @error('newsForm.image_url') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="admin-form-group">
                            <label for="external_url" class="admin-form-label">URL externe (optionnel)</label>
                            <input type="text" id="external_url" wire:model="newsForm.external_url" class="admin-form-input" placeholder="https://exemple.com">
                            @error('newsForm.external_url') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <label for="published_at" class="admin-form-label">Date de publication</label>
                            <input type="datetime-local" id="published_at" wire:model="newsForm.published_at" class="admin-form-input">
                            @error('newsForm.published_at') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="admin-form-group">
                            <label for="expires_at" class="admin-form-label">Date d'expiration (optionnel)</label>
                            <input type="datetime-local" id="expires_at" wire:model="newsForm.expires_at" class="admin-form-input">
                            @error('newsForm.expires_at') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <div class="admin-form-checkbox-group">
                                <input type="checkbox" id="is_published" wire:model="newsForm.is_published" class="admin-form-checkbox">
                                <label for="is_published" class="admin-form-checkbox-label">Publier</label>
                            </div>
                            @error('newsForm.is_published') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="admin-form-group">
                            <div class="admin-form-checkbox-group">
                                <input type="checkbox" id="is_pinned" wire:model="newsForm.is_pinned" class="admin-form-checkbox">
                                <label for="is_pinned" class="admin-form-checkbox-label">Épingler</label>
                            </div>
                            @error('newsForm.is_pinned') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <div class="admin-form-checkbox-group">
                            <input type="checkbox" id="is_active" wire:model="newsForm.is_active" class="admin-form-checkbox">
                            <label for="is_active" class="admin-form-checkbox-label">Actif</label>
                        </div>
                        @error('newsForm.is_active') <span class="admin-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="admin-form-actions">
                        <button type="button" class="admin-btn admin-btn-outline" wire:click="resetNewsForm">Réinitialiser</button>
                        <button type="button" class="admin-btn admin-btn-secondary" wire:click="publishNow">Publier maintenant</button>
                        <button type="submit" class="admin-btn admin-btn-primary">Créer l'actualité</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Formulaire d'édition d'actualité -->
    @if($activeTab === 'edit' && $selectedNews)
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Modifier l'actualité</h2>
            </div>
            <div class="admin-card-body">
                <form wire:submit.prevent="updateNews" class="admin-form">
                    <div class="admin-form-group">
                        <label for="edit_title" class="admin-form-label">Titre</label>
                        <input type="text" id="edit_title" wire:model="newsForm.title" class="admin-form-input" placeholder="Titre de l'actualité">
                        @error('newsForm.title') <span class="admin-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <label for="edit_category" class="admin-form-label">Catégorie</label>
                            <select id="edit_category" wire:model="newsForm.category" class="admin-form-select">
                                @foreach($newsCategories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('newsForm.category') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="admin-form-group">
                            <label for="edit_priority" class="admin-form-label">Priorité</label>
                            <select id="edit_priority" wire:model="newsForm.priority" class="admin-form-select">
                                @foreach($newsPriorities as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('newsForm.priority') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <label for="edit_content" class="admin-form-label">Contenu</label>
                        <textarea id="edit_content" wire:model="newsForm.content" class="admin-form-textarea" rows="10" placeholder="Contenu de l'actualité"></textarea>
                        @error('newsForm.content') <span class="admin-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="admin-form-group">
                        <label for="edit_excerpt" class="admin-form-label">Extrait (optionnel)</label>
                        <textarea id="edit_excerpt" wire:model="newsForm.excerpt" class="admin-form-textarea" rows="3" placeholder="Extrait court de l'actualité"></textarea>
                        @error('newsForm.excerpt') <span class="admin-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <label for="edit_image_url" class="admin-form-label">URL de l'image (optionnel)</label>
                            <input type="text" id="edit_image_url" wire:model="newsForm.image_url" class="admin-form-input" placeholder="https://exemple.com/image.jpg">
                            @error('newsForm.image_url') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="admin-form-group">
                            <label for="edit_external_url" class="admin-form-label">URL externe (optionnel)</label>
                            <input type="text" id="edit_external_url" wire:model="newsForm.external_url" class="admin-form-input" placeholder="https://exemple.com">
                            @error('newsForm.external_url') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <label for="edit_published_at" class="admin-form-label">Date de publication</label>
                            <input type="datetime-local" id="edit_published_at" wire:model="newsForm.published_at" class="admin-form-input">
                            @error('newsForm.published_at') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="admin-form-group">
                            <label for="edit_expires_at" class="admin-form-label">Date d'expiration (optionnel)</label>
                            <input type="datetime-local" id="edit_expires_at" wire:model="newsForm.expires_at" class="admin-form-input">
                            @error('newsForm.expires_at') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <div class="admin-form-checkbox-group">
                                <input type="checkbox" id="edit_is_published" wire:model="newsForm.is_published" class="admin-form-checkbox">
                                <label for="edit_is_published" class="admin-form-checkbox-label">Publier</label>
                            </div>
                            @error('newsForm.is_published') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="admin-form-group">
                            <div class="admin-form-checkbox-group">
                                <input type="checkbox" id="edit_is_pinned" wire:model="newsForm.is_pinned" class="admin-form-checkbox">
                                <label for="edit_is_pinned" class="admin-form-checkbox-label">Épingler</label>
                            </div>
                            @error('newsForm.is_pinned') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <div class="admin-form-checkbox-group">
                            <input type="checkbox" id="edit_is_active" wire:model="newsForm.is_active" class="admin-form-checkbox">
                            <label for="edit_is_active" class="admin-form-checkbox-label">Actif</label>
                        </div>
                        @error('newsForm.is_active') <span class="admin-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="admin-form-actions">
                        <button type="button" class="admin-btn admin-btn-danger" wire:click="deleteNews" wire:confirm="Êtes-vous sûr de vouloir supprimer cette actualité ?">Supprimer</button>
                        <button type="button" class="admin-btn admin-btn-secondary" wire:click="publishNow">Publier maintenant</button>
                        <button type="submit" class="admin-btn admin-btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>