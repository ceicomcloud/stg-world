<div class="auth-extra">
    <div class="auth-extra-tabs">
        <button 
            class="auth-extra-tab {{ !$showGameHistory ? 'active' : '' }}" 
            wire:click="$set('showGameHistory', false)"
        >
            <i class="fas fa-newspaper"></i> Actualités
        </button>
        <button 
            class="auth-extra-tab {{ $showGameHistory ? 'active' : '' }}" 
            wire:click="$set('showGameHistory', true)"
        >
            <i class="fas fa-book-open"></i> Histoire du jeu
        </button>
    </div>
        
    <div class="auth-extra-content">
        @if(!$showGameHistory)
            <!-- Affichage des actualités -->
            <div class="news-banner">
                @if(count($newsItems) > 0)
                    <div class="news-container" x-data="{ autoScroll: @entangle('autoScroll') }" x-init="setInterval(() => { if (autoScroll) Livewire.dispatch('autoNextNews') }, 8000)">
                        <div class="news-controls">
                            <button wire:click="previousNews" class="news-btn news-prev">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button wire:click="toggleAutoScroll" class="news-btn news-auto {{ $autoScroll ? 'active' : '' }}">
                                <i class="fas fa-{{ $autoScroll ? 'pause' : 'play' }}"></i>
                            </button>
                            <button wire:click="nextNews" class="news-btn news-next">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        
                        <div class="news-content">
                            
                            @if(isset($newsItems[$currentIndex]))
                                @php $currentNews = $newsItems[$currentIndex]; @endphp
                                <div class="news-item priority-{{ $currentNews['priority'] }}">
                                    <div class="news-icon">
                                        <i class="fas fa-{{ $currentNews['icon'] }}"></i>
                                    </div>
                                    <div class="news-text">
                                        <span class="news-message">{{ $currentNews['text'] }}</span>
                                        <span class="news-time">{{ $currentNews['time'] }}</span>
                                    </div>
                                    <div class="news-type-badge">
                                        @switch($currentNews['type'])
                                            @case('news')
                                                <span class="badge badge-info">Actualité</span>
                                                @break
                                            @case('info')
                                                <span class="badge badge-primary">Info</span>
                                                @break
                                            @default
                                                <span class="badge badge-secondary">{{ $currentNews['type'] }}</span>
                                        @endswitch
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="news-indicator">
                            <span class="current-index">{{ $currentIndex + 1 }}</span>
                            <span class="separator">/</span>
                            <span class="total-count">{{ count($newsItems) }}</span>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <!-- Affichage de l'histoire du jeu -->
            <div class="game-history">
                <h3>L'histoire de {{ config('app.name') }}</h3>
                <div class="game-history-content">
                    <p>Dans un futur lointain, l'humanité a découvert un réseau de portes stellaires permettant de voyager instantanément entre différentes planètes de la galaxie.</p>
                    
                    <p>Après des siècles d'exploration et de colonisation, différentes factions se sont formées, chacune cherchant à contrôler les ressources et les technologies des mondes découverts.</p>
                    
                    <p>En tant que commandant d'une base spatiale, votre mission est de développer votre empire, de former des alliances et de défendre vos colonies contre les menaces extraterrestres et les autres joueurs.</p>
                    
                    <p>Explorez la galaxie, recherchez de nouvelles technologies, construisez une flotte puissante et écrivez votre propre histoire dans l'univers de {{ config('app.name') }}!</p>
                </div>
            </div>
        @endif
    </div>

    <div class="server-stats">
        <div class="stat-item">
            <i class="fas fa-users"></i>
            <span class="stat-value">{{ number_format($serverStats['total_players']) }}</span>
            <span class="stat-label">Joueurs</span>
        </div>
        <div class="stat-item">
            <i class="fas fa-user-clock"></i>
            <span class="stat-value">{{ number_format($serverStats['online_players']) }}</span>
            <span class="stat-label">En ligne</span>
        </div>
        <div class="stat-item">
            <i class="fas fa-globe"></i>
            <span class="stat-value">{{ number_format($serverStats['total_planets']) }}</span>
            <span class="stat-label">Planètes</span>
        </div>
        <div class="stat-item">
            <i class="fas fa-chart-bar"></i>
            <span class="stat-value">{{ $serverStats['avg_planets_per_player'] }}</span>
            <span class="stat-label">Moy/joueur</span>
        </div>
    </div>
</div>