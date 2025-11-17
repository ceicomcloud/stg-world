<div page="mission">
    <div class="mission-container">
        <div class="mission-header">
            <h2 class="mission-title">
                <i class="fas fa-flag mission-type-colonize"></i>
                Mission de Colonisation
            </h2>
        </div>
        
        <div class="mission-content">
            @if(!$showMissionSummary)
                <div class="mission-form">
                    <!-- Planète cible -->
                    <div class="mission-form-group">
                        <label class="mission-form-label">Planète à coloniser</label>
                        <p class="mission-form-help">Informations sur la planète à coloniser</p>
                        
                        @if($targetPlanet && $targetPlanet->user)
                            <div class="mission-error-message">
                                <i class="fas fa-exclamation-triangle"></i>
                                Cette position est déjà occupée par une planète colonisée.
                            </div>
                        @elseif($targetPlanetTemplate)
                            <!-- Informations sur la planète à coloniser -->
                            <div class="mission-target-info">
                                <h4>Informations sur la planète</h4>
                                <div class="mission-target-details">
                                    <div class="mission-target-detail">
                                        <span class="detail-label">Nom:</span>
                                        <span class="detail-value">{{ $targetPlanetTemplate->name }}</span>
                                    </div>
                                    <div class="mission-target-detail">
                                        <span class="detail-label">Coordonnées:</span>
                                        <span class="detail-value">[{{ $targetGalaxy }}:{{ $targetSystem }}:{{ $targetPosition }}]</span>
                                    </div>
                                    <div class="mission-target-detail">
                                        <span class="detail-label">Statut:</span>
                                        <span class="detail-value">Planète libre</span>
                                    </div>
                                </div>
                            </div>
                        @elseif($templateId)
                            <div class="mission-error-message">
                                <i class="fas fa-exclamation-triangle"></i>
                                Aucune planète n'existe avec ce template.
                            </div>
                        @endif
                        
                        @if($userPlanetCount >= $maxPlanets)
                            <div class="mission-error-message">
                                <i class="fas fa-exclamation-triangle"></i>
                                Vous avez atteint le nombre maximum de planètes ({{ $maxPlanets }}).
                            </div>
                        @endif
                    </div>
                    
                    <!-- Sélection des vaisseaux -->
                    <div class="mission-form-group">
                        <label class="mission-form-label">Sélection des vaisseaux de colonisation</label>
                        
                        @if(count($availableShips) > 0)
                            <div class="mission-units-selection">
                                @foreach($availableShips as $ship)
                                    <div class="mission-unit-item">
                                        <div class="mission-unit-header">
                                            <img src="{{ asset('images/ships/' . $ship['image']) }}" alt="{{ $ship['name'] }}" class="mission-unit-icon">
                                            <h4 class="mission-unit-name">{{ $ship['name'] }}</h4>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Vitesse: {{ $ship['speed'] }}</span>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Disponible: {{ $ship['quantity'] }}</span>
                                        </div>
                                        <div class="mission-unit-quantity">
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="number" class="mission-unit-input w-100"
                                                       wire:model.live="selectedShips.{{ $ship['id'] }}"
                                                       wire:change="updateShipSelection"
                                                       min="0" max="{{ min(1, $ship['quantity']) }}">
                                                <button type="button" class="mission-btn mission-btn-secondary btn-sm"
                                                        wire:click="setMaxShips({{ $ship['id'] }})">
                                                    Max
                                                </button>
                                                <button type="button" class="mission-btn mission-btn-danger btn-sm"
                                                        wire:click="setClearShips({{ $ship['id'] }})">
                                                    Clear
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <p class="mission-form-help">Un seul vaisseau de colonisation est nécessaire pour cette mission.</p>
                        @else
                            <div class="mission-empty-notice">
                                <p>Vous n'avez aucun vaisseau de colonisation disponible sur cette planète.</p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Actions -->
                    <div class="mission-actions">
                        <button type="button" class="mission-btn mission-btn-primary" wire:click="showSummary" 
                            @if(!$canContinue) disabled @endif>
                            <i class="fas fa-check"></i>
                            Continuer
                        </button>
                    </div>
                </div>
            @else
                <!-- Résumé de la mission -->
                <div class="mission-summary">
                    <h3 class="mission-summary-title">Résumé de la mission de colonisation</h3>
                    
                    <div class="mission-summary-items">
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Planète de départ</span>
                            <span class="mission-summary-value">{{ $planet->name }} [{{ $planet->templatePlanet->galaxy }}:{{ $planet->templatePlanet->system }}:{{ $planet->templatePlanet->position }}]</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Coordonnées de destination</span>
                            <span class="mission-summary-value">[{{ $targetGalaxy }}:{{ $targetSystem }}:{{ $targetPosition }}]</span>
                        </div>
                        
                        @if($targetPlanet)
                            <div class="mission-summary-item">
                                <span class="mission-summary-label">Planète à coloniser</span>
                                <span class="mission-summary-value">{{ $targetPlanet->name }}</span>
                            </div>
                            
                            <div class="mission-summary-item">
                                <span class="mission-summary-label">Taille</span>
                                <span class="mission-summary-value">{{ $targetPlanet->fields }} cases</span>
                            </div>
                        @endif
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Type de mission</span>
                            <span class="mission-summary-value mission-type-colonize">Colonisation</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Vaisseaux envoyés</span>
                            <span class="mission-summary-value">1 Colonisateur</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Durée du voyage</span>
                            <span class="mission-summary-value">{{ gmdate('H:i:s', $missionDuration) }}</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Heure d'arrivée</span>
                            <span class="mission-summary-value">{{ now()->addSeconds($missionDuration)->format('d/m/Y H:i:s') }}</span>
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
                    
                    <div class="mission-actions">
                        <button type="button" class="mission-btn mission-btn-secondary" wire:click="backToSelection">
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