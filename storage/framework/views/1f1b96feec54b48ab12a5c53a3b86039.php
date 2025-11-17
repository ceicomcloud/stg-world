<div>
    <!-- Indicateur de type -->
    <div class="modal-type-indicator modal-type-<?php echo e($technologyData['type']); ?>">
        <i class="fas fa-<?php echo e($this->getTypeIcon()); ?>"></i>
        <?php echo e($this->getTypeLabel()); ?>

    </div>

    <!-- Image -->
    <?php if($technologyData['icon']): ?>
        <img src="<?php echo e(asset('images/technologies/' . $technologyData['icon'])); ?>"  alt="<?php echo e($technologyData['label']); ?>" class="modal-image">
    <?php endif; ?>

    <!-- Description -->
    <?php if($technologyData['description']): ?>
        <div class="modal-description">
            <?php echo e($technologyData['description']); ?>

        </div>
    <?php endif; ?>

    <!-- Niveau actuel -->
    <div class="modal-level-info">
        <i class="fas fa-layer-group"></i>
        <span class="level-text">Niveau actuel:</span>
        <span class="level-value">
            <?php echo e($technologyData['current_level']); ?>

            <?php if($technologyData['max_level'] > 0): ?>
                / <?php echo e($technologyData['max_level']); ?>

            <?php endif; ?>
        </span>
    </div>

    <!-- Avantages -->
    <?php if(count($technologyData['advantages']) > 0): ?>
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-plus-circle"></i>
                Avantages
            </h3>
            <div class="advantages-list">
                <?php $__currentLoopData = $technologyData['advantages']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $advantage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="advantage-item">
                        <i class="fas fa-check"></i>
                        <span><?php echo e($advantage['description']); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Désavantages -->
    <?php if(count($technologyData['disadvantages']) > 0): ?>
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-minus-circle"></i>
                Désavantages
            </h3>
            <div class="disadvantages-list">
                <?php $__currentLoopData = $technologyData['disadvantages']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $disadvantage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="disadvantage-item">
                        <i class="fas fa-times"></i>
                        <span><?php echo e($disadvantage['description']); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Prérequis -->
    <?php if(count($technologyData['requirements']) > 0): ?>
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-list-check"></i>
                Prérequis
            </h3>
            <div class="requirements-list">
                <?php $__currentLoopData = $technologyData['requirements']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $requirement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isMet = $this->checkRequirement($requirement);
                    ?>
                    <div class="requirement-item <?php echo e($isMet ? 'requirement-met' : 'requirement-not-met'); ?>">
                        <i class="fas fa-<?php echo e($isMet ? 'check' : 'times'); ?>"></i>
                        <span>
                            <?php echo e($requirement['required_build']['label'] ?? $requirement['required_build']['name'] ?? 'Technologie requise'); ?>

                            niveau <?php echo e($requirement['required_level']); ?>

                        </span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/modal/technology-info.blade.php ENDPATH**/ ?>