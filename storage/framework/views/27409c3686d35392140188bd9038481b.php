<div page="empire">
    <div class="empire-container">
        <div class="empire-header">
            <h1 class="empire-title"><i class="fas fa-globe"></i> Empire</h1>
            <p class="empire-subtitle">Vue d’ensemble de vos planètes, ressources et forces</p>
        </div>

        <div class="empire-summary">
            <div class="summary-card">
                <h3><i class="fas fa-coins"></i> Ressources totales</h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <span class="label">Métal</span>
                        <span class="value"><?php echo e(number_format($totals['resources']['metal']['amount'])); ?></span>
                        <span class="sub">+ <?php echo e(number_format($totals['resources']['metal']['prod24h'])); ?> / 24h</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Cristal</span>
                        <span class="value"><?php echo e(number_format($totals['resources']['crystal']['amount'])); ?></span>
                        <span class="sub">+ <?php echo e(number_format($totals['resources']['crystal']['prod24h'])); ?> / 24h</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Deutérium</span>
                        <span class="value"><?php echo e(number_format($totals['resources']['deuterium']['amount'])); ?></span>
                        <span class="sub">+ <?php echo e(number_format($totals['resources']['deuterium']['prod24h'])); ?> / 24h</span>
                    </div>
                </div>
            </div>

            <div class="summary-card">
                <h3><i class="fas fa-bolt"></i> Énergie</h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <span class="label">Production</span>
                        <span class="value"><?php echo e(number_format($totals['energy']['production'])); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Consommation</span>
                        <span class="value"><?php echo e(number_format($totals['energy']['consumption'])); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Net</span>
                        <span class="value"><?php echo e(number_format($totals['energy']['net'])); ?></span>
                    </div>
                </div>
            </div>

            <div class="summary-card">
                <h3><i class="fas fa-gem"></i> Avantage VIP</h3>
                <?php if(auth()->user()->vip_active && (!auth()->user()->vip_until || now()->isBefore(auth()->user()->vip_until))): ?>
                    <div class="vip-action">
                        <label for="targetPlanet">Planète cible</label>
                        <select id="targetPlanet" class="vip-select" wire:model="targetPlanetId">
                            <?php $__currentLoopData = $availablePlanets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <button class="vip-btn" wire:click="consolidateResources">
                            <i class="fas fa-compress"></i> Regrouper toutes les ressources ici
                        </button>
                    </div>
                    <div class="vip-note">Transfert instantané. La capacité de stockage de la planète cible est respectée.</div>
                <?php else: ?>
                    <div class="vip-disabled">
                        Activez votre VIP pour regrouper toutes vos ressources en un clic vers la planète de votre choix.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="empire-planets">
            <?php $__currentLoopData = $planets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="planet-card">
                    <div class="planet-header">
                        <div class="planet-name"><?php echo e($planet->name); ?></div>
                        <div class="planet-coords">[<?php echo e($planet->templatePlanet->galaxy); ?>:<?php echo e($planet->templatePlanet->system); ?>:<?php echo e($planet->templatePlanet->position); ?>] • <?php echo e(ucfirst($planet->templatePlanet->type)); ?></div>
                    </div>

                    <div class="planet-sections">
                        <div class="planet-section">
                            <h4><i class="fas fa-coins"></i> Ressources</h4>
                            <div class="resource-list">
                                <?php $__empty_1 = true; $__currentLoopData = $planet->resources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="resource-item">
                                        <span class="resource-name"><?php echo e($pr->resource->display_name); ?></span>
                                        <span class="resource-amount"><?php echo e(number_format($pr->current_amount)); ?></span>
                                        <span class="resource-prod">+ <?php echo e(number_format($pr->getCurrentProductionPerHour())); ?> / h</span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="empty-note">Vous ne possédez rien</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="planet-section">
                            <h4><i class="fas fa-building"></i> Bâtiments</h4>
                            <div class="grid-list">
                                <?php $__empty_1 = true; $__currentLoopData = $planet->buildings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="grid-item">
                                        <span class="item-name"><?php echo e($b->build->label ?? $b->build->name); ?></span>
                                        <span class="item-meta">Niveau <?php echo e($b->level); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="empty-note">Vous ne possédez rien</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="planet-section">
                            <h4><i class="fas fa-users"></i> Unités</h4>
                            <div class="grid-list">
                                <?php $__empty_1 = true; $__currentLoopData = $planet->units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="grid-item">
                                        <span class="item-name"><?php echo e($u->unit->label ?? $u->unit->name); ?></span>
                                        <span class="item-meta"><?php echo e(number_format($u->quantity ?? 0)); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="empty-note">Vous ne possédez rien</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="planet-section">
                            <h4><i class="fas fa-rocket"></i> Vaisseaux</h4>
                            <div class="grid-list">
                                <?php $__empty_1 = true; $__currentLoopData = $planet->ships; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="grid-item">
                                        <span class="item-name"><?php echo e($s->ship->label ?? $s->ship->name); ?></span>
                                        <span class="item-meta"><?php echo e(number_format($s->quantity ?? 0)); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="empty-note">Vous ne possédez rien</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="planet-section">
                            <h4><i class="fas fa-shield-alt"></i> Défenses</h4>
                            <div class="grid-list">
                                <?php $__empty_1 = true; $__currentLoopData = $planet->defenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="grid-item">
                                        <span class="item-name"><?php echo e($d->defense->label ?? $d->defense->name); ?></span>
                                        <span class="item-meta"><?php echo e(number_format($d->quantity ?? 0)); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="empty-note">Vous ne possédez rien</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/empire.blade.php ENDPATH**/ ?>