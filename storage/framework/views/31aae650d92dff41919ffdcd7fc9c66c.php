<div page="mission">
    <div class="mission-container">
        <div class="mission-header">
            <h2 class="mission-title">
                <i class="fas fa-compass"></i>
                Mission d'Exploration
            </h2>
        </div>

        <div class="mission-content">
            <?php if(!$showMissionSummary): ?>
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
                                    <span class="detail-value">[<?php echo e($targetCoordinates); ?>]</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sélection des vaisseaux -->
                    <div class="mission-form-group">
                        <label class="mission-form-label">Sélection des vaisseaux d'exploration</label>

                        <?php if(count($availableShips) > 0): ?>
                            <div class="mission-units-selection">
                                <?php $__currentLoopData = $availableShips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="mission-unit-item">
                                        <div class="mission-unit-header">
                                            <?php $imgUrl = $ship['icon_url'] ?? asset('images/ships/' . ($ship['image'] ?? '.png')); ?>
                                            <img src="<?php echo e($imgUrl); ?>" alt="<?php echo e($ship['name']); ?>" class="mission-unit-icon">
                                            <h4 class="mission-unit-name"><?php echo e($ship['name']); ?></h4>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Vitesse: <?php echo e($ship['speed']); ?></span>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Disponible: <?php echo e($ship['quantity']); ?></span>
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
                        <?php else: ?>
                            <div class="mission-empty-notice">
                                <p>Vous n'avez aucun vaisseau d'exploration disponible sur cette planète.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div class="mission-actions">
                        <button type="button" class="mission-btn mission-btn-primary" wire:click="showSummary">
                            <i class="fas fa-check"></i>
                            Continuer
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <!-- Résumé de la mission -->
                <div class="mission-summary">
                    <h3 class="mission-summary-title">Résumé de la mission d'exploration</h3>

                    <div class="mission-summary-items">
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Planète de départ</span>
                            <span class="mission-summary-value"><?php echo e($planet->name); ?> [<?php echo e($planet->templatePlanet->galaxy); ?>:<?php echo e($planet->templatePlanet->system); ?>:<?php echo e($planet->templatePlanet->position); ?>]</span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Planète de destination</span>
                            <span class="mission-summary-value">[<?php echo e($targetCoordinates); ?>]</span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Type de mission</span>
                            <span class="mission-summary-value">Exploration</span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Vaisseaux envoyés</span>
                            <span class="mission-summary-value"><?php echo e($totalShipsSelected); ?></span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Durée du voyage</span>
                            <span class="mission-summary-value"><?php echo e(gmdate('H:i:s', $travelDurationSeconds)); ?></span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Temps d'exploration</span>
                            <span class="mission-summary-value"><?php echo e(gmdate('H:i:s', $explorationDurationSeconds)); ?></span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Durée totale</span>
                            <span class="mission-summary-value"><?php echo e(gmdate('H:i:s', $totalDurationSeconds)); ?></span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Consommation de carburant</span>
                            <span class="mission-summary-value">
                                <img src="<?php echo e(asset('images/resources/deuterium.png')); ?>" alt="Deutérium" class="resource-icon">
                                <?php echo e(number_format($fuelCost)); ?>

                            </span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Heure d'arrivée</span>
                            <span class="mission-summary-value"><?php echo e(now()->addSeconds($travelDurationSeconds)->format('d/m/Y H:i:s')); ?></span>
                        </div>

                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Heure de retour</span>
                            <span class="mission-summary-value"><?php echo e(now()->addSeconds($totalDurationSeconds)->format('d/m/Y H:i:s')); ?></span>
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
            <?php endif; ?>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/mission/mission-explore.blade.php ENDPATH**/ ?>