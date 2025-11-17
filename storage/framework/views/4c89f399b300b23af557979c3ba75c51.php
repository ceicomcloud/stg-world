<div page="relations">
    <div class="empire-container">
        <div class="empire-header">
            <h1 class="empire-title"><i class="fas fa-handshake"></i> Relations (Pactes)</h1>
            <p class="empire-subtitle">Gérez vos demandes et vos pactes actifs</p>
        </div>

        <div class="empire-planets">
            <div class="planet-card">
                <div class="planet-header">
                    <div class="planet-name">Demandes reçues</div>
                </div>
                <div class="planet-section">
                    <?php $__empty_1 = true; $__currentLoopData = $incoming; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $other = $r->requester;
                            $isOnline = optional($other->last_activity)?->gt(\Carbon\Carbon::now()->subMinutes(5));
                        ?>
                        <div class="grid-item">
                            <span class="item-name" wire:click="openUserProfile(<?php echo e($other->id); ?>)" style="cursor: pointer;">
                                <?php echo e($other->name); ?>

                                <?php if($isOnline): ?>
                                    <span class="relation-badge ally" title="En ligne" style="margin-left:6px;">
                                        ● En ligne
                                    </span>
                                <?php endif; ?>
                            </span>
                            <span class="item-meta">
                                <button class="vip-btn" wire:click="accept(<?php echo e($r->id); ?>)">Accepter</button>
                                <button class="vip-btn" style="background:#ef4444" wire:click="reject(<?php echo e($r->id); ?>)">Refuser</button>
                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="empty-note">Aucune demande en attente</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="planet-card">
                <div class="planet-header">
                    <div class="planet-name">Demandes envoyées</div>
                </div>
                <div class="planet-section">
                    <?php $__empty_1 = true; $__currentLoopData = $outgoing; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $other = $r->receiver;
                            $isOnline = optional($other->last_activity)?->gt(\Carbon\Carbon::now()->subMinutes(5));
                        ?>
                        <div class="grid-item">
                            <span class="item-name" wire:click="openUserProfile(<?php echo e($other->id); ?>)" style="cursor: pointer;">
                                <?php echo e($other->name); ?>

                                <?php if($isOnline): ?>
                                    <span class="relation-badge ally" title="En ligne" style="margin-left:6px;">
                                        ● En ligne
                                    </span>
                                <?php endif; ?>
                            </span>
                            <span class="item-meta">
                                <button class="vip-btn" style="background:#ef4444" wire:click="confirmCancel(<?php echo e($r->id); ?>)">Annuler</button>
                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="empty-note">Aucune demande envoyée</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="planet-card">
                <div class="planet-header">
                    <div class="planet-name">Pactes actifs</div>
                </div>
                <div class="planet-section">
                    <?php $__empty_1 = true; $__currentLoopData = $accepted; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $other = $r->requester_id === auth()->id() ? $r->receiver : $r->requester;
                            $isOnline = optional($other->last_activity)?->gt(\Carbon\Carbon::now()->subMinutes(5));
                        ?>
                        <div class="grid-item">
                            <span class="item-name" wire:click="openUserProfile(<?php echo e($other->id); ?>)" style="cursor: pointer;">
                                <?php echo e($other->name); ?>

                                <?php if($isOnline): ?>
                                    <span class="relation-badge ally" title="En ligne" style="margin-left:6px;">
                                        ● En ligne
                                    </span>
                                <?php endif; ?>
                            </span>
                            <span class="item-meta">
                                Depuis <?php echo e(optional($r->accepted_at)->format('d/m/Y')); ?>

                                <button class="vip-btn" style="margin-left:8px;background:#ef4444" wire:click="confirmBreak(<?php echo e($r->id); ?>)">Annuler le pacte</button>
                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="empty-note">Aucun pacte actif</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if (isset($component)) { $__componentOriginal3347679a0053d4c83e098c2ad5c70893 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3347679a0053d4c83e098c2ad5c70893 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.modal-confirmation','data' => ['wire:model' => 'showCancelModal','title' => 'Confirmer l\'annulation','message' => 'Êtes-vous sûr de vouloir annuler cette demande de pacte ?','icon' => 'fas fa-question-circle text-warning','confirmText' => 'Confirmer l\'annulation','cancelText' => 'Annuler','onConfirm' => 'performCancel','onCancel' => 'dismissModals']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.modal-confirmation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'showCancelModal','title' => 'Confirmer l\'annulation','message' => 'Êtes-vous sûr de vouloir annuler cette demande de pacte ?','icon' => 'fas fa-question-circle text-warning','confirmText' => 'Confirmer l\'annulation','cancelText' => 'Annuler','onConfirm' => 'performCancel','onCancel' => 'dismissModals']); ?>
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

        <?php if (isset($component)) { $__componentOriginal3347679a0053d4c83e098c2ad5c70893 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3347679a0053d4c83e098c2ad5c70893 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.modal-confirmation','data' => ['wire:model' => 'showBreakModal','title' => 'Annuler le pacte','message' => 'Êtes-vous sûr de vouloir annuler ce pacte ? L\'autre joueur sera notifié.','icon' => 'fas fa-exclamation-triangle text-danger','confirmText' => 'Oui, annuler le pacte','cancelText' => 'Conserver le pacte','onConfirm' => 'performBreak','onCancel' => 'dismissModals']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.modal-confirmation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'showBreakModal','title' => 'Annuler le pacte','message' => 'Êtes-vous sûr de vouloir annuler ce pacte ? L\'autre joueur sera notifié.','icon' => 'fas fa-exclamation-triangle text-danger','confirmText' => 'Oui, annuler le pacte','cancelText' => 'Conserver le pacte','onConfirm' => 'performBreak','onCancel' => 'dismissModals']); ?>
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
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/relations.blade.php ENDPATH**/ ?>