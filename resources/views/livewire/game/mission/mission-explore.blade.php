<div page="mission">
    <div class="mission-container">
        <div class="mission-header">
            <h2 class="mission-title">
                <i class="fas fa-compass"></i>
                Mission d'Exploration
            </h2>
        </div>

        <div class="mission-content">
            @if(!$showMissionSummary)
                <div class="mission-form">
                    <!-- Planète cible -->
                    <div class="mission-form-group">
                        <label class="mission-form-label">Planète cible</label>
                        <p class="mission-form-help">Informations sur la planète cible</p>
                        <div class="mission-target-info">
                            <h4>Informations sur la planète cible</h4>
                            <div class="mission-target-details">
                                <div class="mission-target-detail">
                                    <span class="detail-label">Coordonnées:</span>
                                    <span class="detail-value">[{{ $targetCoordinates }}]</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sélection des vaisseaux -->
                    <div class="mission-form-group">
                        <label class="mission-form-label">Sélection des vaisseaux d'exploration</label>

                        @if(count($availableShips) > 0)
                            <div class="mission-units-selection">
                                @foreach($availableShips as $ship)
                                    <div class="mission-unit-item">
                                        <div class="mission-unit-header">
                                            @php $imgUrl = $ship['icon_url'] ?? asset('images/ships/' . ($ship['image'] ?? '.png')); @endphp
                                            <img src="{{ $imgUrl }}" alt="{{ $ship['name'] }}" class="mission-unit-icon">
                                            <h4 class="mission-unit-name">{{ $ship['name'] }}</h4>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Vitesse: {{ $ship['speed'] }}</span>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Disponible: {{ $ship['quantity'] }}</span>
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
                        @else
                            <div class="mission-empty-notice">
                                <p>Vous n'avez aucun vaisseau d'exploration disponible sur cette planète.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="mission-actions">
                        <button type="button" class="mission-btn mission-btn-primary" wire:click="showSummary">
                            <i class="fas fa-check"></i>
                            Continuer
                        </button>
                    </div>
                </div>
            @else
                <!-- Résumé de la mission -->
                <div class="mission-summary">
                    <h3 class="mission-summary-title">Résumé de la mission d'exploration</h3>

                    <div class="mission-summary-items">
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Planète de départ</span>
                            <span class="mission-summary-value">{{ $planet->name }} [{{ $planet->templatePlanet->galaxy }}:{{ $planet->templatePlanet->system }}:{{ $planet->templatePlanet->position }}]</span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Planète de destination</span>
                            <span class="mission-summary-value">[{{ $targetCoordinates }}]</span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Type de mission</span>
                            <span class="mission-summary-value">Exploration</span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Vaisseaux envoyés</span>
                            <span class="mission-summary-value">{{ $totalShipsSelected }}</span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Durée du voyage</span>
                            <span class="mission-summary-value">{{ gmdate('H:i:s', $travelDurationSeconds) }}</span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Temps d'exploration</span>
                            <span class="mission-summary-value">{{ gmdate('H:i:s', $explorationDurationSeconds) }}</span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Durée totale</span>
                            <span class="mission-summary-value">{{ gmdate('H:i:s', $totalDurationSeconds) }}</span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Consommation de carburant</span>
                            <span class="mission-summary-value">
                                <img src="{{ asset('images/resources/deuterium.png') }}" alt="Deutérium" class="resource-icon">
                                {{ number_format($fuelCost) }}
                            </span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Heure d'arrivée</span>
                            <span class="mission-summary-value">{{ now()->addSeconds($travelDurationSeconds)->format('d/m/Y H:i:s') }}</span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Heure de retour</span>
                            <span class="mission-summary-value">{{ now()->addSeconds($totalDurationSeconds)->format('d/m/Y H:i:s') }}</span>
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