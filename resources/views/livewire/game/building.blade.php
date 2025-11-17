<div page="building">
    <div class="building-container">
        <!-- En-tête -->
        <div class="building-header">
            <!-- Navigation des types -->
            <div class="type-navigation">
                <a href="{{ route('game.construction.type', ['type' => 'building']) }}" class="type-nav-link {{ $type === 'building' ? 'active' : '' }}">
                    <i class="fas fa-building"></i>
                    Bâtiments
                </a>
                <a href="{{ route('game.construction.type', ['type' => 'unit']) }}" class="type-nav-link {{ $type === 'unit' ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    Unités
                </a>
                <a href="{{ route('game.construction.type', ['type' => 'defense']) }}" class="type-nav-link {{ $type === 'defense' ? 'active' : '' }}">
                    <i class="fas fa-shield-alt"></i>
                    Défenses
                </a>
                <a href="{{ route('game.construction.type', ['type' => 'ship']) }}" class="type-nav-link {{ $type === 'ship' ? 'active' : '' }}">
                    <i class="fas fa-rocket"></i>
                    Vaisseaux
                </a>
                <a href="{{ route('game.construction.type', ['type' => 'equip']) }}" class="type-nav-link {{ $type === 'equip' ? 'active' : '' }}">
                    <i class="fas fa-users-gear"></i>
                    Gestion d’équipe
                </a>
                <a href="{{ route('game.customization') }}" class="type-nav-link">
                    <i class="fas fa-paint-brush"></i>
                    Personnalisation
                </a>
            </div>

        </div>

        <!-- Construction queue removed but keep timer for completion -->
        <div style="display: none;"></div>

        <!-- Aperçu des ressources possédées -->
        <div class="resource-summary">
            <div class="resource-summary-title">
                <i class="fas fa-coins"></i>
                Ressources possédées
            </div>
            <div class="resource-summary-list">
                @foreach($planet->resources as $pr)
                    <div class="resource-chip">
                        @if($pr->resource->icon)
                            <img src="{{ asset('images/resources/' . $pr->resource->icon) }}" alt="{{ $pr->resource->name }}" class="resource-icon" style="width:20px;height:20px;margin-right:6px;" />
                        @else
                            <i class="fas fa-coins" style="margin-right:6px;"></i>
                        @endif
                        <span class="resource-name" style="margin-right:6px;opacity:0.85;">{{ ucfirst($pr->resource->name) }}</span>
                        <span class="resource-amount" style="font-weight:600;">{{ $this->formatNumber($pr->current_amount) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        @if($type === 'equip')
            <!-- Gestion d'équipe -->
                <div class="equip-card">
                    <div class="equip-card-header">
                        <div class="equip-card-title">
                            <i class="fas fa-users-gear"></i>
                            Gestion des équipes d’attaque
                        </div>
                        <div class="equip-card-subtitle">Créez des équipes terrestres ou spatiales avec vos unités/vaisseaux</div>
                        <div class="equip-stats">
                            <span class="equip-stat"><i class="fas fa-hashtag"></i> Actuelles: {{ $equipCountTotal }}</span>
                            <span class="equip-stat"><i class="fas fa-bullseye"></i> Limite: {{ $equipMaxLimit }}</span>
                            <span class="equip-stat earth"><i class="fas fa-person-rifle"></i> Terrestres: {{ $equipCountEarth }}</span>
                            <span class="equip-stat spatial"><i class="fas fa-rocket"></i> Spatiales: {{ $equipCountSpatial }}</span>
                        </div>
                    </div>

                <div class="equip-content">
                    <!-- Formulaire de création/édition -->
                    <div class="equip-form">
                        <div class="equip-form-row">
                            <div class="equip-field">
                                <select class="equip-select" wire:model.live="equipCategory">
                                    <option value="earth">Terrestre</option>
                                    <option value="spatial">Spatial</option>
                                </select>
                            </div>
                            <div class="equip-field">
                                <input type="text" wire:model.live="equipLabel" placeholder="Nom de l’équipe">
                            </div>
                            <!-- Index supprimé: désormais attribué automatiquement -->
                        </div>
                        <div class="equip-form-row">
                            <div class="equip-field full">
                                <input type="text" wire:model.live="equipNotes" placeholder="Notes facultatives">
                            </div>
                        </div>

                        <div class="equip-payload">
                            @if($equipCategory === 'earth')
                                <div class="equip-payload-title"><i class="fas fa-person-rifle"></i> Unités terrestres</div>
                                <div class="equip-payload-grid">
                                    @foreach($planet->units as $planetUnit)
                                        <div class="payload-row">
                                            <div class="payload-label">
                                                <img src="{{ asset('images/units/' . ($planetUnit->unit->icon ?? 'unit.png')) }}" alt="{{ $planetUnit->unit->label }}">
                                                <span>{{ $planetUnit->unit->label }}</span>
                                                <small>Dispo: {{ $planetUnit->quantity }}</small>
                                            </div>
                                            <div class="payload-input">
                                                <input type="number"
                                                       min="0"
                                                       max="{{ $planetUnit->quantity }}"
                                                       wire:model.live="equipPayloadUnits.{{ $planetUnit->unit->id }}"
                                                       placeholder="0">
                                                <button type="button" class="payload-btn max" wire:click="setMaxUnit({{ $planetUnit->unit->id }})">Max</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="equip-payload-title"><i class="fas fa-rocket"></i> Vaisseaux spatiaux</div>
                                <div class="equip-payload-grid">
                                    @foreach($planet->ships as $planetShip)
                                        <div class="payload-row">
                                            <div class="payload-label">
                                                <img src="{{ asset('images/ships/' . ($planetShip->ship->icon ?? 'ship.png')) }}" alt="{{ $planetShip->ship->label }}">
                                                <span>{{ $planetShip->ship->label }}</span>
                                                <small>Dispo: {{ $planetShip->quantity }}</small>
                                            </div>
                                            <div class="payload-input">
                                                <input type="number"
                                                       min="0"
                                                       max="{{ $planetShip->quantity }}"
                                                       wire:model.live="equipPayloadShips.{{ $planetShip->ship->id }}"
                                                       placeholder="0">
                                                <button type="button" class="payload-btn max" wire:click="setMaxShip({{ $planetShip->ship->id }})">Max</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="equip-actions">
                            @if($equipEditId)
                                <button class="equip-btn primary" wire:click="saveTeam"><i class="fas fa-save"></i> Mettre à jour</button>
                                <button class="equip-btn" wire:click="startNewTeam('{{ $equipCategory }}')"><i class="fas fa-plus"></i> Nouvelle équipe</button>
                            @else
                                <button class="equip-btn primary" wire:click="saveTeam"><i class="fas fa-save"></i> Créer l’équipe</button>
                                <button class="equip-btn" wire:click="startNewTeam('{{ $equipCategory }}')"><i class="fas fa-broom"></i> Réinitialiser</button>
                            @endif
                        </div>
                    </div>

                    <!-- Liste des équipes existantes -->
                    <div class="equip-list">
                        <div class="equip-list-title"><i class="fas fa-list"></i> Équipes enregistrées</div>
                        <table class="equip-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Label</th>
                                    <th>Catégorie</th>
                                    <th>Actif</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($equipTeams as $team)
                                    <tr>
                                        <td>{{ $team['team_index'] }}</td>
                                        <td>{{ $team['label'] }}</td>
                                        <td>
                                            <span class="equip-badge {{ $team['category'] === 'earth' ? 'earth' : 'spatial' }}">
                                                {{ $team['category'] === 'earth' ? 'Terrestre' : 'Spatial' }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="equip-badge {{ $team['is_active'] ? 'active' : 'inactive' }}" wire:click="toggleTeamActive({{ $team['id'] }})">
                                                {{ $team['is_active'] ? 'Actif' : 'Inactif' }}
                                            </button>
                                        </td>
                                        <td>
                                            <button class="equip-btn" wire:click="editTeam({{ $team['id'] }})"><i class="fas fa-pen"></i> Éditer</button>
                                            <button class="equip-btn danger" wire:click="deleteTeam({{ $team['id'] }})"><i class="fas fa-trash"></i> Supprimer</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align:center; opacity: 0.7;">Aucune équipe pour l’instant.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <!-- Grille de bâtiments -->
            <div class="buildings-grid">
            @foreach($buildings as $building)
                <div class="building-card">
                    <!-- En-tête de la carte -->
                    <div class="building-card-header" wire:click="openBuildingModal({{ $building['id'] }})">
                        @if($building['icon'])
                            @php
                                $iconPath = match($type) {
                                    'building' => 'buildings',
                                    'unit' => 'units',
                                    'ship' => 'ships',
                                    'defense' => 'defenses',
                                    default => 'buildings'
                                };
                            @endphp
                            <img src="{{ asset('images/' . $iconPath . '/' . $building['icon']) }}" 
                                 alt="{{ $building['label'] }}" 
                                 class="building-image">
                        @else
                            @php
                                $iconClass = match($type) {
                                    'building' => 'fas fa-building',
                                    'unit' => 'fas fa-users',
                                    'ship' => 'fas fa-rocket',
                                    'defense' => 'fas fa-shield-alt',
                                    default => 'fas fa-building'
                                };
                            @endphp
                            <div class="building-image" style="background: linear-gradient(135deg, #1e293b, #334155); display: flex; align-items: center; justify-content: center;">
                                <i class="{{ $iconClass }}" style="font-size: 3rem; color: var(--stargate-primary);"></i>
                            </div>
                        @endif
                        
                        <div class="building-level">
                            @if($building['is_quantity_based'])
                                <i class="fas fa-cubes"></i>
                                Quantité {{ $building['quantity'] }}
                            @else
                                <i class="fas fa-layer-group"></i>
                                Niveau {{ $building['level'] }}
                            @endif
                        </div>
                
                    </div>

                    <!-- Contenu de la carte -->
                    <div class="building-card-content">
                        <h3 class="building-name">
                            <i class="fas fa-{{ $building['category'] === 'resource' ? 'coins' : ($building['category'] === 'military' ? 'shield-alt' : 'cog') }}"></i>
                            {{ $building['label'] }}
                        </h3>
                        
                        @if($building['is_quantity_based'])
                            <!-- Contrôles de quantité pour unités/défenses/vaisseaux -->
                            <div class="quantity-controls">
                                @php
                                    $currentQuantity = (int) $this->getQuantity($building['id']);
                                @endphp
                                <button type="button" wire:click="setQuantity({{ $building['id'] }}, {{ max(1, intval($currentQuantity) - 1) }})" class="quantity-btn minus" {{ $currentQuantity <= 1 ? 'disabled' : '' }}>
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" 
                                       id="quantity-{{ $building['id'] }}" 
                                       wire:model.live.debounce.150ms="quantities.{{ $building['id'] }}" 
                                       min="1" 
                                       max="999" 
                                       class="quantity-input"
                                       placeholder="Quantité">
                                <button type="button" wire:click="setQuantity({{ $building['id'] }}, {{ intval($currentQuantity) + 1 }})" class="quantity-btn plus" {{ $currentQuantity >= 999 ? 'disabled' : '' }}>
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        @endif

                        <!-- Informations rapides -->
                        <div class="building-info">
                            <div class="building-cost">
                                <div class="cost-label">Coût</div>
                                <div class="cost-values">
                                    @if(($building['is_quantity_based'] || $building['level'] < $building['max_level']) && count($building['costs']) > 0)
                                        @foreach($building['costs'] as $resourceName => $costData)
                                            @php
                                                $currentQuantity = (int) $this->getQuantity($building['id']);
                                                $cost = $costData['amount'];
                                                $totalCost = $building['is_quantity_based'] ? $cost * $currentQuantity : $cost;
                                                $hasEnough = ($planetResources[$resourceName] ?? 0) >= $totalCost;
                                            @endphp
                                            <div class="cost-item {{ $hasEnough ? '' : 'insufficient' }}">
                                                @if($costData['icon'])
                                                    <img src="{{ asset('images/resources/' . $costData['icon']) }}" 
                                                         alt="{{ $resourceName }}" 
                                                         class="resource-icon" 
                                                         style="width: 20px; height: 20px; margin-right: 5px;">
                                                @else
                                                    <i class="fas fa-coins"></i>
                                                @endif
                                                <span>{{ $this->formatNumber($totalCost) }}</span>
                                                @if($building['is_quantity_based'] && $currentQuantity > 1)
                                                    <small>({{ $this->formatNumber($cost) }} × {{ $currentQuantity }})</small>
                                                @endif
                                            </div>
                                        @endforeach
                                    @elseif(!$building['is_quantity_based'] && $building['level'] >= $building['max_level'])
                                        <div class="cost-item">
                                            <i class="fas fa-check"></i>
                                            <span>Max</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="building-time">
                                <div class="time-label">Temps</div>
                                <div class="time-value">
                                    @if($building['is_quantity_based'] || $building['level'] < $building['max_level'])
                                        <i class="fas fa-clock"></i>
                                        {{ $this->formatTime($building['build_time']) }}
                                        @if($building['is_quantity_based'] && $this->getQuantity($building['id']) > 1)
                                            <small>(par unité)</small>
                                        @endif
                                    @else
                                        <i class="fas fa-check"></i>
                                        Max
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="building-action">
                            @if($building['is_constructing'])
                            <button class="btn-upgrade" disabled>
                                <i class="fas fa-hammer"></i>
                                En cours de construction
                            </button>
                            @elseif(!$building['is_quantity_based'] && $building['level'] >= $building['max_level'])
                                <button class="btn-upgrade" disabled>
                                    <i class="fas fa-check"></i>
                                    Niveau maximum
                                </button>
                            @elseif($building['has_insufficient_resources'])
                                <button class="btn-upgrade" disabled>
                                    <i class="fas fa-coins"></i>
                                    Ressources insuffisantes
                                </button>
                            @elseif($building['has_insufficient_fields'])
                                <button class="btn-upgrade" disabled>
                                    <i class="fas fa-map"></i>
                                    Plus de place
                                </button>
                            @elseif($building['can_upgrade'])
                                <button class="btn-upgrade" 
                                        wire:click.stop="upgradeBuilding({{ $building['id'] }})"
                                        wire:loading.attr="disabled"
                                        wire:target="upgradeBuilding">
                                    @if($building['is_quantity_based'])
                                        <i class="fas fa-plus"></i>
                                        Construire
                                        @if($this->getQuantity($building['id']) > 1)
                                            ({{ $this->getQuantity($building['id']) }})
                                        @endif
                                    @else
                                        <i class="fas fa-arrow-up"></i>
                                        Améliorer
                                    @endif
                                </button>
                            @else
                                <button class="btn-upgrade" disabled>
                                    <i class="fas fa-lock"></i>
                                    Prérequis manquants
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
            </div>
        @endif
    </div>


</div>