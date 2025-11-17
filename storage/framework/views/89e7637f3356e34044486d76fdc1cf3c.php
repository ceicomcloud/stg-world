<div page="mission">
    <div class="mission-container">
        <div class="mission-header">
            <h2 class="mission-title">
                <i class="fas fa-fighter-jet mission-type-attack"></i>
                Mission d'Attaque Spatiale
            </h2>
        </div>
        
        <div class="mission-content">
            <?php if(!$showMissionSummary): ?>
                <div class="mission-form">
                    <!-- Sélection rapide par équipe -->
                    <div class="mission-form-group">
                        <label class="mission-form-label">Utiliser une équipe</label>
                        <div class="mission-team-selector">
                            <select class="mission-form-control" wire:model.live="selectedTeamId">
                                <option value="">Choisir une équipe…</option>
                                <?php $__currentLoopData = $equipTeams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($team['id']); ?>">#<?php echo e($team['team_index']); ?> — <?php echo e($team['label']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <button type="button" class="mission-btn mission-btn-secondary" wire:click="applySelectedTeam">
                                <i class="fas fa-users-gear"></i>
                                Appliquer
                            </button>
                        </div>
                        <?php if(empty($equipTeams)): ?>
                            <p class="mission-form-help">Aucune équipe spatiale active sur cette planète.</p>
                        <?php endif; ?>
                    </div>
                    <!-- Coordonnées de la cible -->
                    <div class="mission-form-group">
                        <!-- Informations sur la planète cible -->
                        <div class="mission-target-info">
                            <h4>Informations sur la planète cible</h4>
                            <div class="mission-target-details">
                                <div class="mission-target-detail">
                                    <span class="detail-label">Nom:</span>
                                    <span class="detail-value"><?php echo e($targetPlanet->name); ?></span>
                                </div>
                                <div class="mission-target-detail">
                                    <span class="detail-label">Coordonnées:</span>
                                    <span class="detail-value">[<?php echo e($targetGalaxy); ?>:<?php echo e($targetSystem); ?>:<?php echo e($targetPosition); ?>]</span>
                                </div>
                                <div class="mission-target-detail">
                                    <span class="detail-label">Propriétaire:</span>
                                    <span class="detail-value"><?php echo e($targetPlanet->user ? $targetPlanet->user->name : 'Planète non colonisée'); ?></span>
                                </div>
                                <?php if($targetPlanet->user && $targetPlanet->user->alliance): ?>
                                    <div class="mission-target-detail">
                                        <span class="detail-label">Alliance:</span>
                                        <span class="detail-value">[<?php echo e($targetPlanet->user->alliance->tag); ?>] <?php echo e($targetPlanet->user->alliance->name); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sélection des vaisseaux -->
                    <div class="mission-form-group">
                        <label class="mission-form-label">Sélection des vaisseaux</label>
                        
                        <?php if(count($availableShips) > 0): ?>
                            <div class="mission-units-selection">
                                <?php $__currentLoopData = $availableShips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="mission-unit-item">
                                        <div class="mission-unit-header">
                                            <?php $shipImg = $ship['icon_url'] ?? asset('images/ships/' . $ship['image']); ?>
                                            <img src="<?php echo e($shipImg); ?>" alt="<?php echo e($ship['name']); ?>" class="mission-unit-icon">
                                            <h4 class="mission-unit-name"><?php echo e($ship['name']); ?></h4>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Attaque: <?php echo e($ship['attack']); ?></span>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Défense: <?php echo e($ship['defense']); ?></span>
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
                                <p>Vous n'avez aucun vaisseau de combat disponible sur cette planète.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Actions -->
                    <div class="mission-actions">
                        <?php if(count($availableShips) > 0): ?>
                            <button type="button" class="mission-btn mission-btn-attack" wire:click="showSummary">
                                <i class="fas fa-paper-plane"></i>
                                Continuer
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
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
                            <span class="mission-summary-value"><?php echo e($targetPlanet->templatePlanet->galaxy); ?>:<?php echo e($targetPlanet->templatePlanet->system); ?>:<?php echo e($targetPlanet->templatePlanet->position); ?></span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Planète cible</span>
                            <span class="mission-summary-value"><?php echo e($targetPlanet->name); ?></span>
                        </div>
                    
                        <?php if($targetPlanet): ?>
                            <div class="mission-summary-item">
                                <span class="mission-summary-label">Planète cible</span>
                                <span class="mission-summary-value"><?php echo e($targetPlanet->name); ?></span>
                            </div>
                            
                            <div class="mission-summary-item">
                                <span class="mission-summary-label">Propriétaire</span>
                                <span class="mission-summary-value"><?php echo e($targetPlanet->user ? $targetPlanet->user->name : 'Planète non colonisée'); ?></span>
                            </div>
                        <?php endif; ?>
                    
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Vaisseaux envoyés</span>
                            <span class="mission-summary-value"><?php echo e($totalShipsSelected); ?></span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Vitesse de la flotte</span>
                            <span class="mission-summary-value"><?php echo e($this->calculateSpeed()); ?></span>
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
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Durée du voyage</span>
                            <span class="mission-summary-value"><i class="fas fa-clock"></i> <?php echo e(gmdate('H:i:s', $missionDuration)); ?></span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Heure d'arrivée</span>
                            <span class="mission-summary-value"><?php echo e(\Carbon\Carbon::now()->addMinutes($missionDuration)->format('d/m/Y H:i:s')); ?></span>
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
            <?php endif; ?>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/mission/spatial.blade.php ENDPATH**/ ?>