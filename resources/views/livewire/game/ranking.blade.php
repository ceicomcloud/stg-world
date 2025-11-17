<div page="ranking" 
     wire:keydown.arrow-left="previousPage" 
     wire:keydown.arrow-right="nextPage" 
     tabindex="0">
    <div class="ranking-container">
        <!-- Barre de recherche -->        
        <div class="ranking-search-container">
            <div class="search-input-container">
                <input type="text" 
                       wire:model.live.debounce.300ms="searchQuery" 
                       placeholder="Rechercher un joueur..." 
                       class="search-input">
                @if($searchQuery)
                    <button class="search-reset-btn" wire:click="resetSearch">
                        <i class="fas fa-times"></i>
                    </button>
                @endif
            </div>
            @if($compareUserData)
                <div class="compare-info">
                    <span>Comparaison avec: <strong>{{ $compareUserData['name'] }}</strong></span>
                    <button class="compare-cancel-btn" wire:click="cancelComparison">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                </div>
            @endif
        </div>
        
        <!-- Onglets de catégories -->
        <div class="ranking-tabs">
            <div class="ranking-tab {{ $activeCategory === 'total' ? 'active' : '' }}" 
                 wire:click="switchCategory('total')">
                <i class="fas fa-trophy"></i>
                Total
            </div>
            <div class="ranking-tab {{ $activeCategory === 'event' ? 'active' : '' }}" 
                 wire:click="switchCategory('event')">
                <i class="fas fa-flag"></i>
                Événement
            </div>
            <div class="ranking-tab {{ $activeCategory === 'buildings' ? 'active' : '' }}" 
                 wire:click="switchCategory('buildings')">
                <i class="fas fa-building"></i>
                Bâtiments
            </div>
            <div class="ranking-tab {{ $activeCategory === 'units' ? 'active' : '' }}" 
                 wire:click="switchCategory('units')">
                <i class="fas fa-users"></i>
                Unités
            </div>
            <div class="ranking-tab {{ $activeCategory === 'defense' ? 'active' : '' }}" 
                 wire:click="switchCategory('defense')">
                <i class="fas fa-shield-alt"></i>
                Défenses
            </div>
            <div class="ranking-tab {{ $activeCategory === 'ships' ? 'active' : '' }}" 
                 wire:click="switchCategory('ships')">
                <i class="fas fa-rocket"></i>
                Vaisseaux
            </div>
            <div class="ranking-tab {{ $activeCategory === 'technology' ? 'active' : '' }}" 
                 wire:click="switchCategory('technology')">
                <i class="fas fa-flask"></i>
                Technologies
            </div>
        </div>

        <!-- Info: Limites Fort/Faible (Attaque & Espionnage) -->
        <div class="ranking-info-banner">
            <div class="banner-header">
                <i class="fas fa-shield-alt"></i>
                <div class="banner-title">
                    <strong>Système Fort/Faible</strong>
                </div>
            </div>

            @if($spyEnabled || $atkEnabled)
            <div class="banner-grid">
                @if($atkEnabled)
                    <div class="banner-section attack">
                        <div class="section-title"><i class="fas fa-crosshairs"></i> Attaques</div>
                        @if(!is_null($atkExampleBase))
                            <div class="band-visual">
                                <div class="band-range attack">
                                    <span class="band-marker min">{{ number_format($atkMin) }}</span>
                                    <span class="band-center">{{ number_format($atkExampleBase) }}</span>
                                    <span class="band-marker max">{{ number_format($atkMax) }}</span>
                                </div>
                            </div>
                        @else
                            <div class="band-legend muted">Limites d'attaque désactivées.</div>
                        @endif
                    </div>
                @endif

                @if($spyEnabled)
                    <div class="banner-section spy">
                        <div class="section-title"><i class="fas fa-eye"></i> Espionnage</div>
                        @if(!is_null($spyExampleBase))
                            <div class="band-visual">
                                <div class="band-range spy">
                                    <span class="band-marker min">{{ number_format($spyMin) }}</span>
                                    <span class="band-center">{{ number_format($spyExampleBase) }}</span>
                                    <span class="band-marker max">{{ number_format($spyMax) }}</span>
                                </div>
                            </div>
                        @else
                            <div class="band-legend muted">Limites d'espionnage désactivées.</div>
                        @endif
                    </div>
                @endif
            </div>
            @else
                <div class="band-legend muted">Les limitations Fort/Faible sont désactivées par l'administrateur.</div>
            @endif
        </div>

        @if($activeCategory === 'event')
            <!-- Cadre d'information Événement -->
            <div class="event-info-frame">
                <div class="event-info-header">
                    <span>Événement en cours</span>
                </div>

                @if($eventActive)
                    <div class="event-user-reward">
                        <span class="label">Votre gain estimé</span>
                        @if(!is_null($eventUserReward))
                            <span class="value">{{ $eventUserRewardText }}</span>
                            <span class="sub">avec {{ number_format($eventUserPoints) }} points</span>
                        @else
                            <span class="value muted">Aucun point pour l'instant</span>
                            <span class="sub">Gagnez des points pour débloquer une récompense</span>
                        @endif
                    </div>
                    <div class="event-info-grid">
                        <div class="event-info-item">
                            <span class="label">Type</span>
                            <span class="value">{{ $eventTypeLabel }}</span>
                        </div>
                        <div class="event-info-item">
                            <span class="label">Durée</span>
                            <span class="value">{{ $eventDurationDays ? $eventDurationDays . ' jours' : 'N/A' }}</span>
                        </div>
                        <div class="event-info-item">
                            <span class="label">Récompense</span>
                            <span class="value">{{ $rewardTypeLabel }} (base {{ number_format($baseReward) }}, x{{ $pointsMultiplier }})</span>
                        </div>
                    </div>
                @else
                    <div class="band-legend muted">Aucun événement actif actuellement.</div>
                @endif
            </div>
        @endif

        <!-- Tableau de classement -->
        @if(count($rankings) > 0)
            <div class="ranking-table-container">
                <table class="ranking-table">
                    <thead>
                        <tr>
                            <th>Rang</th>
                            <th>Joueur</th>
                            <th>Alliance</th>
                            <th>Points {{ $this->getCategoryLabel($activeCategory) }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rankings as $ranking)
                            <tr class="{{ $ranking->id === $user->id ? 'current-user-row' : ($ranking->isAllied ? 'ally-row' : ($ranking->isEnemy ? 'enemy-row' : '')) }}">
                                <td>
                                    <div class="ranking-position-container">
                                        <div class="ranking-position {{ $ranking->rank <= 3 ? ($ranking->rank === 1 ? 'gold' : ($ranking->rank === 2 ? 'silver' : 'bronze')) : 'regular' }}">
                                            {{ $ranking->rank }}
                                        </div>
                                        @if(!empty($ranking->changeIndicator) && ($ranking->changeIndicator['change'] ?? 0) !== 0)
                                            <div class="ranking-change-indicator {{ $ranking->changeIndicator['class'] ?? '' }}">
                                                <i class="{{ $ranking->changeIndicator['icon'] ?? '' }} icon"></i>
                                                <span class="text">{{ $ranking->changeIndicator['text'] ?? '' }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="player-actions">
                                        <div class="player-name {{ $ranking->id === $user->id ? 'current-user' : '' }} {{ $ranking->bandClass ?? '' }}" 
                                             wire:click="openUserProfile({{ $ranking->id }})" 
                                             style="cursor: pointer;">
                                            {{ $ranking->name }}
                                            <i class="fas fa-eye player-info-icon"></i>
                                            <span class="relation-badges">
                                                @if($ranking->id === $user->id)
                                                    <span class="relation-badge me">
                                                        <i class="fas fa-user icon"></i> Moi
                                                    </span>
                                                @elseif(!empty($ranking->isAllied))
                                                    <span class="relation-badge ally">
                                                        <i class="fas fa-handshake icon"></i> Allié
                                                    </span>
                                                @elseif(!empty($ranking->isEnemy))
                                                    <span class="relation-badge enemy">
                                                        <i class="fas fa-crosshairs icon"></i> Ennemi
                                                    </span>
                                                @endif
                                            </span>
                                        </div>
                                        @if($ranking->id !== $user->id)
                                            <div class="player-compare-actions">
                                                @if($compareUserData && $compareUserData['id'] !== $ranking->id)
                                                    <button class="compare-btn" 
                                                            wire:click="compareUsers({{ $compareUserData['id'] }}, {{ $ranking->id }})" 
                                                            title="Comparer avec {{ $compareUserData['name'] }}">
                                                        <i class="fas fa-balance-scale"></i>
                                                    </button>
                                                @endif
                                                <button class="select-compare-btn {{ $compareUserData && $compareUserData['id'] === $ranking->id ? 'active' : '' }}" 
                                                        wire:click="selectUserForComparison({{ $ranking->id }})" 
                                                        title="{{ $compareUserData && $compareUserData['id'] === $ranking->id ? 'Sélectionné pour comparaison' : 'Sélectionner pour comparaison' }}">
                                                    <i class="fas fa-{{ $compareUserData && $compareUserData['id'] === $ranking->id ? 'check' : 'user-plus' }}"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($ranking->alliance_id)
                                        <div class="alliance-name" 
                                             wire:click="openAllianceProfile({{ $ranking->alliance_id }})" 
                                             style="cursor: pointer; color: var(--stargate-primary); font-weight: 600;">
                                            [{{ $ranking->alliance->tag ?? 'N/A' }}] {{ $ranking->alliance->name ?? 'Alliance inconnue' }}
                                            <i class="fas fa-eye alliance-info-icon" style="margin-left: 0.5rem; font-size: 0.875rem;"></i>
                                        </div>
                                    @else
                                        <div class="no-alliance" style="color: var(--stargate-text-secondary); font-style: italic;">
                                            Aucune alliance
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="points-value">
                                        @if($activeCategory === 'event')
                                            {{ $this->getEventPointsForUser($ranking) }}
                                        @else
                                            {{ $this->getPointsForCategory($ranking->userStat, $activeCategory) }}
                                        @endif
                                    </div>
                                </td>
                        
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($totalPages > 1)
                <div class="ranking-pagination">
                    <button class="pagination-btn {{ $currentPage <= 1 ? 'disabled' : '' }}" 
                            wire:click="previousPage" 
                            {{ $currentPage <= 1 ? 'disabled' : '' }}>
                        <i class="fas fa-chevron-left"></i>
                        Précédent
                    </button>
                    
                    @if($paginationStart > 1)
                        <button class="pagination-btn" wire:click="goToPage(1)">1</button>
                        @if($paginationStart > 2)
                            <span class="pagination-dots">...</span>
                        @endif
                    @endif
                    
                    @for($i = $paginationStart; $i <= $paginationEnd; $i++)
                        <button class="pagination-btn {{ $i === $currentPage ? 'active' : '' }}" 
                                wire:click="goToPage({{ $i }})">
                            {{ $i }}
                        </button>
                    @endfor
                    
                    @if($paginationEnd < $totalPages)
                        @if($paginationEnd < $totalPages - 1)
                            <span class="pagination-dots">...</span>
                        @endif
                        <button class="pagination-btn" wire:click="goToPage({{ $totalPages }})">{{ $totalPages }}</button>
                    @endif
                    
                    <button class="pagination-btn {{ $currentPage >= $totalPages ? 'disabled' : '' }}" 
                            wire:click="nextPage" 
                            {{ $currentPage >= $totalPages ? 'disabled' : '' }}>
                        Suivant
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            @endif

            <!-- Position de l'utilisateur actuel -->
            @if($activeCategory !== 'event' && $userRanking && $userRanking['rank'] > $perPage)
                <div class="user-ranking-info">
                    <div class="ranking-table-container">
                        <table class="ranking-table">
                            <thead>
                                <tr>
                                    <th colspan="{{ $activeCategory === 'total' ? 4 : 3 }}">Votre Position</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="current-user-row">
                                    <td>
                                        <div class="ranking-position-container">
                                            <div class="ranking-position regular">
                                                {{ $userRanking['rank'] ?? 'N/A' }}
                                            </div>
                                            @if(!empty($userChangeIndicator) && ($userChangeIndicator['change'] ?? 0) !== 0)
                                                <div class="ranking-change {{ $userChangeIndicator['class'] ?? '' }}">
                                                    <i class="{{ $userChangeIndicator['icon'] ?? '' }}"></i>
                                                    <span>{{ $userChangeIndicator['text'] ?? '' }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="player-actions">
                                            <div class="player-name current-user {{ ($user->vip_active && ($user->vip_badge_enabled ?? true)) ? 'vip-frame' : '' }}" 
                                                 wire:click="openUserProfile({{ $user->id }})" 
                                                 style="cursor: pointer;">
                                                {{ $user->name }}
                                                <i class="fas fa-eye player-info-icon"></i>
                                            </div>
                                            @if($compareUserData && $compareUserData['id'] !== $user->id)
                                                <div class="player-compare-actions">
                                                    <button class="compare-btn" 
                                                            wire:click="compareUsers({{ $compareUserData['id'] }}, {{ $user->id }})" 
                                                            title="Comparer avec {{ $compareUserData['name'] }}">
                                                        <i class="fas fa-balance-scale"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="points-value">
                                            {{ number_format($userRanking['points']) }}
                                        </div>
                                    </td>
                                    @if($activeCategory === 'total' && $user->userStat)
                                        <td>
                                            <div class="points-breakdown">
                                                @if($user->userStat->building_points > 0)
                                                    <div class="points-category buildings">
                                                        <i class="fas fa-building icon"></i>
                                                        {{ number_format($user->userStat->building_points) }}
                                                    </div>
                                                @endif
                                                @if($user->userStat->units_points > 0)
                                                    <div class="points-category units">
                                                        <i class="fas fa-users icon"></i>
                                                        {{ number_format($user->userStat->units_points) }}
                                                    </div>
                                                @endif
                                                @if($user->userStat->defense_points > 0)
                                                    <div class="points-category defense">
                                                        <i class="fas fa-shield-alt icon"></i>
                                                        {{ number_format($user->userStat->defense_points) }}
                                                    </div>
                                                @endif
                                                @if($user->userStat->ship_points > 0)
                                                    <div class="points-category ships">
                                                        <i class="fas fa-rocket icon"></i>
                                                        {{ number_format($user->userStat->ship_points) }}
                                                    </div>
                                                @endif
                                                @if($user->userStat->technology_points > 0)
                                                    <div class="points-category technology">
                                                        <i class="fas fa-flask icon"></i>
                                                        {{ number_format($user->userStat->technology_points) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @else
            <!-- État vide -->
            <div class="empty-state">
                <div class="icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="message">Aucun classement disponible</div>
                <div class="submessage">Les points des joueurs n'ont pas encore été calculés.</div>
            </div>
        @endif
    </div>
</div>