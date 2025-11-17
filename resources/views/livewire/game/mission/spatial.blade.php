<div page="mission">
    <div class="mission-container">
        <div class="mission-header">
            <h2 class="mission-title">
                <i class="fas fa-fighter-jet mission-type-attack"></i>
                Mission d'Attaque Spatiale
            </h2>
        </div>
        
        <div class="mission-content">
            @if(!$showMissionSummary)
                <div class="mission-form">
                    <!-- Sélection rapide par équipe -->
                    <div class="mission-form-group">
                        <label class="mission-form-label">Utiliser une équipe</label>
                        <div class="mission-team-selector">
                            <select class="mission-form-control" wire:model.live="selectedTeamId">
                                <option value="">Choisir une équipe…</option>
                                @foreach($equipTeams as $team)
                                    <option value="{{ $team['id'] }}">#{{ $team['team_index'] }} — {{ $team['label'] }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="mission-btn mission-btn-secondary" wire:click="applySelectedTeam">
                                <i class="fas fa-users-gear"></i>
                                Appliquer
                            </button>
                        </div>
                        @if(empty($equipTeams))
                            <p class="mission-form-help">Aucune équipe spatiale active sur cette planète.</p>
                        @endif
                    </div>
                    <!-- Coordonnées de la cible -->
                    <div class="mission-form-group">
                        <!-- Informations sur la planète cible -->
                        <div class="mission-target-info">
                            <h4>Informations sur la planète cible</h4>
                            <div class="mission-target-details">
                                <div class="mission-target-detail">
                                    <span class="detail-label">Nom:</span>
                                    <span class="detail-value">{{ $targetPlanet->name }}</span>
                                </div>
                                <div class="mission-target-detail">
                                    <span class="detail-label">Coordonnées:</span>
                                    <span class="detail-value">[{{ $targetGalaxy }}:{{ $targetSystem }}:{{ $targetPosition }}]</span>
                                </div>
                                <div class="mission-target-detail">
                                    <span class="detail-label">Propriétaire:</span>
                                    <span class="detail-value">{{ $targetPlanet->user ? $targetPlanet->user->name : 'Planète non colonisée' }}</span>
                                </div>
                                @if($targetPlanet->user && $targetPlanet->user->alliance)
                                    <div class="mission-target-detail">
                                        <span class="detail-label">Alliance:</span>
                                        <span class="detail-value">[{{ $targetPlanet->user->alliance->tag }}] {{ $targetPlanet->user->alliance->name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sélection des vaisseaux -->
                    <div class="mission-form-group">
                        <label class="mission-form-label">Sélection des vaisseaux</label>
                        
                        @if(count($availableShips) > 0)
                            <div class="mission-units-selection">
                                @foreach($availableShips as $ship)
                                    <div class="mission-unit-item">
                                        <div class="mission-unit-header">
                                            @php $shipImg = $ship['icon_url'] ?? asset('images/ships/' . $ship['image']); @endphp
                                            <img src="{{ $shipImg }}" alt="{{ $ship['name'] }}" class="mission-unit-icon">
                                            <h4 class="mission-unit-name">{{ $ship['name'] }}</h4>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Attaque: {{ $ship['attack'] }}</span>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Défense: {{ $ship['defense'] }}</span>
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
                                <p>Vous n'avez aucun vaisseau de combat disponible sur cette planète.</p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Actions -->
                    <div class="mission-actions">
                        @if(count($availableShips) > 0)
                            <button type="button" class="mission-btn mission-btn-attack" wire:click="showSummary">
                                <i class="fas fa-paper-plane"></i>
                                Continuer
                            </button>
                        @endif
                    </div>
                </div>
            @else
                <!-- Résumé de la mission -->
                <div class="mission-summary">
                    <h3 class="mission-summary-title">Résumé de la mission d'attaque spatial</h3>
                    
                    <div class="mission-summary-items">
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Type de mission</span>
                            <span class="mission-summary-value mission-type-attack">Attaque Spatiale</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Coordonnées cible</span>
                            <span class="mission-summary-value">{{ $targetPlanet->templatePlanet->galaxy }}:{{ $targetPlanet->templatePlanet->system }}:{{ $targetPlanet->templatePlanet->position }}</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Planète cible</span>
                            <span class="mission-summary-value">{{ $targetPlanet->name }}</span>
                        </div>
                    
                        @if($targetPlanet)
                            <div class="mission-summary-item">
                                <span class="mission-summary-label">Planète cible</span>
                                <span class="mission-summary-value">{{ $targetPlanet->name }}</span>
                            </div>
                            
                            <div class="mission-summary-item">
                                <span class="mission-summary-label">Propriétaire</span>
                                <span class="mission-summary-value">{{ $targetPlanet->user ? $targetPlanet->user->name : 'Planète non colonisée' }}</span>
                            </div>
                        @endif
                    
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Vaisseaux envoyés</span>
                            <span class="mission-summary-value">{{ $totalShipsSelected }}</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Vitesse de la flotte</span>
                            <span class="mission-summary-value">{{ $this->calculateSpeed() }}</span>
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
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Durée du voyage</span>
                            <span class="mission-summary-value"><i class="fas fa-clock"></i> {{ gmdate('H:i:s', $missionDuration) }}</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Heure d'arrivée</span>
                            <span class="mission-summary-value">{{ \Carbon\Carbon::now()->addMinutes($missionDuration)->format('d/m/Y H:i:s') }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="mission-actions">
                    <button type="button" class="mission-btn mission-btn-secondary" wire:click="cancelMission">
                        <i class="fas fa-times"></i>
                        Annuler
                    </button>
                    
                    <button type="button" class="mission-btn mission-btn-attack" wire:click="launchAttack">
                        <i class="fas fa-rocket"></i>
                        Lancer l'attaque
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>