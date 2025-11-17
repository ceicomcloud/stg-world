<div class="admin-logs">
    <div class="admin-page-header">
        <h1>Gestion des logs système</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button {{ $activeTab === 'user_logs' ? 'active' : '' }}" wire:click="setActiveTab('user_logs')">
                <i class="fas fa-clipboard-list"></i> Logs utilisateurs
            </button>
            <button class="admin-tab-button {{ $activeTab === 'attack_logs' ? 'active' : '' }}" wire:click="setActiveTab('attack_logs')">
                <i class="fas fa-fighter-jet"></i> Logs d'attaques
            </button>
        </div>
    </div>

    <div class="admin-content-body">
        <!-- Onglet des logs utilisateurs -->
        @if($activeTab === 'user_logs')
            <!-- Statistiques des logs utilisateurs -->
            <div class="admin-stats-grid">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-title">Total des logs</div>
                        <div class="admin-stat-value">{{ $userLogStats['total'] }}</div>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-info">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-title">Logs d'information</div>
                        <div class="admin-stat-value">{{ $userLogStats['by_severity']['info'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-title">Logs d'avertissement</div>
                        <div class="admin-stat-value">{{ $userLogStats['by_severity']['warning'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-danger">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-title">Logs d'erreur</div>
                        <div class="admin-stat-value">{{ $userLogStats['by_severity']['error'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- Filtres et actions pour les logs utilisateurs -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Liste des logs utilisateurs</h2>
                    <div class="admin-card-actions">
                        <button class="admin-btn admin-btn-danger" wire:click="deleteSelectedUserLogs" wire:confirm="Êtes-vous sûr de vouloir supprimer les logs sélectionnés ?">
                            <i class="fas fa-trash"></i> Supprimer sélection
                        </button>
                        <button class="admin-btn admin-btn-danger" wire:click="deleteAllUserLogs" wire:confirm="Êtes-vous sûr de vouloir supprimer TOUS les logs utilisateurs ? Cette action est irréversible.">
                            <i class="fas fa-trash-alt"></i> Tout supprimer
                        </button>
                    </div>
                </div>
                <div class="admin-card-body">
                    <!-- Filtres -->
                    <div class="admin-filters">
                        <div class="admin-search-container">
                            <input type="text" class="admin-search-input" placeholder="Rechercher..." wire:model.live.debounce.300ms="userLogSearch">
                            <i class="fas fa-search admin-search-icon"></i>
                        </div>
                        <div class="admin-filter-group">
                            <select class="admin-select" wire:model.live="userLogActionType">
                                <option value="">Type d'action</option>
                                @foreach($userLogActionTypes as $type => $label)
                                    <option value="{{ $type }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <select class="admin-select" wire:model.live="userLogCategory">
                                <option value="">Catégorie</option>
                                @foreach($userLogCategories as $category => $label)
                                    <option value="{{ $category }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <select class="admin-select" wire:model.live="userLogSeverity">
                                <option value="">Sévérité</option>
                                @foreach($userLogSeverities as $severity => $label)
                                    <option value="{{ $severity }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <select class="admin-select" wire:model.live="userLogPerPage">
                                <option value="15">15 par page</option>
                                <option value="25">25 par page</option>
                                <option value="50">50 par page</option>
                                <option value="100">100 par page</option>
                            </select>
                        </div>
                        <button class="admin-btn admin-btn-outline" wire:click="resetUserLogFilters">
                            <i class="fas fa-redo"></i> Réinitialiser
                        </button>
                    </div>

                    <!-- Tableau des logs utilisateurs -->
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th class="admin-table-checkbox">
                                        <input type="checkbox" wire:model.live="selectAllUserLogs" wire:click="toggleSelectAllUserLogs">
                                    </th>
                                    <th class="admin-sortable" wire:click="sortUserLogs('created_at')">
                                        Date
                                        @if($userLogSortField === 'created_at')
                                            <i class="fas fa-sort-{{ $userLogSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th class="admin-sortable" wire:click="sortUserLogs('user_id')">
                                        Utilisateur
                                        @if($userLogSortField === 'user_id')
                                            <i class="fas fa-sort-{{ $userLogSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th class="admin-sortable" wire:click="sortUserLogs('action_type')">
                                        Action
                                        @if($userLogSortField === 'action_type')
                                            <i class="fas fa-sort-{{ $userLogSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th class="admin-sortable" wire:click="sortUserLogs('action_category')">
                                        Catégorie
                                        @if($userLogSortField === 'action_category')
                                            <i class="fas fa-sort-{{ $userLogSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th>Description</th>
                                    <th class="admin-sortable" wire:click="sortUserLogs('severity')">
                                        Sévérité
                                        @if($userLogSortField === 'severity')
                                            <i class="fas fa-sort-{{ $userLogSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($userLogs as $log)
                                    <tr>
                                        <td class="admin-table-checkbox">
                                            <input type="checkbox" value="{{ $log->id }}" wire:model.live="selectedUserLogs">
                                        </td>
                                        <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                        <td>
                                            @if($log->user)
                                                {{ $log->user->name }}
                                            @else
                                                Utilisateur supprimé
                                            @endif
                                        </td>
                                        <td>{{ $userLogActionTypes[$log->action_type] ?? $log->action_type }}</td>
                                        <td>{{ $userLogCategories[$log->action_category] ?? $log->action_category }}</td>
                                        <td class="admin-truncated-text" title="{{ $log->formatted_description }}">{{ $log->formatted_description }}</td>
                                        <td>
                                            <span class="admin-badge admin-badge-{{ $log->severity }}">
                                                {{ $userLogSeverities[$log->severity] ?? $log->severity }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="admin-table-empty">Aucun log utilisateur trouvé</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="admin-pagination">
                        {{ $userLogs->links() }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Onglet des logs d'attaque -->
        @if($activeTab === 'attack_logs')
            <!-- Statistiques des logs d'attaque -->
            <div class="admin-stats-grid">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon">
                        <i class="fas fa-fighter-jet"></i>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-title">Total des attaques</div>
                        <div class="admin-stat-value">{{ $attackLogStats['total'] }}</div>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-success">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-title">Victoires</div>
                        <div class="admin-stat-value">{{ $attackLogStats['victories'] }}</div>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-danger">
                        <i class="fas fa-skull-crossbones"></i>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-title">Défaites</div>
                        <div class="admin-stat-value">{{ $attackLogStats['defeats'] }}</div>
                    </div>
                </div>
                @foreach($attackLogStats['by_type'] ?? [] as $type => $count)
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon admin-stat-icon-info">
                            <i class="fas fa-crosshairs"></i>
                        </div>
                        <div class="admin-stat-content">
                            <div class="admin-stat-title">{{ $type }}</div>
                            <div class="admin-stat-value">{{ $count }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Filtres et actions pour les logs d'attaque -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Liste des logs d'attaque</h2>
                    <div class="admin-card-actions">
                        <button class="admin-btn admin-btn-danger" wire:click="deleteSelectedAttackLogs" wire:confirm="Êtes-vous sûr de vouloir supprimer les logs d'attaque sélectionnés ?">
                            <i class="fas fa-trash"></i> Supprimer sélection
                        </button>
                        <button class="admin-btn admin-btn-danger" wire:click="deleteAllAttackLogs" wire:confirm="Êtes-vous sûr de vouloir supprimer TOUS les logs d'attaque ? Cette action est irréversible.">
                            <i class="fas fa-trash-alt"></i> Tout supprimer
                        </button>
                    </div>
                </div>
                <div class="admin-card-body">
                    <!-- Filtres -->
                    <div class="admin-filters">
                        <div class="admin-search-container">
                            <input type="text" class="admin-search-input" placeholder="Rechercher..." wire:model.live.debounce.300ms="attackLogSearch">
                            <i class="fas fa-search admin-search-icon"></i>
                        </div>
                        <div class="admin-filter-group">
                            <select class="admin-select" wire:model.live="attackLogType">
                                <option value="">Type d'attaque</option>
                                @foreach($attackLogTypes as $type => $label)
                                    <option value="{{ $type }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <select class="admin-select" wire:model.live="attackLogWon">
                                <option value="">Résultat</option>
                                <option value="1">Victoire</option>
                                <option value="0">Défaite</option>
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <select class="admin-select" wire:model.live="attackLogPerPage">
                                <option value="15">15 par page</option>
                                <option value="25">25 par page</option>
                                <option value="50">50 par page</option>
                                <option value="100">100 par page</option>
                            </select>
                        </div>
                        <button class="admin-btn admin-btn-outline" wire:click="resetAttackLogFilters">
                            <i class="fas fa-redo"></i> Réinitialiser
                        </button>
                    </div>

                    <!-- Tableau des logs d'attaque -->
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th class="admin-table-checkbox">
                                        <input type="checkbox" wire:model.live="selectAllAttackLogs" wire:click="toggleSelectAllAttackLogs">
                                    </th>
                                    <th class="admin-sortable" wire:click="sortAttackLogs('attacked_at')">
                                        Date
                                        @if($attackLogSortField === 'attacked_at')
                                            <i class="fas fa-sort-{{ $attackLogSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th class="admin-sortable" wire:click="sortAttackLogs('attacker_user_id')">
                                        Attaquant
                                        @if($attackLogSortField === 'attacker_user_id')
                                            <i class="fas fa-sort-{{ $attackLogSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th class="admin-sortable" wire:click="sortAttackLogs('defender_user_id')">
                                        Défenseur
                                        @if($attackLogSortField === 'defender_user_id')
                                            <i class="fas fa-sort-{{ $attackLogSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th class="admin-sortable" wire:click="sortAttackLogs('attack_type')">
                                        Type
                                        @if($attackLogSortField === 'attack_type')
                                            <i class="fas fa-sort-{{ $attackLogSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th class="admin-sortable" wire:click="sortAttackLogs('attacker_won')">
                                        Résultat
                                        @if($attackLogSortField === 'attacker_won')
                                            <i class="fas fa-sort-{{ $attackLogSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th class="admin-sortable" wire:click="sortAttackLogs('points_gained')">
                                        Points
                                        @if($attackLogSortField === 'points_gained')
                                            <i class="fas fa-sort-{{ $attackLogSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th>Ressources pillées</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attackLogs as $log)
                                    <tr>
                                        <td class="admin-table-checkbox">
                                            <input type="checkbox" value="{{ $log->id }}" wire:model.live="selectedAttackLogs">
                                        </td>
                                        <td>{{ $log->attacked_at->format('d/m/Y H:i:s') }}</td>
                                        <td>
                                            @if($log->attacker)
                                                {{ $log->attacker->name }}
                                            @else
                                                Utilisateur supprimé
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->defender)
                                                {{ $log->defender->name }}
                                            @else
                                                Utilisateur supprimé
                                            @endif
                                        </td>
                                        <td>{{ $attackLogTypes[$log->attack_type] ?? $log->attack_type }}</td>
                                        <td>
                                            <span class="admin-badge {{ $log->attacker_won ? 'admin-badge-success' : 'admin-badge-danger' }}">
                                                {{ $log->attacker_won ? 'Victoire' : 'Défaite' }}
                                            </span>
                                        </td>
                                        <td>{{ $log->points_gained }}</td>
                                        <td>
                                            @if(!empty($log->resources_pillaged))
                                                <div class="admin-resources-list">
                                                    @foreach($log->resources_pillaged as $resource => $amount)
                                                        <span class="admin-resource-item">{{ $resource }}: {{ $amount }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="admin-text-muted">Aucune</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="admin-table-empty">Aucun log d'attaque trouvé</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="admin-pagination">
                        {{ $attackLogs->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>