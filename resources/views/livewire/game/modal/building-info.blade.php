<div>
    <!-- Indicateur de type -->
    <div class="modal-type-indicator modal-type-{{ $buildingData['type'] }}">
        <i class="fas fa-{{ $this->getTypeIcon() }}"></i>
        {{ $this->getTypeLabel() }}
    </div>

    <!-- Image -->
    @if($buildingData['icon'])
        @php
            $imagePath = match($buildingData['type']) {
                'unit' => 'images/units/',
                'defense' => 'images/defenses/',
                'ship' => 'images/ships/',
                default => 'images/buildings/',
            };
        @endphp
        <img src="{{ asset($imagePath . $buildingData['icon']) }}"  alt="{{ $buildingData['label'] }}" class="modal-image">
    @endif

    <!-- Description -->
    @if($buildingData['description'])
        <div class="modal-description">
            {{ $buildingData['description'] }}
        </div>
    @endif

    <!-- Niveau ou Quantité actuel -->
    @if($buildingData['is_quantity_based'])
        <div class="modal-quantity-info">
            <i class="fas fa-cubes"></i>
            <span class="quantity-text">Quantité actuelle:</span>
            <span class="quantity-value">{{ number_format($buildingQuantity) }}</span>
        </div>
    @else
        <div class="modal-level-info">
            <i class="fas fa-layer-group"></i>
            <span class="level-text">Niveau actuel:</span>
            <span class="level-value">{{ $buildingLevel }} / {{ $buildingData['max_level'] }}</span>
        </div>
    @endif

    <!-- Statistiques pour unités, défenses et vaisseaux -->
    @if(in_array($buildingData['type'], ['unit', 'defense', 'ship']))
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-chart-bar"></i>
                Statistiques
            </h3>
            <div class="stats-grid">
                @if($buildingData['life'] > 0)
                    <div class="stat-item">
                        <i class="fas fa-heart"></i>
                        <span class="stat-label">Vie:</span>
                        <span class="stat-value">{{ number_format($buildingData['life']) }}</span>
                    </div>
                @endif
                @if($buildingData['attack_power'] > 0)
                    <div class="stat-item">
                        <i class="fas fa-sword"></i>
                        <span class="stat-label">Attaque:</span>
                        <span class="stat-value">{{ number_format($buildingData['attack_power']) }}</span>
                    </div>
                @endif
                @if($buildingData['shield_power'] > 0)
                    <div class="stat-item">
                        <i class="fas fa-shield"></i>
                        <span class="stat-label">Bouclier:</span>
                        <span class="stat-value">{{ number_format($buildingData['shield_power']) }}</span>
                    </div>
                @endif
                @if($buildingData['speed'] > 0)
                    <div class="stat-item">
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="stat-label">Vitesse:</span>
                        <span class="stat-value">{{ number_format($buildingData['speed']) }}</span>
                    </div>
                @endif
                @if($buildingData['cargo_capacity'] > 0)
                    <div class="stat-item">
                        <i class="fas fa-boxes"></i>
                        <span class="stat-label">Capacité cargo:</span>
                        <span class="stat-value">{{ number_format($buildingData['cargo_capacity']) }}</span>
                    </div>
                @endif
                @if($buildingData['fuel_consumption'] > 0)
                    <div class="stat-item">
                        <i class="fas fa-gas-pump"></i>
                        <span class="stat-label">Consommation:</span>
                        <span class="stat-value">{{ number_format($buildingData['fuel_consumption']) }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Avantages -->
    @if(count($buildingData['advantages']) > 0)
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-plus-circle"></i>
                Avantages
            </h3>
            <div class="advantages-list">
                @foreach($buildingData['advantages'] as $advantage)
                    <div class="advantage-item">
                        <i class="fas fa-check"></i>
                        <span>{{ $advantage['description'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Désavantages -->
    @if(count($buildingData['disadvantages']) > 0)
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-minus-circle"></i>
                Désavantages
            </h3>
            <div class="disadvantages-list">
                @foreach($buildingData['disadvantages'] as $disadvantage)
                    <div class="disadvantage-item">
                        <i class="fas fa-times"></i>
                        <span>{{ $disadvantage['description'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Prérequis -->
    @if(count($buildingData['requirements']) > 0)
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-list-check"></i>
                Prérequis
            </h3>
            <div class="requirements-list">
                @foreach($buildingData['requirements'] as $requirement)
                    @php
                        $isMet = $this->checkRequirement($requirement);
                    @endphp
                    <div class="requirement-item {{ $isMet ? 'requirement-met' : 'requirement-not-met' }}">
                        <i class="fas fa-{{ $isMet ? 'check' : 'times' }}"></i>
                        <span>
                            {{ $requirement['required_build']['label'] ?? $requirement['required_build']['name'] ?? 'Bâtiment requis' }}

                            niveau {{ $requirement['required_level'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Actions -->
    <div class="modal-section">
        <h3 class="section-title">
            <i class="fas fa-tools"></i>
            Actions
        </h3>

        @if(!$buildingData['is_quantity_based'] && $buildingLevel > 0 && $buildingData['type'] === 'building')
            <div class="action-item">
                <div class="action-header">
                    <i class="fas fa-dumpster-fire"></i>
                    <span>Détruire ce bâtiment</span>
                </div>
                <div class="action-description">
                    Rendu instantané de 50% des coûts cumulés. La capacité de stockage peut limiter le montant ajouté.
                </div>
                @php($preview = $this->getBuildingRefundPreview())
                @if(count($preview) > 0)
                    <div class="refund-preview">
                        @foreach($preview as $item)
                            <div class="refund-item">
                                <i class="fas fa-coins"></i>
                                <span>+{{ number_format($item['amount'], 0, ',', ' ') }} {{ $item['resource'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
                @if($confirmDestroy)
                    <div class="confirm-box">
                        <div class="confirm-text">
                            <i class="fas fa-exclamation-triangle"></i>
                            Confirmer la destruction ? Cette action est irréversible.
                        </div>
                        <div class="confirm-buttons">
                            <button type="button" class="modal-btn" wire:click="cancelDestroyBuilding">
                                <i class="fas fa-times"></i> Annuler
                            </button>
                            <button type="button" class="modal-btn modal-btn-danger" wire:click="confirmDestroyBuilding">
                                <i class="fas fa-check"></i> Confirmer
                            </button>
                        </div>
                    </div>
                @else
                    <div class="action-buttons">
                        <button type="button" class="modal-btn modal-btn-danger" wire:click="requestDestroyBuilding">
                            <i class="fas fa-trash"></i> Détruire
                        </button>
                    </div>
                @endif
            </div>
        @endif

        @if($buildingData['is_quantity_based'] && $buildingData['type'] === 'unit' && $buildingQuantity > 0)
            <div class="action-item">
                <div class="action-header">
                    <i class="fas fa-users-slash"></i>
                    <span>Supprimer des unités</span>
                </div>
                <div class="action-controls">
                    <label for="remove_qty" class="action-label">Quantité à supprimer</label>
                    <input id="remove_qty" type="number" min="1" max="{{ $buildingQuantity }}" class="modal-input" wire:model.lazy="unitsRemoveQuantity" />
                </div>
                <div class="action-description">
                    Rendu instantané de 50% du coût de la quantité sélectionnée.
                </div>
                @php($unitPreview = $this->getUnitRefundPreview())
                @if(count($unitPreview) > 0)
                    <div class="refund-preview">
                        @foreach($unitPreview as $item)
                            <div class="refund-item">
                                <i class="fas fa-coins"></i>
                                <span>+{{ number_format($item['amount'], 0, ',', ' ') }} {{ $item['resource'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
                @if($confirmRemoveUnits)
                    <div class="confirm-box">
                        <div class="confirm-text">
                            <i class="fas fa-exclamation-triangle"></i>
                            Confirmer la suppression de {{ number_format($unitsRemoveQuantity) }} unité(s) ?
                        </div>
                        <div class="confirm-buttons">
                            <button type="button" class="modal-btn" wire:click="cancelRemoveUnits">
                                <i class="fas fa-times"></i> Annuler
                            </button>
                            <button type="button" class="modal-btn modal-btn-warning" wire:click="confirmRemoveUnitsAction">
                                <i class="fas fa-check"></i> Confirmer
                            </button>
                        </div>
                    </div>
                @else
                    <div class="action-buttons">
                        <button type="button" class="modal-btn modal-btn-warning" wire:click="requestRemoveUnits">
                            <i class="fas fa-user-minus"></i> Supprimer
                        </button>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>