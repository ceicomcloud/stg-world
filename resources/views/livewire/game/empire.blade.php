<div page="empire">
    <div class="empire-container">
        <div class="empire-header">
            <h1 class="empire-title"><i class="fas fa-globe"></i> Empire</h1>
            <p class="empire-subtitle">Vue d’ensemble de vos planètes, ressources et forces</p>
        </div>

        <div class="empire-summary">
            <div class="summary-card">
                <h3><i class="fas fa-coins"></i> Ressources totales</h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <span class="label">Métal</span>
                        <span class="value">{{ number_format($totals['resources']['metal']['amount']) }}</span>
                        <span class="sub">+ {{ number_format($totals['resources']['metal']['prod24h']) }} / 24h</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Cristal</span>
                        <span class="value">{{ number_format($totals['resources']['crystal']['amount']) }}</span>
                        <span class="sub">+ {{ number_format($totals['resources']['crystal']['prod24h']) }} / 24h</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Deutérium</span>
                        <span class="value">{{ number_format($totals['resources']['deuterium']['amount']) }}</span>
                        <span class="sub">+ {{ number_format($totals['resources']['deuterium']['prod24h']) }} / 24h</span>
                    </div>
                </div>
            </div>

            <div class="summary-card">
                <h3><i class="fas fa-bolt"></i> Énergie</h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <span class="label">Production</span>
                        <span class="value">{{ number_format($totals['energy']['production']) }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Consommation</span>
                        <span class="value">{{ number_format($totals['energy']['consumption']) }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Net</span>
                        <span class="value">{{ number_format($totals['energy']['net']) }}</span>
                    </div>
                </div>
            </div>

            <div class="summary-card">
                <h3><i class="fas fa-gem"></i> Avantage VIP</h3>
                @if(auth()->user()->vip_active && (!auth()->user()->vip_until || now()->isBefore(auth()->user()->vip_until)))
                    <div class="vip-action">
                        <label for="targetPlanet">Planète cible</label>
                        <select id="targetPlanet" class="vip-select" wire:model="targetPlanetId">
                            @foreach($availablePlanets as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <button class="vip-btn" wire:click="consolidateResources">
                            <i class="fas fa-compress"></i> Regrouper toutes les ressources ici
                        </button>
                    </div>
                    <div class="vip-note">Transfert instantané. La capacité de stockage de la planète cible est respectée.</div>
                @else
                    <div class="vip-disabled">
                        Activez votre VIP pour regrouper toutes vos ressources en un clic vers la planète de votre choix.
                    </div>
                @endif
            </div>
        </div>

        <div class="empire-planets">
            @foreach($planets as $planet)
                <div class="planet-card">
                    <div class="planet-header">
                        <div class="planet-name">{{ $planet->name }}</div>
                        <div class="planet-coords">[{{ $planet->templatePlanet->galaxy }}:{{ $planet->templatePlanet->system }}:{{ $planet->templatePlanet->position }}] • {{ ucfirst($planet->templatePlanet->type) }}</div>
                    </div>

                    <div class="planet-sections">
                        <div class="planet-section">
                            <h4><i class="fas fa-coins"></i> Ressources</h4>
                            <div class="resource-list">
                                @forelse($planet->resources as $pr)
                                    <div class="resource-item">
                                        <span class="resource-name">{{ $pr->resource->display_name }}</span>
                                        <span class="resource-amount">{{ number_format($pr->current_amount) }}</span>
                                        <span class="resource-prod">+ {{ number_format($pr->getCurrentProductionPerHour()) }} / h</span>
                                    </div>
                                @empty
                                    <div class="empty-note">Vous ne possédez rien</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="planet-section">
                            <h4><i class="fas fa-building"></i> Bâtiments</h4>
                            <div class="grid-list">
                                @forelse($planet->buildings as $b)
                                    <div class="grid-item">
                                        <span class="item-name">{{ $b->build->label ?? $b->build->name }}</span>
                                        <span class="item-meta">Niveau {{ $b->level }}</span>
                                    </div>
                                @empty
                                    <div class="empty-note">Vous ne possédez rien</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="planet-section">
                            <h4><i class="fas fa-users"></i> Unités</h4>
                            <div class="grid-list">
                                @forelse($planet->units as $u)
                                    <div class="grid-item">
                                        <span class="item-name">{{ $u->unit->label ?? $u->unit->name }}</span>
                                        <span class="item-meta">{{ number_format($u->quantity ?? 0) }}</span>
                                    </div>
                                @empty
                                    <div class="empty-note">Vous ne possédez rien</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="planet-section">
                            <h4><i class="fas fa-rocket"></i> Vaisseaux</h4>
                            <div class="grid-list">
                                @forelse($planet->ships as $s)
                                    <div class="grid-item">
                                        <span class="item-name">{{ $s->ship->label ?? $s->ship->name }}</span>
                                        <span class="item-meta">{{ number_format($s->quantity ?? 0) }}</span>
                                    </div>
                                @empty
                                    <div class="empty-note">Vous ne possédez rien</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="planet-section">
                            <h4><i class="fas fa-shield-alt"></i> Défenses</h4>
                            <div class="grid-list">
                                @forelse($planet->defenses as $d)
                                    <div class="grid-item">
                                        <span class="item-name">{{ $d->defense->label ?? $d->defense->name }}</span>
                                        <span class="item-meta">{{ number_format($d->quantity ?? 0) }}</span>
                                    </div>
                                @empty
                                    <div class="empty-note">Vous ne possédez rien</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>