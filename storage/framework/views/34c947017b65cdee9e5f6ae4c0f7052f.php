<div page="inventory">
    <div class="inventory-container">
        <div class="inventory-header">
            <h1 class="inventory-title"><i class="fas fa-boxes"></i> Inventaire</h1>
            <p class="inventory-subtitle">Gérez vos articles, sélectionnez une planète, et utilisez-les avec confirmation.</p>
        </div>

        <div class="management-section">
            <div class="section-header">
                <h3><i class="fas fa-globe"></i> Planète cible</h3>
            </div>
            <div class="planet-selector">
                <label for="planetSelect">Choisir une planète</label>
                <select id="planetSelect" class="form-select" wire:change="setSelectedPlanet($event.target.value)">
                    <?php $__currentLoopData = $planets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p['id']); ?>" <?php echo e($selectedPlanetId == $p['id'] ? 'selected' : ''); ?>>
                            <?php echo e($p['name']); ?> <?php echo e($p['is_main'] ? '• Principale' : ''); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <p class="planet-description" style="margin-top:0.5rem; font-style: italic;">
                    Certaines utilisations (packs de ressources, unités/défenses/vaisseaux, boosts) s’appliquent à la planète sélectionnée.
                </p>
            </div>
        </div>

        <div class="management-section">
            <div class="section-header">
                <h3><i class="fas fa-archive"></i> Articles possédés</h3>
            </div>
            <div class="items-list">
                <?php $__empty_1 = true; $__currentLoopData = $inventories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="item-card">
                        <div class="item-header">
                            <div class="item-art">
                                <i class="<?php echo e($inv['icon'] ?: 'fas fa-box'); ?>"></i>
                            </div>
                            <div class="item-name"><?php echo e($inv['name']); ?></div>
                        </div>

                        <div class="item-desc"><?php echo e($inv['description'] ?? '—'); ?></div>

                        <div class="item-action">
                            <?php if($inv['usable'] && $inv['quantity'] > 0): ?>
                                <button class="use-button"
                                    wire:click="useItem(<?php echo e($inv['id']); ?>)"
                                    wire:confirm="Confirmer l'utilisation de <?php echo e(addslashes($inv['name'])); ?> ?">
                                    <i class="fas fa-play"></i> Utiliser
                                </button>
                            <?php else: ?>
                                <button class="use-button disabled" disabled>
                                    <i class="fas fa-ban"></i> Non utilisable
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="item-footer">
                            <div class="item-meta">
                                <span class="badge rarity-<?php echo e($inv['rarity']); ?>"><?php echo e(ucfirst($inv['rarity'])); ?></span>
                                <?php if($inv['duration_seconds']): ?>
                                    <span class="badge">Durée: <?php echo e(floor($inv['duration_seconds']/3600)); ?>h</span>
                                <?php endif; ?>
                            </div>
                            <span class="badge quantity">x<?php echo e(number_format($inv['quantity'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <span>Aucun article dans votre inventaire pour le moment.</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/inventory.blade.php ENDPATH**/ ?>