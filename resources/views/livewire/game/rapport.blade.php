<div page="report">
    <div class="report-container">
        @php
            $winner = data_get($report, 'combat.winner', $log->attacker_won ? 'attacker' : 'defender');
            $attackerPower = (int) data_get($report, 'attacker.total_power', 0);
            $defenderPower = (int) data_get($report, 'defender.total_power', 0);
            $unitsCatalog = (array) data_get($report, 'units_catalog', []);

            $iconPath = function($meta, $side = null) {
                // Priorité aux icônes personnalisées par camp si disponibles
                if ($side && !empty($meta[$side]['icon_url'])) {
                    return $meta[$side]['icon_url'];
                }
                $type = $meta['type'] ?? 'unit';
                $icon = $meta['icon'] ?? null;
                if (!$icon) return null;
                $folder = $type === 'ship' ? 'ships' : ($type === 'defense' ? 'defenses' : 'units');
                return asset('images/' . $folder . '/' . $icon);
            };
            // Libellé lisible pour le type d'attaque
            $attackTypeLabel = match($log->attack_type) {
                'earth' => 'Terrestre',
                'spatial' => 'Spatial',
                default => ucfirst($log->attack_type ?? 'inconnu'),
            };
        @endphp
        @if($log)
            <div class="report-header">
                <div class="header-left">
                    <h2>Rapport de Combat</h2>
                    <div class="meta-line">
                        <span class="badge {{ $winner === 'attacker' ? 'badge-success' : ($winner === 'defender' ? 'badge-danger' : 'badge-warning') }}">
                            {{ $winner === 'attacker' ? 'Victoire Attaquant' : ($winner === 'defender' ? 'Victoire Défenseur' : 'Match Nul') }}
                        </span>
                        <span class="badge badge-info">Type: {{ $attackTypeLabel }}</span>
                        <span class="badge badge-secondary">Clé: {{ $log->access_key }}</span>
                    </div>
                    <p class="date">Date: {{ $log->attacked_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="header-right power-cards">
                    <div class="power-card attacker">
                        <div class="label">Puissance Attaquant</div>
                        <div class="value">{{ number_format($attackerPower) }}</div>
                    </div>
                    <div class="power-card defender">
                        <div class="label">Puissance Défenseur</div>
                        <div class="value">{{ number_format($defenderPower) }}</div>
                    </div>
                </div>
            </div>

            <div class="report-summary">
                <div class="attacker">
                    <h3>Attaquant</h3>
                    <p>Joueur: {{ $log->attackerUser->name }}</p>
                    <p>Planète: {{ $log->attackerPlanet->name }} [{{ $log->attackerPlanet->galaxy }}:{{ $log->attackerPlanet->system }}:{{ $log->attackerPlanet->position }}]</p>
                </div>
                <div class="defender">
                    <h3>Défenseur</h3>
                    <p>Joueur: {{ $log->defenderUser->name }}</p>
                    <p>Planète: {{ $log->defenderPlanet->name }} [{{ $log->defenderPlanet->galaxy }}:{{ $log->defenderPlanet->system }}:{{ $log->defenderPlanet->position }}]</p>
                </div>
            </div>

            <div class="engaged-units">
                <h3>Unités Engagées</h3>
                <div class="units-grid">
                    <div class="units-column">
                        <h4><i class="fas fa-swords"></i> Attaquant</h4>
                        <div class="unit-list">
                            @foreach((array) data_get($report, 'attacker.initial_units', []) as $unitId => $qty)
                                @php $meta = $unitsCatalog[$unitId] ?? null; @endphp
                                <div class="unit-card">
                                    @if($meta)
                                        <img class="unit-icon" src="{{ $iconPath($meta, 'attacker') }}" alt="{{ data_get($meta, 'attacker.name', $meta['label'] ?? $unitId) }}" />
                                    @endif
                                    <div class="unit-info">
                                        <div class="unit-name">{{ data_get($meta, 'attacker.name', $meta['label'] ?? $unitId) }}</div>
                                        <div class="unit-qty">x{{ number_format($qty) }}</div>
                                        @if($meta)
                                            <div class="unit-stats">
                                                ATK {{ $meta['stats']['attack_power'] ?? 0 }} · DEF {{ $meta['stats']['defense_power'] ?? 0 }} · VIE {{ $meta['stats']['life'] ?? 0 }} · BOUCLIER {{ $meta['stats']['shield_power'] ?? 0 }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="units-column">
                        <h4><i class="fas fa-shield"></i> Défenseur</h4>
                        <div class="unit-list">
                            @foreach((array) data_get($report, 'defender.initial_units', []) as $unitId => $qty)
                                @php $meta = $unitsCatalog[$unitId] ?? null; @endphp
                                <div class="unit-card">
                                    @if($meta)
                                        <img class="unit-icon" src="{{ $iconPath($meta, 'defender') }}" alt="{{ data_get($meta, 'defender.name', $meta['label'] ?? $unitId) }}" />
                                    @endif
                                    <div class="unit-info">
                                        <div class="unit-name">{{ data_get($meta, 'defender.name', $meta['label'] ?? $unitId) }}</div>
                                        <div class="unit-qty">x{{ number_format($qty) }}</div>
                                        @if($meta)
                                            <div class="unit-stats">
                                                ATK {{ $meta['stats']['attack_power'] ?? 0 }} · DEF {{ $meta['stats']['defense_power'] ?? 0 }} · VIE {{ $meta['stats']['life'] ?? 0 }} · BOUCLIER {{ $meta['stats']['shield_power'] ?? 0 }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($log->attack_type === 'spatial')
                            <div class="unit-list sub-block">
                                <h5>Défenses</h5>
                                @foreach((array) data_get($report, 'defender.initial_defenses', []) as $unitId => $qty)
                                    @php $meta = $unitsCatalog[$unitId] ?? null; @endphp
                                    <div class="unit-card">
                                        @if($meta)
                                            <img class="unit-icon" src="{{ $iconPath($meta, 'defender') }}" alt="{{ data_get($meta, 'defender.name', $meta['label'] ?? $unitId) }}" />
                                        @endif
                                        <div class="unit-info">
                                            <div class="unit-name">{{ data_get($meta, 'defender.name', $meta['label'] ?? $unitId) }}</div>
                                            <div class="unit-qty">x{{ number_format($qty) }}</div>
                                            @if($meta)
                                                <div class="unit-stats">
                                                    DEF {{ $meta['stats']['defense_power'] ?? 0 }} · VIE {{ $meta['stats']['life'] ?? 0 }} · BOUCLIER {{ $meta['stats']['shield_power'] ?? 0 }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="report-result">
                <p><strong>Type:</strong> {{ $attackTypeLabel }}</p>
                <p><strong>Résultat:</strong> {{ $log->attacker_won ? 'Victoire attaquant' : 'Victoire défenseur' }}</p>
                <p><strong>Points gagnés:</strong> {{ number_format($log->points_gained) }}</p>
            </div>

            <div class="combat-details">
                <h3>Rounds du Combat</h3>
                @if(is_array(data_get($report, 'combat.rounds')))
                    <div class="rounds">
                        @foreach($report['combat']['rounds'] as $round)
                            <div class="round">
                                <h5>Round {{ $round['round'] ?? $loop->iteration }}</h5>
                                <div class="round-grid">
                                    <div class="round-metric">
                                        <span class="label">Puissance Attaquant</span>
                                        <span class="value">{{ number_format($round['attacker_power'] ?? 0) }}</span>
                                    </div>
                                    <div class="round-metric">
                                        <span class="label">Puissance Défenseur</span>
                                        <span class="value">{{ number_format($round['defender_power'] ?? 0) }}</span>
                                    </div>
                                    <div class="round-metric">
                                        <span class="label">Bouclier Attaquant</span>
                                        <span class="value">{{ number_format($round['attacker_shield'] ?? 0) }}</span>
                                    </div>
                                    <div class="round-metric">
                                        <span class="label">Bouclier Défenseur</span>
                                        <span class="value">{{ number_format($round['defender_shield'] ?? 0) }}</span>
                                    </div>
                                    <div class="round-metric">
                                        <span class="label">Dégâts Attaquant</span>
                                        <span class="value">{{ number_format($round['damage_to_defender'] ?? $round['attacker_damage'] ?? 0) }}</span>
                                    </div>
                                    <div class="round-metric">
                                        <span class="label">Dégâts Défenseur</span>
                                        <span class="value">{{ number_format($round['damage_to_attacker'] ?? $round['defender_damage'] ?? 0) }}</span>
                                    </div>
                                </div>

                                @if(!empty($round['attacker_losses']))
                                    <div class="losses">
                                        <strong>Pertes Attaquant:</strong>
                                        @foreach($round['attacker_losses'] as $unitId => $lost)
                                            @php $meta = $unitsCatalog[$unitId] ?? null; @endphp
                                            <span class="loss-item">{{ data_get($meta, 'attacker.name', $meta['label'] ?? $unitId) }} - {{ number_format($lost) }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if(!empty($round['defender_losses']))
                                    <div class="losses">
                                        <strong>Pertes Défenseur:</strong>
                                        @foreach($round['defender_losses'] as $unitId => $lost)
                                            @php $meta = $unitsCatalog[$unitId] ?? null; @endphp
                                            <span class="loss-item">{{ data_get($meta, 'defender.name', $meta['label'] ?? $unitId) }} - {{ number_format($lost) }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="survivors">
                <h3>Survivants</h3>
                <div class="units-grid">
                    <div class="units-column">
                        <h4>Attaquant</h4>
                        <div class="unit-list">
                            @foreach((array) data_get($report, 'combat.surviving_attacker_units', []) as $unitId => $qty)
                                @php $meta = $unitsCatalog[$unitId] ?? null; @endphp
                                <div class="unit-card">
                                    @if($meta)
                                        <img class="unit-icon" src="{{ $iconPath($meta, 'attacker') }}" alt="{{ data_get($meta, 'attacker.name', $meta['label'] ?? $unitId) }}" />
                                    @endif
                                    <div class="unit-info">
                                        <div class="unit-name">{{ data_get($meta, 'attacker.name', $meta['label'] ?? $unitId) }}</div>
                                        <div class="unit-qty">x{{ number_format($qty) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="units-column">
                        <h4>Défenseur</h4>
                        <div class="unit-list">
                            @foreach((array) data_get($report, 'combat.surviving_defender_units', []) as $unitId => $qty)
                                @php $meta = $unitsCatalog[$unitId] ?? null; @endphp
                                <div class="unit-card">
                                    @if($meta)
                                        <img class="unit-icon" src="{{ $iconPath($meta, 'defender') }}" alt="{{ data_get($meta, 'defender.name', $meta['label'] ?? $unitId) }}" />
                                    @endif
                                    <div class="unit-info">
                                        <div class="unit-name">{{ data_get($meta, 'defender.name', $meta['label'] ?? $unitId) }}</div>
                                        <div class="unit-qty">x{{ number_format($qty) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            @if(!empty($report['pillaged_resources']))
                <div class="pillaged">
                    <h3>Ressources Pillées</h3>
                    <div class="pillaged-list">
                        @php
                            // Charger les ressources templates pour afficher leur nom lisible
                            $templateResources = \App\Models\Template\TemplateResource::all()->keyBy('id');
                        @endphp
                        @foreach($report['pillaged_resources'] as $resourceId => $amount)
                            <div class="pillaged-item">
                                @php
                                    $resourceName = isset($templateResources[$resourceId]) ? $templateResources[$resourceId]->name : "Ressource #{$resourceId}";
                                    $resourceDisplayName = isset($templateResources[$resourceId])
                                        ? ($templateResources[$resourceId]->display_name ?? ucfirst($resourceName))
                                        : "Ressource #{$resourceId}";
                                @endphp
                                <span class="pillaged-name">{{ $resourceDisplayName }}</span>
                                <span class="pillaged-amount">{{ number_format($amount) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <div class="report-not-found">
                <p>Rapport introuvable pour la clé fournie.</p>
            </div>
        @endif
    </div>
</div>