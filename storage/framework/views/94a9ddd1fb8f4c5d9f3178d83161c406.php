<div page="mission">
    <div class="mission-container">
        <div class="mission-header">
            <h2 class="mission-title">
                <i class="fas fa-exchange-alt mission-type-basement"></i>
                Mission Basement
            </h2>
        </div>
        
        <div class="mission-content">
            <?php if(!$showMissionSummary): ?>
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
                                    <span class="detail-value"><?php echo e($targetPlanet->name); ?></span>
                                </div>
                                <div class="mission-target-detail">
                                    <span class="detail-label">Coordonnées</span>
                                    <span class="detail-value">[<?php echo e($targetPlanet->templatePlanet->galaxy); ?>:<?php echo e($targetPlanet->templatePlanet->system); ?>:<?php echo e($targetPlanet->templatePlanet->position); ?>]</span>
                                </div>
                                <div class="mission-target-detail">
                                    <span class="detail-label">Propriétaire</span>
                                    <span class="detail-value"><?php echo e($targetPlanet->user->name); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Sélection des unités -->
                    <?php if(count($availableUnits) > 0): ?>
                        <div class="mission-form-group">
                            <label class="mission-form-label"><i class="fas fa-users"></i> Sélection des unités</label>
                            <p class="mission-form-help">Choisissez les unités à transférer vers la planète de destination</p>
                            
                            <div class="mission-units-selection">
                                <?php $__currentLoopData = $availableUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="mission-unit-item" style="--i: <?php echo e($loop->index); ?>">
                                        <div class="mission-unit-header">
                                            <?php $unitImg = $unit['icon_url'] ?? asset('images/units/' . $unit['image']); ?>
                                            <img src="<?php echo e($unitImg); ?>" alt="<?php echo e($unit['name']); ?>" class="mission-unit-icon">
                                            <h4 class="mission-unit-name"><?php echo e($unit['name']); ?></h4>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Disponible: <?php echo e(number_format($unit['quantity'])); ?></span>
                                        </div>
                                        <div class="mission-unit-quantity">
                                            <div class="mission-unit-buttons">
                                                <input type="number" class="mission-unit-input" 
                                                    wire:model.live="selectedUnits.<?php echo e($unit['id']); ?>" 
                                                    wire:change="updateUnitSelection"
                                                    min="0" max="<?php echo e($unit['quantity']); ?>">
                                                <button type="button" class="btn-clear" wire:click="setClearUnits(<?php echo e($unit['id']); ?>)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <button type="button" class="btn-max" wire:click="setMaxUnits(<?php echo e($unit['id']); ?>)">
                                                    <i class="fas fa-angle-double-up"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Sélection des vaisseaux -->
                    <?php if(count($availableShips) > 0): ?>
                        <div class="mission-form-group">
                            <label class="mission-form-label"><i class="fas fa-rocket"></i> Sélection des vaisseaux</label>
                            <p class="mission-form-help">Choisissez les vaisseaux à transférer vers la planète de destination</p>
                            
                            <div class="mission-units-selection">
                                <?php $__currentLoopData = $availableShips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="mission-unit-item" style="--i: <?php echo e($loop->index); ?>">
                                        <div class="mission-unit-header">
                                            <?php $shipImg = $ship['icon_url'] ?? asset('images/ships/' . $ship['image']); ?>
                                            <img src="<?php echo e($shipImg); ?>" alt="<?php echo e($ship['name']); ?>" class="mission-unit-icon">
                                            <h4 class="mission-unit-name"><?php echo e($ship['name']); ?></h4>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Disponible: <?php echo e(number_format($ship['quantity'])); ?></span>
                                        </div>
                                        <div class="mission-unit-quantity">
                                            <div class="mission-unit-buttons">
                                                <input type="number" class="mission-unit-input"
                                                    wire:model.live="selectedShips.<?php echo e($ship['id']); ?>"
                                                    wire:change="updateShipSelection"
                                                    min="0" max="<?php echo e($ship['quantity']); ?>">
                                                <button type="button" class="btn-clear" wire:click="setClearShips(<?php echo e($ship['id']); ?>)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <button type="button" class="btn-max" wire:click="setMaxShips(<?php echo e($ship['id']); ?>)">
                                                    <i class="fas fa-angle-double-up"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Actions -->
                    <div class="mission-actions">
                        <button type="button" class="mission-btn mission-btn-primary" wire:click="showSummary"
                                <?php if(($totalUnitsSelected <= 0 && $totalShipsSelected <= 0) || !$targetPlanetId): ?> disabled <?php endif; ?>>
                            <i class="fas fa-check"></i>
                            Continuer
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <!-- Résumé de la mission -->
                <div class="mission-summary">
                    <h3 class="mission-summary-title"><i class="fas fa-clipboard-check"></i> Résumé de la mission basement</h3>
                    
                    <div class="mission-summary-items">
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Planète de départ</span>
                            <span class="mission-summary-value"><?php echo e($planet->name); ?> [<?php echo e($planet->templatePlanet->galaxy); ?>:<?php echo e($planet->templatePlanet->system); ?>:<?php echo e($planet->templatePlanet->position); ?>]</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Planète de destination</span>
                            <span class="mission-summary-value"><?php echo e($targetPlanet->name); ?> [<?php echo e($targetPlanet->templatePlanet->galaxy); ?>:<?php echo e($targetPlanet->templatePlanet->system); ?>:<?php echo e($targetPlanet->templatePlanet->position); ?>]</span>
                        </div>
                        
                        <?php if($totalUnitsSelected > 0): ?>
                            <div class="mission-summary-item">
                                <span class="mission-summary-label">Unités transférées</span>
                                <span class="mission-summary-value"><?php echo e(number_format($totalUnitsSelected)); ?> unité(s)</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($totalShipsSelected > 0): ?>
                            <div class="mission-summary-item">
                                <span class="mission-summary-label">Vaisseaux transférés</span>
                                <span class="mission-summary-value"><?php echo e(number_format($totalShipsSelected)); ?> vaisseau(x)</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Durée du transfert</span>
                            <span class="mission-summary-value"><i class="fas fa-clock"></i> 
                                <?php if($missionDuration > 0): ?>
                                    <?php echo e(gmdate('H:i:s', $missionDuration)); ?>

                                <?php else: ?>
                                    Instantané
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Consommation de deutérium</span>
                            <span class="mission-summary-value"><i class="fas fa-gas-pump"></i> 
                                <?php if($fuelConsumption > 0): ?>
                                    <?php echo e(number_format($fuelConsumption)); ?>

                                <?php else: ?>
                                    Gratuit
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Heure de départ</span>
                            <span class="mission-summary-value"><i class="fas fa-rocket"></i> <?php echo e(now()->format('d/m/Y H:i:s')); ?></span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Heure d'arrivée</span>
                            <span class="mission-summary-value"><i class="fas fa-flag-checkered"></i> 
                                <?php if($missionDuration > 0): ?>
                                    <?php echo e(now()->addSeconds($missionDuration)->format('d/m/Y H:i:s')); ?>

                                <?php else: ?>
                                    <?php echo e(now()->format('d/m/Y H:i:s')); ?>

                                <?php endif; ?>
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
            <?php endif; ?>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/mission/mission-basement.blade.php ENDPATH**/ ?>