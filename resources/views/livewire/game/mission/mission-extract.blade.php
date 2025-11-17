<div page="mission">
    <div class="mission-container">
        <div class="mission-header">
            <h2 class="mission-title">
                <i class="fas fa-pickaxe mission-type-extract"></i>
                Mission d'Extraction
            </h2>
        </div>
        
        <div class="mission-content">
            @if(!$showMissionSummary)
                <div class="mission-form">
                    <!-- Coordonnées de destination -->
                    <div class="mission-form-group">
                        @if($targetPlanetTemplate)
                            <div class="mission-target-info">
                                <h4><i class="fas fa-info-circle"></i> Informations sur la planète cible</h4>
                                <div class="mission-target-details">
                                    <div class="mission-target-detail">
                                        <span class="detail-label">Nom</span>
                                        <span class="detail-value">{{ $targetPlanetTemplate->name }}</span>
                                    </div>
                                    <div class="mission-target-detail">
                                        <span class="detail-label">Coordonnées</span>
                                        <span class="detail-value">[{{ $targetGalaxy }}:{{ $targetSystem }}:{{ $targetPosition }}]</span>
                                    </div>
                                    <div class="mission-target-detail">
                                        <span class="detail-label">Statut</span>
                                        <span class="detail-value">{{ $targetPlanet ? 'Planète colonisée' : 'Planète non colonisée' }}</span>
                                    </div>
                                    @if($targetPlanet && $targetPlanet->user)
                                        <div class="mission-target-detail">
                                            <span class="detail-label">Propriétaire</span>
                                            <span class="detail-value">{{ $targetPlanet->user->name }}</span>
                                        </div>
                                        @if($targetPlanet->user->alliance)
                                            <div class="mission-target-detail">
                                                <span class="detail-label">Alliance</span>
                                                <span class="detail-value">[{{ $targetPlanet->user->alliance->tag }}] {{ $targetPlanet->user->alliance->name }}</span>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Sélection des vaisseaux -->
                    <div class="mission-form-group">
                        <label class="mission-form-label"><i class="fas fa-rocket"></i> Sélection des vaisseaux</label>
                        <p class="mission-form-help">Choisissez les Transporteurs Delta à envoyer pour cette mission d'extraction</p>
                        
                        @if(count($transporteurDeltaShips) > 0)
                            <div class="mission-units-selection">
                                @foreach($transporteurDeltaShips as $ship)
                                    <div class="mission-unit-item" style="--i: {{ $loop->index }}">
                                        <div class="mission-unit-header">
                                            <img src="{{ $ship['icon_url'] ?? asset('images/ships/' . $ship['image']) }}" alt="{{ $ship['name'] }}" class="mission-unit-icon">
                                            <h4 class="mission-unit-name">{{ $ship['name'] }}</h4>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Capacité: {{ number_format($ship['capacity']) }}</span>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Vitesse: {{ number_format($ship['speed']) }}</span>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Disponible: {{ number_format($ship['quantity']) }}</span>
                                        </div>
                                        <div class="mission-unit-quantity">
                                            <div class="mission-unit-buttons">
                                                <input type="number" class="mission-unit-input"
                                                       wire:model.defer="selectedShips.{{ $ship['id'] }}"
                                                       wire:change="updateShipSelection"
                                                       min="0" max="{{ $ship['quantity'] }}">
                                                <button type="button" class="btn-clear" wire:click="setClearShips({{ $ship['id'] }})">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <button type="button" class="btn-max" wire:click="setMaxShips({{ $ship['id'] }})">
                                                    <i class="fas fa-angle-double-up"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mission-empty-notice">
                                <i class="fas fa-exclamation-triangle"></i>
                                <p>Vous n'avez aucun Transporteur Delta disponible sur cette planète.</p>
                                <a href="{{ route('game.construction.type', ['type' => 'ship']) }}" class="mission-btn mission-btn-secondary">
                                    <i class="fas fa-industry"></i> Aller au chantier spatial
                                </a>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Actions -->
                    <div class="mission-actions">
                        <button type="button" class="mission-btn mission-btn-primary" wire:click="showSummary"
                                @if($totalShipsSelected <= 0 || !$targetGalaxy || !$targetSystem || !$targetPosition) disabled @endif>
                            <i class="fas fa-check"></i>
                            Continuer
                        </button>
                    </div>
                </div>
            @else
                <!-- Résumé de la mission -->
                <div class="mission-summary">
                    <h3 class="mission-summary-title"><i class="fas fa-clipboard-check"></i> Résumé de la mission d'extraction</h3>
                    
                    <div class="mission-summary-items">
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Planète de départ</span>
                            <span class="mission-summary-value">{{ $planet->name }} [{{ $planet->templatePlanet->galaxy }}:{{ $planet->templatePlanet->system }}:{{ $planet->templatePlanet->position }}]</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Coordonnées de destination</span>
                            <span class="mission-summary-value">[{{ $targetGalaxy }}:{{ $targetSystem }}:{{ $targetPosition }}]</span>
                        </div>
                        
                        @if($targetPlanetTemplate)
                            <div class="mission-summary-item">
                                <span class="mission-summary-label">Planète cible</span>
                                <span class="mission-summary-value">{{ $targetPlanetTemplate->name }}</span>
                            </div>
                        @endif
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Type de mission</span>
                            <span class="mission-summary-value mission-type-extract"><i class="fas fa-pickaxe"></i> Extraction</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Vaisseaux envoyés</span>
                            <span class="mission-summary-value">{{ number_format($totalShipsSelected) }} Transporteur(s) Delta</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Capacité d'extraction</span>
                            <span class="mission-summary-value">{{ number_format($totalCapacity) }} unités</span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Durée du voyage</span>
                            <span class="mission-summary-value"><i class="fas fa-clock"></i> {{ gmdate('H:i:s', $travelDurationMinutes * 60) }}</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Consommation de carburant</span>
                            <span class="mission-summary-value">
                                <div class="mission-summary-resource">
                                    <img src="{{ asset('images/resources/deuterium.png') }}" alt="Deutérium" class="mission-summary-resource-icon">
                                    <span>{{ number_format($fuelConsumption) }}</span>
                                </div>
                            </span>
                        </div>
                        
                    </div>
                    
                    <div class="mission-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Les ressources extraites seront aléatoires et dépendront de la capacité de vos vaisseaux et des ressources disponibles sur la planète cible.</p>
                    </div>
                    
                    <div class="mission-actions">
                        <button type="button" class="mission-btn mission-btn-secondary" wire:click="$set('showMissionSummary', false)">
                            <i class="fas fa-arrow-left"></i>
                            Retour
                        </button>
                        <button type="button" class="mission-btn mission-btn-primary" wire:click="launchMission">
                            <i class="fas fa-rocket"></i>
                            Lancer la mission
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>