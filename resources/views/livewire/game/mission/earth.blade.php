<div page="mission">
    <div class="mission-container">
        <div class="mission-header">
            <h2 class="mission-title">
                <i class="fas fa-shield-alt mission-type-attack"></i>
                Mission d'Attaque Terrestre
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
                            <p class="mission-form-help">Aucune équipe terrestre active sur cette planète.</p>
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
                    
                    <!-- Sélection des unités terrestres -->
                    <div class="mission-form-group">
                        <label class="mission-form-label">Sélection des unités terrestres</label>
                        
                        @if(count($availableUnits) > 0)
                            <div class="mission-units-selection">
                                @foreach($availableUnits as $unit)
                                    <div class="mission-unit-item">
                                        <div class="mission-unit-header">
                                            @php $unitImg = $unit['icon_url'] ?? asset('images/units/' . $unit['image']); @endphp
                                            <img src="{{ $unitImg }}" alt="{{ $unit['name'] }}" class="mission-unit-icon">
                                            <h4 class="mission-unit-name">{{ $unit['name'] }}</h4>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Attaque: {{ $unit['attack'] }}</span>
                                            <span>Défense: {{ $unit['defense'] }}</span>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Disponible: {{ number_format($unit['quantity']) }}</span>
                                            <span>Cargo: {{ number_format($unit['cargo_capacity']) ?? 0 }}</span>
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
                        @else
                            <div class="mission-empty-notice">
                                <p>Vous n'avez aucune unité terrestre de combat disponible sur cette planète.</p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Informations sur la sélection -->
                    @if($totalUnitsSelected > 0)
                        <div class="mission-selection-info">
                            <div class="mission-info-item">
                                <span class="info-label">Unités sélectionnées:</span>
                                <span class="info-value">{{ number_format($totalUnitsSelected) }}</span>
                            </div>
                            <div class="mission-info-item">
                                <span class="info-label">Capacité de transport totale:</span>
                                <span class="info-value">{{ number_format($totalCargoCapacity) }}</span>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Actions -->
                    <div class="mission-actions">
                        @if(count($availableUnits) > 0)
                            <button type="button" class="mission-btn mission-btn-attack" wire:click="showSummary" @if($attackInProgress) disabled @endif>
                                <i class="fas fa-paper-plane"></i>
                                Continuer
                            </button>
                        @endif
                    </div>
                </div>
            @else
                <!-- Résumé de la mission -->
                <div class="mission-summary">
                    <h3 class="mission-summary-title">Résumé de la mission d'attaque terrestre</h3>
                    
                    <div class="mission-summary-items">
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Type de mission</span>
                            <span class="mission-summary-value mission-type-attack">Attaque Terrestre</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Coordonnées cible</span>
                            <span class="mission-summary-value">{{ $targetGalaxy }}:{{ $targetSystem }}:{{ $targetPosition }}</span>
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
                        
                            @if($targetPlanet->user && $targetPlanet->user->alliance)
                                <div class="mission-summary-item">
                                    <span class="mission-summary-label">Alliance</span>
                                    <span class="mission-summary-value">{{ $targetPlanet->user->alliance->name }} [{{ $targetPlanet->user->alliance->tag }}]</span>
                                </div>
                            @endif
                        @endif
                    
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Unités envoyées</span>
                            <span class="mission-summary-value">{{ number_format($totalUnitsSelected) }}</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Capacité de transport</span>
                            <span class="mission-summary-value">{{ number_format($totalCargoCapacity) }}</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Type de combat</span>
                            <span class="mission-summary-value">Combat instantané</span>
                        </div>
                    </div>
                </div>
                
                <!-- Indicateur d'attaque en cours -->
                @if($attackInProgress)
                    <div class="mission-attack-progress">
                        <div class="attack-progress-content">
                            <div class="spinner"></div>
                            <h3>Attaque en cours...</h3>
                            <p>Veuillez patienter pendant que le combat se déroule</p>
                        </div>
                    </div>
                @else
                    <!-- Actions -->
                    <div class="mission-actions">
                        <button type="button" class="mission-btn mission-btn-secondary" wire:click="cancelMission">
                            <i class="fas fa-times"></i>
                            Annuler
                        </button>
                        
                        <button type="button" class="mission-btn mission-btn-attack" wire:click="launchAttack">
                            <i class="fas fa-fist-raised"></i>
                            Commencer le combat
                        </button>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>