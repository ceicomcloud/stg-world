<div class="game-resource-panel" layout="gameresource">
    <div class="resource-header">
        <div class="planet-selector">
            <div class="planet-dropdown" x-data="{ open: false }" x-on:click.outside="open = false">
                <button class="planet-button" x-on:click="open = !open" :class="{ 'open': open }">
                    <i class="fas fa-globe"></i>
                    <span>{{ $planet['name'] ?? 'Sélectionner une planète' }}</span>
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
                        <div class="planet-type-badge {{ $planetOption['is_main_planet'] ? 'main-planet' : 'secondary-planet' }}">
                            {{ $planetOption['is_main_planet'] ? 'Principale' : 'Secondaire' }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="resource-list">
        <!-- Primary Resources -->
        <div class="resource-category">
            <h4 class="category-title">Ressources Primaires</h4>

            @foreach($primaryResources as $resource)
            <div class="resource-item">
                <div class="resource-icon">
                    <img src="/images/resources/{{ $resource['icon'] }}" alt="{{ $resource['name'] }}" />
                </div>
                <div class="resource-info">
                    <span class="resource-name">{{ $resource['name'] }}</span>
                    <div class="resource-values">
                        <span class="current-amount">{{ number_format($resource['current_amount']) }}</span>
                        <span class="production-rate {{ $resource['production_rate'] > 0 ? 'positive' : ($resource['production_rate'] < 0 ? 'negative' : 'neutral') }}">
                            @if($resource['production_rate'] > 0)
                                <i class="fas fa-arrow-up"></i>
                            @elseif($resource['production_rate'] < 0)
                                <i class="fas fa-arrow-down"></i>
                            @else
                                <i class="fas fa-minus"></i>
                            @endif
                            {{ number_format(abs($resource['production_rate'])) }}/h
                        </span>
                    </div>
                </div>
                <div class="resource-storage">
                    <div class="storage-bar">
                        <div class="storage-fill" style="width: {{ $resource['storage_capacity'] > 0 ? ($resource['current_amount'] / $resource['storage_capacity'] * 100) : 0 }}%; background-color: {{ ($resource['storage_capacity'] > 0 ? ($resource['current_amount'] / $resource['storage_capacity'] * 100) : 0) > 90 ? '#e74c3c' : (($resource['storage_capacity'] > 0 ? ($resource['current_amount'] / $resource['storage_capacity'] * 100) : 0) > 70 ? '#f39c12' : '#27ae60') }}"></div>
                    </div>
                    <span class="storage-text">{{ number_format($resource['storage_capacity']) }}</span>
                </div>
            </div>
            @endforeach
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