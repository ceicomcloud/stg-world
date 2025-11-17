<div layout="server_news_banner">
    <div class="server-event-banner" role="status" aria-live="polite">
        @if(count($newsItems) > 0)
            <div class="server-event-inner" x-data="{ autoScroll: @entangle('autoScroll') }" x-init="setInterval(() => { if (autoScroll) Livewire.dispatch('autoNextNews') }, 8000)">
                <!-- Compact controls for navigation -->
                <div class="news-controls">
                    <button wire:click="previousNews" class="news-btn news-prev" aria-label="Actualité précédente">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button wire:click="toggleAutoScroll" class="news-btn news-auto {{ $autoScroll ? 'active' : '' }}" aria-label="Lecture automatique">
                        <i class="fas fa-{{ $autoScroll ? 'pause' : 'play' }}"></i>
                    </button>
                    <button wire:click="nextNews" class="news-btn news-next" aria-label="Actualité suivante">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                <!-- News content aligned like server-event-banner -->
                @if(isset($newsItems[$currentIndex]))
                    @php $currentNews = $newsItems[$currentIndex]; @endphp
                    <div class="event-icon" aria-hidden="true">
                        <i class="fas fa-{{ $currentNews['icon'] }}"></i>
                    </div>

                    <div class="event-text">
                        <span class="news-message">{{ $currentNews['text'] }}</span>
                        <span class="event-time">{{ $currentNews['time'] }}</span>
                    </div>

                    <div class="event-badge">
                        @switch($currentNews['type'])
                            @case('news')
                                <span class="badge badge-info">Actualité</span>
                                @break
                            @case('colonization')
                                <span class="badge badge-success">Colonisation</span>
                                @break
                            @case('registration')
                                <span class="badge badge-primary">Nouveau joueur</span>
                                @break
                        @endswitch
                    </div>

                    <div class="news-indicator" aria-label="Index de l’actualité">
                        <span class="current-index">{{ $currentIndex + 1 }}</span>
                        <span class="separator">/</span>
                        <span class="total-count">{{ count($newsItems) }}</span>
                    </div>
                @endif
            </div>
        @else
            <div class="server-event-inner">
                <div class="event-icon" aria-hidden="true">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="event-text">
                    <span class="news-message">Bienvenue sur {{ config('app.name') }} ! Aucune actualité récente.</span>
                </div>
            </div>
        @endif
    </div>
</div>