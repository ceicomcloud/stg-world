<div class="game-resource-panel" layout="gameresource">
    <!-- En-tête compact avec infos joueur -->
    <div class="player-info-header">
        <div class="player-avatar">
            <i class="fas fa-user-astronaut"></i>
        </div>
        <div class="player-details">
            <div class="player-name">{{ Auth::user()->username }}</div>
            <div class="player-level">Niveau {{ Auth::user()->level ?? 1 }}</div>
            <div class="player-xp">
                <div class="xp-bar">
                    <div class="xp-fill" style="width: {{ Auth::user()->xp_percentage ?? 0 }}%;"></div>
                </div>
                <span class="xp-text">{{ Auth::user()->xp ?? 0 }} XP</span>
            </div>
        </div>
    </div>

    <!-- Player Stats Grid - Compact -->
    <div class="player-stats-grid">
        <div class="stat-item">
            <i class="fas fa-brain"></i>
            <span class="stat-label">Recherche</span>
            <span class="stat-value">{{ number_format($researchPointsProduction ?? 0) }}/h</span>
        </div>
        <div class="stat-item">
            <i class="fas fa-bolt"></i>
            <span class="stat-label">Énergie</span>
            <span class="stat-value">{{ number_format($totalEnergyRemaining ?? 0) }}</span>
        </div>
        <div class="stat-item">
            <i class="fas fa-star"></i>
            <span class="stat-label">Réputation</span>
            <span class="stat-value">{{ number_format(Auth::user()->reputation ?? 0) }}</span>
        </div>
        <div class="stat-item">
            <i class="fas fa-medal"></i>
            <span class="stat-label">Classement</span>
            <span class="stat-value">#{{ Auth::user()->ranking ?? '-' }}</span>
        </div>
    </div>

    <!-- Sélecteur de planète compact -->
    <div class="planet-selector-compact">
        <div class="planet-dropdown" x-data="{ open: false }" x-on:click.outside="open = false">
            <button class="planet-button-compact" x-on:click="open = !open" :class="{ 'open': open }">
                <i class="fas fa-globe"></i>
                <span>{{ $planet['name'] ?? 'Sélectionner' }}</span>
                <i class="fas fa-chevron-down dropdown-icon" :class="{ 'rotated': open }"></i>
            </button>

            <div class="planet-options" x-show="open" x-transition>
                @foreach($availablePlanets as $planetOption)
                <div class="planet-option {{ isset($planet['id']) && $planetOption['id'] === $planet['id'] ? 'active' : '' }}" wire:click="selectPlanet({{ json_encode($planetOption) }})" x-on:click="open = false">
                    <div class="planet-option-info">
                        <i class="fas fa-globe"></i>
                        <div class="planet-details">
                            <span class="planet-name">{{ $planetOption['name'] }}</span>
                            <span class="planet-coords">{{ $planetOption['description'] }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="resource-list">
        <!-- Primary Resources -->
        <div class="resource-category">
            <h4 class="category-title">Ressources Primaires</h4>
            
            <div class="resource-grid">
                @foreach($primaryResources as $resource)
                <div class="resource-card">
                    <div class="card-icon">
                        <img src="/images/resources/{{ $resource['icon'] }}" alt="{{ $resource['name'] }}" />
                    </div>
                    <div class="card-info">
                        <div class="card-name">{{ $resource['name'] }}</div>
                        <div class="card-amount">{{ number_format($resource['current_amount']) }}</div>
                        <div class="card-production {{ $resource['production_rate'] > 0 ? 'positive' : ($resource['production_rate'] < 0 ? 'negative' : 'neutral') }}">
                            @if($resource['production_rate'] > 0)
                                <i class="fas fa-arrow-up"></i>
                            @elseif($resource['production_rate'] < 0)
                                <i class="fas fa-arrow-down"></i>
                            @else
                                <i class="fas fa-minus"></i>
                            @endif
                            {{ number_format(abs($resource['production_rate'])) }}/h
                        </div>
                    </div>
                    <div class="card-storage">
                        <div class="storage-text">{{ number_format($resource['storage_capacity']) }}</div>
                        <div class="storage-bar">
                            <div class="storage-fill" style="width: {{ $resource['storage_capacity'] > 0 ? ($resource['current_amount'] / $resource['storage_capacity'] * 100) : 0 }}%;"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Resource Summary -->
    <div class="resource-summary">
        <div class="summary-item">
            <i class="fas fa-flask"></i>
            <span class="summary-label">Points de Recherche</span>
            <span class="summary-value research">+{{ number_format($researchPointsProduction) }}/h</span>
        </div>

        <div class="summary-item">
            <i class="fas fa-bolt"></i>
            <span class="summary-label">Consommation Énergie</span>
            <span class="summary-value negative">-{{ number_format($totalEnergyConsumption) }}/h</span>
        </div>

        <div class="summary-item">
            <i class="fas fa-battery-half"></i>
            <span class="summary-label">Énergie Restante</span>
            <span class="summary-value neutral">{{ number_format($totalEnergyRemaining) }}/h</span>
        </div>

        <div class="summary-item">
            <i class="fas fa-coins"></i>
            <span class="summary-label">Or</span>
            <span class="summary-value neutral">{{ number_format(Auth::user()->gold_balance ?? 0) }}</span>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="resource-actions">
        <a href="{{ route('game.trade') }}" wire:navigate.hover class="action-btn primary">
            <i class="fas fa-exchange-alt"></i>
            Commerce
        </a>

        <a href="{{ route('game.manage-planet') }}" wire:navigate.hover class="action-btn secondary">
            <i class="fas fa-cogs"></i>
            Gestion
        </a>
    </div>
    <div class="resource-actions">
        <a href="{{ route('game.manage-players') }}" wire:navigate.hover class="action-btn secondary">
            <i class="fas fa-user-cog"></i>
            Gestion Joueurs
        </a>
        <a href="{{ route('game.inventory') }}" wire:navigate.hover class="action-btn secondary">
            <i class="fas fa-boxes"></i>
            Inventaire
        </a>
    </div>
    <div class="resource-actions">
        <a href="{{ route('dashboard.index') }}" wire:navigate.hover class="action-btn secondary">
            <i class="fas fa-home"></i>
            Comptes
        </a>
    </div>
</div>