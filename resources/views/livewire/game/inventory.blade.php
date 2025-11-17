<div page="inventory">
    <div class="inventory-container">
        <div class="inventory-header">
            <h1 class="inventory-title"><i class="fas fa-boxes"></i> Inventaire</h1>
            <p class="inventory-subtitle">Gérez vos articles, sélectionnez une planète, et utilisez-les avec confirmation.</p>
        </div>

        <div class="management-section">
            <div class="section-header">
                <h3><i class="fas fa-globe"></i> Planète cible</h3>
            </div>
            <div class="planet-selector">
                <label for="planetSelect">Choisir une planète</label>
                <select id="planetSelect" class="form-select" wire:change="setSelectedPlanet($event.target.value)">
                    @foreach($planets as $p)
                        <option value="{{ $p['id'] }}" {{ $selectedPlanetId == $p['id'] ? 'selected' : '' }}>
                            {{ $p['name'] }} {{ $p['is_main'] ? '• Principale' : '' }}
                        </option>
                    @endforeach
                </select>
                <p class="planet-description" style="margin-top:0.5rem; font-style: italic;">
                    Certaines utilisations (packs de ressources, unités/défenses/vaisseaux, boosts) s’appliquent à la planète sélectionnée.
                </p>
            </div>
        </div>

        <div class="management-section">
            <div class="section-header">
                <h3><i class="fas fa-archive"></i> Articles possédés</h3>
            </div>
            <div class="items-list">
                @forelse($inventories as $inv)
                    <div class="item-card">
                        <div class="item-header">
                            <div class="item-art">
                                <i class="{{ $inv['icon'] ?: 'fas fa-box' }}"></i>
                            </div>
                            <div class="item-name">{{ $inv['name'] }}</div>
                        </div>

                        <div class="item-desc">{{ $inv['description'] ?? '—' }}</div>

                        <div class="item-action">
                            @if($inv['usable'] && $inv['quantity'] > 0)
                                <button class="use-button"
                                    wire:click="useItem({{ $inv['id'] }})"
                                    wire:confirm="Confirmer l'utilisation de {{ addslashes($inv['name']) }} ?">
                                    <i class="fas fa-play"></i> Utiliser
                                </button>
                            @else
                                <button class="use-button disabled" disabled>
                                    <i class="fas fa-ban"></i> Non utilisable
                                </button>
                            @endif
                        </div>

                        <div class="item-footer">
                            <div class="item-meta">
                                <span class="badge rarity-{{ $inv['rarity'] }}">{{ ucfirst($inv['rarity']) }}</span>
                                @if($inv['duration_seconds'])
                                    <span class="badge">Durée: {{ floor($inv['duration_seconds']/3600) }}h</span>
                                @endif
                            </div>
                            <span class="badge quantity">x{{ number_format($inv['quantity']) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <span>Aucun article dans votre inventaire pour le moment.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>