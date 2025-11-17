<div class="daily-quests-modal">
    <?php if(empty($quests)): ?>
        <div class="empty-state">
            <i class="fas fa-ghost"></i>
            <span>Aucune quête pour aujourd'hui.</span>
        </div>
    <?php else: ?>
        <div class="quests-list">
            <?php $__currentLoopData = $quests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $progress = (int)($quest['progress'] ?? 0);
                    $target = (int)($quest['target'] ?? 1);
                    $percent = min(100, (int) floor(($progress / max(1,$target)) * 100));
                    $claimed = !empty($quest['claimed_at']);
                    $complete = $progress >= $target;
                ?>
                <div class="quest-card">
                    <div class="quest-header">
                        <div class="quest-title"><?php echo e($quest['title'] ?? 'Quête'); ?></div>
                        <?php if($claimed): ?>
                            <span class="badge badge-claimed"><i class="fas fa-check-circle"></i> Réclamée</span>
                        <?php elseif($complete): ?>
                            <span class="badge badge-ready"><i class="fas fa-gift"></i> Prête</span>
                        <?php else: ?>
                            <span class="badge badge-progress"><i class="fas fa-hourglass-half"></i> En cours</span>
                        <?php endif; ?>
                    </div>
                    <div class="quest-description"><?php echo e($quest['description'] ?? ''); ?></div>

                    <div class="quest-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo e($percent); ?>%"></div>
                        </div>
                        <div class="progress-text"><?php echo e($progress); ?> / <?php echo e($target); ?></div>
                    </div>

                    <?php if(isset($quest['reward']) && $quest['reward']['type'] === 'resource'): ?>
                        <div class="quest-reward">
                            <i class="fas fa-cubes"></i>
                            <span>Récompense: <?php echo e(number_format($quest['reward']['amount'])); ?> <?php echo e(ucfirst($quest['reward']['resource'])); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="quest-actions">
                        <button class="btn btn-primary" wire:click="claimReward('<?php echo e($quest['key']); ?>')" <?php if(!$complete || $claimed): echo 'disabled'; endif; ?>>
                            <i class="fas fa-gift"></i> Réclamer
                        </button>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/modal/daily-quests.blade.php ENDPATH**/ ?>