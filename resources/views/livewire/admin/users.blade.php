<div class="admin-users">
    <div class="admin-page-header">
        <h1>Gestion des utilisateurs</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button {{ $activeTab === 'list' ? 'active' : '' }}" wire:click="setActiveTab('list')">
                <i class="fas fa-users"></i> Liste des utilisateurs
            </button>
            <button class="admin-tab-button {{ $activeTab === 'create' ? 'active' : '' }}" wire:click="setActiveTab('create')">
                <i class="fas fa-user-plus"></i> Créer un utilisateur
            </button>
            @if($activeTab === 'detail')
                <button class="admin-tab-button active">
                    <i class="fas fa-user"></i> {{ $selectedUser->name }}
                </button>
            @endif
        </div>
    </div>

    <div class="admin-content-body">
        <!-- Liste des utilisateurs -->
        @if($activeTab === 'list')
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Liste des utilisateurs</h2>
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
                            <label for="filterRole">Rôle:</label>
                            <select id="filterRole" wire:model.live="filterRole" class="admin-select">
                                <option value="">Tous</option>
                                <option value="player">Joueur</option>
                                <option value="helper">Helper</option>
                                <option value="modo">Modérateur</option>
                                <option value="admin">Admin</option>
                                <option value="owner">Propriétaire</option>
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
                            <label for="filterVacation">Vacances:</label>
                            <select id="filterVacation" wire:model.live="filterVacation" class="admin-select">
                                <option value="">Tous</option>
                                <option value="vacation">En vacances</option>
                                <option value="no-vacation">Pas en vacances</option>
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
                                    <th wire:click="sortBy('name')" class="admin-sortable">
                                        Nom
                                        @if($sortField === 'name')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('email')" class="admin-sortable">
                                        Email
                                        @if($sortField === 'email')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th>Faction</th>
                                    <th wire:click="sortBy('role')" class="admin-sortable">
                                        Rôle
                                        @if($sortField === 'role')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('is_active')" class="admin-sortable">
                                        Statut
                                        @if($sortField === 'is_active')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('created_at')" class="admin-sortable">
                                        Inscription
                                        @if($sortField === 'created_at')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->faction->name ?? 'Aucune' }}</td>
                                        <td>
                                            <span class="admin-badge admin-badge-{{ $user->role }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($user->is_active)
                                                <span class="admin-badge admin-badge-success">Actif</span>
                                            @else
                                                <span class="admin-badge admin-badge-danger">Inactif</span>
                                            @endif
                                            @if($user->vacation_mode)
                                                <span class="admin-badge admin-badge-warning">Vacances</span>
                                            @endif
                                        </td>
                                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <div class="admin-actions">
                                                <button wire:click="selectUser({{ $user->id }})" class="admin-action-button admin-action-info" title="Voir les détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="admin-table-empty">Aucun utilisateur trouvé</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="admin-pagination">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Création d'utilisateur -->
        @if($activeTab === 'create')
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Créer un nouvel utilisateur</h2>
                </div>
                <div class="admin-card-body">
                    <form wire:submit.prevent="createUser" class="admin-form">
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="name">Nom d'utilisateur</label>
                                <input type="text" id="name" wire:model="newUser.name" class="admin-input" required>
                                @error('newUser.name') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" wire:model="newUser.email" class="admin-input" required>
                                @error('newUser.email') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="password">Mot de passe</label>
                                <input type="password" id="password" wire:model="newUser.password" class="admin-input" required>
                                @error('newUser.password') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="faction">Faction</label>
                                <select id="faction" wire:model="newUser.faction_id" class="admin-select" required>
                                    <option value="">Sélectionner une faction</option>
                                    @foreach($factions as $faction)
                                        <option value="{{ $faction->id }}">{{ $faction->name }}</option>
                                    @endforeach
                                </select>
                                @error('newUser.faction_id') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="role">Rôle</label>
                                <select id="role" wire:model="newUser.role" class="admin-select" required>
                                    <option value="player">Joueur</option>
                                    <option value="helper">Helper</option>
                                    <option value="modo">Modérateur</option>
                                    <option value="admin">Admin</option>
                                    <option value="owner">Propriétaire</option>
                                </select>
                                @error('newUser.role') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="is_active">Statut</label>
                                <div class="admin-toggle-container">
                                    <label class="admin-toggle">
                                        <input type="checkbox" id="is_active" wire:model="newUser.is_active">
                                        <span class="admin-toggle-slider"></span>
                                    </label>
                                    <span class="admin-toggle-label">{{ $newUser['is_active'] ? 'Actif' : 'Inactif' }}</span>
                                </div>
                                @error('newUser.is_active') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-actions">
                            <button type="button" wire:click="resetNewUser" class="admin-button admin-button-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </button>
                            <button type="submit" class="admin-button admin-button-primary">
                                <i class="fas fa-save"></i> Créer l'utilisateur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Détails de l'utilisateur -->
        @if($activeTab === 'detail' && $selectedUser)
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Détails de l'utilisateur: {{ $selectedUser->name }}</h2>
                    <div class="admin-card-tabs">
                        <button class="admin-tab-button {{ $userDetailTab === 'profile' ? 'active' : '' }}" wire:click="setUserDetailTab('profile')">
                            <i class="fas fa-user"></i> Profil
                        </button>
                        <button class="admin-tab-button {{ $userDetailTab === 'planets' ? 'active' : '' }}" wire:click="setUserDetailTab('planets')">
                            <i class="fas fa-globe"></i> Planètes
                        </button>
                        <button class="admin-tab-button {{ $userDetailTab === 'technologies' ? 'active' : '' }}" wire:click="setUserDetailTab('technologies')">
                            <i class="fas fa-flask"></i> Technologies
                        </button>
                        <button class="admin-tab-button {{ $userDetailTab === 'stats' ? 'active' : '' }}" wire:click="setUserDetailTab('stats')">
                            <i class="fas fa-chart-bar"></i> Statistiques
                        </button>
                        <button class="admin-tab-button {{ $userDetailTab === 'attacks' ? 'active' : '' }}" wire:click="setUserDetailTab('attacks')">
                            <i class="fas fa-fighter-jet"></i> Attaques
                        </button>
                        <button class="admin-tab-button {{ $userDetailTab === 'logs' ? 'active' : '' }}" wire:click="setUserDetailTab('logs')">
                            <i class="fas fa-history"></i> Logs
                        </button>
                        <button class="admin-tab-button {{ $userDetailTab === 'badges' ? 'active' : '' }}" wire:click="setUserDetailTab('badges')">
                            <i class="fas fa-award"></i> Badges
                        </button>
                    </div>
                </div>
                <div class="admin-card-body">
                    <!-- Profil -->
                    @if($userDetailTab === 'profile')
                        <div class="admin-user-profile">
                            <div class="admin-profile-header">
                                <div class="admin-profile-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="admin-profile-info">
                                    <h3>{{ $selectedUser->name }}</h3>
                                    <p>{{ $selectedUser->email }}</p>
                                    <div class="admin-profile-badges">
                                        <span class="admin-badge admin-badge-{{ $selectedUser->role }}">{{ ucfirst($selectedUser->role) }}</span>
                                        @if($selectedUser->is_active)
                                            <span class="admin-badge admin-badge-success">Actif</span>
                                        @else
                                            <span class="admin-badge admin-badge-danger">Inactif</span>
                                        @endif
                                        @if($selectedUser->vacation_mode)
                                            <span class="admin-badge admin-badge-warning">En vacances</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="admin-profile-details">
                                <div class="admin-profile-section">
                                    <h4>Informations générales</h4>
                                    <div class="admin-profile-grid">
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">ID</span>
                                            <span class="admin-profile-value">{{ $selectedUser->id }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Faction</span>
                                            <span class="admin-profile-value">{{ $selectedUser->faction->name ?? 'Aucune' }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Expérience</span>
                                            <span class="admin-profile-value">{{ number_format($selectedUser->getCurrentExperience()) }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Niveau</span>
                                            <span class="admin-profile-value">{{ $selectedUser->getLevel() }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Inscription</span>
                                            <span class="admin-profile-value">{{ $selectedUser->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Dernière connexion</span>
                                            <span class="admin-profile-value">{{ $selectedUser->last_login_at ? $selectedUser->last_login_at->format('d/m/Y H:i') : 'Jamais' }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Planètes</span>
                                            <span class="admin-profile-value">{{ $selectedUser->planets->count() }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Badges</span>
                                            <span class="admin-profile-value">{{ $selectedUser->badges->count() }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="admin-profile-section">
                                    <h4>Actions</h4>
                                    <div class="admin-profile-actions">
                                        <button class="admin-button admin-button-warning" wire:click="toggleActionForm('ban')">
                                            <i class="fas fa-ban"></i> Bannir
                                        </button>
                                        @if($selectedUser && $selectedUser->is_active)
                                            <button class="admin-button admin-button-danger" wire:click="toggleSelectedUserActive(false)" wire:confirm="Désactiver cet utilisateur ?">
                                                <i class="fas fa-user-slash"></i> Désactiver
                                            </button>
                                        @else
                                            <button class="admin-button admin-button-success" wire:click="toggleSelectedUserActive(true)" wire:confirm="Activer cet utilisateur ?">
                                                <i class="fas fa-user-check"></i> Activer
                                            </button>
                                        @endif
                                        <button class="admin-button admin-button-info" wire:click="toggleActionForm('message')">
                                            <i class="fas fa-envelope"></i> Envoyer un message
                                        </button>
                                        <button class="admin-button admin-button-secondary" wire:click="toggleActionForm('edit')">
                                            <i class="fas fa-edit"></i> Modifier
                                        </button>
                                        <button class="admin-button admin-button-primary" wire:click="toggleActionForm('gold')">
                                            <i class="fas fa-coins"></i> Ajouter de l'or
                                        </button>
                                    </div>
                                    
                                    <!-- Formulaire Bannir -->
                                    @if($showBanForm)
                                        <div class="admin-form mt-3">
                                            <div class="admin-form-grid admin-form-grid-2">
                                                <div class="admin-form-group">
                                                    <label class="admin-form-label">Raison</label>
                                                    <input type="text" class="admin-input" wire:model.live="banForm.reason" placeholder="Motif du bannissement">
                                                </div>
                                                <div class="admin-form-group">
                                                    <label class="admin-form-label">Expiration (optionnel)</label>
                                                    <input type="datetime-local" class="admin-input" wire:model.live="banForm.expires_at">
                                                </div>
                                            </div>
                                            <div class="admin-form-actions">
                                                <button class="admin-button admin-button-secondary" type="button" wire:click="toggleActionForm('ban')"><i class="fas fa-times"></i> Annuler</button>
                                                <button class="admin-button admin-button-warning" type="button" wire:click="banSelectedUser"><i class="fas fa-ban"></i> Confirmer le bannissement</button>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Formulaire Message -->
                                    @if($showMessageForm)
                                        <div class="admin-form mt-3">
                                            <div class="admin-form-grid admin-form-grid-1">
                                                <div class="admin-form-group">
                                                    <label class="admin-form-label">Sujet</label>
                                                    <input type="text" class="admin-input" wire:model.live="messageForm.subject" placeholder="Sujet du message">
                                                </div>
                                                <div class="admin-form-group">
                                                    <label class="admin-form-label">Message</label>
                                                    <textarea class="admin-textarea" rows="4" wire:model.live="messageForm.message" placeholder="Contenu du message"></textarea>
                                                </div>
                                            </div>
                                            <div class="admin-form-actions">
                                                <button class="admin-button admin-button-secondary" type="button" wire:click="toggleActionForm('message')"><i class="fas fa-times"></i> Annuler</button>
                                                <button class="admin-button admin-button-info" type="button" wire:click="sendMessageToSelectedUser"><i class="fas fa-paper-plane"></i> Envoyer</button>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Formulaire Modifier -->
                                    @if($showEditForm)
                                        <div class="admin-form mt-3">
                                            <div class="admin-form-grid admin-form-grid-2">
                                                <div class="admin-form-group">
                                                    <label class="admin-form-label">Nom</label>
                                                    <input type="text" class="admin-input" wire:model.live="editForm.name">
                                                </div>
                                                <div class="admin-form-group">
                                                    <label class="admin-form-label">Email</label>
                                                    <input type="email" class="admin-input" wire:model.live="editForm.email">
                                                </div>
                                                <div class="admin-form-group">
                                                    <label class="admin-form-label">Faction</label>
                                                    <select class="admin-select" wire:model.live="editForm.faction_id">
                                                        <option value="">Choisir une faction</option>
                                                        @foreach($factions as $f)
                                                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="admin-form-group">
                                                    <label class="admin-form-label">Rôle</label>
                                                    <select class="admin-select" wire:model.live="editForm.role">
                                                        <option value="player">Joueur</option>
                                                        <option value="helper">Helper</option>
                                                        <option value="modo">Modo</option>
                                                        <option value="admin">Admin</option>
                                                        <option value="owner">Propriétaire</option>
                                                    </select>
                                                </div>
                                                <div class="admin-form-group">
                                                    <label class="admin-form-label">Statut</label>
                                                    <label class="admin-switch">
                                                        <input type="checkbox" wire:model.live="editForm.is_active">
                                                        <span class="admin-switch-slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="admin-form-actions">
                                                <button class="admin-button admin-button-secondary" type="button" wire:click="toggleActionForm('edit')"><i class="fas fa-times"></i> Annuler</button>
                                                <button class="admin-button admin-button-primary" type="button" wire:click="updateSelectedUser"><i class="fas fa-save"></i> Mettre à jour</button>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Formulaire Crédit d'or -->
                                    @if($showGoldForm)
                                        <div class="admin-form mt-3">
                                            <div class="admin-form-grid admin-form-grid-2">
                                                <div class="admin-form-group">
                                                    <label class="admin-form-label">Montant (or)</label>
                                                    <input type="number" class="admin-input" wire:model.live="goldForm.amount" min="1">
                                                </div>
                                                <div class="admin-form-group">
                                                    <label class="admin-form-label">Motif</label>
                                                    <input type="text" class="admin-input" wire:model.live="goldForm.reason" placeholder="Motif de l'ajout">
                                                </div>
                                            </div>
                                            <div class="admin-form-actions">
                                                <button class="admin-button admin-button-secondary" type="button" wire:click="toggleActionForm('gold')"><i class="fas fa-times"></i> Annuler</button>
                                                <button class="admin-button admin-button-primary" type="button" wire:click="addGoldToSelectedUser"><i class="fas fa-coins"></i> Créditer et notifier</button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Planètes -->
                    @if($userDetailTab === 'planets')
                        <div class="admin-user-planets">
                            <div class="admin-table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nom</th>
                                            <th>Type</th>
                                            <th>Coordonnées</th>
                                            <th>Taille</th>
                                            <th>Principale</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($userPlanets as $planet)
                                            <tr>
                                                <td>{{ $planet->id }}</td>
                                                <td>{{ $planet->name }}</td>
                                                <td>{{ $planet->templatePlanet->name }}</td>
                                                <td>[{{ $planet->galaxy }}:{{ $planet->system }}:{{ $planet->position }}]</td>
                                                <td>{{ $planet->size }}</td>
                                                <td>
                                                    @if($planet->is_main)
                                                        <span class="admin-badge admin-badge-success">Principale</span>
                                                    @else
                                                        <span class="admin-badge admin-badge-secondary">Colonie</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($planet->is_active)
                                                        <span class="admin-badge admin-badge-success">Active</span>
                                                    @else
                                                        <span class="admin-badge admin-badge-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="admin-actions">
                                                        <a href="{{ route('admin.planets', ['id' => $planet->id]) }}" class="admin-action-button admin-action-info" title="Voir les détails de la planète">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="admin-table-empty">Aucune planète trouvée</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Logs -->
                    @if($userDetailTab === 'logs')
                        <div class="admin-user-logs">
                            <div class="admin-table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Action</th>
                                            <th>Description</th>
                                            <th>Planète</th>
                                            <th>Cible</th>
                                            <th>Sévérité</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($userLogs as $log)
                                            <tr>
                                                <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <span class="admin-badge">
                                                        <i class="fas fa-{{ $log->action_category === 'auth' ? 'lock' : ($log->action_category === 'resource' ? 'coins' : ($log->action_category === 'building' ? 'building' : ($log->action_category === 'message' ? 'envelope' : ($log->action_category === 'combat' ? 'fighter-jet' : 'cog')))) }}"></i>
                                                        {{ $log->action_type }}
                                                    </span>
                                                </td>
                                                <td>{{ $log->formatted_description }}</td>
                                                <td>{{ $log->planet->name ?? '-' }}</td>
                                                <td>{{ $log->targetUser->name ?? '-' }}</td>
                                                <td>
                                                    <span class="admin-badge admin-badge-{{ $log->severity === 'info' ? 'info' : ($log->severity === 'warning' ? 'warning' : ($log->severity === 'error' ? 'danger' : 'critical')) }}">
                                                        {{ ucfirst($log->severity) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="admin-table-empty">Aucun log trouvé</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="admin-pagination">
                                {{ $userLogs->links() }}
                            </div>
                        </div>
                    @endif
                    
                    <!-- Badges -->
                    @if($userDetailTab === 'badges')
                        <div class="admin-user-badges">
                            @if(count($userBadges) > 0)
                                <div class="admin-badges-grid">
                                    @foreach($userBadges as $badge)
                                        <div class="admin-badge-card">
                                            <div class="admin-badge-icon">
                                                <i class="fas fa-{{ $badge->icon }}"></i>
                                            </div>
                                            <div class="admin-badge-info">
                                                <h4>{{ $badge->name }}</h4>
                                                <p>{{ $badge->description }}</p>
                                                <div class="admin-badge-meta">
                                                    <span class="admin-badge admin-badge-{{ $badge->rarity }}">{{ ucfirst($badge->rarity) }}</span>
                                                    <span class="admin-badge-date">Obtenu le {{ $badge->pivot->created_at->format('d/m/Y') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="admin-empty-state">
                                    <i class="fas fa-award admin-empty-icon"></i>
                                    <p>Cet utilisateur n'a pas encore de badges</p>
                                </div>
                            @endif
                        </div>
                    @endif
                    
                    <!-- Technologies -->
                    @if($userDetailTab === 'technologies')
                        <div class="admin-user-technologies">
                            <div class="admin-table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Technologie</th>
                                            <th>Niveau</th>
                                            <th>Recherche en cours</th>
                                            <th>Début recherche</th>
                                            <th>Fin recherche</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($userTechnologies as $tech)
                                            <tr>
                                                <td>{{ $tech->id }}</td>
                                                <td>{{ $tech->technology->name ?? 'Inconnue' }}</td>
                                                <td>{{ $tech->level }}</td>
                                                <td>
                                                    @if($tech->is_researching)
                                                        <span class="admin-badge admin-badge-info">En cours</span>
                                                    @else
                                                        <span class="admin-badge admin-badge-secondary">Non</span>
                                                    @endif
                                                </td>
                                                <td>{{ $tech->research_start_time ? $tech->research_start_time->format('d/m/Y H:i') : '-' }}</td>
                                                <td>{{ $tech->research_end_time ? $tech->research_end_time->format('d/m/Y H:i') : '-' }}</td>
                                                <td>
                                                    @if($tech->is_active)
                                                        <span class="admin-badge admin-badge-success">Active</span>
                                                    @else
                                                        <span class="admin-badge admin-badge-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="admin-actions">
                                                        <button class="admin-action-button admin-action-success" title="Augmenter" wire:click="incrementTechnology({{ $tech->id }})">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                        <button class="admin-action-button admin-action-danger" title="Diminuer" wire:click="decrementTechnology({{ $tech->id }})">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="admin-table-empty">Aucune technologie trouvée</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Statistiques -->
                    @if($userDetailTab === 'stats')
                        <div class="admin-user-stats">
                            @if($userStats)
                                <div class="admin-profile-section">
                                    <h4 class="admin-profile-section-title"><i class="fas fa-chart-line"></i> Points et Classement</h4>
                                    <div class="admin-profile-grid">
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Points totaux</span>
                                            <span class="admin-profile-value">{{ number_format($userStats->total_points) }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Points bâtiments</span>
                                            <span class="admin-profile-value">{{ number_format($userStats->building_points) }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Points unités</span>
                                            <span class="admin-profile-value">{{ number_format($userStats->units_points) }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Points défenses</span>
                                            <span class="admin-profile-value">{{ number_format($userStats->defense_points) }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Points vaisseaux</span>
                                            <span class="admin-profile-value">{{ number_format($userStats->ship_points) }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Points technologies</span>
                                            <span class="admin-profile-value">{{ number_format($userStats->technology_points) }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Classement actuel</span>
                                            <span class="admin-profile-value">{{ number_format($userStats->current_rank) }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Évolution</span>
                                            <span class="admin-profile-value">
                                                @if($userStats->rank_change > 0)
                                                    <span class="admin-text-success"><i class="fas fa-arrow-up"></i> +{{ $userStats->rank_change }}</span>
                                                @elseif($userStats->rank_change < 0)
                                                    <span class="admin-text-danger"><i class="fas fa-arrow-down"></i> {{ $userStats->rank_change }}</span>
                                                @else
                                                    <span class="admin-text-muted"><i class="fas fa-minus"></i> 0</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="admin-profile-section">
                                    <h4 class="admin-profile-section-title"><i class="fas fa-fist-raised"></i> Statistiques de combat terrestre</h4>
                                    <div class="admin-profile-grid">
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Points d'attaque</span>
                                            <span class="admin-profile-value">{{ number_format($userStats->earth_attack) }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Points de défense</span>
                                            <span class="admin-profile-value">{{ number_format($userStats->earth_defense) }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Attaques réussies</span>
                                            <span class="admin-profile-value">{{ number_format($userStats->earth_attack_count) }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Défenses réussies</span>
                                            <span class="admin-profile-value">{{ number_format($userStats->earth_defense_count) }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Défaites</span>
                                            <span class="admin-profile-value">{{ number_format($userStats->earth_loser_count) }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="admin-profile-section">
                                    <h4 class="admin-profile-section-title"><i class="fas fa-space-shuttle"></i> Statistiques de combat spatial</h4>
                                    <div class="admin-profile-grid">
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Points d'attaque</span>
                                            <span class="admin-profile-value">{{ number_format($userStats->spatial_attack) }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Points de défense</span>
                                            <span class="admin-profile-value">{{ number_format($userStats->spatial_defense) }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Attaques réussies</span>
                                            <span class="admin-profile-value">{{ number_format($userStats->spatial_attack_count) }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Défenses réussies</span>
                                            <span class="admin-profile-value">{{ number_format($userStats->spatial_defense_count) }}</span>
                                        </div>
                                        <div class="admin-profile-item">
                                            <span class="admin-profile-label">Défaites</span>
                                            <span class="admin-profile-value">{{ number_format($userStats->spatial_loser_count) }}</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="admin-empty-state">
                                    <i class="fas fa-chart-bar admin-empty-icon"></i>
                                    <p>Aucune statistique disponible pour cet utilisateur</p>
                                </div>
                            @endif
                        </div>
                    @endif
                    
                    <!-- Logs d'attaque -->
                    @if($userDetailTab === 'attacks')
                        <div class="admin-user-attacks">
                            <div class="admin-table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Attaquant</th>
                                            <th>Défenseur</th>
                                            <th>Résultat</th>
                                            <th>Points gagnés</th>
                                            <th>Ressources pillées</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($playerAttackLogs as $log)
                                            <tr>
                                                <td>{{ $log->attacked_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <span class="admin-badge admin-badge-{{ $log->attack_type === 'raid' ? 'warning' : ($log->attack_type === 'conquest' ? 'danger' : 'info') }}">
                                                        {{ ucfirst($log->attack_type) }}
                                                    </span>
                                                </td>
                                                <td>{{ $log->attackerUser->name }}</td>
                                                <td>{{ $log->defenderUser->name }}</td>
                                                <td>
                                                    @if($log->attacker_won)
                                                        <span class="admin-badge admin-badge-success">Victoire attaquant</span>
                                                    @else
                                                        <span class="admin-badge admin-badge-danger">Victoire défenseur</span>
                                                    @endif
                                                </td>
                                                <td>{{ number_format($log->points_gained) }}</td>
                                                <td>
                                                    @if(is_array($log->resources_pillaged) && count($log->resources_pillaged) > 0)
                                                        @foreach($log->resources_pillaged as $resource => $amount)
                                                            <span class="admin-badge">{{ ucfirst($resource) }}: {{ number_format($amount) }}</span>
                                                        @endforeach
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="admin-table-empty">Aucun log d'attaque trouvé</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="admin-pagination">
                                {{ $playerAttackLogs->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>