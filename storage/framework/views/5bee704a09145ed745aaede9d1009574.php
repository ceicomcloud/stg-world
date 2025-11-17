<div>
    <h3>👥 Gestion des Membres</h3>
                    
    <div class="member-management-section">
        <?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="member-management-item">
                <div class="member-basic-info">
                    <div class="member-avatar">
                        <span class="member-initial"><?php echo e(substr($member->user->name, 0, 1)); ?></span>
                    </div>
                    <div class="member-details">
                        <span class="member-name"><?php echo e($member->user->name); ?></span>
                        <span class="member-current-rank">
                            <?php if($member->rank): ?>
                                <?php echo e($member->rank->name); ?>

                            <?php else: ?>
                                Aucun rang
                            <?php endif; ?>
                        </span>
                        <?php if($member->user_id === $alliance->leader_id): ?>
                            <span class="leader-badge">👑 Leader</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="member-stats-detailed">
                    <div class="stat-item">
                        <span class="stat-label">Rejoint:</span>
                        <span class="stat-value"><?php echo e($member->joined_at->format('d/m/Y')); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Contribution:</span>
                        <span class="stat-value"><?php echo e(number_format($member->contributed_deuterium)); ?> D</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Dernière activité:</span>
                        <span class="stat-value"><?php echo e($member->user->last_activity ? $member->user->last_activity->diffForHumans() : 'Inconnue'); ?></span>
                    </div>
                </div>
                
                <?php if($member->user_id !== $alliance->leader_id && $userAllianceMember && $userAllianceMember->hasPermission('manage_members')): ?>
                    <div class="member-actions">
                        <div class="rank-assignment">
                            <select class="form-select" wire:change="assignRank(<?php echo e($member->id); ?>, $event.target.value)">
                                <option value="">Aucun rang</option>
                                <?php $__currentLoopData = $ranks->where('level', '<', $userAllianceMember->rank->level ?? 999); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($rank->id); ?>" <?php echo e($member->rank_id == $rank->id ? 'selected' : ''); ?>>
                                        <?php echo e($rank->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        
                        <button class="btn btn-sm btn-danger" 
                                wire:click="confirmKick(<?php echo e($member->id); ?>)">
                            🚫 Exclure
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        
        <?php echo e($members->links()); ?>

    </div>

    <?php if (isset($component)) { $__componentOriginal3347679a0053d4c83e098c2ad5c70893 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3347679a0053d4c83e098c2ad5c70893 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.modal-confirmation','data' => ['wire:model' => 'showKickModal','wire:key' => 'alliance-modal-kick','title' => 'Exclure le membre','message' => 'Êtes-vous sûr de vouloir exclure ce membre de l\'alliance ?','icon' => 'fas fa-user-slash text-danger','confirmText' => 'Oui, exclure','cancelText' => 'Annuler','onConfirm' => 'performKick','onCancel' => 'dismissModals']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.modal-confirmation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'showKickModal','wire:key' => 'alliance-modal-kick','title' => 'Exclure le membre','message' => 'Êtes-vous sûr de vouloir exclure ce membre de l\'alliance ?','icon' => 'fas fa-user-slash text-danger','confirmText' => 'Oui, exclure','cancelText' => 'Annuler','onConfirm' => 'performKick','onCancel' => 'dismissModals']); ?>
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
</div>
<?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/alliance/management.blade.php ENDPATH**/ ?>