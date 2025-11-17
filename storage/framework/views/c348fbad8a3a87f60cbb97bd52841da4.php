<div>
    <h3>👥 Membres de l'Alliance (<?php echo e($members->total()); ?>)</h3>
                    
    <div class="members-list">
        <?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="member-item">
                <div class="member-info">
                    <span class="member-name"><?php echo e($member->user->name); ?></span>
                    <?php if($member->rank): ?>
                        <span class="member-rank"><?php echo e($member->rank->name); ?></span>
                    <?php endif; ?>
                    <?php if($member->user_id === $alliance->leader_id): ?>
                        <span style="color: gold;">👑</span>
                    <?php endif; ?>
                </div>
                <div class="member-stats">
                    <span>Rejoint: <?php echo e($member->joined_at->format('d/m/Y')); ?></span>
                    <span>Contribution: <?php echo e(number_format($member->contributed_deuterium)); ?> D</span>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    
    <?php echo e($members->links()); ?>

</div>
<?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/alliance/members.blade.php ENDPATH**/ ?>