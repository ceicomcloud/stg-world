<div class="game-resource-panel" layout="gameresource">
    <div class="resource-header">
        <div class="planet-selector">
            <div class="planet-dropdown" x-data="{ open: false }" x-on:click.outside="open = false">
                <button class="planet-button" x-on:click="open = !open" :class="{ 'open': open }">
                    <i class="fas fa-globe"></i>
                    <span><?php echo e($planet['name'] ?? 'Sélectionner une planète'); ?></span>
                    <i class="fas fa-chevron-down dropdown-icon" :class="{ 'rotated': open }"></i>
                </button>

                <div class="planet-options" x-show="open" x-transition>
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $availablePlanets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planetOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="planet-option <?php echo e(isset($planet['id']) && $planetOption['id'] === $planet['id'] ? 'active' : ''); ?>" wire:click="selectPlanet(<?php echo e(json_encode($planetOption)); ?>)" x-on:click="open = false">
                        <div class="planet-option-info">
                            <i class="fas fa-globe"></i>
                            <div class="planet-details">
                                <span class="planet-name"><?php echo e($planetOption['name']); ?></span>
                                <span class="planet-coords"><?php echo e($planetOption['description']); ?></span>
                            </div>
                        </div>
                        <div class="planet-type-badge <?php echo e($planetOption['is_main_planet'] ? 'main-planet' : 'secondary-planet'); ?>">
                            <?php echo e($planetOption['is_main_planet'] ? 'Principale' : 'Secondaire'); ?>

                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>
    </div>

    <div class="resource-list">
        <!-- Primary Resources -->
        <div class="resource-category">
            <h4 class="category-title">Ressources Primaires</h4>

            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $primaryResources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="resource-item">
                <div class="resource-icon">
                    <img src="/images/resources/<?php echo e($resource['icon']); ?>" alt="<?php echo e($resource['name']); ?>" />
                </div>
                <div class="resource-info">
                    <span class="resource-name"><?php echo e($resource['name']); ?></span>
                    <div class="resource-values">
                        <span class="current-amount"><?php echo e(number_format($resource['current_amount'])); ?></span>
                        <span class="production-rate <?php echo e($resource['production_rate'] > 0 ? 'positive' : ($resource['production_rate'] < 0 ? 'negative' : 'neutral')); ?>">
                            <!--[if BLOCK]><![endif]--><?php if($resource['production_rate'] > 0): ?>
                                <i class="fas fa-arrow-up"></i>
                            <?php elseif($resource['production_rate'] < 0): ?>
                                <i class="fas fa-arrow-down"></i>
                            <?php else: ?>
                                <i class="fas fa-minus"></i>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <?php echo e(number_format(abs($resource['production_rate']))); ?>/h
                        </span>
                    </div>
                </div>
                <div class="resource-storage">
                    <div class="storage-bar">
                        <div class="storage-fill" style="width: <?php echo e($resource['storage_capacity'] > 0 ? ($resource['current_amount'] / $resource['storage_capacity'] * 100) : 0); ?>%; background-color: <?php echo e(($resource['storage_capacity'] > 0 ? ($resource['current_amount'] / $resource['storage_capacity'] * 100) : 0) > 90 ? '#e74c3c' : (($resource['storage_capacity'] > 0 ? ($resource['current_amount'] / $resource['storage_capacity'] * 100) : 0) > 70 ? '#f39c12' : '#27ae60')); ?>"></div>
                    </div>
                    <span class="storage-text"><?php echo e(number_format($resource['storage_capacity'])); ?></span>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>

    <!-- Resource Summary -->
    <div class="resource-summary">
        <div class="summary-item">
            <i class="fas fa-flask"></i>
            <span class="summary-label">Points de Recherche</span>
            <span class="summary-value research">+<?php echo e(number_format($researchPointsProduction)); ?>/h</span>
        </div>

        <div class="summary-item">
            <i class="fas fa-bolt"></i>
            <span class="summary-label">Consommation Énergie</span>
            <span class="summary-value negative">-<?php echo e(number_format($totalEnergyConsumption)); ?>/h</span>
        </div>

        <div class="summary-item">
            <i class="fas fa-battery-half"></i>
            <span class="summary-label">Énergie Restante</span>
            <span class="summary-value neutral"><?php echo e(number_format($totalEnergyRemaining)); ?>/h</span>
        </div>

        <div class="summary-item">
            <i class="fas fa-coins"></i>
            <span class="summary-label">Or</span>
            <span class="summary-value neutral"><?php echo e(number_format(Auth::user()->gold_balance ?? 0)); ?></span>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="resource-actions">
        <a href="<?php echo e(route('game.trade')); ?>" wire:navigate.hover class="action-btn primary">
            <i class="fas fa-exchange-alt"></i>
            Commerce
        </a>

        <a href="<?php echo e(route('game.manage-planet')); ?>" wire:navigate.hover class="action-btn secondary">
            <i class="fas fa-cogs"></i>
            Gestion
        </a>
    </div>
    <div class="resource-actions">
        <a href="<?php echo e(route('game.manage-players')); ?>" wire:navigate.hover class="action-btn secondary">
            <i class="fas fa-user-cog"></i>
            Gestion Joueurs
        </a>
        <a href="<?php echo e(route('game.inventory')); ?>" wire:navigate.hover class="action-btn secondary">
            <i class="fas fa-boxes"></i>
            Inventaire
        </a>
    </div>
    <div class="resource-actions">
        <a href="<?php echo e(route('dashboard.index')); ?>" wire:navigate.hover class="action-btn secondary">
            <i class="fas fa-home"></i>
            Comptes
        </a>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/resource.blade.php ENDPATH**/ ?>