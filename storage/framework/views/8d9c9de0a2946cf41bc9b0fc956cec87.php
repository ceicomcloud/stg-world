<div>
    <!-- Indicateur de type -->
    <div class="modal-type-indicator modal-type-<?php echo e($buildingData['type']); ?>">
        <i class="fas fa-<?php echo e($this->getTypeIcon()); ?>"></i>
        <?php echo e($this->getTypeLabel()); ?>

    </div>

    <!-- Image -->
    <!--[if BLOCK]><![endif]--><?php if($buildingData['icon']): ?>
        <?php
            $imagePath = match($buildingData['type']) {
                'unit' => 'images/units/',
                'defense' => 'images/defenses/',
                'ship' => 'images/ships/',
                default => 'images/buildings/',
            };
        ?>
        <img src="<?php echo e(asset($imagePath . $buildingData['icon'])); ?>"  alt="<?php echo e($buildingData['label']); ?>" class="modal-image">
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Description -->
    <!--[if BLOCK]><![endif]--><?php if($buildingData['description']): ?>
        <div class="modal-description">
            <?php echo e($buildingData['description']); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Niveau ou Quantité actuel -->
    <!--[if BLOCK]><![endif]--><?php if($buildingData['is_quantity_based']): ?>
        <div class="modal-quantity-info">
            <i class="fas fa-cubes"></i>
            <span class="quantity-text">Quantité actuelle:</span>
            <span class="quantity-value"><?php echo e(number_format($buildingQuantity)); ?></span>
        </div>
    <?php else: ?>
        <div class="modal-level-info">
            <i class="fas fa-layer-group"></i>
            <span class="level-text">Niveau actuel:</span>
            <span class="level-value"><?php echo e($buildingLevel); ?> / <?php echo e($buildingData['max_level']); ?></span>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Statistiques pour unités, défenses et vaisseaux -->
    <!--[if BLOCK]><![endif]--><?php if(in_array($buildingData['type'], ['unit', 'defense', 'ship'])): ?>
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-chart-bar"></i>
                Statistiques
            </h3>
            <div class="stats-grid">
                <!--[if BLOCK]><![endif]--><?php if($buildingData['life'] > 0): ?>
                    <div class="stat-item">
                        <i class="fas fa-heart"></i>
                        <span class="stat-label">Vie:</span>
                        <span class="stat-value"><?php echo e(number_format($buildingData['life'])); ?></span>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <!--[if BLOCK]><![endif]--><?php if($buildingData['attack_power'] > 0): ?>
                    <div class="stat-item">
                        <i class="fas fa-sword"></i>
                        <span class="stat-label">Attaque:</span>
                        <span class="stat-value"><?php echo e(number_format($buildingData['attack_power'])); ?></span>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <!--[if BLOCK]><![endif]--><?php if($buildingData['shield_power'] > 0): ?>
                    <div class="stat-item">
                        <i class="fas fa-shield"></i>
                        <span class="stat-label">Bouclier:</span>
                        <span class="stat-value"><?php echo e(number_format($buildingData['shield_power'])); ?></span>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <!--[if BLOCK]><![endif]--><?php if($buildingData['speed'] > 0): ?>
                    <div class="stat-item">
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="stat-label">Vitesse:</span>
                        <span class="stat-value"><?php echo e(number_format($buildingData['speed'])); ?></span>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <!--[if BLOCK]><![endif]--><?php if($buildingData['cargo_capacity'] > 0): ?>
                    <div class="stat-item">
                        <i class="fas fa-boxes"></i>
                        <span class="stat-label">Capacité cargo:</span>
                        <span class="stat-value"><?php echo e(number_format($buildingData['cargo_capacity'])); ?></span>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <!--[if BLOCK]><![endif]--><?php if($buildingData['fuel_consumption'] > 0): ?>
                    <div class="stat-item">
                        <i class="fas fa-gas-pump"></i>
                        <span class="stat-label">Consommation:</span>
                        <span class="stat-value"><?php echo e(number_format($buildingData['fuel_consumption'])); ?></span>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Avantages -->
    <!--[if BLOCK]><![endif]--><?php if(count($buildingData['advantages']) > 0): ?>
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-plus-circle"></i>
                Avantages
            </h3>
            <div class="advantages-list">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $buildingData['advantages']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $advantage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="advantage-item">
                        <i class="fas fa-check"></i>
                        <span><?php echo e($advantage['description']); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Désavantages -->
    <!--[if BLOCK]><![endif]--><?php if(count($buildingData['disadvantages']) > 0): ?>
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-minus-circle"></i>
                Désavantages
            </h3>
            <div class="disadvantages-list">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $buildingData['disadvantages']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $disadvantage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="disadvantage-item">
                        <i class="fas fa-times"></i>
                        <span><?php echo e($disadvantage['description']); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Prérequis -->
    <!--[if BLOCK]><![endif]--><?php if(count($buildingData['requirements']) > 0): ?>
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-list-check"></i>
                Prérequis
            </h3>
            <div class="requirements-list">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $buildingData['requirements']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $requirement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isMet = $this->checkRequirement($requirement);
                    ?>
                    <div class="requirement-item <?php echo e($isMet ? 'requirement-met' : 'requirement-not-met'); ?>">
                        <i class="fas fa-<?php echo e($isMet ? 'check' : 'times'); ?>"></i>
                        <span>
                            <?php echo e($requirement['required_build']['label'] ?? $requirement['required_build']['name'] ?? 'Bâtiment requis'); ?>


                            niveau <?php echo e($requirement['required_level']); ?>

                        </span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Actions -->
    <div class="modal-section">
        <h3 class="section-title">
            <i class="fas fa-tools"></i>
            Actions
        </h3>

        <!--[if BLOCK]><![endif]--><?php if(!$buildingData['is_quantity_based'] && $buildingLevel > 0 && $buildingData['type'] === 'building'): ?>
            <div class="action-item">
                <div class="action-header">
                    <i class="fas fa-dumpster-fire"></i>
                    <span>Détruire ce bâtiment</span>
                </div>
                <div class="action-description">
                    Rendu instantané de 50% des coûts cumulés. La capacité de stockage peut limiter le montant ajouté.
                </div>
                <?php ($preview = $this->getBuildingRefundPreview()); ?>
                <!--[if BLOCK]><![endif]--><?php if(count($preview) > 0): ?>
                    <div class="refund-preview">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $preview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="refund-item">
                                <i class="fas fa-coins"></i>
                                <span>+<?php echo e(number_format($item['amount'], 0, ',', ' ')); ?> <?php echo e($item['resource']); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <!--[if BLOCK]><![endif]--><?php if($confirmDestroy): ?>
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
                <?php else: ?>
                    <div class="action-buttons">
                        <button type="button" class="modal-btn modal-btn-danger" wire:click="requestDestroyBuilding">
                            <i class="fas fa-trash"></i> Détruire
                        </button>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php if($buildingData['is_quantity_based'] && $buildingData['type'] === 'unit' && $buildingQuantity > 0): ?>
            <div class="action-item">
                <div class="action-header">
                    <i class="fas fa-users-slash"></i>
                    <span>Supprimer des unités</span>
                </div>
                <div class="action-controls">
                    <label for="remove_qty" class="action-label">Quantité à supprimer</label>
                    <input id="remove_qty" type="number" min="1" max="<?php echo e($buildingQuantity); ?>" class="modal-input" wire:model.lazy="unitsRemoveQuantity" />
                </div>
                <div class="action-description">
                    Rendu instantané de 50% du coût de la quantité sélectionnée.
                </div>
                <?php ($unitPreview = $this->getUnitRefundPreview()); ?>
                <!--[if BLOCK]><![endif]--><?php if(count($unitPreview) > 0): ?>
                    <div class="refund-preview">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $unitPreview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="refund-item">
                                <i class="fas fa-coins"></i>
                                <span>+<?php echo e(number_format($item['amount'], 0, ',', ' ')); ?> <?php echo e($item['resource']); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <!--[if BLOCK]><![endif]--><?php if($confirmRemoveUnits): ?>
                    <div class="confirm-box">
                        <div class="confirm-text">
                            <i class="fas fa-exclamation-triangle"></i>
                            Confirmer la suppression de <?php echo e(number_format($unitsRemoveQuantity)); ?> unité(s) ?
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
                <?php else: ?>
                    <div class="action-buttons">
                        <button type="button" class="modal-btn modal-btn-warning" wire:click="requestRemoveUnits">
                            <i class="fas fa-user-minus"></i> Supprimer
                        </button>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/modal/building-info.blade.php ENDPATH**/ ?>