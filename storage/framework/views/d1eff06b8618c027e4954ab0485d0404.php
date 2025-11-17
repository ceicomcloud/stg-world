<div>
    <h3>📝 Candidatures</h3>
                    
    <div class="applications-section">
        <?php if(isset($pendingApplications) && $pendingApplications->count() > 0): ?>
            <?php $__currentLoopData = $pendingApplications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $application): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="application-item">
                    <div class="application-header">
                        <div class="applicant-info">
                            <div class="applicant-avatar">
                                <span class="applicant-initial"><?php echo e(substr($application->user->name, 0, 1)); ?></span>
                            </div>
                            <div class="applicant-details">
                                <h4 class="applicant-name"><?php echo e($application->user->name); ?></h4>
                                <span class="application-date">Candidature du <?php echo e($application->created_at->format('d/m/Y à H:i')); ?></span>
                            </div>
                        </div>
                        <div class="application-status">
                            <span class="status-badge status-pending">En attente</span>
                        </div>
                    </div>
                    
                    <?php if($application->message): ?>
                        <div class="application-message">
                            <h5>💬 Message de candidature:</h5>
                            <p><?php echo e($application->message); ?></p>
                        </div>
                    <?php endif; ?>
                                                        
                    <div class="application-actions">
                        <button class="btn btn-success" 
                                wire:click="confirmAcceptApplication(<?php echo e($application->id); ?>)">
                            ✅ Accepter
                        </button>
                        <button class="btn btn-danger" 
                                wire:click="confirmRejectApplication(<?php echo e($application->id); ?>)">
                            ❌ Rejeter
                        </button>
                        <button class="btn btn-secondary" 
                                wire:click="viewUserProfile(<?php echo e($application->user->id); ?>)">
                            👤 Voir le profil
                        </button>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            
            <!-- Pagination -->
            <div class="applications-pagination">
                <?php echo e($pendingApplications->links()); ?>

            </div>
        <?php else: ?>
            <div class="no-applications">
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h4>Aucune candidature en attente</h4>
                    <p>Il n'y a actuellement aucune candidature à examiner.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($component)) { $__componentOriginal3347679a0053d4c83e098c2ad5c70893 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3347679a0053d4c83e098c2ad5c70893 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.modal-confirmation','data' => ['wire:model' => 'showAcceptAppModal','wire:key' => 'alliance-modal-accept-app','title' => 'Accepter la candidature','message' => 'Accepter cette candidature ?','icon' => 'fas fa-check-circle text-success','confirmText' => 'Accepter','cancelText' => 'Annuler','onConfirm' => 'performAcceptApplication','onCancel' => 'dismissModals']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.modal-confirmation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'showAcceptAppModal','wire:key' => 'alliance-modal-accept-app','title' => 'Accepter la candidature','message' => 'Accepter cette candidature ?','icon' => 'fas fa-check-circle text-success','confirmText' => 'Accepter','cancelText' => 'Annuler','onConfirm' => 'performAcceptApplication','onCancel' => 'dismissModals']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.modal-confirmation','data' => ['wire:model' => 'showRejectAppModal','wire:key' => 'alliance-modal-reject-app','title' => 'Rejeter la candidature','message' => 'Rejeter cette candidature ?','icon' => 'fas fa-times-circle text-danger','confirmText' => 'Rejeter','cancelText' => 'Annuler','onConfirm' => 'performRejectApplication','onCancel' => 'dismissModals']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.modal-confirmation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'showRejectAppModal','wire:key' => 'alliance-modal-reject-app','title' => 'Rejeter la candidature','message' => 'Rejeter cette candidature ?','icon' => 'fas fa-times-circle text-danger','confirmText' => 'Rejeter','cancelText' => 'Annuler','onConfirm' => 'performRejectApplication','onCancel' => 'dismissModals']); ?>
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
<?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/alliance/application.blade.php ENDPATH**/ ?>