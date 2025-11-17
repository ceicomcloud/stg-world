<div page="galaxy">
    <div class="galaxy-container" 
     wire:keydown.arrow-left="previousSystem" 
     wire:keydown.arrow-right="nextSystem" 
     tabindex="0" x-data="{ mode: <?php if ((object) ('viewMode') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('viewMode'->value()); ?>')<?php echo e('viewMode'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('viewMode'); ?>')<?php endif; ?>, sys: <?php if ((object) ('targetSystem') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('targetSystem'->value()); ?>')<?php echo e('targetSystem'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('targetSystem'); ?>')<?php endif; ?>, legendOpen: false }">
        <!-- Header affiné: titre au-dessus, navigation compacte, stats en ligne -->
        <div class="galaxy-header">
            <div class="system-title-header">Galaxie <?php echo e($currentGalaxy); ?> — Système <?php echo e($currentSystem); ?>/<?php echo e($maxSystems); ?></div>
            <div class="galaxy-header-row">
                <div class="view-toggle">
                    <button type="button" class="toggle-btn" :class="{ 'active': mode === '2d' }" @click="mode='2d'" wire:click="setViewMode('2d')">2D</button>
                    <button type="button" class="toggle-btn" :class="{ 'active': mode === '3d' }" @click="mode='3d'" wire:click="setViewMode('3d')">3D</button>
                </div>
                <div class="system-navigation compact" x-on:keydown.enter="$wire.goToSystem(sys)" x-on:keydown.arrow-left="$wire.previousSystem()" x-on:keydown.arrow-right="$wire.nextSystem()">
                    <button class="nav-btn" wire:click="previousSystem" title="Système précédent"><i class="fas fa-chevron-left"></i></button>
                    <input type="number" class="system-input" wire:model.live="targetSystem" x-model.number="sys" x-on:input.debounce.300ms="$wire.set('targetSystem', sys)" min="1" max="<?php echo e($maxSystems); ?>" placeholder="Système" />
                    <button class="go-btn" wire:click="goToSystem" title="Aller"><i class="fas fa-arrow-right"></i></button>
                    <button class="nav-btn" wire:click="nextSystem" title="Système suivant"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>

        <!-- Alerte des événements galactiques actifs -->
        <!--[if BLOCK]><![endif]--><?php if(!empty($activeEvents)): ?>
        <div class="galactic-events-banner">
            <div class="events-list">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $activeEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="event-item severity-<?php echo e($ev['severity']); ?>">
                        <span class="event-icon"><?php echo e($ev['icon'] ?? '✦'); ?></span>
                        <span class="event-title"><?php echo e($ev['title']); ?></span>
                        <!--[if BLOCK]><![endif]--><?php if(!empty($ev['position'])): ?>
                            <span class="event-scope">[<?php echo e($currentGalaxy); ?>:<?php echo e($currentSystem); ?>:<?php echo e($ev['position']); ?>]</span>
                        <?php else: ?>
                            <span class="event-scope">Système entier</span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <!--[if BLOCK]><![endif]--><?php if(!empty($ev['description'])): ?>
                            <span class="event-desc">— <?php echo e($ev['description']); ?></span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <!--[if BLOCK]><![endif]--><?php if(!empty($ev['end_at'])): ?>
                            <span class="event-time">(se termine <?php echo e(\Carbon\Carbon::parse($ev['end_at'])->diffForHumans()); ?>)</span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php if($viewMode === '3d'): ?>
        <!-- Vue 3D de la galaxie -->
        <div class="galaxy-view" x-transition.opacity.duration.250ms>
            <!-- Soleil au centre -->
            <div class="sun-container">
                <div class="sun">
                    <div class="sun-core"></div>
                    <div class="sun-corona"></div>
                    <div class="sun-flares"></div>
                </div>
            </div>

            <!-- Planètes en orbite -->
            <div class="planets-container" x-transition.opacity.duration.200ms>
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $systemPlanets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position => $planetData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="planet-orbit orbit-<?php echo e($position); ?>" style="--orbit-angle: <?php echo e($planetPositions3D[$position]['angle'] ?? 0); ?>deg;"></div>
                    <div class="planet-wrapper" style="top: <?php echo e($planetPositions3D[$position]['y'] ?? 50); ?>%; left: <?php echo e($planetPositions3D[$position]['x'] ?? 50); ?>%;">
                        <!--[if BLOCK]><![endif]--><?php if($planetData && $planetData['planet']): ?>
                            <!-- Planète occupée -->
                            <div class="planet occupied 
                                <?php echo e($planetData['is_own'] ? 'own-planet' : ($planetData['is_ally'] ? 'ally-planet' : 'enemy-planet')); ?>

                                <?php echo e($planetData['is_main'] ? 'main-planet' : ''); ?>

                                <?php echo e($planetData['is_protected'] ? 'protected-planet' : ''); ?>

                                <?php echo e($planetData['is_vacation_mode'] ? 'vacation-mode-planet' : ''); ?>"
                                wire:click="openPlanetModal(<?php echo e($planetData['planet']->id); ?>)"
                                data-planet-id="<?php echo e($planetData['planet']->id); ?>">
                                
                                <div class="planet-surface planet-type-<?php echo e($planetData['template']->type ?? 'planet'); ?>"
                                    style="background-image: url('<?php echo e($planetData['planet'] && $planetData['planet']->image ? asset('images/planets/' . $planetData['planet']->image) : asset('images/planets/planet-' . (($position % 10) + 1) . '.png')); ?>')">
                                    <div class="planet-atmosphere"></div>
                                    <!--[if BLOCK]><![endif]--><?php if($planetData['is_protected']): ?>
                                        <div class="shield-protection-indicator">
                                            <i class="fas fa-shield-alt"></i>
                                        </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php if($planetData['is_vacation_mode']): ?>
                                        <div class="vacation-mode-indicator">
                                            <i class="fas fa-umbrella-beach"></i>
                                        </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                
                                <div class="planet-info">
                                    <div class="planet-name"><?php echo e($planetData['planet']->name); ?></div>
                                    <div class="planet-coordinates"><?php echo e($planetData['coordinates']); ?></div>
                                    <div class="planet-owner <?php echo e($planetData['is_own'] ? 'own' : 'enemy'); ?> <?php echo e($planetData['is_vacation_mode'] ? 'vacation-mode' : ''); ?>">
                                        <?php echo e($planetData['user']->name ?? 'Inconnu'); ?>

                                        <!--[if BLOCK]><![endif]--><?php if($planetData['is_vacation_mode']): ?>
                                            <span class="vacation-badge"><i class="fas fa-umbrella-beach"></i></span>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                </div>
                            </div>
                        <?php elseif($planetData && $planetData['template'] && $planetData['is_bot']): ?>
                            <!-- Planète PNJ (Bot) -->
                            <div class="planet bot-planet" wire:click="openPlanetModal(null, <?php echo e($currentGalaxy); ?>, <?php echo e($currentSystem); ?>, <?php echo e($position); ?>)">
                                <div class="bot-slot">
                                    <i class="fas fa-robot"></i>
                                </div>
                                
                                <div class="planet-info">
                                    <div class="planet-coordinates"><?php echo e($planetData['coordinates']); ?></div>
                                    <div class="planet-status">Planète PNJ</div>
                                    <div class="planet-type"><?php echo e(ucfirst($planetData['template']->type)); ?></div>
                                </div>
                            </div>
                        <?php elseif($planetData && $planetData['template']): ?>
                            <!-- Position avec template mais libre -->
                            <div class="planet empty" wire:click="openPlanetModal(null, <?php echo e($currentGalaxy); ?>, <?php echo e($currentSystem); ?>, <?php echo e($position); ?>)">
                                <div class="empty-slot">
                                    <i class="fas fa-plus"></i>
                                </div>
                                
                                <div class="planet-info">
                                    <div class="planet-coordinates"><?php echo e($planetData['coordinates']); ?></div>
                                    <div class="planet-status">Position libre</div>
                                    <div class="planet-type"><?php echo e(ucfirst($planetData['template']->type)); ?></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Position sans template (non colonisable) -->
                            <div class="planet empty disabled">
                                <div class="empty-slot disabled">
                                    <i class="fas fa-times"></i>
                                </div>
                                
                                <div class="planet-info">
                                    <div class="planet-coordinates">[<?php echo e($currentGalaxy); ?>:<?php echo e($currentSystem); ?>:<?php echo e($position); ?>]</div>
                                    <div class="planet-status">Non disponible</div>
                                </div>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php if($viewMode === '2d'): ?>
        <!-- Vue 2D de la galaxie -->
        <div class="galaxy-view-2d" x-transition.opacity.duration.250ms>
            <div class="system-grid">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $systemPlanets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position => $planetData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'grid-tile',
                            'tile-occupied' => ($planetData && $planetData['planet']),
                            'tile-own' => ($planetData && $planetData['planet'] && $planetData['is_own']),
                            'tile-ally' => ($planetData && $planetData['planet'] && !$planetData['is_own'] && $planetData['is_ally']),
                            'tile-enemy' => ($planetData && $planetData['planet'] && !$planetData['is_own'] && !$planetData['is_ally']),
                            'tile-main' => ($planetData && $planetData['planet'] && $planetData['is_main']),
                            'tile-bot' => ($planetData && $planetData['template'] && $planetData['is_bot']),
                            'tile-empty' => ($planetData && $planetData['template'] && !$planetData['is_bot'] && !$planetData['planet']),
                            'tile-disabled' => (!$planetData || (!$planetData['template'] && !$planetData['planet'])),
                        ]); ?>"
                        <?php if($planetData && $planetData['planet']): ?>
                            wire:click="openPlanetModal(<?php echo e($planetData['planet']->id); ?>)"
                        <?php elseif($planetData && $planetData['template']): ?>
                            wire:click="openPlanetModal(null, <?php echo e($currentGalaxy); ?>, <?php echo e($currentSystem); ?>, <?php echo e($position); ?>)"
                        <?php endif; ?>
                    >
                        <div class="tile-header">
                            <span class="tile-position">#<?php echo e($position); ?></span>
                            <span class="tile-coords"><?php echo e($planetData['coordinates'] ?? "[{$currentGalaxy}:{$currentSystem}:{$position}]"); ?></span>
                        </div>
                        <div class="tile-body">
                            <!--[if BLOCK]><![endif]--><?php if($planetData && $planetData['planet']): ?>
                                <div class="tile-icon">
                                    <i class="fas fa-globe"></i>
                                    <!--[if BLOCK]><![endif]--><?php if($planetData['is_main']): ?>
                                        <i class="fas fa-crown badge-main" title="Planète principale"></i>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php if($planetData['is_protected']): ?>
                                        <i class="fas fa-shield-alt badge-protected" title="Protection active"></i>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php if($planetData['is_vacation_mode']): ?>
                                        <i class="fas fa-umbrella-beach badge-vacation" title="Mode vacances"></i>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="tile-info">
                                    <div class="tile-name"><?php echo e($planetData['planet']->name); ?></div>
                                    <div class="tile-owner"><?php echo e($planetData['user']->name ?? 'Inconnu'); ?></div>
                                </div>
                            <?php elseif($planetData && $planetData['template'] && $planetData['is_bot']): ?>
                                <div class="tile-icon"><i class="fas fa-robot"></i></div>
                                <div class="tile-info">
                                    <div class="tile-name">PNJ</div>
                                    <div class="tile-type"><?php echo e(ucfirst($planetData['template']->type)); ?></div>
                                </div>
                            <?php elseif($planetData && $planetData['template']): ?>
                                <div class="tile-icon"><i class="fas fa-plus"></i></div>
                                <div class="tile-info">
                                    <div class="tile-name">Libre</div>
                                    <div class="tile-type"><?php echo e(ucfirst($planetData['template']->type)); ?></div>
                                </div>
                            <?php else: ?>
                                <div class="tile-icon"><i class="fas fa-times"></i></div>
                                <div class="tile-info">
                                    <div class="tile-name">Indisponible</div>
                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!-- Footer affiné: légende en ligne pliable sans titre -->
        <div class="galaxy-footer">
            <button class="toggle-btn" @click="legendOpen=!legendOpen" :class="{ 'active': legendOpen }">
                <i class="fas" :class="legendOpen ? 'fa-eye-slash' : 'fa-eye'"></i>
                <span x-text="legendOpen ? 'Masquer' : 'Afficher'"></span>
            </button>
            <div class="galaxy-legend-inline" x-show="legendOpen" x-transition.opacity.duration.200ms>
                <div class="legend-item"><div class="legend-color own-planet"></div><span>Vos planètes</span></div>
                <div class="legend-item"><div class="legend-color enemy-planet"></div><span>Planètes ennemies</span></div>
                <div class="legend-item"><div class="legend-color ally-planet"></div><span>Planètes alliées</span></div>
                <div class="legend-item"><div class="legend-color bot-planet"></div><span>Planètes PNJ</span></div>
                <div class="legend-item"><div class="legend-color empty"></div><span>Positions libres</span></div>
                <div class="legend-item"><i class="fas fa-crown legend-icon"></i><span>Planète principale</span></div>
                <div class="legend-item"><i class="fas fa-shield-alt legend-icon"></i><span>Protection planétaire active</span></div>
                <div class="legend-item"><i class="fas fa-umbrella-beach legend-icon"></i><span>Joueur en mode vacances</span></div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/galaxy.blade.php ENDPATH**/ ?>