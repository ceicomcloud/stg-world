<div page="profile">
    <div class="profile-content">
        <div class="profile-sidebar">
            <div class="profile-header">
                <img src="{{ $this->avatarUrl }}" alt="Avatar" class="profile-avatar" />
                <h1 class="profile-name">{{ $user->name }}</h1>
                <p class="profile-email">{{ $user->email }}</p>
                <div class="profile-join-date">
                    <i class="fas fa-calendar-alt"></i>
                    Membre depuis {{ $this->formatDate($user->created_at) }}
                </div>
            </div>

            <div class="profile-stats">
                <div class="stat-item">
                    <span class="stat-value">{{ $user->getLevel() }}</span>
                    <span class="stat-label">Niveau</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">{{ $this->formatNumber($user->getCurrentExperience()) }}</span>
                    <span class="stat-label">Expérience</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">{{ $this->formatNumber($this->researchPoints) }}</span>
                    <span class="stat-label">Points de Recherche</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">{{ $this->badgeCount }}</span>
                    <span class="stat-label">Badges</span>
                </div>
            </div>
        </div>

        <div class="profile-main">
            <div class="profile-card progression-section">
                <h2 class="section-title"><i class="fas fa-chart-line"></i> Progression</h2>

                <div class="level-info">
                    <span class="current-level">Niveau {{ $user->getLevel() }}</span>
                    <span class="next-level">Niveau {{ $user->getLevel() + 1 }}</span>
                </div>

                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $this->experienceProgress }}%"></div>
                </div>

                <div class="progress-text">
                    {{ $this->experienceToNext }} / {{ $this->experienceForNext }} XP ({{ round($this->experienceProgress) }}%)
                </div>
            </div>

            <div class="profile-card vacation-mode-section">
                <h2 class="section-title"><i class="fas fa-plane"></i> Mode Vacances</h2>
                <div class="vacation-mode-content">
                    @if($this->is_in_vacation_mode)
                        <div class="vacation-active">
                            <div class="vacation-status">
                                <i class="fas fa-check-circle"></i> Mode vacances actif
                            </div>
                            <div class="vacation-info">
                                <p>Votre compte est actuellement en mode vacances.</p>
                                <p>Fin du mode vacances: <strong>{{ $this->formatDate($this->vacation_mode_end_date) }}</strong></p>
                                <p>Jours restants: <strong>{{ $this->remaining_vacation_days }}</strong></p>
                            </div>
                            <button class="btn-primary" wire:click="disableVacationMode" wire:loading.attr="disabled">
                                <i class="fas fa-power-off"></i> Désactiver le mode vacances
                            </button>
                        </div>
                    @else
                        <div class="vacation-inactive">
                            <div class="vacation-info">
                                <p>Le mode vacances permet de protéger votre compte pendant votre absence.</p>
                                <p>Pendant cette période:</p>
                                <ul>
                                    <li>Vos missions seront suspendues</li>
                                    <li>Votre production de ressources sera arrêtée</li>
                                    <li>Vous ne pourrez pas être attaqué</li>
                                </ul>
                            </div>
                            <div class="vacation-form">
                                <label for="vacationDays">Durée (en jours):</label>
                                <input type="range" id="vacationDays" wire:model.live="vacationDays" min="{{ $minVacationDays }}" max="{{ $maxVacationDays }}" step="1" class="vacation-slider">
                                <div class="vacation-days-display">
                                    <span>{{ $vacationDays }} jours</span>
                                    <span class="vacation-range">(Min: {{ $minVacationDays }}, Max: {{ $maxVacationDays }})</span>
                                </div>
                                <button class="btn-primary" wire:click="enableVacationMode" wire:loading.attr="disabled">
                                    <i class="fas fa-plane"></i> Activer le mode vacances
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="profile-card">
                <h2 class="section-title"><i class="fas fa-trophy"></i> Réalisations</h2>
                <div class="achievements-grid">
                    @foreach($this->achievements as $achievement)
                        <div class="achievement-card {{ $achievement['unlocked'] ? 'unlocked' : 'locked' }} rarity-{{ $achievement['rarity'] }}">
                            <i class="achievement-icon {{ $achievement['icon'] }}"></i>
                            <div class="achievement-name">{{ $achievement['name'] }}</div>
                            <div class="achievement-description">{{ $achievement['description'] }}</div>
                            
                            @if(!$achievement['unlocked'])
                                    <div class="achievement-progress">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: {{ $achievement['progress'] }}%"></div>
                                        </div>
                                        <div class="progress-text">{{ number_format($achievement['progress'], 1) }}%</div>
                                    </div>
                            @endif
                            
                            <div class="achievement-meta">
                                <span class="achievement-type">{{ ucfirst($achievement['type']) }}</span>
                                <span class="achievement-rarity">{{ ucfirst($achievement['rarity']) }}</span>
                                @if($achievement['points_reward'] > 0)
                                    <span class="achievement-reward">+{{ $achievement['points_reward'] }} PTS</span>
                                @endif
                            </div>
                            
                            @if($achievement['unlocked'] && $achievement['unlocked_at'])
                                <div class="achievement-date">
                                    <i class="fas fa-check-circle"></i> Déverrouillé le {{ $this->formatDate($achievement['unlocked_at']) }}
                                </div>
                            @elseif(!$achievement['unlocked'])
                                <div class="achievement-requirement">
                                    <i class="fas fa-lock"></i> Non déverrouillé
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>