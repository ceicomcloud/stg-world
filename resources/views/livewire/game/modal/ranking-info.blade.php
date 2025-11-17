<div>
    <!-- Avatar/Image du joueur -->
    <div class="modal-player-avatar">
        @if(!empty($userData['avatar_url']))
            <img src="{{ $userData['avatar_url'] }}"
                 alt="Avatar de {{ $userData['name'] ?? 'Utilisateur' }}"
                 class="player-avatar-img"
                 style="width:64px; height:64px; border-radius:50%; object-fit:cover;" />
        @else
            <i class="fas fa-user-circle"></i>
        @endif
    </div>

    <!-- Nom du joueur -->
    <div class="modal-player-name">
        <h2>{{ $userData['name'] ?? 'Utilisateur inconnu' }}</h2>
        <div class="player-join-date">
            <i class="fas fa-calendar-alt"></i>
            Membre depuis {{ $userData['created_at']->format('d/m/Y') ?? 'N/A' }}
        </div>
        @if($userId !== auth()->id() && ($canRequestPact ?? false))
            <div class="modal-actions" style="margin-top:8px; display:flex; gap:8px;">
                <button class="vip-btn" wire:click="sendPactRequest">
                    <i class="fas fa-handshake"></i> Demander un pacte
                </button>
            </div>
        @endif
        @if($userId !== auth()->id() && !$canRequestPact)
            <div class="modal-actions" style="margin-top:8px; display:flex; gap:8px;">
                @if(($relationStatus ?? 'none') === 'accepted')
                    <span class="empty-note">🤝 Pacte déjà actif avec ce joueur.</span>
                @elseif(($relationStatus ?? 'none') === 'pending')
                    <span class="empty-note">⏳ Demande de pacte déjà en attente.</span>
                @endif
            </div>
        @endif
    </div>

    <!-- Points totaux -->
    <div class="modal-total-points">
        <i class="fas fa-trophy"></i>
        <span class="points-text">Points totaux:</span>
        <span class="points-value">{{ number_format($userData['total_points']) }}</span>
    </div>

    @if(empty($userData['points_hidden']) || !$userData['points_hidden'])
        <!-- Statistiques détaillées -->
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-chart-bar"></i>
                Répartition des Points
            </h3>
            <div class="points-breakdown-detailed">
                @if($userData['building_points'] > 0)
                    <div class="points-category-detailed buildings">
                        <div class="category-header">
                            <i class="fas fa-building"></i>
                            <span class="category-name">Bâtiments</span>
                            <span class="category-points">{{ number_format($userData['building_points']) }}</span>
                        </div>
                        <div class="category-bar">
                            <div class="category-progress" style="width: {{ $this->getPointsPercentage($userData['building_points'], $userData['total_points']) }}%"></div>
                        </div>
                        <div class="category-percentage">{{ $this->getPointsPercentage($userData['building_points'], $userData['total_points']) }}%</div>
                    </div>
                @endif
                
                @if($userData['units_points'] > 0)
                    <div class="points-category-detailed units">
                        <div class="category-header">
                            <i class="fas fa-users"></i>
                            <span class="category-name">Unités</span>
                            <span class="category-points">{{ number_format($userData['units_points']) }}</span>
                        </div>
                        <div class="category-bar">
                            <div class="category-progress" style="width: {{ $this->getPointsPercentage($userData['units_points'], $userData['total_points']) }}%"></div>
                        </div>
                        <div class="category-percentage">{{ $this->getPointsPercentage($userData['units_points'], $userData['total_points']) }}%</div>
                    </div>
                @endif
                
                @if($userData['defense_points'] > 0)
                    <div class="points-category-detailed defense">
                        <div class="category-header">
                            <i class="fas fa-shield-alt"></i>
                            <span class="category-name">Défenses</span>
                            <span class="category-points">{{ number_format($userData['defense_points']) }}</span>
                        </div>
                        <div class="category-bar">
                            <div class="category-progress" style="width: {{ $this->getPointsPercentage($userData['defense_points'], $userData['total_points']) }}%"></div>
                        </div>
                        <div class="category-percentage">{{ $this->getPointsPercentage($userData['defense_points'], $userData['total_points']) }}%</div>
                    </div>
                @endif
                
                @if($userData['ship_points'] > 0)
                    <div class="points-category-detailed ships">
                        <div class="category-header">
                            <i class="fas fa-rocket"></i>
                            <span class="category-name">Vaisseaux</span>
                            <span class="category-points">{{ number_format($userData['ship_points']) }}</span>
                        </div>
                        <div class="category-bar">
                            <div class="category-progress" style="width: {{ $this->getPointsPercentage($userData['ship_points'], $userData['total_points']) }}%"></div>
                        </div>
                        <div class="category-percentage">{{ $this->getPointsPercentage($userData['ship_points'], $userData['total_points']) }}%</div>
                    </div>
                @endif
                
                @if($userData['technology_points'] > 0)
                    <div class="points-category-detailed technology">
                        <div class="category-header">
                            <i class="fas fa-flask"></i>
                            <span class="category-name">Technologies</span>
                            <span class="category-points">{{ number_format($userData['technology_points']) }}</span>
                        </div>
                        <div class="category-bar">
                            <div class="category-progress" style="width: {{ $this->getPointsPercentage($userData['technology_points'], $userData['total_points']) }}%"></div>
                        </div>
                        <div class="category-percentage">{{ $this->getPointsPercentage($userData['technology_points'], $userData['total_points']) }}%</div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Statistiques de Combat -->
    <div class="modal-section">
        <h3 class="section-title">
            <i class="fas fa-sword"></i>
            Statistiques de Combat
        </h3>
        <div class="combat-stats-grid">
            <!-- Combat Terrestre -->
            <div class="combat-category earth">
                <h4 class="combat-category-title">
                    <i class="fas fa-globe"></i>
                    Combat Terrestre
                </h4>
                <div class="combat-stats-row">
                    <div class="combat-stat">
                        <i class="fas fa-sword"></i>
                        <span class="stat-label">Points d'Attaque:</span>
                        <span class="stat-value">{{ number_format($userData['earth_attack'] ?? 0) }}</span>
                    </div>
                </div>
                <div class="combat-stats-row">
                    <div class="combat-stat">
                        <i class="fas fa-shield"></i>
                        <span class="stat-label">Points de Défense:</span>
                        <span class="stat-value">{{ number_format($userData['earth_defense'] ?? 0) }}</span>
                    </div>
                </div>
                <div class="combat-stats-row">
                    <div class="combat-stat">
                        <i class="fas fa-trophy"></i>
                        <span class="stat-label">Victoires Attaque:</span>
                        <span class="stat-value">{{ number_format($userData['earth_attack_count'] ?? 0) }}</span>
                    </div>
                </div>
                <div class="combat-stats-row">
                    <div class="combat-stat">
                        <i class="fas fa-medal"></i>
                        <span class="stat-label">Victoires Défense:</span>
                        <span class="stat-value">{{ number_format($userData['earth_defense_count'] ?? 0) }}</span>
                    </div>
                </div>
                <div class="combat-stats-row">
                    <div class="combat-stat defeat">
                        <i class="fas fa-times-circle"></i>
                        <span class="stat-label">Défaites:</span>
                        <span class="stat-value">{{ number_format($userData['earth_loser_count'] ?? 0) }}</span>
                    </div>
                </div>
            </div>

            <!-- Combat Spatial -->
            <div class="combat-category spatial">
                <h4 class="combat-category-title">
                    <i class="fas fa-rocket"></i>
                    Combat Spatial
                </h4>
                <div class="combat-stats-row">
                    <div class="combat-stat">
                        <i class="fas fa-sword"></i>
                        <span class="stat-label">Points d'Attaque:</span>
                        <span class="stat-value">{{ number_format($userData['spatial_attack'] ?? 0) }}</span>
                    </div>
                </div>
                <div class="combat-stats-row">
                    <div class="combat-stat">
                        <i class="fas fa-shield"></i>
                        <span class="stat-label">Points de Défense:</span>
                        <span class="stat-value">{{ number_format($userData['spatial_defense'] ?? 0) }}</span>
                    </div>
                </div>
                <div class="combat-stats-row">
                    <div class="combat-stat">
                        <i class="fas fa-trophy"></i>
                        <span class="stat-label">Victoires Attaque:</span>
                        <span class="stat-value">{{ number_format($userData['spatial_attack_count'] ?? 0) }}</span>
                    </div>
                </div>
                <div class="combat-stats-row">
                    <div class="combat-stat">
                        <i class="fas fa-medal"></i>
                        <span class="stat-label">Victoires Défense:</span>
                        <span class="stat-value">{{ number_format($userData['spatial_defense_count'] ?? 0) }}</span>
                    </div>
                </div>
                <div class="combat-stats-row">
                    <div class="combat-stat defeat">
                        <i class="fas fa-times-circle"></i>
                        <span class="stat-label">Défaites:</span>
                        <span class="stat-value">{{ number_format($userData['spatial_loser_count'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations générales -->
    <div class="modal-section">
        <h3 class="section-title">
            <i class="fas fa-info-circle"></i>
            Informations Générales
        </h3>
        <div class="general-info-grid">
            <div class="info-item">
                <i class="fas fa-globe"></i>
                <span class="info-label">Planètes:</span>
                <span class="info-value">{{ $userData['planets_count'] }}</span>
            </div>
            <div class="info-item">
                <i class="fas fa-trophy"></i>
                <span class="info-label">Rang global:</span>
                <span class="info-value">#{{ $userRank }}</span>
            </div>
        </div>
    </div>
</div>