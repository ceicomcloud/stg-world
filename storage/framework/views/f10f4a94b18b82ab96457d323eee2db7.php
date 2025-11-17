<div page="game">
    <div class="game-container">
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('game.server-news-banner', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-189426807-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        <!--[if BLOCK]><![endif]--><?php if($eventActive && $activeServerEvent): ?>
            <div class="server-event-banner" role="status" aria-live="polite">
                <div class="server-event-inner">
                    <span class="event-icon"><i class="fas fa-bolt"></i></span>
                    <span class="event-text">
                        Événement en cours: <strong><?php echo e(ucfirst($activeServerEvent['type'] ?? 'inconnu')); ?></strong>
                        <!--[if BLOCK]><![endif]--><?php if(!empty($activeServerEvent['end_at'])): ?>
                            — se termine: <span class="event-time"><?php echo e($activeServerEvent['end_at']); ?></span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <!--[if BLOCK]><![endif]--><?php if(!empty($activeServerEvent['points_multiplier'])): ?>
                            — multiplicateur: <span class="event-mult">x<?php echo e(number_format($activeServerEvent['points_multiplier'], 2)); ?></span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </span>
                    <!--[if BLOCK]><![endif]--><?php if(!empty($activeServerEvent['reward_type'])): ?>
                        <span class="event-badge">
                            Récompense: <?php echo e($activeServerEvent['reward_type'] === 'gold' ? 'Or' : 'Ressources'); ?>

                        </span>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        
        <!--[if BLOCK]><![endif]--><?php if($currentPlanet): ?>
            <!-- Compact Planet Header -->
            <div class="planet-header-compact">
                <div class="planet-main-info">
                    <div class="planet-visual-small">
                        <div class="planet-sphere-small planet-<?php echo e($currentPlanet->templatePlanet->type ?? 'planet'); ?>"></div>
                    </div>
                    <div class="planet-details">
                        <h1 class="planet-name"><?php echo e($currentPlanet->name ?? 'Planète Inconnue'); ?></h1>
                        <div class="planet-meta">
                            <span class="coordinates">[<?php echo e($currentPlanet->templatePlanet->galaxy ?? 'N/A'); ?>:<?php echo e($currentPlanet->templatePlanet->system ?? 'N/A'); ?>:<?php echo e($currentPlanet->templatePlanet->position ?? 'N/A'); ?>]</span>
                            <span class="planet-type"><?php echo e(ucfirst($currentPlanet->templatePlanet->type ?? 'Inconnu')); ?></span>
                            <span class="fields"><?php echo e($currentPlanet->used_fields); ?>/<?php echo e($currentPlanet->templatePlanet->fields ?? 'N/A'); ?> cases</span>
                        </div>
                    </div>
                </div>
                
                <!-- Compact Resource Bonuses -->
                <!--[if BLOCK]><![endif]--><?php if($currentPlanet->templatePlanet): ?>
                    <div class="resource-bonuses-compact">
                        <div class="bonus-item-compact">
                            <img src="/images/resources/metal.png" alt="metal" />
                            <span><?php echo e(number_format(($currentPlanet->templatePlanet->metal_bonus - 1) * 100, 0)); ?>%</span>
                        </div>
                        <div class="bonus-item-compact">
                            <img src="/images/resources/crystal.png" alt="crystal" />
                            <span><?php echo e(number_format(($currentPlanet->templatePlanet->crystal_bonus - 1) * 100, 0)); ?>%</span>
                        </div>
                        <div class="bonus-item-compact">
                            <img src="/images/resources/deuterium.png" alt="deuterium" />
                            <span><?php echo e(number_format(($currentPlanet->templatePlanet->deuterium_bonus - 1) * 100, 0)); ?>%</span>
                        </div>
                        <div class="bonus-item-compact">
                            <i class="fas fa-bolt"></i>
                            <span><?php echo e(number_format(($currentPlanet->templatePlanet->energy_bonus - 1) * 100, 0)); ?>%</span>
                        </div>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            <!-- Daily Reward Banner -->
            <!--[if BLOCK]><![endif]--><?php if($dailyRewardClaimable): ?>
                <div class="daily-reward-banner">
                    <div class="daily-reward-header">
                        <h3><i class="fas fa-gift"></i> Récompense quotidienne (Jour <?php echo e($dailyRewardDay); ?>/7)</h3>
                        <div class="daily-reward-preview">
                            <span><img src="/images/resources/metal.png" alt="metal" /> <?php echo e(number_format($dailyRewardPreview['metal'] ?? 0)); ?></span>
                            <span><img src="/images/resources/crystal.png" alt="crystal" /> <?php echo e(number_format($dailyRewardPreview['crystal'] ?? 0)); ?></span>
                            <span><img src="/images/resources/deuterium.png" alt="deuterium" /> <?php echo e(number_format($dailyRewardPreview['deuterium'] ?? 0)); ?></span>
                            <span><i class="fas fa-coins"></i> <?php echo e(number_format($dailyRewardPreview['gold'] ?? 0)); ?> or</span>
                        </div>
                        <button class="btn-claim" wire:click="claimDailyReward"><i class="fas fa-hand-holding-heart"></i> Récupérer</button>
                    </div>
                    <div class="daily-reward-steps">
                        <!--[if BLOCK]><![endif]--><?php for($i = 1; $i <= 7; $i++): ?>
                            <div class="reward-step <?php echo e($i === $dailyRewardDay ? 'current' : ''); ?>">
                                <span class="step-index">J<?php echo e($i); ?></span>
                            </div>
                        <?php endfor; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <!--[if BLOCK]><![endif]--><?php if(!empty($dailyRewardSchedule)): ?>
                        <div class="daily-reward-schedule">
                            <!--[if BLOCK]><![endif]--><?php for($i = 1; $i <= 7; $i++): ?>
                                <?php
                                    $sched = $dailyRewardSchedule[$i] ?? ['metal'=>0,'crystal'=>0,'deuterium'=>0,'gold'=>0];
                                ?>
                                <div class="reward-day-card <?php echo e($i === $dailyRewardDay ? 'current' : ''); ?>">
                                    <div class="reward-day-header">Jour <?php echo e($i); ?></div>
                                    <div class="reward-day-items">
                                        <span title="Métal"><img src="/images/resources/metal.png" alt="metal" /> <?php echo e(number_format($sched['metal'])); ?></span>
                                        <span title="Cristal"><img src="/images/resources/crystal.png" alt="crystal" /> <?php echo e(number_format($sched['crystal'])); ?></span>
                                        <span title="Deutérium"><img src="/images/resources/deuterium.png" alt="deuterium" /> <?php echo e(number_format($sched['deuterium'])); ?></span>
                                        <span title="Or"><i class="fas fa-coins"></i> <?php echo e(number_format($sched['gold'])); ?> or</span>
                                    </div>
                                </div>
                            <?php endfor; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <!-- Main Content Grid -->
            <div class="game-grid">
                <!-- Left Column: Queues -->
                <div class="grid-left">
                    <!--[if BLOCK]><![endif]--><?php if($queues && (count($queues['building']) > 0 || count($queues['unit']) > 0 || count($queues['defense']) > 0 || count($queues['ship']) > 0)): ?>
                        <div class="current-queues-compact">
                            <h3><i class="fas fa-list"></i> Files d'attente</h3>
                            
                            <!--[if BLOCK]><![endif]--><?php if(count($queues['building']) > 0): ?>
                                <div class="queue-section-compact">
                                    <h4><i class="fas fa-building"></i> Bâtiments</h4>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $queues['building']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="queue-item-compact">
                                            <div class="queue-info">
                                                <span class="name"><?php echo e($item->item->label ?? 'Inconnu'); ?></span>
                                                <span class="level">Niv. <?php echo e($item->level); ?></span>
                                            </div>
                                            <div class="queue-timer" x-data="queueTimer('<?php echo e($item->end_time ? $item->end_time->unix() : ''); ?>', '<?php echo e(now()->unix()); ?>')" x-init="init()" x-text="display"></div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                            <!--[if BLOCK]><![endif]--><?php if(count($queues['unit']) > 0): ?>
                                <div class="queue-section-compact">
                                    <h4><i class="fas fa-users"></i> Unités</h4>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $queues['unit']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="queue-item-compact">
                                            <div class="queue-info">
                                                <span class="name"><?php echo e($item->item->label ?? 'Inconnu'); ?></span>
                                                <span class="quantity">x<?php echo e($item->quantity); ?></span>
                                            </div>
                                            <div class="queue-timer" x-data="queueTimer('<?php echo e($item->end_time ? $item->end_time->unix() : ''); ?>', '<?php echo e(now()->unix()); ?>')" x-init="init()" x-text="display"></div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                            <!--[if BLOCK]><![endif]--><?php if(count($queues['defense']) > 0): ?>
                                <div class="queue-section-compact">
                                    <h4><i class="fas fa-shield-alt"></i> Défenses</h4>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $queues['defense']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="queue-item-compact">
                                            <div class="queue-info">
                                                <span class="name"><?php echo e($item->item->label ?? 'Inconnu'); ?></span>
                                                <span class="quantity">x<?php echo e($item->quantity); ?></span>
                                            </div>
                                            <div class="queue-timer" x-data="queueTimer('<?php echo e($item->end_time ? $item->end_time->unix() : ''); ?>', '<?php echo e(now()->unix()); ?>')" x-init="init()" x-text="display"></div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                            <!--[if BLOCK]><![endif]--><?php if(count($queues['ship']) > 0): ?>
                                <div class="queue-section-compact">
                                    <h4><i class="fas fa-rocket"></i> Vaisseaux</h4>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $queues['ship']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="queue-item-compact">
                                            <div class="queue-info">
                                                <span class="name"><?php echo e($item->item->label ?? 'Inconnu'); ?></span>
                                                <span class="quantity">x<?php echo e($item->quantity); ?></span>
                                            </div>
                                            <div class="queue-timer" x-data="queueTimer('<?php echo e($item->end_time ? $item->end_time->unix() : ''); ?>', '<?php echo e(now()->unix()); ?>')" x-init="init()" x-text="display"></div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                
                <!-- Right Column: Mission Radar -->
                <div class="grid-right">
                    <!--[if BLOCK]><![endif]--><?php if(count($missions) > 0): ?>
                        <div class="mission-radar-compact">
                            <h3><i class="fas fa-radar"></i> Radar des Missions</h3>
                            <div class="mission-list-compact">
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $missions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="mission-item-compact mission-<?php echo e($mission->status); ?>" wire:click="openMissionInfo(<?php echo e($mission->id); ?>)">
                                        <div class="mission-header-compact">
                                            <div class="mission-info-left">
                                                <span class="mission-type"><?php echo e($mission->getType()); ?></span>
                                                <span class="mission-status status-<?php echo e($mission->status); ?>"><?php echo e($mission->getStatus()); ?></span>
                                            </div>
                                            <div class="mission-info-right">
                                                <div class="mission-timer" data-end-time="<?php echo e($mission->status === 'traveling' && $mission->arrival_time ? $mission->arrival_time->unix() : (
                                                        ($mission->status === 'returning' || $mission->status === 'collecting' || $mission->status === 'exploring') && $mission->return_time ? $mission->return_time->unix() : ''
                                                    )); ?>"></div>
                                                <!--[if BLOCK]><![endif]--><?php if($mission->status === 'traveling'): ?>
                                                    <div class="mission-actions-compact">
                                                        <button wire:click.stop="confirmMissionRecall(<?php echo e($mission->id); ?>)" 
                                                                class="btn-recall"
                                                                title="Rappeler la mission">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    </div>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                        </div>
                                        <div class="mission-route-compact">
                                            <span class="from"><?php echo e($mission->fromPlanet->name ?? 'Planète'); ?> [<?php echo e($mission->fromPlanet->templatePlanet->galaxy ?? 'N/A'); ?>:<?php echo e($mission->fromPlanet->templatePlanet->system ?? 'N/A'); ?>:<?php echo e($mission->fromPlanet->templatePlanet->position ?? 'N/A'); ?>]</span>
                                            <i class="fas fa-arrow-right"></i>
                                            <span class="to">[<?php echo e($mission->to_galaxy); ?>:<?php echo e($mission->to_system); ?>:<?php echo e($mission->to_position); ?>]</span>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>

            <!-- Modal de confirmation pour le rappel de mission -->
            <?php if (isset($component)) { $__componentOriginal3347679a0053d4c83e098c2ad5c70893 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3347679a0053d4c83e098c2ad5c70893 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.modal-confirmation','data' => ['wire:model' => 'showRecallModal','title' => 'Rappeler la mission','message' => 'Êtes-vous sûr de vouloir rappeler cette mission ?','icon' => 'fas fa-question-circle text-warning','confirmText' => 'Oui, rappeler','cancelText' => 'Continuer la mission','onConfirm' => 'performMissionRecall','onCancel' => 'dismissModals']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.modal-confirmation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'showRecallModal','title' => 'Rappeler la mission','message' => 'Êtes-vous sûr de vouloir rappeler cette mission ?','icon' => 'fas fa-question-circle text-warning','confirmText' => 'Oui, rappeler','cancelText' => 'Continuer la mission','onConfirm' => 'performMissionRecall','onCancel' => 'dismissModals']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3347679a0053d4c83e098c2ad5c70893)): ?>
<?php $attributes = $__attributesOriginal3347679a0053d4c83e098c2ad5c70893; ?>
<?php unset($__attributesOriginal3347679a0053d4c83e098c2ad5c70893); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3347679a0053d4c83e098c2ad5c70893)): ?>
<?php $component = $__componentOriginal3347679a0053d4c83e098c2ad5c70893; ?>
<?php unset($__componentOriginal3347679a0053d4c83e098c2ad5c70893); ?>
<?php endif; ?>

            <!-- Player Level and Progression -->
            <div class="player-level-section">
                <h3><i class="fas fa-star"></i> Niveau et Progression</h3>
                <div class="level-display">
                    <div class="level-badge"><?php echo e($user->getLevel()); ?></div>
                    <div class="level-info">
                        <div class="level-title">Niveau <?php echo e($user->getLevel()); ?></div>
                        <div class="level-exp"><?php echo e(number_format($user->getCurrentExperience())); ?> / <?php echo e(number_format($user->getRequiredExperienceForLevel($user->getLevel()))); ?> XP</div>
                    </div>
                </div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: <?php echo e($user->getLevelProgress()); ?>%"></div>
                </div>
            </div>

            <!-- Combat Statistics -->
            <div class="combat-stats-section">
                <h3><i class="fas fa-fighter-jet"></i> Statistiques de Combat</h3>
                <div class="combat-stats-grid">
                    <div class="combat-category">
                        <h4><i class="fas fa-globe"></i> Combat Terrestre</h4>
                        <div class="combat-stats-list">
                            <div class="combat-stat-item">
                                <span class="combat-stat-label">Attaques</span>
                                <span class="combat-stat-value"><?php echo e($user->userStat->earth_attack_count ?? 0); ?></span>
                            </div>
                            <div class="combat-stat-item">
                                <span class="combat-stat-label">Défenses</span>
                                <span class="combat-stat-value"><?php echo e($user->userStat->earth_defense_count ?? 0); ?></span>
                            </div>
                            <div class="combat-stat-item">
                                <span class="combat-stat-label">Victoires</span>
                                <span class="combat-stat-value positive"><?php echo e(($user->userStat->earth_attack_count ?? 0) - ($user->userStat->earth_loser_count ?? 0)); ?></span>
                            </div>
                            <div class="combat-stat-item">
                                <span class="combat-stat-label">Défaites</span>
                                <span class="combat-stat-value negative"><?php echo e($user->userStat->earth_loser_count ?? 0); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="combat-category">
                        <h4><i class="fas fa-space-shuttle"></i> Combat Spatial</h4>
                        <div class="combat-stats-list">
                            <div class="combat-stat-item">
                                <span class="combat-stat-label">Attaques</span>
                                <span class="combat-stat-value"><?php echo e($user->userStat->spatial_attack_count ?? 0); ?></span>
                            </div>
                            <div class="combat-stat-item">
                                <span class="combat-stat-label">Défenses</span>
                                <span class="combat-stat-value"><?php echo e($user->userStat->spatial_defense_count ?? 0); ?></span>
                            </div>
                            <div class="combat-stat-item">
                                <span class="combat-stat-label">Victoires</span>
                                <span class="combat-stat-value positive"><?php echo e(($user->userStat->spatial_attack_count ?? 0) - ($user->userStat->spatial_loser_count ?? 0)); ?></span>
                            </div>
                            <div class="combat-stat-item">
                                <span class="combat-stat-label">Défaites</span>
                                <span class="combat-stat-value negative"><?php echo e($user->userStat->spatial_loser_count ?? 0); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Badges Section -->
            <div class="badges-section">
                <h3><i class="fas fa-medal"></i> Badges</h3>
                <div class="badges-container">
                    <div class="badges-group">
                        <h4>Badges Récents</h4>
                        <div class="badges-list">
                            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $recentBadges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="badge-item">
                                    <div class="badge-symbol <?php echo e($badge->rarity); ?>">
                                        <i class="fas fa-medal"></i>
                                    </div>
                                    <div class="badge-info">
                                        <div class="badge-name"><?php echo e($badge->name); ?></div>
                                        <div class="badge-meta">
                                            <span class="badge-rarity <?php echo e($badge->rarity); ?>"><?php echo e(ucfirst($badge->rarity)); ?></span>
                                            <span><?php echo e($badge->pivot?->earned_at ? \Carbon\Carbon::parse($badge->pivot->earned_at)->diffForHumans() : '—'); ?></span>
                                        </div>
                                        <!--[if BLOCK]><![endif]--><?php if(!empty($badge->description)): ?>
                                            <div class="badge-description"><?php echo e($badge->description); ?></div>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="no-badges">Aucun badge obtenu pour le moment</div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                    <div class="badges-group">
                        <h4>Badges à Débloquer</h4>
                        <div class="badges-list">
                            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $upcomingBadges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $badgeService = app(\App\Services\BadgeService::class);
                                    $progress = $badgeService->getBadgeProgress($user, $badge);
                                    $reqType = $badge->requirement_type;
                                    $reqValue = $badge->requirement_value;
                                    $requirementText = match($reqType) {
                                        \App\Models\Template\TemplateBadge::REQUIREMENT_REACH_LEVEL => 'Atteindre le niveau ' . $reqValue,
                                        \App\Models\Template\TemplateBadge::REQUIREMENT_TOTAL_EXPERIENCE => 'Atteindre ' . number_format($reqValue) . ' XP total',
                                        \App\Models\Template\TemplateBadge::REQUIREMENT_RESEARCH_POINTS => 'Accumuler ' . number_format($reqValue) . ' points de recherche',
                                        \App\Models\Template\TemplateBadge::REQUIREMENT_CUSTOM => (!empty($badge->description) ? $badge->description : 'Condition personnalisée'),
                                        default => 'Condition inconnue',
                                    };
                                ?>
                                <div class="badge-item">
                                    <div class="badge-symbol <?php echo e($badge->rarity); ?>">
                                        <i class="fas fa-medal"></i>
                                    </div>
                                    <div class="badge-info">
                                        <div class="badge-name"><?php echo e($badge->name); ?></div>
                                        <div class="badge-meta">
                                            <span class="badge-rarity <?php echo e($badge->rarity); ?>"><?php echo e(ucfirst($badge->rarity)); ?></span>
                                        </div>
                                        <div class="badge-condition">
                                            <?php echo e($requirementText); ?>

                                        </div>
                                        <div class="badge-progress">
                                            <div class="badge-progress-bar" style="width: <?php echo e($progress); ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="no-badges">Aucun badge en progression</div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Planets Carousel -->
            <div class="planets-carousel-section">
                <h3><i class="fas fa-globe"></i> Vos Planètes</h3>
                <div class="planets-carousel">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $planets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="planet-card <?php echo e($planet->id == $currentPlanet->id ? 'active' : ''); ?>" wire:click="switchPlanet(<?php echo e($planet->id); ?>)">
                            <div class="planet-card-header">
                                <div class="planet-mini-sphere planet-<?php echo e($planet->templatePlanet->type ?? 'planet'); ?>"></div>
                                <div class="planet-card-title">
                                    <div class="planet-card-name"><?php echo e($planet->name); ?></div>
                                    <div class="planet-card-coords">[<?php echo e($planet->templatePlanet->galaxy ?? 'N/A'); ?>:<?php echo e($planet->templatePlanet->system ?? 'N/A'); ?>:<?php echo e($planet->templatePlanet->position ?? 'N/A'); ?>]</div>
                                </div>
                            </div>
                            <div class="planet-card-details">
                                <div class="planet-card-fields">
                                    <span class="planet-card-fields-label">Cases:</span>
                                    <span class="planet-card-fields-value"><?php echo e($planet->used_fields); ?>/<?php echo e($planet->templatePlanet->fields ?? 'N/A'); ?></span>
                                </div>
                                <div class="planet-card-resources">
                                    <div class="planet-resource-item">
                                        <img src="/images/resources/metal.png" alt="metal" class="planet-resource-icon">
                                        <span class="planet-resource-value"><?php echo e(number_format($planet->resources->where('resource_id', 1)->first()->current_amount ?? 0)); ?></span>
                                    </div>
                                    <div class="planet-resource-item">
                                        <img src="/images/resources/crystal.png" alt="crystal" class="planet-resource-icon">
                                        <span class="planet-resource-value"><?php echo e(number_format($planet->resources->where('resource_id', 2)->first()->current_amount ?? 0)); ?></span>
                                    </div>
                                    <div class="planet-resource-item">
                                        <img src="/images/resources/deuterium.png" alt="deuterium" class="planet-resource-icon">
                                        <span class="planet-resource-value"><?php echo e(number_format($planet->resources->where('resource_id', 3)->first()->current_amount ?? 0)); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        <?php else: ?>
            <div class="planet-header">
                <h1 class="planet-name">Aucune planète sélectionnée</h1>
                <p>Veuillez sélectionner une planète pour voir ses informations.</p>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/game.blade.php ENDPATH**/ ?>