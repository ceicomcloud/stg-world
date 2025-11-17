<div page="galaxy">
    <div class="galaxy-container" 
     wire:keydown.arrow-left="previousSystem" 
     wire:keydown.arrow-right="nextSystem" 
     tabindex="0" x-data="{ mode: @entangle('viewMode'), sys: @entangle('targetSystem'), legendOpen: false }">
        <!-- Header affiné: titre au-dessus, navigation compacte, stats en ligne -->
        <div class="galaxy-header">
            <div class="system-title-header">Galaxie {{ $currentGalaxy }} — Système {{ $currentSystem }}/{{ $maxSystems }}</div>
            <div class="galaxy-header-row">
                <div class="view-toggle">
                    <button type="button" class="toggle-btn" :class="{ 'active': mode === '2d' }" @click="mode='2d'" wire:click="setViewMode('2d')">2D</button>
                    <button type="button" class="toggle-btn" :class="{ 'active': mode === '3d' }" @click="mode='3d'" wire:click="setViewMode('3d')">3D</button>
                </div>
                <div class="system-navigation compact" x-on:keydown.enter="$wire.goToSystem(sys)" x-on:keydown.arrow-left="$wire.previousSystem()" x-on:keydown.arrow-right="$wire.nextSystem()">
                    <button class="nav-btn" wire:click="previousSystem" title="Système précédent"><i class="fas fa-chevron-left"></i></button>
                    <input type="number" class="system-input" wire:model.live="targetSystem" x-model.number="sys" x-on:input.debounce.300ms="$wire.set('targetSystem', sys)" min="1" max="{{ $maxSystems }}" placeholder="Système" />
                    <button class="go-btn" wire:click="goToSystem" title="Aller"><i class="fas fa-arrow-right"></i></button>
                    <button class="nav-btn" wire:click="nextSystem" title="Système suivant"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>

        <!-- Alerte des événements galactiques actifs -->
        @if(!empty($activeEvents))
        <div class="galactic-events-banner">
            <div class="events-list">
                @foreach($activeEvents as $ev)
                    <div class="event-item severity-{{ $ev['severity'] }}">
                        <span class="event-icon">{{ $ev['icon'] ?? '✦' }}</span>
                        <span class="event-title">{{ $ev['title'] }}</span>
                        @if(!empty($ev['position']))
                            <span class="event-scope">[{{ $currentGalaxy }}:{{ $currentSystem }}:{{ $ev['position'] }}]</span>
                        @else
                            <span class="event-scope">Système entier</span>
                        @endif
                        @if(!empty($ev['description']))
                            <span class="event-desc">— {{ $ev['description'] }}</span>
                        @endif
                        @if(!empty($ev['end_at']))
                            <span class="event-time">(se termine {{ \Carbon\Carbon::parse($ev['end_at'])->diffForHumans() }})</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($viewMode === '3d')
        <!-- Vue 3D de la galaxie -->
        <div class="galaxy-view" x-transition.opacity.duration.250ms>
            <!-- Soleil au centre -->
            <div class="sun-container">
                <div class="sun">
                    <div class="sun-core"></div>
                    <div class="sun-corona"></div>
                    <div class="sun-flares"></div>
                </div>
            </div>

            <!-- Planètes en orbite -->
            <div class="planets-container" x-transition.opacity.duration.200ms>
                @foreach ($systemPlanets as $position => $planetData)
                    <div class="planet-orbit orbit-{{ $position }}" style="--orbit-angle: {{ $planetPositions3D[$position]['angle'] ?? 0 }}deg;"></div>
                    <div class="planet-wrapper" style="top: {{ $planetPositions3D[$position]['y'] ?? 50 }}%; left: {{ $planetPositions3D[$position]['x'] ?? 50 }}%;">
                        @if($planetData && $planetData['planet'])
                            <!-- Planète occupée -->
                            <div class="planet occupied 
                                {{ $planetData['is_own'] ? 'own-planet' : ($planetData['is_ally'] ? 'ally-planet' : 'enemy-planet') }}
                                {{ $planetData['is_main'] ? 'main-planet' : '' }}
                                {{ $planetData['is_protected'] ? 'protected-planet' : '' }}
                                {{ $planetData['is_vacation_mode'] ? 'vacation-mode-planet' : '' }}"
                                wire:click="openPlanetModal({{ $planetData['planet']->id }})"
                                data-planet-id="{{ $planetData['planet']->id }}">
                                
                                <div class="planet-surface planet-type-{{ $planetData['template']->type ?? 'planet' }}"
                                    style="background-image: url('{{ $planetData['planet'] && $planetData['planet']->image ? asset('images/planets/' . $planetData['planet']->image) : asset('images/planets/planet-' . (($position % 10) + 1) . '.png') }}')">
                                    <div class="planet-atmosphere"></div>
                                    @if($planetData['is_protected'])
                                        <div class="shield-protection-indicator">
                                            <i class="fas fa-shield-alt"></i>
                                        </div>
                                    @endif
                                    @if($planetData['is_vacation_mode'])
                                        <div class="vacation-mode-indicator">
                                            <i class="fas fa-umbrella-beach"></i>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="planet-info">
                                    <div class="planet-name">{{ $planetData['planet']->name }}</div>
                                    <div class="planet-coordinates">{{ $planetData['coordinates'] }}</div>
                                    <div class="planet-owner {{ $planetData['is_own'] ? 'own' : 'enemy' }} {{ $planetData['is_vacation_mode'] ? 'vacation-mode' : '' }}">
                                        {{ $planetData['user']->name ?? 'Inconnu' }}
                                        @if($planetData['is_vacation_mode'])
                                            <span class="vacation-badge"><i class="fas fa-umbrella-beach"></i></span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @elseif($planetData && $planetData['template'] && $planetData['is_bot'])
                            <!-- Planète PNJ (Bot) -->
                            <div class="planet bot-planet" wire:click="openPlanetModal(null, {{ $currentGalaxy }}, {{ $currentSystem }}, {{ $position }})">
                                <div class="bot-slot">
                                    <i class="fas fa-robot"></i>
                                </div>
                                
                                <div class="planet-info">
                                    <div class="planet-coordinates">{{ $planetData['coordinates'] }}</div>
                                    <div class="planet-status">Planète PNJ</div>
                                    <div class="planet-type">{{ ucfirst($planetData['template']->type) }}</div>
                                </div>
                            </div>
                        @elseif($planetData && $planetData['template'])
                            <!-- Position avec template mais libre -->
                            <div class="planet empty" wire:click="openPlanetModal(null, {{ $currentGalaxy }}, {{ $currentSystem }}, {{ $position }})">
                                <div class="empty-slot">
                                    <i class="fas fa-plus"></i>
                                </div>
                                
                                <div class="planet-info">
                                    <div class="planet-coordinates">{{ $planetData['coordinates'] }}</div>
                                    <div class="planet-status">Position libre</div>
                                    <div class="planet-type">{{ ucfirst($planetData['template']->type) }}</div>
                                </div>
                            </div>
                        @else
                            <!-- Position sans template (non colonisable) -->
                            <div class="planet empty disabled">
                                <div class="empty-slot disabled">
                                    <i class="fas fa-times"></i>
                                </div>
                                
                                <div class="planet-info">
                                    <div class="planet-coordinates">[{{ $currentGalaxy }}:{{ $currentSystem }}:{{ $position }}]</div>
                                    <div class="planet-status">Non disponible</div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($viewMode === '2d')
        <!-- Vue 2D de la galaxie -->
        <div class="galaxy-view-2d" x-transition.opacity.duration.250ms>
            <div class="system-grid">
                @foreach ($systemPlanets as $position => $planetData)
                    <div @class([
                            'grid-tile',
                            'tile-occupied' => ($planetData && $planetData['planet']),
                            'tile-own' => ($planetData && $planetData['planet'] && $planetData['is_own']),
                            'tile-ally' => ($planetData && $planetData['planet'] && !$planetData['is_own'] && $planetData['is_ally']),
                            'tile-enemy' => ($planetData && $planetData['planet'] && !$planetData['is_own'] && !$planetData['is_ally']),
                            'tile-main' => ($planetData && $planetData['planet'] && $planetData['is_main']),
                            'tile-bot' => ($planetData && $planetData['template'] && $planetData['is_bot']),
                            'tile-empty' => ($planetData && $planetData['template'] && !$planetData['is_bot'] && !$planetData['planet']),
                            'tile-disabled' => (!$planetData || (!$planetData['template'] && !$planetData['planet'])),
                        ])
                        @if($planetData && $planetData['planet'])
                            wire:click="openPlanetModal({{ $planetData['planet']->id }})"
                        @elseif($planetData && $planetData['template'])
                            wire:click="openPlanetModal(null, {{ $currentGalaxy }}, {{ $currentSystem }}, {{ $position }})"
                        @endif
                    >
                        <div class="tile-header">
                            <span class="tile-position">#{{ $position }}</span>
                            <span class="tile-coords">{{ $planetData['coordinates'] ?? "[{$currentGalaxy}:{$currentSystem}:{$position}]" }}</span>
                        </div>
                        <div class="tile-body">
                            @if($planetData && $planetData['planet'])
                                <div class="tile-icon">
                                    <i class="fas fa-globe"></i>
                                    @if($planetData['is_main'])
                                        <i class="fas fa-crown badge-main" title="Planète principale"></i>
                                    @endif
                                    @if($planetData['is_protected'])
                                        <i class="fas fa-shield-alt badge-protected" title="Protection active"></i>
                                    @endif
                                    @if($planetData['is_vacation_mode'])
                                        <i class="fas fa-umbrella-beach badge-vacation" title="Mode vacances"></i>
                                    @endif
                                </div>
                                <div class="tile-info">
                                    <div class="tile-name">{{ $planetData['planet']->name }}</div>
                                    <div class="tile-owner">{{ $planetData['user']->name ?? 'Inconnu' }}</div>
                                </div>
                            @elseif($planetData && $planetData['template'] && $planetData['is_bot'])
                                <div class="tile-icon"><i class="fas fa-robot"></i></div>
                                <div class="tile-info">
                                    <div class="tile-name">PNJ</div>
                                    <div class="tile-type">{{ ucfirst($planetData['template']->type) }}</div>
                                </div>
                            @elseif($planetData && $planetData['template'])
                                <div class="tile-icon"><i class="fas fa-plus"></i></div>
                                <div class="tile-info">
                                    <div class="tile-name">Libre</div>
                                    <div class="tile-type">{{ ucfirst($planetData['template']->type) }}</div>
                                </div>
                            @else
                                <div class="tile-icon"><i class="fas fa-times"></i></div>
                                <div class="tile-info">
                                    <div class="tile-name">Indisponible</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Footer affiné: légende en ligne pliable sans titre -->
        <div class="galaxy-footer">
            <button class="toggle-btn" @click="legendOpen=!legendOpen" :class="{ 'active': legendOpen }">
                <i class="fas" :class="legendOpen ? 'fa-eye-slash' : 'fa-eye'"></i>
                <span x-text="legendOpen ? 'Masquer' : 'Afficher'"></span>
            </button>
            <div class="galaxy-legend-inline" x-show="legendOpen" x-transition.opacity.duration.200ms>
                <div class="legend-item"><div class="legend-color own-planet"></div><span>Vos planètes</span></div>
                <div class="legend-item"><div class="legend-color enemy-planet"></div><span>Planètes ennemies</span></div>
                <div class="legend-item"><div class="legend-color ally-planet"></div><span>Planètes alliées</span></div>
                <div class="legend-item"><div class="legend-color bot-planet"></div><span>Planètes PNJ</span></div>
                <div class="legend-item"><div class="legend-color empty"></div><span>Positions libres</span></div>
                <div class="legend-item"><i class="fas fa-crown legend-icon"></i><span>Planète principale</span></div>
                <div class="legend-item"><i class="fas fa-shield-alt legend-icon"></i><span>Protection planétaire active</span></div>
                <div class="legend-item"><i class="fas fa-umbrella-beach legend-icon"></i><span>Joueur en mode vacances</span></div>
            </div>
        </div>
    </div>
</div>
