<div class="admin-badges">
    <div class="admin-page-header">
        <h1>Gestion des badges</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button {{ $activeTab === 'list' ? 'active' : '' }}" wire:click="setActiveTab('list')">
                <i class="fas fa-award"></i> Liste des badges
            </button>
            <button class="admin-tab-button {{ $activeTab === 'create' ? 'active' : '' }}" wire:click="setActiveTab('create')">
                <i class="fas fa-plus-circle"></i> Créer un badge
            </button>
            @if($selectedBadge)
                <button class="admin-tab-button {{ $activeTab === 'edit' ? 'active' : '' }}" wire:click="setActiveTab('edit')">
                    <i class="fas fa-edit"></i> {{ $selectedBadge->name }}
                </button>
            @endif
        </div>
    </div>

    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-icon admin-stat-icon-primary">
                <i class="fas fa-award"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value">{{ $badgeStats['total'] }}</div>
                <div class="admin-stat-label">Total des badges</div>
            </div>
        </div>
        
        <div class="admin-stat-card">
            <div class="admin-stat-icon admin-stat-icon-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value">{{ $badgeStats['active'] }}</div>
                <div class="admin-stat-label">Badges actifs</div>
            </div>
        </div>
        
        <div class="admin-stat-card">
            <div class="admin-stat-icon admin-stat-icon-danger">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value">{{ $badgeStats['inactive'] }}</div>
                <div class="admin-stat-label">Badges inactifs</div>
            </div>
        </div>
        
        <div class="admin-stat-card">
            <div class="admin-stat-icon admin-stat-icon-warning">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value">{{ $mostAwardedBadges->isNotEmpty() ? $mostAwardedBadges->first()['count'] : 0 }}</div>
                <div class="admin-stat-label">Badge le plus attribué</div>
            </div>
        </div>
    </div>

    <!-- Onglet Liste des badges -->
    @if($activeTab === 'list')
        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Liste des badges</h2>
                <div class="admin-card-tools">
                    <div class="admin-search-box">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="admin-search-input">
                        <i class="fas fa-search admin-search-icon"></i>
                    </div>
                </div>
            </div>
            <div class="admin-card-body">
                <div class="admin-filters">
                    <div class="admin-filter-group">
                        <label for="filterType">Type:</label>
                        <select id="filterType" wire:model.live="filterType" class="admin-select">
                            <option value="">Tous</option>
                            @foreach($badgeTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="admin-filter-group">
                        <label for="filterRarity">Rareté:</label>
                        <select id="filterRarity" wire:model.live="filterRarity" class="admin-select">
                            <option value="">Toutes</option>
                            @foreach($badgeRarities as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="admin-filter-group">
                        <label for="filterActive">Statut:</label>
                        <select id="filterActive" wire:model.live="filterActive" class="admin-select">
                            <option value="">Tous</option>
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                        </select>
                    </div>
                    <div class="admin-filter-group">
                        <label for="perPage">Par page:</label>
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
                                <th wire:click="sortBy('id')" class="admin-sortable">
                                    ID
                                    @if($sortField === 'id')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </th>
                                <th>Icône</th>
                                <th wire:click="sortBy('name')" class="admin-sortable">
                                    Nom
                                    @if($sortField === 'name')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('type')" class="admin-sortable">
                                    Type
                                    @if($sortField === 'type')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('rarity')" class="admin-sortable">
                                    Rareté
                                    @if($sortField === 'rarity')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('points_reward')" class="admin-sortable">
                                    Points
                                    @if($sortField === 'points_reward')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </th>
                                <th>Utilisateurs</th>
                                <th wire:click="sortBy('is_active')" class="admin-sortable">
                                    Statut
                                    @if($sortField === 'is_active')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($badges as $badge)
                                <tr>
                                    <td>{{ $badge->id }}</td>
                                    <td>
                                        <div class="admin-badge-icon-small admin-badge-{{ $badge->rarity }}">
                                            <i class="fas {{ $badge->icon }}"></i>
                                        </div>
                                    </td>
                                    <td>{{ $badge->name }}</td>
                                    <td>
                                        <span class="admin-badge admin-badge-{{ $badge->type }}">
                                            {{ $badgeTypes[$badge->type] ?? $badge->type }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="admin-badge admin-badge-{{ $badge->rarity }}">
                                            {{ $badgeRarities[$badge->rarity] ?? $badge->rarity }}
                                        </span>
                                    </td>
                                    <td>{{ $badge->points_reward }}</td>
                                    <td>{{ $this->getBadgeUserCount($badge->id) }}</td>
                                    <td>
                                        <span class="admin-badge {{ $badge->is_active ? 'admin-badge-success' : 'admin-badge-danger' }}">
                                            {{ $badge->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="admin-table-actions">
                                            <button class="admin-btn admin-btn-icon admin-btn-primary" wire:click="selectBadge({{ $badge->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="admin-btn admin-btn-icon admin-btn-danger" wire:click="deleteBadge({{ $badge->id }})" wire:confirm="Êtes-vous sûr de vouloir supprimer ce badge ?">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="admin-table-empty">Aucun badge trouvé</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="admin-pagination">
                    {{ $badges->links() }}
                </div>
            </div>
        </div>

        <!-- Statistiques des badges -->
        <div class="admin-grid-2">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Badges les plus attribués</h2>
                </div>
                <div class="admin-card-body">
                    @if($mostAwardedBadges->isNotEmpty())
                        <div class="admin-badges-grid">
                            @foreach($mostAwardedBadges as $item)
                                <div class="admin-badge-card">
                                    <div class="admin-badge-icon admin-badge-{{ $item['badge']->rarity }}">
                                        <i class="fas {{ $item['badge']->icon }}"></i>
                                    </div>
                                    <div class="admin-badge-info">
                                        <h4>{{ $item['badge']->name }}</h4>
                                        <p>{{ Str::limit($item['badge']->description, 100) }}</p>
                                        <div class="admin-badge-meta">
                                            <span class="admin-badge admin-badge-{{ $item['badge']->rarity }}">
                                                {{ $badgeRarities[$item['badge']->rarity] ?? $item['badge']->rarity }}
                                            </span>
                                            <span class="admin-badge-count">
                                                <i class="fas fa-users"></i> {{ $item['count'] }} utilisateurs
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="admin-empty-state">
                            <i class="fas fa-award"></i>
                            <p>Aucun badge n'a encore été attribué</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Badges récemment attribués</h2>
                </div>
                <div class="admin-card-body">
                    @if($recentlyAwardedBadges->isNotEmpty())
                        <div class="admin-table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Badge</th>
                                        <th>Utilisateur</th>
                                        <th>Date d'obtention</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentlyAwardedBadges as $userBadge)
                                        <tr>
                                            <td>
                                                <div class="admin-badge-row">
                                                    <div class="admin-badge-icon-small admin-badge-{{ $userBadge->badge->rarity }}">
                                                        <i class="fas {{ $userBadge->badge->icon }}"></i>
                                                    </div>
                                                    <span>{{ $userBadge->badge->name }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $userBadge->user->name }}</td>
                                            <td>{{ $userBadge->earned_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="admin-empty-state">
                            <i class="fas fa-trophy"></i>
                            <p>Aucun badge n'a encore été attribué</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Onglet Créer un badge -->
    @if($activeTab === 'create')
        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Créer un nouveau badge</h2>
            </div>
            <div class="admin-card-body">
                <form wire:submit.prevent="createBadge">
                    <div class="admin-form-section">
                        <h3>Informations générales</h3>
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="badgeName" class="admin-form-label">Nom du badge</label>
                                <input type="text" id="badgeName" wire:model="badgeForm.name" class="admin-input" placeholder="Nom du badge">
                                @error('badgeForm.name') <span class="admin-form-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="badgeIcon" class="admin-form-label">Icône (classe Font Awesome)</label>
                                <div class="admin-input-group">
                                    <span class="admin-input-group-text"><i class="fas {{ $badgeForm['icon'] ?: 'fa-award' }}"></i></span>
                                    <input type="text" id="badgeIcon" wire:model="badgeForm.icon" class="admin-input" placeholder="fa-award">
                                </div>
                                @error('badgeForm.icon') <span class="admin-form-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="badgeDescription" class="admin-form-label">Description</label>
                            <textarea id="badgeDescription" wire:model="badgeForm.description" class="admin-textarea" rows="3" placeholder="Description du badge"></textarea>
                            @error('badgeForm.description') <span class="admin-form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="admin-form-section">
                        <h3>Propriétés du badge</h3>
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="badgeType" class="admin-form-label">Type de badge</label>
                                <select id="badgeType" wire:model="badgeForm.type" class="admin-select">
                                    <option value="">Sélectionner un type</option>
                                    @foreach($badgeTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('badgeForm.type') <span class="admin-form-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="badgeRarity" class="admin-form-label">Rareté</label>
                                <select id="badgeRarity" wire:model="badgeForm.rarity" class="admin-select">
                                    <option value="">Sélectionner une rareté</option>
                                    @foreach($badgeRarities as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('badgeForm.rarity') <span class="admin-form-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="badgeRequirementType" class="admin-form-label">Type de condition</label>
                                <select id="badgeRequirementType" wire:model="badgeForm.requirement_type" class="admin-select">
                                    <option value="">Sélectionner une condition</option>
                                    @foreach($requirementTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('badgeForm.requirement_type') <span class="admin-form-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="badgeRequirementValue" class="admin-form-label">Valeur requise</label>
                                <input type="number" id="badgeRequirementValue" wire:model="badgeForm.requirement_value" class="admin-input" min="0">
                                @error('badgeForm.requirement_value') <span class="admin-form-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="badgePointsReward" class="admin-form-label">Points de récompense</label>
                                <input type="number" id="badgePointsReward" wire:model="badgeForm.points_reward" class="admin-input" min="0">
                                @error('badgeForm.points_reward') <span class="admin-form-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Statut</label>
                                <div class="admin-toggle-container">
                                    <label class="admin-toggle">
                                        <input type="checkbox" wire:model="badgeForm.is_active">
                                        <span class="admin-toggle-slider"></span>
                                    </label>
                                    <span>{{ $badgeForm['is_active'] ? 'Actif' : 'Inactif' }}</span>
                                </div>
                                @error('badgeForm.is_active') <span class="admin-form-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-actions">
                        <button type="button" class="admin-btn admin-btn-secondary" wire:click="resetBadgeForm">Réinitialiser</button>
                        <button type="submit" class="admin-btn admin-btn-primary">Créer le badge</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Onglet Éditer un badge -->
    @if($activeTab === 'edit' && $selectedBadge)
        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Éditer le badge: {{ $selectedBadge->name }}</h2>
            </div>
            <div class="admin-card-body">
                <form wire:submit.prevent="updateBadge">
                    <div class="admin-form-section">
                        <h3>Informations générales</h3>
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="editBadgeName" class="admin-form-label">Nom du badge</label>
                                <input type="text" id="editBadgeName" wire:model="badgeForm.name" class="admin-input" placeholder="Nom du badge">
                                @error('badgeForm.name') <span class="admin-form-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="editBadgeIcon" class="admin-form-label">Icône (classe Font Awesome)</label>
                                <div class="admin-input-group">
                                    <span class="admin-input-group-text"><i class="fas {{ $badgeForm['icon'] ?: 'fa-award' }}"></i></span>
                                    <input type="text" id="editBadgeIcon" wire:model="badgeForm.icon" class="admin-input" placeholder="fa-award">
                                </div>
                                @error('badgeForm.icon') <span class="admin-form-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="editBadgeDescription" class="admin-form-label">Description</label>
                            <textarea id="editBadgeDescription" wire:model="badgeForm.description" class="admin-textarea" rows="3" placeholder="Description du badge"></textarea>
                            @error('badgeForm.description') <span class="admin-form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="admin-form-section">
                        <h3>Propriétés du badge</h3>
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="editBadgeType" class="admin-form-label">Type de badge</label>
                                <select id="editBadgeType" wire:model="badgeForm.type" class="admin-select">
                                    <option value="">Sélectionner un type</option>
                                    @foreach($badgeTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('badgeForm.type') <span class="admin-form-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="editBadgeRarity" class="admin-form-label">Rareté</label>
                                <select id="editBadgeRarity" wire:model="badgeForm.rarity" class="admin-select">
                                    <option value="">Sélectionner une rareté</option>
                                    @foreach($badgeRarities as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('badgeForm.rarity') <span class="admin-form-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="editBadgeRequirementType" class="admin-form-label">Type de condition</label>
                                <select id="editBadgeRequirementType" wire:model="badgeForm.requirement_type" class="admin-select">
                                    <option value="">Sélectionner une condition</option>
                                    @foreach($requirementTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('badgeForm.requirement_type') <span class="admin-form-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="editBadgeRequirementValue" class="admin-form-label">Valeur requise</label>
                                <input type="number" id="editBadgeRequirementValue" wire:model="badgeForm.requirement_value" class="admin-input" min="0">
                                @error('badgeForm.requirement_value') <span class="admin-form-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="editBadgePointsReward" class="admin-form-label">Points de récompense</label>
                                <input type="number" id="editBadgePointsReward" wire:model="badgeForm.points_reward" class="admin-input" min="0">
                                @error('badgeForm.points_reward') <span class="admin-form-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Statut</label>
                                <div class="admin-toggle-container">
                                    <label class="admin-toggle">
                                        <input type="checkbox" wire:model="badgeForm.is_active">
                                        <span class="admin-toggle-slider"></span>
                                    </label>
                                    <span>{{ $badgeForm['is_active'] ? 'Actif' : 'Inactif' }}</span>
                                </div>
                                @error('badgeForm.is_active') <span class="admin-form-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-section">
                        <h3>Statistiques du badge</h3>
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Nombre d'utilisateurs</label>
                                <div class="admin-input-static">
                                    {{ $this->getBadgeUserCount($selectedBadge->id) }} utilisateur(s)
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-actions">
                        <button type="button" class="admin-btn admin-btn-danger" wire:click="deleteBadge({{ $selectedBadge->id }})" wire:confirm="Êtes-vous sûr de vouloir supprimer ce badge ?">Supprimer</button>
                        <button type="submit" class="admin-btn admin-btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>