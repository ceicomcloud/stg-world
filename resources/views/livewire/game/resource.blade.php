<div class="game-resource-panel" layout="gameresource">
    <!-- Planet Selector at Top -->
    <div class="planet-selector-top">
        <div class="planet-dropdown" x-data="{ open: false }" x-on:click.outside="open = false">
            <button class="planet-button-top" x-on:click="open = !open" :class="{ 'open': open }">
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

    <!-- Compact Player Info -->
    <div class="player-info-compact">
        <div class="player-level-info">
            <span class="player-name-compact">{{ Auth::user()->username }}</span>
            <span class="player-level-compact">Niv. {{ Auth::user()->level ?? 1 }}</span>
        </div>
        <div class="player-xp-compact">
            <div class="xp-bar-compact">
                <div class="xp-fill-compact" style="width: {{ Auth::user()->xp_percentage ?? 0 }}%;"></div>
            </div>
        </div>
    </div>

    <!-- Compact Stats -->
    <div class="stats-compact">
        <div class="stat-compact">
            <i class="fas fa-brain"></i>
            <span>{{ number_format($researchPointsProduction ?? 0) }}</span>
        </div>
        <div class="stat-compact">
            <i class="fas fa-bolt"></i>
            <span>{{ number_format($totalEnergyRemaining ?? 0) }}</span>
        </div>
        <div class="stat-compact">
            <i class="fas fa-star"></i>
            <span>{{ number_format(Auth::user()->reputation ?? 0) }}</span>
        </div>
        <div class="stat-compact">
            <i class="fas fa-medal"></i>
            <span>#{{ Auth::user()->ranking ?? '-' }}</span>
        </div>
    </div>

    <!-- Compact Resource List -->
    <div class="resource-list-compact">
        @foreach($primaryResources as $resource)
        <div class="resource-item-compact">
            <div class="resource-icon-compact">
                <img src="/images/resources/{{ $resource['icon'] }}" alt="{{ $resource['name'] }}" />
            </div>
            <div class="resource-info-compact">
                <div class="resource-name-compact">{{ $resource['name'] }}</div>
                <div class="resource-amount-compact">{{ number_format($resource['current_amount']) }}</div>
                <div class="resource-production-compact {{ $resource['production_rate'] > 0 ? 'positive' : ($resource['production_rate'] < 0 ? 'negative' : 'neutral') }}">
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
            <div class="resource-storage-compact">
                <div class="storage-bar-compact">
                    <div class="storage-fill-compact" style="width: {{ $resource['storage_capacity'] > 0 ? ($resource['current_amount'] / $resource['storage_capacity'] * 100) : 0 }}%;"></div>
                </div>
                <div class="storage-text-compact">{{ number_format($resource['storage_capacity']) }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Quick Actions -->
    <div class="resource-actions-compact">
        <a href="{{ route('game.trade') }}" wire:navigate.hover class="action-btn-compact primary">
            <i class="fas fa-exchange-alt"></i>
            Commerce
        </a>
        <a href="{{ route('game.manage-planet') }}" wire:navigate.hover class="action-btn-compact secondary">
            <i class="fas fa-cogs"></i>
            Gestion
        </a>
    </div>
</div>