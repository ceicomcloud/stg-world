<div page="managePlanet">
    <div class="managePlanet-container">
        <!-- En-tête avec sélecteur de planète -->
        <div class="manage-planet-header">
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

        @if($planet)
            <!-- Informations de la planète -->
            <div class="planet-overview">
                <div class="planet-info-card">
                    <div class="planet-visual">
                        @if($isEditingImage)
                            <!-- Mode édition d'image -->
                            <div class="image-gallery">
                                <h3>Choisir une image de planète</h3>
                                <div class="gallery-grid">
                                    @foreach($availableImages as $image)
                                        <div class="gallery-item {{ $selectedImage === $image ? 'selected' : '' }}" 
                                            wire:click="selectPlanetImage('{{ $image }}')">
                                            <img src="{{ asset('images/planets/' . $image) }}" alt="{{ $image }}">
                                            @if($selectedImage === $image)
                                                <div class="selected-overlay">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <div class="gallery-actions">
                                    <button wire:click="savePlanetImage" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Sauvegarder
                                    </button>
                                    <button wire:click="cancelEditingImage" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Annuler
                                    </button>
                                </div>
                            </div>
                        @else
                            <!-- Affichage normal avec bouton d'édition -->
                            <div class="planet-image-container">
                                <img src="{{ asset('images/planets/' . ($planetInfo['image'] ?? 'planet-1.png')) }}" 
                                    alt="{{ $planetInfo['name'] }}" class="planet-image">
                                <button wire:click="startEditingImage" class="edit-image-btn" title="Changer l'image">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                    <div class="planet-details">
                        @if($isEditing)
                            <!-- Mode édition -->
                            <div class="edit-form">
                                <div class="form-group">
                                    <label for="editName">Nom de la planète</label>
                                    <input type="text" id="editName" wire:model="editName" class="form-input" placeholder="Nom de la planète">
                                    @error('editName') <span class="error-message">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="editDescription">Description</label>
                                    <textarea id="editDescription" wire:model="editDescription" class="form-textarea" placeholder="Description de la planète" rows="3"></textarea>
                                    @error('editDescription') <span class="error-message">{{ $message }}</span> @enderror
                                </div>
                                <div class="edit-actions">
                                    <button wire:click="savePlanetInfo" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Sauvegarder
                                    </button>
                                    <button wire:click="cancelEditing" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Annuler
                                    </button>
                                </div>
                            </div>
                        @else
                            <!-- Mode affichage -->
                            <div class="planet-info-display">
                                <div class="planet-name-section">
                                    <h2 class="planet-name">{{ $planetInfo['name'] }}</h2>
                                    <button wire:click="startEditing" class="edit-btn" title="Modifier les informations">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                                @if($planetInfo['description'])
                                    <div class="planet-description">
                                        <i class="fas fa-info-circle"></i>
                                        {{ $planetInfo['description'] }}
                                    </div>
                                @endif
                                <div class="planet-coordinates">
                                    <i class="fas fa-map-marker-alt"></i>
                                    [{{ $planetInfo['coordinates']['galaxy'] }}:{{ $planetInfo['coordinates']['system'] }}:{{ $planetInfo['coordinates']['position'] }}]
                                </div>
                                <div class="planet-fields">
                                    <i class="fas fa-th-large"></i>
                                    {{ $planetInfo['used_fields'] }}/{{ $planetInfo['total_fields'] }} cases utilisées
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="planet-bonuses">
                        <h4>Bonus de production</h4>
                        <div class="bonus-grid">
                            <div class="bonus-item">
                                <img src="/images/resources/metal.png" alt="Métal" />
                                <span>{{ number_format(($planetInfo['bonuses']['metal'] - 1) * 100, 0) }}%</span>
                            </div>
                            <div class="bonus-item">
                                <img src="/images/resources/crystal.png" alt="Cristal" />
                                <span>{{ number_format(($planetInfo['bonuses']['crystal'] - 1) * 100, 0) }}%</span>
                            </div>
                            <div class="bonus-item">
                                <img src="/images/resources/deuterium.png" alt="Deutérium" />
                                <span>{{ number_format(($planetInfo['bonuses']['deuterium'] - 1) * 100, 0) }}%</span>
                            </div>
                            <div class="bonus-item">
                                <i class="fas fa-bolt"></i>
                                <span>{{ number_format(($planetInfo['bonuses']['energy'] - 1) * 100, 0) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Protection Planétaire -->
            <div class="shield-protection-section">
                <div class="shield-protection-card">
                    <div class="shield-protection-header">
                        <div class="shield-protection-title">
                            <i class="fas fa-shield-alt"></i>
                            Protection Planétaire
                        </div>
                        @if($planetInfo['shield_protection_active'] ?? false)
                            <div class="shield-protection-status active">
                                <i class="fas fa-check-circle"></i>
                                Actif
                            </div>
                        @elseif(!($planetInfo['can_activate_shield'] ?? true))
                            <div class="shield-protection-status cooldown">
                                <i class="fas fa-clock"></i>
                                En attente
                            </div>
                        @else
                            <div class="shield-protection-status inactive">
                                <i class="fas fa-times-circle"></i>
                                Inactif
                            </div>
                        @endif
                    </div>
                    
                    <div class="shield-protection-content">
                        <div class="shield-protection-info">
                            <div class="shield-info-item">
                                <i class="fas fa-info-circle"></i>
                                <span class="shield-info-text">La protection planétaire empêche toute attaque contre votre planète pendant 7 jours.</span>
                            </div>
                            <div class="shield-info-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span class="shield-info-text">Vous ne pouvez activer la protection qu'une fois tous les 30 jours.</span>
                            </div>
                            <div class="shield-info-item">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span class="shield-info-text">Pendant la protection, vous ne pouvez pas attaquer d'autres planètes.</span>
                            </div>
                        </div>
                        
                        @if($planetInfo['shield_protection_active'] ?? false)
                            <div class="shield-protection-progress">
                                <div class="progress-label">
                                    <span>Temps restant: {{ $planetInfo['remaining_shield_time'] ?? '0' }} heures</span>
                                    <span>{{ number_format($planetInfo['shield_protection_progress'] ?? 0, 1) }}%</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar" style="width: {{ $planetInfo['shield_protection_progress'] ?? 0 }}%"></div>
                                </div>
                            </div>
                        @endif
                        
                        <div class="shield-protection-actions">
                            @if(!($planetInfo['shield_protection_active'] ?? false))
                                @if($planetInfo['can_activate_shield'] ?? true)
                                    <button wire:click="activateShieldProtection" class="activate-shield-btn" onclick="if(!confirm('Confirmer l\'activation de la protection planétaire ?')) { event.stopImmediatePropagation(); event.preventDefault(); }">
                                        <i class="fas fa-shield-alt"></i>
                                        Activer la protection
                                    </button>
                                @endif
                            @endif
                        </div>

                        @if(!($planetInfo['shield_protection_active'] ?? false))
                            <!-- Prérequis d'activation -->
                            <div class="shield-requirements">
                                <div class="requirements-title">
                                    <i class="fas fa-list-check"></i> Éléments nécessaires
                                </div>
                                <ul class="requirements-list">
                                    <li>
                                        <i class="fas fa-flask"></i>
                                        Technologie Champ d'Inversion: niveau requis {{ $planetInfo['shield_required_tech_level'] }}, actuel {{ $planetInfo['shield_current_tech_level'] }}
                                        <span class="status {{ ($planetInfo['shield_tech_met'] ?? false) ? 'ok' : 'ko' }}">{{ ($planetInfo['shield_tech_met'] ?? false) ? 'OK' : 'Manquant' }}</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-battery-full"></i>
                                        Générateurs de Bouclier: requis {{ number_format($planetInfo['shield_required_generators'] ?? 0) }}, actuels {{ number_format($planetInfo['shield_current_generators'] ?? 0) }}
                                        <span class="status {{ ($planetInfo['shield_generators_met'] ?? false) ? 'ok' : 'ko' }}">{{ ($planetInfo['shield_generators_met'] ?? false) ? 'OK' : 'Manquant' }}</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-gas-pump"></i>
                                        Deuterium: requis {{ number_format($planetInfo['shield_required_deuterium'] ?? 0) }}, actuel {{ number_format($planetInfo['shield_current_deuterium'] ?? 0) }}
                                        <span class="status {{ ($planetInfo['shield_deuterium_met'] ?? false) ? 'ok' : 'ko' }}">{{ ($planetInfo['shield_deuterium_met'] ?? false) ? 'OK' : 'Manquant' }}</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-hourglass-half"></i>
                                        Cooldown: {{ $planetInfo['shield_cooldown_text'] ?? 'Disponible maintenant' }}
                                        <span class="status {{ ($planetInfo['can_activate_shield'] ?? false) ? 'ok' : 'ko' }}">{{ ($planetInfo['can_activate_shield'] ?? false) ? 'OK' : 'En attente' }}</span>
                                    </li>
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Porte des étoiles -->
            <div class="stargate-section">
                <div class="stargate-card">
                    <div class="stargate-header">
                        <div class="stargate-title">
                            <i class="fas fa-ring"></i>
                            Porte des étoiles
                        </div>
                        @if($planetInfo['stargate_active'] ?? false)
                            <div class="stargate-status active">
                                <i class="fas fa-check-circle"></i>
                                Active
                            </div>
                        @elseif(!($planetInfo['stargate_can_toggle'] ?? true))
                            <div class="stargate-status cooldown">
                                <i class="fas fa-clock"></i>
                                En attente
                            </div>
                        @else
                            <div class="stargate-status inactive">
                                <i class="fas fa-times-circle"></i>
                                Inactive
                            </div>
                        @endif
                    </div>

                    <div class="stargate-content">
                        <div class="stargate-info">
                            <div class="stargate-info-item">
                                <i class="fas fa-info-circle"></i>
                                <span class="stargate-info-text">Lorsque la Porte des étoiles est active, les attaques terrestres sur cette planète sont interdites.</span>
                            </div>
                            <div class="stargate-info-item">
                                <i class="fas fa-coins"></i>
                                <span class="stargate-info-text">Coût activation: {{ number_format($planetInfo['stargate_activation_cost'] ?? 0) }} Deuterium | Coût désactivation: {{ number_format($planetInfo['stargate_deactivation_cost'] ?? 0) }} Deuterium</span>
                            </div>
                            <div class="stargate-info-item">
                                <i class="fas fa-hourglass-half"></i>
                                <span class="stargate-info-text">Cooldown de 24h entre chaque activation/désactivation.</span>
                            </div>
                        </div>

                        <div class="stargate-actions">
                            @if($planetInfo['stargate_active'] ?? false)
                                @if($planetInfo['stargate_can_toggle'] ?? true)
                                    <button wire:click="deactivateStargate" class="deactivate-stargate-btn">
                                        <i class="fas fa-toggle-off"></i>
                                        Désactiver ({{ number_format($planetInfo['stargate_deactivation_cost'] ?? 0) }})
                                    </button>
                                @else
                                    <div class="stargate-cooldown-info">
                                        <i class="fas fa-clock"></i>
                                        Disponible dans {{ $planetInfo['stargate_cooldown_text'] ?? 'Disponible bientôt' }}
                                    </div>
                                @endif
                            @else
                                @if($planetInfo['stargate_can_toggle'] ?? true)
                                    <button wire:click="activateStargate" class="activate-stargate-btn" onclick="if(!confirm('Confirmer l\'activation de la Porte des étoiles ?')) { event.stopImmediatePropagation(); event.preventDefault(); }">
                                        <i class="fas fa-toggle-on"></i>
                                        Activer ({{ number_format($planetInfo['stargate_activation_cost'] ?? 0) }})
                                    </button>
                                @else
                                    <div class="stargate-cooldown-info">
                                        <i class="fas fa-clock"></i>
                                        Disponible dans {{ $planetInfo['stargate_cooldown_text'] ?? 'Disponible bientôt' }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gestion de la production -->
            <div class="production-management">
                <div class="production-header">
                    <h3><i class="fas fa-industry"></i> Gestion de la production</h3>
                    <div class="production-info">
                        <span class="info-text">Ajustez les taux de production en temps réel</span>
                    </div>
                </div>
                
                <div class="production-grid">
                    @forelse($planetResources as $resourceName => $resource)
                        <div class="production-card">
                            <div class="resource-header">
                                <div class="resource-icon">
                                    <img src="/images/resources/{{ $resource['icon'] }}" alt="{{ $resource['name'] }}" />
                                </div>
                                <h4>{{ $resource['name'] }}</h4>
                            </div>
                            
                            <div class="resource-stats">
                                <div class="stat-item">
                                    <span class="stat-label">Quantité actuelle</span>
                                    <span class="stat-value">{{ number_format($resource['current_amount']) }}</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">Capacité de stockage</span>
                                    <span class="stat-value">{{ number_format($resource['storage_capacity']) }}</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">Production/heure</span>
                                    <span class="stat-value">{{ number_format($resource['current_production_per_hour'] ?? $resource['base_production_per_hour'], 0) }}</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">Production/24h</span>
                                    <span class="stat-value production-24h">{{ number_format($resource['daily_production'], 0) }}</span>
                                </div>
                            </div>
                            
                            <div class="production-control">
                                <label class="control-label">Taux de production</label>
                                <div class="production-slider">
                                    <input type="range" 
                                        min="0" 
                                        max="100" 
                                        value="{{ $productionRates[$resourceName] ?? 100 }}"
                                        wire:input="updateProductionRate('{{ $resourceName }}', $event.target.value)"
                                        wire:change="saveProductionRate('{{ $resourceName }}', $event.target.value)"
                                        class="slider">
                                    <div class="slider-labels">
                                        <span>0%</span>
                                        <span class="current-rate">{{ $productionRates[$resourceName] ?? 100 }}%</span>
                                        <span>100%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="no-resources">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Aucune ressource trouvée pour cette planète</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Grille de gestion -->
            <div class="management-grid">
                <!-- Bâtiments -->
                <div class="management-section">
                    <div class="section-header">
                        <h3><i class="fas fa-building"></i> Bâtiments</h3>
                        <span class="count">{{ count($planetBuildings) }}</span>
                    </div>
                    <div class="items-list">
                        @forelse($planetBuildings as $building)
                            <div class="item-card {{ !$building['is_active'] ? 'inactive' : '' }}">
                                <div class="item-icon">
                                    <img src="/images/buildings/{{ $building['icon'] }}" alt="{{ $building['name'] }}" />
                                    @if(!$building['is_active'])
                                        <div class="inactive-overlay">
                                            <i class="fas fa-power-off"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="item-info">
                                    <span class="item-name">{{ $building['name'] }}</span>
                                    <span class="item-level">Niveau {{ $building['level'] }}</span>
                                    <span class="item-status {{ $building['is_active'] ? 'active' : 'inactive' }}">
                                        <i class="fas {{ $building['is_active'] ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                        {{ $building['is_active'] ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                                <div class="item-actions">
                                    <button 
                                        wire:click="toggleBuildingStatus({{ $building['id'] }})"
                                        class="toggle-btn {{ $building['is_active'] ? 'deactivate' : 'activate' }}"
                                        title="{{ $building['is_active'] ? 'Désactiver' : 'Activer' }} le bâtiment">
                                        <i class="fas {{ $building['is_active'] ? 'fa-power-off' : 'fa-play' }}"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="fas fa-building"></i>
                                <p>Aucun bâtiment construit</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Unités -->
                <div class="management-section">
                    <div class="section-header">
                        <h3><i class="fas fa-users"></i> Unités</h3>
                        <span class="count">{{ array_sum(array_column($planetUnits, 'quantity')) }}</span>
                    </div>
                    <div class="items-list">
                        @forelse($planetUnits as $unit)
                            <div class="item-card">
                                <div class="item-icon">
                                    <img src="/images/units/{{ $unit['icon'] }}" alt="{{ $unit['name'] }}" />
                                </div>
                                <div class="item-info">
                                    <span class="item-name">{{ $unit['name'] }}</span>
                                    <span class="item-quantity">x{{ number_format($unit['quantity']) }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                <p>Aucune unité produite</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Défenses -->
                <div class="management-section">
                    <div class="section-header">
                        <h3><i class="fas fa-shield-alt"></i> Défenses</h3>
                        <span class="count">{{ array_sum(array_column($planetDefenses, 'quantity')) }}</span>
                    </div>
                    <div class="items-list">
                        @forelse($planetDefenses as $defense)
                            <div class="item-card">
                                <div class="item-icon">
                                    <img src="/images/defenses/{{ $defense['icon'] }}" alt="{{ $defense['name'] }}" />
                                </div>
                                <div class="item-info">
                                    <span class="item-name">{{ $defense['name'] }}</span>
                                    <span class="item-quantity">x{{ number_format($defense['quantity']) }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="fas fa-shield-alt"></i>
                                <p>Aucune défense construite</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Vaisseaux -->
                <div class="management-section">
                    <div class="section-header">
                        <h3><i class="fas fa-rocket"></i> Vaisseaux</h3>
                        <span class="count">{{ array_sum(array_column($planetShips, 'quantity')) }}</span>
                    </div>
                    <div class="items-list">
                        @forelse($planetShips as $ship)
                            <div class="item-card">
                                <div class="item-icon">
                                    <img src="/images/ships/{{ $ship['icon'] }}" alt="{{ $ship['name'] }}" />
                                </div>
                                <div class="item-info">
                                    <span class="item-name">{{ $ship['name'] }}</span>
                                    <span class="item-quantity">x{{ number_format($ship['quantity']) }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="fas fa-rocket"></i>
                                <p>Aucun vaisseau construit</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @else
            <div class="no-planet-selected">
                <i class="fas fa-globe"></i>
                <h3>Aucune planète sélectionnée</h3>
                <p>Veuillez sélectionner une planète pour voir ses informations de gestion.</p>
            </div>
        @endif
    </div>
</div>
