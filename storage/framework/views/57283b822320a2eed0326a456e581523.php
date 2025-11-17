<div page="mission">
    <div class="mission-container">
        <div class="mission-header">
            <h2 class="mission-title">
                <i class="fas fa-exchange-alt mission-type-transport"></i>
                Mission de Transport
            </h2>
        </div>
        
        <div class="mission-content">
            <?php if(!$showMissionSummary): ?>
                <div class="mission-form">
                    <!-- Planète cible -->
                    <div class="mission-form-group">
                        <label class="mission-form-label"><i class="fas fa-globe-americas"></i> Planète cible</label>
                        <p class="mission-form-help">Informations sur la planète de destination</p>
                        
                        <!-- Informations sur la planète cible -->
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
                                    <span class="detail-value"><?php echo e($targetPlanet->user ? $targetPlanet->user->name : 'Planète non colonisée'); ?></span>
                                </div>
                                <?php if($targetPlanet->user && $targetPlanet->user->alliance): ?>
                                    <div class="mission-target-detail">
                                        <span class="detail-label">Alliance</span>
                                        <span class="detail-value">[<?php echo e($targetPlanet->user->alliance->tag); ?>] <?php echo e($targetPlanet->user->alliance->name); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sélection des vaisseaux -->
                    <div class="mission-form-group">
                        <label class="mission-form-label"><i class="fas fa-rocket"></i> Sélection des vaisseaux</label>
                        <p class="mission-form-help">Choisissez les vaisseaux à envoyer pour cette mission</p>
                        
                        <?php if(count($availableShips) > 0): ?>
                            <div class="mission-units-selection">
                                <?php $__currentLoopData = $availableShips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="mission-unit-item" style="--i: <?php echo e($loop->index); ?>" wire:key="ship-<?php echo e($ship['id']); ?>">
                                        <div class="mission-unit-header">
                                            <img src="<?php echo e($ship['icon_url'] ?? asset('images/ships/' . $ship['image'])); ?>" alt="<?php echo e($ship['name']); ?>" class="mission-unit-icon">
                                            <h4 class="mission-unit-name"><?php echo e($ship['name']); ?></h4>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Capacité: <?php echo e(number_format($ship['capacity'])); ?></span>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Vitesse: <?php echo e(number_format($ship['speed'])); ?></span>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Disponible: <?php echo e(number_format($ship['quantity'])); ?></span>
                                        </div>
                                        <div class="mission-unit-quantity">
                                            <div class="mission-unit-buttons">
                                                <input type="number" class="mission-unit-input"
                                                       wire:model.lazy="selectedShips.<?php echo e($ship['id']); ?>"
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
                                <i class="fas fa-exclamation-triangle"></i>
                                <p>Vous n'avez aucun vaisseau de transport disponible sur cette planète.</p>
                                <a href="<?php echo e(route('game.construction.type', ['type' => 'ship'])); ?>" class="mission-btn mission-btn-secondary">
                                    <i class="fas fa-industry"></i> Aller au chantier spatial
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Sélection des ressources -->
                    <div class="mission-form-group">
                        <label class="mission-form-label"><i class="fas fa-boxes"></i> Ressources à transporter</label>
                        <p class="mission-form-help">Sélectionnez les ressources que vous souhaitez envoyer</p>
                        
                        <!-- Informations sur la capacité -->
                        <div class="mission-capacity-info">
                            <h4 class="capacity-title"><i class="fas fa-box-open"></i> Capacité de transport</h4>
                            <div class="mission-capacity-bar">
                                <div class="mission-capacity-progress" style="width: <?php echo e($totalCapacity > 0 ? min(100, ($usedCapacity / $totalCapacity) * 100) : 0); ?>%"></div>
                            </div>
                            <div class="mission-capacity-text">
                                <span><?php echo e(number_format($usedCapacity)); ?> / <?php echo e(number_format($totalCapacity)); ?></span>
                                <span><?php echo e($totalCapacity > 0 ? number_format(min(100, ($usedCapacity / $totalCapacity) * 100), 1) : 0); ?>%</span>
                            </div>
                        </div>
                        
                        <!-- Aperçu des ressources sélectionnées -->
                        <div class="mission-resources-preview">
                            <h4 class="resources-preview-title"><i class="fas fa-luggage-cart"></i> Ressources sélectionnées</h4>
                            <div class="mission-resources-preview-items">
                                <?php
                                    $hasSelectedResources = false;
                                    foreach ($resourcesForTransport as $resourceId => $amount) {
                                        if ($amount > 0) {
                                            $hasSelectedResources = true;
                                            break;
                                        }
                                    }
                                ?>
                                
                                <?php if($hasSelectedResources): ?>
                                    <?php $__currentLoopData = $planet->resources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planetResource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php 
                                            $resourceId = $planetResource->resource_id;
                                            $resourceIcon = $planetResource->resource->icon;
                                            $resourceDisplayName = $planetResource->resource->display_name;
                                        ?>
                                        <?php if(isset($resourcesForTransport[$resourceId]) && $resourcesForTransport[$resourceId] > 0): ?>
                                            <div class="mission-resource-preview-item">
                                                <img src="<?php echo e(asset('images/resources/' . $resourceIcon)); ?>" alt="<?php echo e($resourceDisplayName); ?>" class="mission-resource-preview-icon">
                                                <span class="mission-resource-preview-amount"><?php echo e(number_format($resourcesForTransport[$resourceId])); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <div class="mission-resources-preview-empty">
                                        <i class="fas fa-info-circle"></i>
                                        <span>Aucune ressource sélectionnée</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Formulaire de sélection des ressources -->
                        <div class="mission-resources-selection">
                            <?php $resourceIndex = 0; ?>
                            <?php $__currentLoopData = $planet->resources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planetResource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php 
                                    $resourceId = $planetResource->resource_id;
                                    $resourceDisplayName = $planetResource->resource->display_name;
                                    $resourceIcon = $planetResource->resource->icon;
                                ?>
                                <!-- Sélection de <?php echo e($resourceDisplayName); ?> -->
                                <div class="mission-resource-item" style="--i: <?php echo e($resourceIndex); ?>">
                                    <!-- En-tête avec icône et nom -->
                                    <div class="mission-resource-header">
                                        <img src="<?php echo e(asset('images/resources/' . $resourceIcon)); ?>" alt="<?php echo e($resourceDisplayName); ?>" class="mission-resource-icon">
                                        <h4 class="mission-resource-name"><?php echo e($resourceDisplayName); ?></h4>
                                    </div>
                                    <!-- Affichage du montant disponible -->
                                    <div class="mission-resource-details">
                                        <span>Disponible:</span>
                                        <span><?php echo e(number_format($planetResource->current_amount ?? 0)); ?></span>
                                    </div>
                                    
                                    <div class="mission-unit-quantity">
                                        <div class="mission-unit-buttons">
                                            <input type="number" class="mission-unit-input"
                                                   wire:model.lazy="resourcesForTransport.<?php echo e($resourceId); ?>"
                                                   wire:change="updateResourceAmount(<?php echo e($resourceId); ?>, $event.target.value)"
                                                   min="0" max="<?php echo e($planetResource->current_amount ?? 0); ?>">
                                            <button type="button" class="btn-clear" wire:click="setClearResource(<?php echo e($resourceId); ?>)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <button type="button" class="btn-max" wire:click="setMaxResource(<?php echo e($resourceId); ?>)">
                                                <i class="fas fa-angle-double-up"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php $resourceIndex++; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
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
                    <h3 class="mission-summary-title"><i class="fas fa-clipboard-check"></i> Résumé de la mission de transport</h3>
                    
                    <div class="mission-summary-items">
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Planète de départ</span>
                            <span class="mission-summary-value"><?php echo e($planet->name); ?> [<?php echo e($planet->templatePlanet->galaxy); ?>:<?php echo e($planet->templatePlanet->system); ?>:<?php echo e($planet->templatePlanet->position); ?>]</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Planète de destination</span>
                            <span class="mission-summary-value"><?php echo e($targetPlanet->name); ?> [<?php echo e($targetGalaxy); ?>:<?php echo e($targetSystem); ?>:<?php echo e($targetPosition); ?>]</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Propriétaire</span>
                            <span class="mission-summary-value"><?php echo e($targetPlanet->user ? $targetPlanet->user->name : 'Planète non colonisée'); ?></span>
                        </div>
                        
                        <?php if($targetPlanet->user && $targetPlanet->user->alliance): ?>
                            <div class="mission-summary-item">
                                <span class="mission-summary-label">Alliance</span>
                                <span class="mission-summary-value"><?php echo e($targetPlanet->user->alliance->name); ?> [<?php echo e($targetPlanet->user->alliance->tag); ?>]</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Type de mission</span>
                            <span class="mission-summary-value mission-type-transport"><i class="fas fa-exchange-alt"></i> Transport</span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Vaisseaux envoyés</span>
                            <span class="mission-summary-value"><?php echo e(number_format($totalShipsSelected)); ?></span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Ressources transportées</span>
                            <span class="mission-summary-value">
                                <div class="mission-summary-resources">
                                    <?php $__currentLoopData = $planet->resources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planetResource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php 
                                            $resourceId = $planetResource->resource_id;
                                            $resourceDisplayName = $planetResource->resource->display_name;
                                            $resourceIcon = $planetResource->resource->icon;
                                        ?>
                                        <?php if(isset($resourcesForTransport[$resourceId]) && $resourcesForTransport[$resourceId] > 0): ?>
                                            <div class="mission-summary-resource">
                                                <img src="<?php echo e(asset('images/resources/' . $resourceIcon)); ?>" alt="<?php echo e($resourceDisplayName); ?>" class="mission-summary-resource-icon">
                                                <span><?php echo e(number_format($resourcesForTransport[$resourceId])); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Durée du voyage</span>
                            <span class="mission-summary-value"><i class="fas fa-clock"></i> <?php echo e(gmdate('H:i:s', $missionDuration)); ?></span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Heure d'arrivée</span>
                            <span class="mission-summary-value"><i class="fas fa-calendar-check"></i> <?php echo e(now()->addSeconds($missionDuration)->format('d/m/Y H:i:s')); ?></span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Heure de retour</span>
                            <span class="mission-summary-value"><i class="fas fa-calendar-day"></i> <?php echo e(now()->addSeconds($missionDuration * 2)->format('d/m/Y H:i:s')); ?></span>
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
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/mission/transport.blade.php ENDPATH**/ ?>