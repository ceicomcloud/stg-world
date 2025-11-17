<div class="admin-dashboard">
    <div class="admin-page-header">
        <h1>Tableau de bord</h1>
        <div class="admin-page-actions">
            <span class="admin-date">{{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="admin-stats-grid">
        <!-- Utilisateurs -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-title">Utilisateurs</div>
                <div class="admin-stat-value">{{ $gameStats['users']['total'] }}</div>
                <div class="admin-stat-details">
                    <span class="admin-stat-detail">
                        <i class="fas fa-user-check"></i> {{ $gameStats['users']['active'] }} actifs
                    </span>
                    <span class="admin-stat-detail">
                        <i class="fas fa-user-plus"></i> {{ $gameStats['users']['new'] }} nouveaux
                    </span>
                    <span class="admin-stat-detail">
                        <i class="fas fa-umbrella-beach"></i> {{ $gameStats['users']['in_vacation'] }} en vacances
                    </span>
                </div>
            </div>
        </div>

        <!-- Planètes -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fas fa-globe"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-title">Planètes</div>
                <div class="admin-stat-value">{{ $gameStats['planets']['total'] }}</div>
                <div class="admin-stat-details">
                    <span class="admin-stat-detail">
                        <i class="fas fa-flag"></i> {{ $gameStats['planets']['colonized'] }} colonisées
                    </span>
                    <span class="admin-stat-detail">
                        <i class="fas fa-globe-americas"></i> {{ $gameStats['planets']['free'] }} disponibles
                    </span>
                </div>
            </div>
        </div>

        <!-- Alliances -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fas fa-handshake"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-title">Alliances</div>
                <div class="admin-stat-value">{{ $gameStats['alliances']['total'] }}</div>
                <div class="admin-stat-details">
                    <span class="admin-stat-detail">
                        <i class="fas fa-users"></i> {{ $gameStats['alliances']['members'] }} membres
                    </span>
                </div>
            </div>
        </div>

        <!-- Templates -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fas fa-cubes"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-title">Templates</div>
                <div class="admin-stat-value">{{ array_sum($gameStats['templates']) }}</div>
                <div class="admin-stat-details">
                    <span class="admin-stat-detail">
                        <i class="fas fa-building"></i> {{ $gameStats['templates']['buildings'] }} bâtiments
                    </span>
                    <span class="admin-stat-detail">
                        <i class="fas fa-user-astronaut"></i> {{ $gameStats['templates']['units'] }} unités
                    </span>
                    <span class="admin-stat-detail">
                        <i class="fas fa-shield-alt"></i> {{ $gameStats['templates']['defenses'] }} défenses
                    </span>
                    <span class="admin-stat-detail">
                        <i class="fas fa-rocket"></i> {{ $gameStats['templates']['ships'] }} vaisseaux
                    </span>
                    <span class="admin-stat-detail">
                        <i class="fas fa-atom"></i> {{ $gameStats['templates']['technologies'] }} technologies
                    </span>
                    <span class="admin-stat-detail">
                        <i class="fas fa-coins"></i> {{ $gameStats['templates']['resources'] }} ressources
                    </span>
                    <span class="admin-stat-detail">
                        <i class="fas fa-globe-europe"></i> {{ $gameStats['templates']['planets'] }} planètes
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribution des factions -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Distribution des factions</h2>
        </div>
        <div class="admin-card-body">
            <div class="admin-faction-distribution">
                @foreach($gameStats['factions']['distribution'] as $faction => $count)
                    <div class="admin-faction-item">
                        <div class="admin-faction-name">{{ $faction }}</div>
                        <div class="admin-faction-bar">
                            <div class="admin-faction-progress" style="width: {{ ($count / max(1, $gameStats['users']['total'])) * 100 }}%"></div>
                        </div>
                        <div class="admin-faction-count">{{ $count }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Derniers logs utilisateur -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Derniers logs utilisateur</h2>
        </div>
        <div class="admin-card-body">
            <div class="admin-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Planète</th>
                            <th>Cible</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLogs as $log)
                            <tr>
                                <td>{{ $log['user'] }}</td>
                                <td>
                                    <span class="admin-badge {{ $this->getLogSeverityClass($log['severity']) }}">
                                        <i class="fas fa-{{ $this->getLogCategoryIcon($log['action_category']) }}"></i>
                                         {{ $log['action_type'] }}
                                    </span>
                                </td>
                                <td>{{ $log['description'] }}</td>
                                <td>{{ $log['planet'] ?? '-' }}</td>
                                <td>{{ $log['target_user'] ?? '-' }}</td>
                                <td>{{ $log['created_at'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="admin-table-empty">Aucun log disponible</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>