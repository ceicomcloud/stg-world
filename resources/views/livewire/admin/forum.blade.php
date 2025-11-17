<div class="admin-forum">
    <div class="admin-page-header">
        <h1>Gestion du forum</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button {{ $activeTab === 'reports' ? 'active' : '' }}" wire:click="setActiveTab('reports')">
                <i class="fas fa-flag"></i> Signalements
            </button>
        </div>
    </div>

    <div class="admin-content-body">
        <!-- Onglet des signalements -->
        @if($activeTab === 'reports')
            <!-- Statistiques des signalements -->
            <div class="admin-stats-grid">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon">
                        <i class="fas fa-flag"></i>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-title">Total des signalements</div>
                        <div class="admin-stat-value">{{ $reportStats['total'] }}</div>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-warning">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-title">En attente</div>
                        <div class="admin-stat-value">{{ $reportStats['pending'] }}</div>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-title">Résolus</div>
                        <div class="admin-stat-value">{{ $reportStats['resolved'] }}</div>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-title">Rejetés</div>
                        <div class="admin-stat-value">{{ $reportStats['dismissed'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Filtres et liste des signalements -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Liste des signalements</h2>
                </div>
                <div class="admin-card-body">
                    <!-- Filtres -->
                    <div class="admin-filters">
                        <div class="admin-search-container">
                            <input type="text" class="admin-search-input" placeholder="Rechercher..." wire:model.live.debounce.300ms="search">
                            <i class="fas fa-search admin-search-icon"></i>
                        </div>
                        <div class="admin-filter-group">
                            <select class="admin-select" wire:model.live="statusFilter">
                                <option value="pending">En attente</option>
                                <option value="resolved">Résolus</option>
                                <option value="dismissed">Rejetés</option>
                                <option value="all">Tous</option>
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <select class="admin-select" wire:model.live="perPage">
                                <option value="15">15 par page</option>
                                <option value="30">30 par page</option>
                                <option value="50">50 par page</option>
                                <option value="100">100 par page</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tableau des signalements -->
                    <div class="admin-table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th wire:click="sortBy('id')" class="admin-sortable-column">
                                        ID
                                        @if($sortField === 'id')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('reason')" class="admin-sortable-column">
                                        Raison
                                        @if($sortField === 'reason')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th>Signalé par</th>
                                    <th wire:click="sortBy('created_at')" class="admin-sortable-column">
                                        Date
                                        @if($sortField === 'created_at')
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
                                @forelse($reports as $report)
                                    <tr>
                                        <td>{{ $report->id }}</td>
                                        <td>{{ $report->reason }}</td>
                                        <td>
                                            @if($report->reportedBy)
                                                {{ $report->reportedBy->name }}
                                            @else
                                                <span class="admin-text-muted">Utilisateur supprimé</span>
                                            @endif
                                        </td>
                                        <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($report->status === 'pending')
                                                <span class="admin-badge admin-badge-warning">En attente</span>
                                            @elseif($report->status === 'resolved')
                                                <span class="admin-badge admin-badge-success">Résolu</span>
                                            @elseif($report->status === 'dismissed')
                                                <span class="admin-badge admin-badge-danger">Rejeté</span>
                                            @else
                                                <span class="admin-badge">{{ $report->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="admin-table-actions">
                                                <button class="admin-btn admin-btn-info admin-btn-sm" wire:click="viewReport({{ $report->id }})">
                                                    <i class="fas fa-eye"></i> Voir
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="admin-table-empty">Aucun signalement trouvé</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="admin-pagination-container">
                        {{ $reports->links() }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Onglet de visualisation d'un signalement -->
        @if($activeTab === 'view_report' && $selectedReport && $selectedPost)
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Détails du signalement #{{ $selectedReport->id }}</h2>
                    <div class="admin-card-actions">
                        <button class="admin-btn admin-btn-outline" wire:click="setActiveTab('reports')">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </button>
                    </div>
                </div>
                <div class="admin-card-body">
                    <!-- Informations sur le signalement -->
                    <div class="admin-report-details">
                        <div class="admin-report-section">
                            <h3>Informations sur le signalement</h3>
                            <div class="admin-report-info-grid">
                                <div class="admin-report-info-item">
                                    <div class="admin-report-info-label">ID</div>
                                    <div class="admin-report-info-value">{{ $selectedReport->id }}</div>
                                </div>
                                <div class="admin-report-info-item">
                                    <div class="admin-report-info-label">Raison</div>
                                    <div class="admin-report-info-value">{{ $selectedReport->reason }}</div>
                                </div>
                                <div class="admin-report-info-item">
                                    <div class="admin-report-info-label">Signalé par</div>
                                    <div class="admin-report-info-value">
                                        @if($selectedReport->reportedBy)
                                            {{ $selectedReport->reportedBy->name }}
                                        @else
                                            <span class="admin-text-muted">Utilisateur supprimé</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="admin-report-info-item">
                                    <div class="admin-report-info-label">Date</div>
                                    <div class="admin-report-info-value">{{ $selectedReport->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                                <div class="admin-report-info-item">
                                    <div class="admin-report-info-label">Statut</div>
                                    <div class="admin-report-info-value">
                                        @if($selectedReport->status === 'pending')
                                            <span class="admin-badge admin-badge-warning">En attente</span>
                                        @elseif($selectedReport->status === 'resolved')
                                            <span class="admin-badge admin-badge-success">Résolu</span>
                                        @elseif($selectedReport->status === 'dismissed')
                                            <span class="admin-badge admin-badge-danger">Rejeté</span>
                                        @else
                                            <span class="admin-badge">{{ $selectedReport->status }}</span>
                                        @endif
                                    </div>
                                </div>
                                @if($selectedReport->reviewed_by)
                                    <div class="admin-report-info-item">
                                        <div class="admin-report-info-label">Examiné par</div>
                                        <div class="admin-report-info-value">
                                            @if($selectedReport->reviewedBy)
                                                {{ $selectedReport->reviewedBy->name }}
                                            @else
                                                <span class="admin-text-muted">Administrateur supprimé</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="admin-report-info-item">
                                        <div class="admin-report-info-label">Examiné le</div>
                                        <div class="admin-report-info-value">{{ $selectedReport->reviewed_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($selectedReport->description)
                            <div class="admin-report-section">
                                <h3>Description du signalement</h3>
                                <div class="admin-report-description">
                                    {{ $selectedReport->description }}
                                </div>
                            </div>
                        @endif

                        <!-- Contenu du post signalé -->
                        <div class="admin-report-section">
                            <h3>Post signalé</h3>
                            <div class="admin-report-post">
                                <div class="admin-report-post-header">
                                    <div class="admin-report-post-author">
                                        @if($selectedPost->user)
                                            <span class="admin-report-post-author-name">{{ $selectedPost->user->name }}</span>
                                        @else
                                            <span class="admin-text-muted">Utilisateur supprimé</span>
                                        @endif
                                        <span class="admin-report-post-date">{{ $selectedPost->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="admin-report-post-topic">
                                        <a href="{{ route('game.forum.topic', ['categoryId' => $selectedPost->topic->forum->category->slug, 'forumId' => $selectedPost->topic->forum->slug, 'topicId' => $selectedPost->topic->slug]) }}" target="_blank" class="admin-link">
                                            <i class="fas fa-external-link-alt"></i> Voir dans le forum
                                        </a>
                                    </div>
                                </div>
                                <div class="admin-report-post-content">
                                    {!! nl2br(e($selectedPost->content)) !!}
                                </div>
                            </div>
                        </div>

                        <!-- Notes d'administration -->
                        <div class="admin-report-section">
                            <h3>Notes d'administration</h3>
                            <div class="admin-form-group">
                                <textarea class="admin-textarea" wire:model="adminNotes" placeholder="Ajouter des notes (optionnel)..."></textarea>
                                @error('adminNotes') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="admin-report-actions">
                            @if($selectedReport->status === 'pending')
                                <button class="admin-btn admin-btn-success" wire:click="resolveReport" wire:confirm="Êtes-vous sûr de vouloir marquer ce signalement comme résolu ?">
                                    <i class="fas fa-check"></i> Marquer comme résolu
                                </button>
                                <button class="admin-btn admin-btn-warning" wire:click="dismissReport" wire:confirm="Êtes-vous sûr de vouloir rejeter ce signalement ?">
                                    <i class="fas fa-times"></i> Rejeter le signalement
                                </button>
                                <button class="admin-btn admin-btn-danger" wire:click="deletePost" wire:confirm="Êtes-vous sûr de vouloir supprimer ce post ? Cette action est irréversible.">
                                    <i class="fas fa-trash"></i> Supprimer le post
                                </button>
                            @else
                                <div class="admin-report-status-message">
                                    @if($selectedReport->status === 'resolved')
                                        <i class="fas fa-check-circle"></i> Ce signalement a été marqué comme résolu
                                    @elseif($selectedReport->status === 'dismissed')
                                        <i class="fas fa-times-circle"></i> Ce signalement a été rejeté
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>