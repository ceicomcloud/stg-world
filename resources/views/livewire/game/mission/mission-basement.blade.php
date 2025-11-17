<div page="mission">
    <div class="mission-container">
        <div class="mission-header">
            <h2 class="mission-title">
                <i class="fas fa-exchange-alt mission-type-basement"></i>
                Mission Basement
            </h2>
        </div>
        
        <div class="mission-content">
            @if(!$showMissionSummary)
                <div class="mission-form">
                    <!-- Planète de destination -->
                    <div class="mission-form-group">
                        <label class="mission-form-label"><i class="fas fa-crosshairs"></i> Planète de destination</label>
                        <p class="mission-form-help">Informations sur la planète de destination pour le transfert instantané</p>
                        
                        <div class="mission-target-info">
                            <h4><i class="fas fa-info-circle"></i> Informations sur la planète cible</h4>
                            <div class="mission-target-details">
                                <div class="mission-target-detail">
                                    <span class="detail-label">Nom</span>
                                    <span class="detail-value">{{ $targetPlanet->name }}</span>
                                </div>
                                <div class="mission-target-detail">
                                    <span class="detail-label">Coordonnées</span>
                                    <span class="detail-value">[{{ $targetPlanet->templatePlanet->galaxy }}:{{ $targetPlanet->templatePlanet->system }}:{{ $targetPlanet->templatePlanet->position }}]</span>
                                </div>
                                <div class="mission-target-detail">
                                    <span class="detail-label">Propriétaire</span>
                                    <span class="detail-value">{{ $targetPlanet->user->name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Sélection des unités -->
                    @if(count($availableUnits) > 0)
                        <div class="mission-form-group">
                            <label class="mission-form-label"><i class="fas fa-users"></i> Sélection des unités</label>
                            <p class="mission-form-help">Choisissez les unités à transférer vers la planète de destination</p>
                            
                            <div class="mission-units-selection">
                                @foreach($availableUnits as $unit)
                                    <div class="mission-unit-item" style="--i: {{ $loop->index }}">
                                        <div class="mission-unit-header">
                                            @php $unitImg = $unit['icon_url'] ?? asset('images/units/' . $unit['image']); @endphp
                                            <img src="{{ $unitImg }}" alt="{{ $unit['name'] }}" class="mission-unit-icon">
                                            <h4 class="mission-unit-name">{{ $unit['name'] }}</h4>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Disponible: {{ number_format($unit['quantity']) }}</span>
                                        </div>
                                        <div class="mission-unit-quantity">
                                            <div class="mission-unit-buttons">
                                                <input type="number" class="mission-unit-input" 
                                                    wire:model.live="selectedUnits.{{ $unit['id'] }}" 
                                                    wire:change="updateUnitSelection"
                                                    min="0" max="{{ $unit['quantity'] }}">
                                                <button type="button" class="btn-clear" wire:click="setClearUnits({{ $unit['id'] }})">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <button type="button" class="btn-max" wire:click="setMaxUnits({{ $unit['id'] }})">
                                                    <i class="fas fa-angle-double-up"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Sélection des vaisseaux -->
                    @if(count($availableShips) > 0)
                        <div class="mission-form-group">
                            <label class="mission-form-label"><i class="fas fa-rocket"></i> Sélection des vaisseaux</label>
                            <p class="mission-form-help">Choisissez les vaisseaux à transférer vers la planète de destination</p>
                            
                            <div class="mission-units-selection">
                                @foreach($availableShips as $ship)
                                    <div class="mission-unit-item" style="--i: {{ $loop->index }}">
                                        <div class="mission-unit-header">
                                            @php $shipImg = $ship['icon_url'] ?? asset('images/ships/' . $ship['image']); @endphp
                                            <img src="{{ $shipImg }}" alt="{{ $ship['name'] }}" class="mission-unit-icon">
                                            <h4 class="mission-unit-name">{{ $ship['name'] }}</h4>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Disponible: {{ number_format($ship['quantity']) }}</span>
                                        </div>
                                        <div class="mission-unit-quantity">
                                            <div class="mission-unit-buttons">
                                                <input type="number" class="mission-unit-input"
                                                    wire:model.live="selectedShips.{{ $ship['id'] }}"
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
                        </div>
                    @endif
                    
                    <!-- Actions -->
                    <div class="mission-actions">
                        <button type="button" class="mission-btn mission-btn-primary" wire:click="showSummary"
                                @if(($totalUnitsSelected <= 0 && $totalShipsSelected <= 0) || !$targetPlanetId) disabled @endif>
                            <i class="fas fa-check"></i>
                            Continuer
                        </button>
                    </div>
                </div>
            @else
                <!-- Résumé de la mission -->
                <div class="mission-summary">
                    <h3 class="mission-summary-title"><i class="fas fa-clipboard-check"></i> Résumé de la mission basement</h3>
                    
                    <div class="mission-summary-items">
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Planète de départ</span>
                            <span class="mission-summary-value">{{ $planet->name }} [{{ $planet->templatePlanet->galaxy }}:{{ $planet->templatePlanet->system }}:{{ $planet->templatePlanet->position }}]</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Planète de destination</span>
                            <span class="mission-summary-value">{{ $targetPlanet->name }} [{{ $targetPlanet->templatePlanet->galaxy }}:{{ $targetPlanet->templatePlanet->system }}:{{ $targetPlanet->templatePlanet->position }}]</span>
                        </div>
                        
                        @if($totalUnitsSelected > 0)
                            <div class="mission-summary-item">
                                <span class="mission-summary-label">Unités transférées</span>
                                <span class="mission-summary-value">{{ number_format($totalUnitsSelected) }} unité(s)</span>
                            </div>
                        @endif
                        
                        @if($totalShipsSelected > 0)
                            <div class="mission-summary-item">
                                <span class="mission-summary-label">Vaisseaux transférés</span>
                                <span class="mission-summary-value">{{ number_format($totalShipsSelected) }} vaisseau(x)</span>
                            </div>
                        @endif
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Durée du transfert</span>
                            <span class="mission-summary-value"><i class="fas fa-clock"></i> 
                                @if($missionDuration > 0)
                                    {{ gmdate('H:i:s', $missionDuration) }}
                                @else
                                    Instantané
                                @endif
                            </span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Consommation de deutérium</span>
                            <span class="mission-summary-value"><i class="fas fa-gas-pump"></i> 
                                @if($fuelConsumption > 0)
                                    {{ number_format($fuelConsumption) }}
                                @else
                                    Gratuit
                                @endif
                            </span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Heure de départ</span>
                            <span class="mission-summary-value"><i class="fas fa-rocket"></i> {{ now()->format('d/m/Y H:i:s') }}</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Heure d'arrivée</span>
                            <span class="mission-summary-value"><i class="fas fa-flag-checkered"></i> 
                                @if($missionDuration > 0)
                                    {{ now()->addSeconds($missionDuration)->format('d/m/Y H:i:s') }}
                                @else
                                    {{ now()->format('d/m/Y H:i:s') }}
                                @endif
                            </span>
                        </div>
                    </div>
                    
                    <div class="mission-warning">
                        <i class="fas fa-info-circle"></i>
                        <p>Les unités et vaisseaux seront transférés vers la planète de destination après le délai de voyage. La mission consommera du deutérium selon la distance.</p>
                    </div>
                    
                    <div class="mission-actions">
                        <button type="button" class="mission-btn mission-btn-secondary" wire:click="$set('showMissionSummary', false)">
                            <i class="fas fa-arrow-left"></i>
                            Retour
                        </button>
                        <button type="button" class="mission-btn mission-btn-primary" wire:click="launchMission">
                            <i class="fas fa-rocket"></i>
                            Lancer le transfert
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>