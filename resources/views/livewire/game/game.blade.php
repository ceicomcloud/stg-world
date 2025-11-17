<div page="game">
    <div class="game-container">
        <livewire:game.server-news-banner />
        @if($eventActive && $activeServerEvent)
            <div class="server-event-banner" role="status" aria-live="polite">
                <div class="server-event-inner">
                    <span class="event-icon"><i class="fas fa-bolt"></i></span>
                    <span class="event-text">
                        Événement en cours: <strong>{{ ucfirst($activeServerEvent['type'] ?? 'inconnu') }}</strong>
                        @if(!empty($activeServerEvent['end_at']))
                            — se termine: <span class="event-time">{{ $activeServerEvent['end_at'] }}</span>
                        @endif
                        @if(!empty($activeServerEvent['points_multiplier']))
                            — multiplicateur: <span class="event-mult">x{{ number_format($activeServerEvent['points_multiplier'], 2) }}</span>
                        @endif
                    </span>
                    @if(!empty($activeServerEvent['reward_type']))
                        <span class="event-badge">
                            Récompense: {{ $activeServerEvent['reward_type'] === 'gold' ? 'Or' : 'Ressources' }}
                        </span>
                    @endif
                </div>
            </div>
        @endif
        
        @if($currentPlanet)
            <!-- Compact Planet Header -->
            <div class="planet-header-compact">
                <div class="planet-main-info">
                    <div class="planet-visual-small">
                        <div class="planet-sphere-small planet-{{ $currentPlanet->templatePlanet->type ?? 'planet' }}"></div>
                    </div>
                    <div class="planet-details">
                        <h1 class="planet-name">{{ $currentPlanet->name ?? 'Planète Inconnue' }}</h1>
                        <div class="planet-meta">
                            <span class="coordinates">[{{ $currentPlanet->templatePlanet->galaxy ?? 'N/A' }}:{{ $currentPlanet->templatePlanet->system ?? 'N/A' }}:{{ $currentPlanet->templatePlanet->position ?? 'N/A' }}]</span>
                            <span class="planet-type">{{ ucfirst($currentPlanet->templatePlanet->type ?? 'Inconnu') }}</span>
                            <span class="fields">{{ $currentPlanet->used_fields }}/{{ $currentPlanet->templatePlanet->fields ?? 'N/A' }} cases</span>
                        </div>
                    </div>
                </div>
                
                <!-- Compact Resource Bonuses -->
                @if($currentPlanet->templatePlanet)
                    <div class="resource-bonuses-compact">
                        <div class="bonus-item-compact">
                            <img src="/images/resources/metal.png" alt="metal" />
                            <span>{{ number_format(($currentPlanet->templatePlanet->metal_bonus - 1) * 100, 0) }}%</span>
                        </div>
                        <div class="bonus-item-compact">
                            <img src="/images/resources/crystal.png" alt="crystal" />
                            <span>{{ number_format(($currentPlanet->templatePlanet->crystal_bonus - 1) * 100, 0) }}%</span>
                        </div>
                        <div class="bonus-item-compact">
                            <img src="/images/resources/deuterium.png" alt="deuterium" />
                            <span>{{ number_format(($currentPlanet->templatePlanet->deuterium_bonus - 1) * 100, 0) }}%</span>
                        </div>
                        <div class="bonus-item-compact">
                            <i class="fas fa-bolt"></i>
                            <span>{{ number_format(($currentPlanet->templatePlanet->energy_bonus - 1) * 100, 0) }}%</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Daily Reward Banner -->
            @if($dailyRewardClaimable)
                <div class="daily-reward-banner">
                    <div class="daily-reward-header">
                        <h3><i class="fas fa-gift"></i> Récompense quotidienne (Jour {{ $dailyRewardDay }}/7)</h3>
                        <div class="daily-reward-preview">
                            <span><img src="/images/resources/metal.png" alt="metal" /> {{ number_format($dailyRewardPreview['metal'] ?? 0) }}</span>
                            <span><img src="/images/resources/crystal.png" alt="crystal" /> {{ number_format($dailyRewardPreview['crystal'] ?? 0) }}</span>
                            <span><img src="/images/resources/deuterium.png" alt="deuterium" /> {{ number_format($dailyRewardPreview['deuterium'] ?? 0) }}</span>
                            <span><i class="fas fa-coins"></i> {{ number_format($dailyRewardPreview['gold'] ?? 0) }} or</span>
                        </div>
                        <button class="btn-claim" wire:click="claimDailyReward"><i class="fas fa-hand-holding-heart"></i> Récupérer</button>
                    </div>
                    <div class="daily-reward-steps">
                        @for($i = 1; $i <= 7; $i++)
                            <div class="reward-step {{ $i === $dailyRewardDay ? 'current' : '' }}">
                                <span class="step-index">J{{ $i }}</span>
                            </div>
                        @endfor
                    </div>
                    @if(!empty($dailyRewardSchedule))
                        <div class="daily-reward-schedule">
                            @for($i = 1; $i <= 7; $i++)
                                @php
                                    $sched = $dailyRewardSchedule[$i] ?? ['metal'=>0,'crystal'=>0,'deuterium'=>0,'gold'=>0];
                                @endphp
                                <div class="reward-day-card {{ $i === $dailyRewardDay ? 'current' : '' }}">
                                    <div class="reward-day-header">Jour {{ $i }}</div>
                                    <div class="reward-day-items">
                                        <span title="Métal"><img src="/images/resources/metal.png" alt="metal" /> {{ number_format($sched['metal']) }}</span>
                                        <span title="Cristal"><img src="/images/resources/crystal.png" alt="crystal" /> {{ number_format($sched['crystal']) }}</span>
                                        <span title="Deutérium"><img src="/images/resources/deuterium.png" alt="deuterium" /> {{ number_format($sched['deuterium']) }}</span>
                                        <span title="Or"><i class="fas fa-coins"></i> {{ number_format($sched['gold']) }} or</span>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    @endif
                </div>
            @endif

            <!-- Main Content Grid -->
            <div class="game-grid">
                <!-- Left Column: Queues -->
                <div class="grid-left">
                    @if($queues && (count($queues['building']) > 0 || count($queues['unit']) > 0 || count($queues['defense']) > 0 || count($queues['ship']) > 0))
                        <div class="current-queues-compact">
                            <h3><i class="fas fa-list"></i> Files d'attente</h3>
                            
                            @if(count($queues['building']) > 0)
                                <div class="queue-section-compact">
                                    <h4><i class="fas fa-building"></i> Bâtiments</h4>
                                    @foreach($queues['building'] as $item)
                                        <div class="queue-item-compact">
                                            <div class="queue-info">
                                                <span class="name">{{ $item->item->label ?? 'Inconnu' }}</span>
                                                <span class="level">Niv. {{ $item->level }}</span>
                                            </div>
                                            <div class="queue-timer" x-data="queueTimer('{{ $item->end_time ? $item->end_time->unix() : '' }}', '{{ now()->unix() }}')" x-init="init()" x-text="display"></div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if(count($queues['unit']) > 0)
                                <div class="queue-section-compact">
                                    <h4><i class="fas fa-users"></i> Unités</h4>
                                    @foreach($queues['unit'] as $item)
                                        <div class="queue-item-compact">
                                            <div class="queue-info">
                                                <span class="name">{{ $item->item->label ?? 'Inconnu' }}</span>
                                                <span class="quantity">x{{ $item->quantity }}</span>
                                            </div>
                                            <div class="queue-timer" x-data="queueTimer('{{ $item->end_time ? $item->end_time->unix() : '' }}', '{{ now()->unix() }}')" x-init="init()" x-text="display"></div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if(count($queues['defense']) > 0)
                                <div class="queue-section-compact">
                                    <h4><i class="fas fa-shield-alt"></i> Défenses</h4>
                                    @foreach($queues['defense'] as $item)
                                        <div class="queue-item-compact">
                                            <div class="queue-info">
                                                <span class="name">{{ $item->item->label ?? 'Inconnu' }}</span>
                                                <span class="quantity">x{{ $item->quantity }}</span>
                                            </div>
                                            <div class="queue-timer" x-data="queueTimer('{{ $item->end_time ? $item->end_time->unix() : '' }}', '{{ now()->unix() }}')" x-init="init()" x-text="display"></div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if(count($queues['ship']) > 0)
                                <div class="queue-section-compact">
                                    <h4><i class="fas fa-rocket"></i> Vaisseaux</h4>
                                    @foreach($queues['ship'] as $item)
                                        <div class="queue-item-compact">
                                            <div class="queue-info">
                                                <span class="name">{{ $item->item->label ?? 'Inconnu' }}</span>
                                                <span class="quantity">x{{ $item->quantity }}</span>
                                            </div>
                                            <div class="queue-timer" x-data="queueTimer('{{ $item->end_time ? $item->end_time->unix() : '' }}', '{{ now()->unix() }}')" x-init="init()" x-text="display"></div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
                
                <!-- Right Column: Mission Radar -->
                <div class="grid-right">
                    @if(count($missions) > 0)
                        <div class="mission-radar-compact">
                            <h3><i class="fas fa-radar"></i> Radar des Missions</h3>
                            <div class="mission-list-compact">
                                @foreach($missions as $mission)
                                    <div class="mission-item-compact mission-{{ $mission->status }}" wire:click="openMissionInfo({{ $mission->id }})">
                                        <div class="mission-header-compact">
                                            <div class="mission-info-left">
                                                <span class="mission-type">{{ $mission->getType() }}</span>
                                                <span class="mission-status status-{{ $mission->status }}">{{ $mission->getStatus() }}</span>
                                            </div>
                                            <div class="mission-info-right">
                                                <div class="mission-timer" data-end-time="{{
                                                    $mission->status === 'traveling' && $mission->arrival_time ? $mission->arrival_time->unix() : (
                                                        ($mission->status === 'returning' || $mission->status === 'collecting' || $mission->status === 'exploring') && $mission->return_time ? $mission->return_time->unix() : ''
                                                    )
                                                }}"></div>
                                                @if($mission->status === 'traveling')
                                                    <div class="mission-actions-compact">
                                                        <button wire:click.stop="confirmMissionRecall({{ $mission->id }})" 
                                                                class="btn-recall"
                                                                title="Rappeler la mission">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mission-route-compact">
                                            <span class="from">{{ $mission->fromPlanet->name ?? 'Planète' }} [{{ $mission->fromPlanet->templatePlanet->galaxy ?? 'N/A' }}:{{ $mission->fromPlanet->templatePlanet->system ?? 'N/A' }}:{{ $mission->fromPlanet->templatePlanet->position ?? 'N/A' }}]</span>
                                            <i class="fas fa-arrow-right"></i>
                                            <span class="to">[{{ $mission->to_galaxy }}:{{ $mission->to_system }}:{{ $mission->to_position }}]</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Modal de confirmation pour le rappel de mission -->
            <x-input.modal-confirmation 
                wire:model="showRecallModal" 
                title="Rappeler la mission" 
                message="Êtes-vous sûr de vouloir rappeler cette mission ?" 
                icon="fas fa-question-circle text-warning" 
                confirmText="Oui, rappeler" 
                cancelText="Continuer la mission" 
                onConfirm="performMissionRecall" 
                onCancel="dismissModals" 
            />

            <!-- Player Level and Progression -->
            <div class="player-level-section">
                <h3><i class="fas fa-star"></i> Niveau et Progression</h3>
                <div class="level-display">
                    <div class="level-badge">{{ $user->getLevel() }}</div>
                    <div class="level-info">
                        <div class="level-title">Niveau {{ $user->getLevel() }}</div>
                        <div class="level-exp">{{ number_format($user->getCurrentExperience()) }} / {{ number_format($user->getRequiredExperienceForLevel($user->getLevel())) }} XP</div>
                    </div>
                </div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: {{ $user->getLevelProgress() }}%"></div>
                </div>
            </div>

            <!-- Combat Statistics -->
            <div class="combat-stats-section">
                <h3><i class="fas fa-fighter-jet"></i> Statistiques de Combat</h3>
                <div class="combat-stats-grid">
                    <div class="combat-category">
                        <h4><i class="fas fa-globe"></i> Combat Terrestre</h4>
                        <div class="combat-stats-list">
                            <div class="combat-stat-item">
                                <span class="combat-stat-label">Attaques</span>
                                <span class="combat-stat-value">{{ $user->userStat->earth_attack_count ?? 0 }}</span>
                            </div>
                            <div class="combat-stat-item">
                                <span class="combat-stat-label">Défenses</span>
                                <span class="combat-stat-value">{{ $user->userStat->earth_defense_count ?? 0 }}</span>
                            </div>
                            <div class="combat-stat-item">
                                <span class="combat-stat-label">Victoires</span>
                                <span class="combat-stat-value positive">{{ ($user->userStat->earth_attack_count ?? 0) - ($user->userStat->earth_loser_count ?? 0) }}</span>
                            </div>
                            <div class="combat-stat-item">
                                <span class="combat-stat-label">Défaites</span>
                                <span class="combat-stat-value negative">{{ $user->userStat->earth_loser_count ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="combat-category">
                        <h4><i class="fas fa-space-shuttle"></i> Combat Spatial</h4>
                        <div class="combat-stats-list">
                            <div class="combat-stat-item">
                                <span class="combat-stat-label">Attaques</span>
                                <span class="combat-stat-value">{{ $user->userStat->spatial_attack_count ?? 0 }}</span>
                            </div>
                            <div class="combat-stat-item">
                                <span class="combat-stat-label">Défenses</span>
                                <span class="combat-stat-value">{{ $user->userStat->spatial_defense_count ?? 0 }}</span>
                            </div>
                            <div class="combat-stat-item">
                                <span class="combat-stat-label">Victoires</span>
                                <span class="combat-stat-value positive">{{ ($user->userStat->spatial_attack_count ?? 0) - ($user->userStat->spatial_loser_count ?? 0) }}</span>
                            </div>
                            <div class="combat-stat-item">
                                <span class="combat-stat-label">Défaites</span>
                                <span class="combat-stat-value negative">{{ $user->userStat->spatial_loser_count ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Badges Section -->
            <div class="badges-section">
                <h3><i class="fas fa-medal"></i> Badges</h3>
                <div class="badges-container">
                    <div class="badges-group">
                        <h4>Badges Récents</h4>
                        <div class="badges-list">
                            @forelse($recentBadges as $badge)
                                <div class="badge-item">
                                    <div class="badge-symbol {{ $badge->rarity }}">
                                        <i class="fas fa-medal"></i>
                                    </div>
                                    <div class="badge-info">
                                        <div class="badge-name">{{ $badge->name }}</div>
                                        <div class="badge-meta">
                                            <span class="badge-rarity {{ $badge->rarity }}">{{ ucfirst($badge->rarity) }}</span>
                                            <span>{{ $badge->pivot?->earned_at ? \Carbon\Carbon::parse($badge->pivot->earned_at)->diffForHumans() : '—' }}</span>
                                        </div>
                                        @if(!empty($badge->description))
                                            <div class="badge-description">{{ $badge->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="no-badges">Aucun badge obtenu pour le moment</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="badges-group">
                        <h4>Badges à Débloquer</h4>
                        <div class="badges-list">
                            @forelse($upcomingBadges as $badge)
                                @php
                                    $badgeService = app(\App\Services\BadgeService::class);
                                    $progress = $badgeService->getBadgeProgress($user, $badge);
                                    $reqType = $badge->requirement_type;
                                    $reqValue = $badge->requirement_value;
                                    $requirementText = match($reqType) {
                                        \App\Models\Template\TemplateBadge::REQUIREMENT_REACH_LEVEL => 'Atteindre le niveau ' . $reqValue,
                                        \App\Models\Template\TemplateBadge::REQUIREMENT_TOTAL_EXPERIENCE => 'Atteindre ' . number_format($reqValue) . ' XP total',
                                        \App\Models\Template\TemplateBadge::REQUIREMENT_RESEARCH_POINTS => 'Accumuler ' . number_format($reqValue) . ' points de recherche',
                                        \App\Models\Template\TemplateBadge::REQUIREMENT_CUSTOM => (!empty($badge->description) ? $badge->description : 'Condition personnalisée'),
                                        default => 'Condition inconnue',
                                    };
                                @endphp
                                <div class="badge-item">
                                    <div class="badge-symbol {{ $badge->rarity }}">
                                        <i class="fas fa-medal"></i>
                                    </div>
                                    <div class="badge-info">
                                        <div class="badge-name">{{ $badge->name }}</div>
                                        <div class="badge-meta">
                                            <span class="badge-rarity {{ $badge->rarity }}">{{ ucfirst($badge->rarity) }}</span>
                                        </div>
                                        <div class="badge-condition">
                                            {{ $requirementText }}
                                        </div>
                                        <div class="badge-progress">
                                            <div class="badge-progress-bar" style="width: {{ $progress }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="no-badges">Aucun badge en progression</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Planets Carousel -->
            <div class="planets-carousel-section">
                <h3><i class="fas fa-globe"></i> Vos Planètes</h3>
                <div class="planets-carousel">
                    @foreach($planets as $planet)
                        <div class="planet-card {{ $planet->id == $currentPlanet->id ? 'active' : '' }}" wire:click="switchPlanet({{ $planet->id }})">
                            <div class="planet-card-header">
                                <div class="planet-mini-sphere planet-{{ $planet->templatePlanet->type ?? 'planet' }}"></div>
                                <div class="planet-card-title">
                                    <div class="planet-card-name">{{ $planet->name }}</div>
                                    <div class="planet-card-coords">[{{ $planet->templatePlanet->galaxy ?? 'N/A' }}:{{ $planet->templatePlanet->system ?? 'N/A' }}:{{ $planet->templatePlanet->position ?? 'N/A' }}]</div>
                                </div>
                            </div>
                            <div class="planet-card-details">
                                <div class="planet-card-fields">
                                    <span class="planet-card-fields-label">Cases:</span>
                                    <span class="planet-card-fields-value">{{ $planet->used_fields }}/{{ $planet->templatePlanet->fields ?? 'N/A' }}</span>
                                </div>
                                <div class="planet-card-resources">
                                    <div class="planet-resource-item">
                                        <img src="/images/resources/metal.png" alt="metal" class="planet-resource-icon">
                                        <span class="planet-resource-value">{{ number_format($planet->resources->where('resource_id', 1)->first()->current_amount ?? 0) }}</span>
                                    </div>
                                    <div class="planet-resource-item">
                                        <img src="/images/resources/crystal.png" alt="crystal" class="planet-resource-icon">
                                        <span class="planet-resource-value">{{ number_format($planet->resources->where('resource_id', 2)->first()->current_amount ?? 0) }}</span>
                                    </div>
                                    <div class="planet-resource-item">
                                        <img src="/images/resources/deuterium.png" alt="deuterium" class="planet-resource-icon">
                                        <span class="planet-resource-value">{{ number_format($planet->resources->where('resource_id', 3)->first()->current_amount ?? 0) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="planet-header">
                <h1 class="planet-name">Aucune planète sélectionnée</h1>
                <p>Veuillez sélectionner une planète pour voir ses informations.</p>
            </div>
        @endif
    </div>
</div>