<div page="mission">
    <div class="mission-container">
        <div class="mission-header">
            <h2 class="mission-title">
                <i class="fas fa-shield-alt mission-type-attack"></i>
                Mission d'Attaque Terrestre
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
                            <p class="mission-form-help">Aucune équipe terrestre active sur cette planète.</p>
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
                    
                    <!-- Sélection des unités terrestres -->
                    <div class="mission-form-group">
                        <label class="mission-form-label">Sélection des unités terrestres</label>
                        
                        <?php if(count($availableUnits) > 0): ?>
                            <div class="mission-units-selection">
                                <?php $__currentLoopData = $availableUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="mission-unit-item">
                                        <div class="mission-unit-header">
                                            <?php $unitImg = $unit['icon_url'] ?? asset('images/units/' . $unit['image']); ?>
                                            <img src="<?php echo e($unitImg); ?>" alt="<?php echo e($unit['name']); ?>" class="mission-unit-icon">
                                            <h4 class="mission-unit-name"><?php echo e($unit['name']); ?></h4>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Attaque: <?php echo e($unit['attack']); ?></span>
                                            <span>Défense: <?php echo e($unit['defense']); ?></span>
                                        </div>
                                        <div class="mission-unit-details">
                                            <span>Disponible: <?php echo e(number_format($unit['quantity'])); ?></span>
                                            <span>Cargo: <?php echo e(number_format($unit['cargo_capacity']) ?? 0); ?></span>
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
                        <?php else: ?>
                            <div class="mission-empty-notice">
                                <p>Vous n'avez aucune unité terrestre de combat disponible sur cette planète.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Informations sur la sélection -->
                    <?php if($totalUnitsSelected > 0): ?>
                        <div class="mission-selection-info">
                            <div class="mission-info-item">
                                <span class="info-label">Unités sélectionnées:</span>
                                <span class="info-value"><?php echo e(number_format($totalUnitsSelected)); ?></span>
                            </div>
                            <div class="mission-info-item">
                                <span class="info-label">Capacité de transport totale:</span>
                                <span class="info-value"><?php echo e(number_format($totalCargoCapacity)); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Actions -->
                    <div class="mission-actions">
                        <?php if(count($availableUnits) > 0): ?>
                            <button type="button" class="mission-btn mission-btn-attack" wire:click="showSummary" <?php if($attackInProgress): ?> disabled <?php endif; ?>>
                                <i class="fas fa-paper-plane"></i>
                                Continuer
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
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
                            <span class="mission-summary-value"><?php echo e($targetGalaxy); ?>:<?php echo e($targetSystem); ?>:<?php echo e($targetPosition); ?></span>
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
                        
                            <?php if($targetPlanet->user && $targetPlanet->user->alliance): ?>
                                <div class="mission-summary-item">
                                    <span class="mission-summary-label">Alliance</span>
                                    <span class="mission-summary-value"><?php echo e($targetPlanet->user->alliance->name); ?> [<?php echo e($targetPlanet->user->alliance->tag); ?>]</span>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Unités envoyées</span>
                            <span class="mission-summary-value"><?php echo e(number_format($totalUnitsSelected)); ?></span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Capacité de transport</span>
                            <span class="mission-summary-value"><?php echo e(number_format($totalCargoCapacity)); ?></span>
                        </div>
                        
                        <div class="mission-summary-item">
                            <span class="mission-summary-label">Type de combat</span>
                            <span class="mission-summary-value">Combat instantané</span>
                        </div>
                    </div>
                </div>
                
                <!-- Indicateur d'attaque en cours -->
                <?php if($attackInProgress): ?>
                    <div class="mission-attack-progress">
                        <div class="attack-progress-content">
                            <div class="spinner"></div>
                            <h3>Attaque en cours...</h3>
                            <p>Veuillez patienter pendant que le combat se déroule</p>
                        </div>
                    </div>
                <?php else: ?>
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
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/mission/earth.blade.php ENDPATH**/ ?>