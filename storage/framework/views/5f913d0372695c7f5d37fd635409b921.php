<div>
    <h3>⚔️ Guerres</h3>
                    
    <?php if($wars->count() > 0): ?>
        <div class="wars-list">
            <?php $__currentLoopData = $wars; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $war): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="war-item">
                    <div class="war-header">
                        <div class="war-alliances">
                            <?php echo e($war->attackerAlliance->name); ?> ⚔️ <?php echo e($war->defenderAlliance->name); ?>

                        </div>
                        <span class="war-status <?php echo e($war->status); ?>">
                            <?php echo e($war->formatted_status); ?>

                        </span>
                    </div>
                    
                    <?php if($war->reason): ?>
                        <p><strong>Raison:</strong> <?php echo e($war->reason); ?></p>
                    <?php endif; ?>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                        <small style="color: var(--stargate-text-secondary);">
                            Déclarée par <?php echo e($war->declaredBy->name); ?> le <?php echo e($war->created_at->format('d/m/Y H:i')); ?>

                        </small>
                        
                        <?php if($war->isActive() && $war->canBeEndedBy(Auth::user())): ?>
                            <button class="btn btn-sm btn-secondary" 
                                    wire:click="confirmEndWar(<?php echo e($war->id); ?>)">
                                🏳️ Terminer
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        
        <?php echo e($wars->links()); ?>

    <?php else: ?>
        <p style="text-align: center; color: var(--stargate-text-secondary); padding: 40px;">
            Aucune guerre en cours
        </p>
    <?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal3347679a0053d4c83e098c2ad5c70893 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3347679a0053d4c83e098c2ad5c70893 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.modal-confirmation','data' => ['wire:model' => 'showEndWarModal','wire:key' => 'alliance-modal-endwar','title' => 'Terminer la guerre','message' => 'Terminer cette guerre ?','icon' => 'fas fa-flag text-warning','confirmText' => 'Oui, terminer','cancelText' => 'Continuer la guerre','onConfirm' => 'performEndWar','onCancel' => 'dismissModals']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.modal-confirmation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'showEndWarModal','wire:key' => 'alliance-modal-endwar','title' => 'Terminer la guerre','message' => 'Terminer cette guerre ?','icon' => 'fas fa-flag text-warning','confirmText' => 'Oui, terminer','cancelText' => 'Continuer la guerre','onConfirm' => 'performEndWar','onCancel' => 'dismissModals']); ?>
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
<?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/alliance/wars.blade.php ENDPATH**/ ?>