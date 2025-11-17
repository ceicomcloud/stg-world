<div class="admin-sidebar <?php echo e($isOpen ? 'open' : ''); ?>">
    <!-- En-tête du menu -->
    <div class="admin-sidebar-header">
        <div class="admin-logo">
            <i class="fas fa-circle-notch fa-spin"></i>
            <span><?php echo e(config('app.name')); ?> Admin</span>
        </div>
        <button class="admin-sidebar-toggle" wire:click="toggleMenu">
            <i class="fas <?php echo e($isOpen ? 'fa-times' : 'fa-bars'); ?>"></i>
        </button>
    </div>
    
    <!-- Navigation -->
    <nav class="admin-nav">
        <?php $__currentLoopData = $menuSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="admin-nav-section">
                <div class="admin-nav-title"><?php echo e($section['title']); ?></div>
                
                <?php $__currentLoopData = $section['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route($item['route'])); ?>" 
                       class="admin-nav-item <?php echo e($this->isRouteActive($item['route']) ? 'active' : ''); ?>">
                        <i class="fas fa-<?php echo e($item['icon']); ?>"></i>
                        <span><?php echo e($item['name']); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>
    
    <!-- Informations utilisateur -->
    <div class="admin-user-info">
        <div class="admin-user-avatar">
            <?php echo e($this->getUserInitials()); ?>

        </div>
        <div class="admin-user-details">
            <div class="admin-user-name"><?php echo e($user->name); ?></div>
            <div class="admin-user-role"><?php echo e($user->getRoleName()); ?></div>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/admin/widget/menu.blade.php ENDPATH**/ ?>