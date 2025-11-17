<div page="mission">
    <div class="mission-container">
        <div class="mission-header">
            <h2 class="mission-title">
                <i class="fas fa-flag mission-type-colonize"></i>
                Mission de Colonisation
            </h2>
        </div>
        
        <div class="mission-content">
            <?php if(!$showMissionSummary): ?>
                <div class="mission-form">
                    <!-- Planète cible -->
                    <div class="mission-form-group">
                        <label class="mission-form-label">Planète à coloniser</label>
                        <p class="mission-form-help">Informations sur la planète à coloniser</p>
                        
                        <?php if($targetPlanet && $targetPlanet->user): ?>
                            <div class="mission-error-message">
                                <i class="fas fa-exclamation-triangle"></i>
                                Cette position est déjà occupée par une planète colonisée.
                            </div>
                        <?php elseif($targetPlanetTemplate): ?>
                            <!-- Informations sur la planète à coloniser -->
                            <div class="mission-target-info">
                                <h4>Informations sur la planète</h4>
                                <div class="mission-target-details">
                                    <div class="mission-target-detail">
                                        <span class="detail-label">Nom:</span>
                                        <span class="detail-value"><?php echo e($targetPlanetTemplate->name); ?></span>
                                    </div>
                                    <div class="mission-target-detail">
                                        <span class="detail-label">Coordonnées:</span>
                                        <span class="detail-value">[<?php echo e($targetGalaxy); ?>:<?php echo e($targetSystem); ?>:<?php echo e($targetPosition); ?>]</span>
                                    </div>
                                    <div class="mission-target-detail">
                                        <span class="detail-label">Statut:</span>
                                        <span class="detail-value">Planète libre</span>
                                    </div>
                                </div>
                            </div>
                        <?php elseif($templateId): ?>
                            <div class="mission-error-message">
                                <i class="fas fa-exclamation-triangle"></i>
                                Aucune planète n'existe avec ce template.
                            </div>
                        <?php endif; ?>
                        
                        <?php if($userPlanetCount >= $maxPlanets): ?>
                            <div class="mission-error-message">
                                <i class="fas fa-exclamation-triangle"></i>
                                Vous avez atteint le nombre maximum de planètes (<?php echo e($maxPlanets); ?>).
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Sélection des vaisseaux -->
                    <div class="mission-form-group">
                        <label class="mission-form-label">Sélection des vaisseaux de colonisation</label>
                        
                        <?php if(count($availableShips) > 0): ?>
                            <div class="mission-units-selection">
                                <?php $__currentLoopData = $availableShips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="mission-unit-item">
                                        <div class="mission-unit-header">
                                            <img src="<?php echo e(asset('images/ships/' . $ship['image'])); ?>" alt="<?php echo e($ship['name']); ?>" class="mission-unit-icon">
                                            <h4 class="mission-unit-name"><?php echo e($ship['name']); ?></h4>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Vitesse: <?php echo e($ship['speed']); ?></span>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Disponible: <?php echo e($ship['quantity']); ?></span>
                                        </div>
                                        <div class="mission-unit-quantity">
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="number" class="mission-unit-input w-100"
                                                       wire:model.live="selectedShips.<?php echo e($ship['id']); ?>"
                                                       wire:change="updateShipSelection"
                                                       min="0" max="<?php echo e(min(1, $ship['quantity'])); ?>">
                                                <button type="button" class="mission-btn mission-btn-secondary btn-sm"
                                                        wire:click="setMaxShips(<?php echo e($ship['id']); ?>)">
                                                    Max
                                                </button>
                                                <button type="button" class="mission-btn mission-btn-danger btn-sm"
                                                        wire:click="setClearShips(<?php echo e($ship['id']); ?>)">
                                                    Clear
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <p class="mission-form-help">Un seul vaisseau de colonisation est nécessaire pour cette mission.</p>
                        <?php else: ?>
                            <div class="mission-empty-notice">
                                <p>Vous n'avez aucun vaisseau de colonisation disponible sur cette planète.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Actions -->
                    <div class="mission-actions">
                        <button type="button" class="mission-btn mission-btn-primary" wire:click="showSummary" 
                            <?php if(!$canContinue): ?> disabled <?php endif; ?>>
                            <i class="fas fa-check"></i>
                            Continuer
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <!-- Résumé de la mission -->
                <div class="mission-summary">
                    <h3 class="mission-summary-title">Résumé de la mission de colonisation</h3>
                    
                    <div class="mission-summary-items">
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Planète de départ</span>
                            <span class="mission-summary-value"><?php echo e($planet->name); ?> [<?php echo e($planet->templatePlanet->galaxy); ?>:<?php echo e($planet->templatePlanet->system); ?>:<?php echo e($planet->templatePlanet->position); ?>]</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Coordonnées de destination</span>
                            <span class="mission-summary-value">[<?php echo e($targetGalaxy); ?>:<?php echo e($targetSystem); ?>:<?php echo e($targetPosition); ?>]</span>
                        </div>
                        
                        <?php if($targetPlanet): ?>
                            <div class="mission-summary-item">
                                <span class="mission-summary-label">Planète à coloniser</span>
                                <span class="mission-summary-value"><?php echo e($targetPlanet->name); ?></span>
                            </div>
                            
                            <div class="mission-summary-item">
                                <span class="mission-summary-label">Taille</span>
                                <span class="mission-summary-value"><?php echo e($targetPlanet->fields); ?> cases</span>
                            </div>
                        <?php endif; ?>
                        
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
                            <span class="mission-summary-value"><?php echo e(gmdate('H:i:s', $missionDuration)); ?></span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Heure d'arrivée</span>
                            <span class="mission-summary-value"><?php echo e(now()->addSeconds($missionDuration)->format('d/m/Y H:i:s')); ?></span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Consommation de carburant</span>
                            <span class="mission-summary-value">
                                <div class="mission-summary-resource">
                                    <img src="<?php echo e(asset('images/resources/deuterium.png')); ?>" alt="Deutérium" class="mission-summary-resource-icon">
                                    <span><?php echo e(number_format($fuelConsumption)); ?></span>
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
            <?php endif; ?>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/mission/colonize.blade.php ENDPATH**/ ?>