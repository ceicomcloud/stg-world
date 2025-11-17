<div page="report">
    <div class="report-container">
        <?php
            $winner = data_get($report, 'combat.winner', $log->attacker_won ? 'attacker' : 'defender');
            $attackerPower = (int) data_get($report, 'attacker.total_power', 0);
            $defenderPower = (int) data_get($report, 'defender.total_power', 0);
            $unitsCatalog = (array) data_get($report, 'units_catalog', []);

            $iconPath = function($meta, $side = null) {
                // Priorité aux icônes personnalisées par camp si disponibles
                if ($side && !empty($meta[$side]['icon_url'])) {
                    return $meta[$side]['icon_url'];
                }
                $type = $meta['type'] ?? 'unit';
                $icon = $meta['icon'] ?? null;
                if (!$icon) return null;
                $folder = $type === 'ship' ? 'ships' : ($type === 'defense' ? 'defenses' : 'units');
                return asset('images/' . $folder . '/' . $icon);
            };
            // Libellé lisible pour le type d'attaque
            $attackTypeLabel = match($log->attack_type) {
                'earth' => 'Terrestre',
                'spatial' => 'Spatial',
                default => ucfirst($log->attack_type ?? 'inconnu'),
            };
        ?>
        <?php if($log): ?>
            <div class="report-header">
                <div class="header-left">
                    <h2>Rapport de Combat</h2>
                    <div class="meta-line">
                        <span class="badge <?php echo e($winner === 'attacker' ? 'badge-success' : ($winner === 'defender' ? 'badge-danger' : 'badge-warning')); ?>">
                            <?php echo e($winner === 'attacker' ? 'Victoire Attaquant' : ($winner === 'defender' ? 'Victoire Défenseur' : 'Match Nul')); ?>

                        </span>
                        <span class="badge badge-info">Type: <?php echo e($attackTypeLabel); ?></span>
                        <span class="badge badge-secondary">Clé: <?php echo e($log->access_key); ?></span>
                    </div>
                    <p class="date">Date: <?php echo e($log->attacked_at->format('d/m/Y H:i')); ?></p>
                </div>
                <div class="header-right power-cards">
                    <div class="power-card attacker">
                        <div class="label">Puissance Attaquant</div>
                        <div class="value"><?php echo e(number_format($attackerPower)); ?></div>
                    </div>
                    <div class="power-card defender">
                        <div class="label">Puissance Défenseur</div>
                        <div class="value"><?php echo e(number_format($defenderPower)); ?></div>
                    </div>
                </div>
            </div>

            <div class="report-summary">
                <div class="attacker">
                    <h3>Attaquant</h3>
                    <p>Joueur: <?php echo e($log->attackerUser->name); ?></p>
                    <p>Planète: <?php echo e($log->attackerPlanet->name); ?> [<?php echo e($log->attackerPlanet->galaxy); ?>:<?php echo e($log->attackerPlanet->system); ?>:<?php echo e($log->attackerPlanet->position); ?>]</p>
                </div>
                <div class="defender">
                    <h3>Défenseur</h3>
                    <p>Joueur: <?php echo e($log->defenderUser->name); ?></p>
                    <p>Planète: <?php echo e($log->defenderPlanet->name); ?> [<?php echo e($log->defenderPlanet->galaxy); ?>:<?php echo e($log->defenderPlanet->system); ?>:<?php echo e($log->defenderPlanet->position); ?>]</p>
                </div>
            </div>

            <div class="engaged-units">
                <h3>Unités Engagées</h3>
                <div class="units-grid">
                    <div class="units-column">
                        <h4><i class="fas fa-swords"></i> Attaquant</h4>
                        <div class="unit-list">
                            <?php $__currentLoopData = (array) data_get($report, 'attacker.initial_units', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unitId => $qty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $meta = $unitsCatalog[$unitId] ?? null; ?>
                                <div class="unit-card">
                                    <?php if($meta): ?>
                                        <img class="unit-icon" src="<?php echo e($iconPath($meta, 'attacker')); ?>" alt="<?php echo e(data_get($meta, 'attacker.name', $meta['label'] ?? $unitId)); ?>" />
                                    <?php endif; ?>
                                    <div class="unit-info">
                                        <div class="unit-name"><?php echo e(data_get($meta, 'attacker.name', $meta['label'] ?? $unitId)); ?></div>
                                        <div class="unit-qty">x<?php echo e(number_format($qty)); ?></div>
                                        <?php if($meta): ?>
                                            <div class="unit-stats">
                                                ATK <?php echo e($meta['stats']['attack_power'] ?? 0); ?> · DEF <?php echo e($meta['stats']['defense_power'] ?? 0); ?> · VIE <?php echo e($meta['stats']['life'] ?? 0); ?> · BOUCLIER <?php echo e($meta['stats']['shield_power'] ?? 0); ?>

                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <div class="units-column">
                        <h4><i class="fas fa-shield"></i> Défenseur</h4>
                        <div class="unit-list">
                            <?php $__currentLoopData = (array) data_get($report, 'defender.initial_units', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unitId => $qty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $meta = $unitsCatalog[$unitId] ?? null; ?>
                                <div class="unit-card">
                                    <?php if($meta): ?>
                                        <img class="unit-icon" src="<?php echo e($iconPath($meta, 'defender')); ?>" alt="<?php echo e(data_get($meta, 'defender.name', $meta['label'] ?? $unitId)); ?>" />
                                    <?php endif; ?>
                                    <div class="unit-info">
                                        <div class="unit-name"><?php echo e(data_get($meta, 'defender.name', $meta['label'] ?? $unitId)); ?></div>
                                        <div class="unit-qty">x<?php echo e(number_format($qty)); ?></div>
                                        <?php if($meta): ?>
                                            <div class="unit-stats">
                                                ATK <?php echo e($meta['stats']['attack_power'] ?? 0); ?> · DEF <?php echo e($meta['stats']['defense_power'] ?? 0); ?> · VIE <?php echo e($meta['stats']['life'] ?? 0); ?> · BOUCLIER <?php echo e($meta['stats']['shield_power'] ?? 0); ?>

                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php if($log->attack_type === 'spatial'): ?>
                            <div class="unit-list sub-block">
                                <h5>Défenses</h5>
                                <?php $__currentLoopData = (array) data_get($report, 'defender.initial_defenses', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unitId => $qty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $meta = $unitsCatalog[$unitId] ?? null; ?>
                                    <div class="unit-card">
                                        <?php if($meta): ?>
                                            <img class="unit-icon" src="<?php echo e($iconPath($meta, 'defender')); ?>" alt="<?php echo e(data_get($meta, 'defender.name', $meta['label'] ?? $unitId)); ?>" />
                                        <?php endif; ?>
                                        <div class="unit-info">
                                            <div class="unit-name"><?php echo e(data_get($meta, 'defender.name', $meta['label'] ?? $unitId)); ?></div>
                                            <div class="unit-qty">x<?php echo e(number_format($qty)); ?></div>
                                            <?php if($meta): ?>
                                                <div class="unit-stats">
                                                    DEF <?php echo e($meta['stats']['defense_power'] ?? 0); ?> · VIE <?php echo e($meta['stats']['life'] ?? 0); ?> · BOUCLIER <?php echo e($meta['stats']['shield_power'] ?? 0); ?>

                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="report-result">
                <p><strong>Type:</strong> <?php echo e($attackTypeLabel); ?></p>
                <p><strong>Résultat:</strong> <?php echo e($log->attacker_won ? 'Victoire attaquant' : 'Victoire défenseur'); ?></p>
                <p><strong>Points gagnés:</strong> <?php echo e(number_format($log->points_gained)); ?></p>
            </div>

            <div class="combat-details">
                <h3>Rounds du Combat</h3>
                <?php if(is_array(data_get($report, 'combat.rounds'))): ?>
                    <div class="rounds">
                        <?php $__currentLoopData = $report['combat']['rounds']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $round): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="round">
                                <h5>Round <?php echo e($round['round'] ?? $loop->iteration); ?></h5>
                                <div class="round-grid">
                                    <div class="round-metric">
                                        <span class="label">Puissance Attaquant</span>
                                        <span class="value"><?php echo e(number_format($round['attacker_power'] ?? 0)); ?></span>
                                    </div>
                                    <div class="round-metric">
                                        <span class="label">Puissance Défenseur</span>
                                        <span class="value"><?php echo e(number_format($round['defender_power'] ?? 0)); ?></span>
                                    </div>
                                    <div class="round-metric">
                                        <span class="label">Bouclier Attaquant</span>
                                        <span class="value"><?php echo e(number_format($round['attacker_shield'] ?? 0)); ?></span>
                                    </div>
                                    <div class="round-metric">
                                        <span class="label">Bouclier Défenseur</span>
                                        <span class="value"><?php echo e(number_format($round['defender_shield'] ?? 0)); ?></span>
                                    </div>
                                    <div class="round-metric">
                                        <span class="label">Dégâts Attaquant</span>
                                        <span class="value"><?php echo e(number_format($round['damage_to_defender'] ?? $round['attacker_damage'] ?? 0)); ?></span>
                                    </div>
                                    <div class="round-metric">
                                        <span class="label">Dégâts Défenseur</span>
                                        <span class="value"><?php echo e(number_format($round['damage_to_attacker'] ?? $round['defender_damage'] ?? 0)); ?></span>
                                    </div>
                                </div>

                                <?php if(!empty($round['attacker_losses'])): ?>
                                    <div class="losses">
                                        <strong>Pertes Attaquant:</strong>
                                        <?php $__currentLoopData = $round['attacker_losses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unitId => $lost): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php $meta = $unitsCatalog[$unitId] ?? null; ?>
                                            <span class="loss-item"><?php echo e(data_get($meta, 'attacker.name', $meta['label'] ?? $unitId)); ?> - <?php echo e(number_format($lost)); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if(!empty($round['defender_losses'])): ?>
                                    <div class="losses">
                                        <strong>Pertes Défenseur:</strong>
                                        <?php $__currentLoopData = $round['defender_losses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unitId => $lost): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php $meta = $unitsCatalog[$unitId] ?? null; ?>
                                            <span class="loss-item"><?php echo e(data_get($meta, 'defender.name', $meta['label'] ?? $unitId)); ?> - <?php echo e(number_format($lost)); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="survivors">
                <h3>Survivants</h3>
                <div class="units-grid">
                    <div class="units-column">
                        <h4>Attaquant</h4>
                        <div class="unit-list">
                            <?php $__currentLoopData = (array) data_get($report, 'combat.surviving_attacker_units', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unitId => $qty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $meta = $unitsCatalog[$unitId] ?? null; ?>
                                <div class="unit-card">
                                    <?php if($meta): ?>
                                        <img class="unit-icon" src="<?php echo e($iconPath($meta, 'attacker')); ?>" alt="<?php echo e(data_get($meta, 'attacker.name', $meta['label'] ?? $unitId)); ?>" />
                                    <?php endif; ?>
                                    <div class="unit-info">
                                        <div class="unit-name"><?php echo e(data_get($meta, 'attacker.name', $meta['label'] ?? $unitId)); ?></div>
                                        <div class="unit-qty">x<?php echo e(number_format($qty)); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <div class="units-column">
                        <h4>Défenseur</h4>
                        <div class="unit-list">
                            <?php $__currentLoopData = (array) data_get($report, 'combat.surviving_defender_units', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unitId => $qty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $meta = $unitsCatalog[$unitId] ?? null; ?>
                                <div class="unit-card">
                                    <?php if($meta): ?>
                                        <img class="unit-icon" src="<?php echo e($iconPath($meta, 'defender')); ?>" alt="<?php echo e(data_get($meta, 'defender.name', $meta['label'] ?? $unitId)); ?>" />
                                    <?php endif; ?>
                                    <div class="unit-info">
                                        <div class="unit-name"><?php echo e(data_get($meta, 'defender.name', $meta['label'] ?? $unitId)); ?></div>
                                        <div class="unit-qty">x<?php echo e(number_format($qty)); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if(!empty($report['pillaged_resources'])): ?>
                <div class="pillaged">
                    <h3>Ressources Pillées</h3>
                    <div class="pillaged-list">
                        <?php
                            // Charger les ressources templates pour afficher leur nom lisible
                            $templateResources = \App\Models\Template\TemplateResource::all()->keyBy('id');
                        ?>
                        <?php $__currentLoopData = $report['pillaged_resources']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resourceId => $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="pillaged-item">
                                <?php
                                    $resourceName = isset($templateResources[$resourceId]) ? $templateResources[$resourceId]->name : "Ressource #{$resourceId}";
                                    $resourceDisplayName = isset($templateResources[$resourceId])
                                        ? ($templateResources[$resourceId]->display_name ?? ucfirst($resourceName))
                                        : "Ressource #{$resourceId}";
                                ?>
                                <span class="pillaged-name"><?php echo e($resourceDisplayName); ?></span>
                                <span class="pillaged-amount"><?php echo e(number_format($amount)); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="report-not-found">
                <p>Rapport introuvable pour la clé fournie.</p>
            </div>
        <?php endif; ?>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/rapport.blade.php ENDPATH**/ ?>