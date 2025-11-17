<div page="dashboard">
    <div class="dashboard-content">
        <div class="central-menu">
            <div class="menu-card">
                <div class="menu-header">
                    <h1 class="game-title">
                        <i class="fas fa-circle-notch stargate-spin"></i>
                        <?php echo e(config('app.name')); ?>

                    </h1>
                </div>

                <div class="user-info">
                    <div class="user-avatar">
                        <?php if(!empty($avatarUrl)): ?>
                            <img src="<?php echo e($avatarUrl); ?>" alt="Avatar de <?php echo e($user->name); ?>" style="width:64px; height:64px; border-radius:50%; object-fit:cover;" />
                        <?php else: ?>
                            <i class="fas fa-user-astronaut"></i>
                        <?php endif; ?>
                    </div>
                    <div class="user-details">
                        <h2 class="username"><?php echo e($user->name); ?></h2>
                        <p class="user-email"><?php echo e($user->email); ?></p>
                    </div>
                </div>

                <div class="main-action">
                    <button class="btn-play" wire:click="handleMainAction" wire:loading.attr="disabled">
                        <?php if($user->main_planet_id): ?>
                            <i class="fas fa-play"></i>
                        <?php else: ?>
                            <i class="fas fa-user-plus"></i>
                        <?php endif; ?>
                        <span><?php echo e($this->getMainButtonText()); ?></span>
                    </button>
                </div>

                <div class="secondary-menu">
                    <button class="btn-secondary" wire:click="goToProfile">
                        <i class="fas fa-user"></i>
                        Profil
                    </button>
                    <button class="btn-secondary" wire:click="goToSettings">
                        <i class="fas fa-cog"></i>
                        Paramètres
                    </button>
                    <button class="btn-secondary" wire:click="logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Déconnexion
                    </button>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/dashboard/dashboard.blade.php ENDPATH**/ ?>