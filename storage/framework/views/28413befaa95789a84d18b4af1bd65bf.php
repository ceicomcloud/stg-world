<div layout="dashboardNavbar">
    <nav class="navbar">
        <div class="navbar-container">
            <!-- Navigation Links -->
            <div class="navbar-nav">
                <a href="<?php echo e(route('dashboard.index')); ?>" wire:navigate class="nav-link <?php echo e(request()->routeIs('dashboard*') ? 'active' : ''); ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    Tableau de bord
                </a>
                <a href="<?php echo e(route('dashboard.profile')); ?>" wire:navigate class="nav-link <?php echo e(request()->routeIs('dashboard.profile') ? 'active' : ''); ?>">
                    <i class="fas fa-user"></i>
                    Profil
                </a>
                <a href="<?php echo e(route('dashboard.settings')); ?>" wire:navigate class="nav-link <?php echo e(request()->routeIs('dashboard.settings') ? 'active' : ''); ?>">
                    <i class="fas fa-cog"></i>
                    Paramètres
                </a>
            </div>
        </div>
    </nav>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/dashboard/navbar.blade.php ENDPATH**/ ?>