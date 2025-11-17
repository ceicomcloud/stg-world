<?php if (isset($component)) { $__componentOriginala4259a06672d2e0312d07ba974b557fd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala4259a06672d2e0312d07ba974b557fd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.game','data' => ['title' => 'Alliance']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.game'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Alliance']); ?>
    <?php
        $user = auth()->user();
        $alliance = $user?->alliance;
        $userAllianceMember = $user?->allianceMember;
    ?>
    <div page="alliance">
        <div class="alliance-container">
            <!-- Navigation Tabs (persistants via layout) -->
            <div class="alliance-tabs">
            <?php if($alliance): ?>
                <a wire:navigate.hover 
                   href="<?php echo e(route('game.alliance.overview')); ?>"
                   class="alliance-tab <?php echo e(request()->routeIs('game.alliance.overview') ? 'active' : ''); ?>">
                    📊 Vue d'ensemble
                </a>
                <a wire:navigate.hover 
                   href="<?php echo e(route('game.alliance.members')); ?>"
                   class="alliance-tab <?php echo e(request()->routeIs('game.alliance.members') ? 'active' : ''); ?>">
                    👥 Membres
                </a>
                <a wire:navigate.hover 
                   href="<?php echo e(route('game.alliance.bank')); ?>"
                   class="alliance-tab <?php echo e(request()->routeIs('game.alliance.bank') ? 'active' : ''); ?>">
                    🏦 Banque
                </a>
                <?php if($userAllianceMember && $userAllianceMember->hasPermission('manage_ranks')): ?>
                    <a wire:navigate.hover 
                       href="<?php echo e(route('game.alliance.ranks')); ?>"
                       class="alliance-tab <?php echo e(request()->routeIs('game.alliance.ranks') ? 'active' : ''); ?>">
                        🎖️ Rangs
                    </a>
                <?php endif; ?>
                <?php if($userAllianceMember && $userAllianceMember->hasPermission('manage_members')): ?>
                    <a wire:navigate.hover 
                       href="<?php echo e(route('game.alliance.management')); ?>"
                       class="alliance-tab <?php echo e(request()->routeIs('game.alliance.management') ? 'active' : ''); ?>">
                        ⚙️ Gestion Membres
                    </a>
                <?php endif; ?>
                <?php if($userAllianceMember && $userAllianceMember->hasPermission('manage_applications')): ?>
                    <a wire:navigate.hover 
                       href="<?php echo e(route('game.alliance.applications')); ?>"
                       class="alliance-tab <?php echo e(request()->routeIs('game.alliance.applications') ? 'active' : ''); ?>">
                        📝 Candidatures
                    </a>
                <?php endif; ?>
                <a wire:navigate.hover 
                   href="<?php echo e(route('game.alliance.wars')); ?>"
                   class="alliance-tab <?php echo e(request()->routeIs('game.alliance.wars') ? 'active' : ''); ?>">
                    ⚔️ Guerres
                </a>
                <?php if($userAllianceMember && $userAllianceMember->hasPermission('manage_alliance')): ?>
                    <a wire:navigate.hover 
                       href="<?php echo e(route('game.alliance.technologies')); ?>"
                       class="alliance-tab <?php echo e(request()->routeIs('game.alliance.technologies') ? 'active' : ''); ?>">
                        🔬 Technologies
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a wire:navigate.hover 
                   href="<?php echo e(route('game.alliance.search')); ?>"
                   class="alliance-tab <?php echo e(request()->routeIs('game.alliance.search') ? 'active' : ''); ?>">
                    🔍 Rechercher
                </a>
                <a wire:navigate.hover 
                   href="<?php echo e(route('game.alliance.create')); ?>"
                   class="alliance-tab <?php echo e(request()->routeIs('game.alliance.create') ? 'active' : ''); ?>">
                    ➕ Créer
                </a>
            <?php endif; ?>
            </div>

            <div class="alliance-content">
                <?php echo e($slot); ?>

            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala4259a06672d2e0312d07ba974b557fd)): ?>
<?php $attributes = $__attributesOriginala4259a06672d2e0312d07ba974b557fd; ?>
<?php unset($__attributesOriginala4259a06672d2e0312d07ba974b557fd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala4259a06672d2e0312d07ba974b557fd)): ?>
<?php $component = $__componentOriginala4259a06672d2e0312d07ba974b557fd; ?>
<?php unset($__componentOriginala4259a06672d2e0312d07ba974b557fd); ?>
<?php endif; ?><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/layouts/alliance-layout.blade.php ENDPATH**/ ?>