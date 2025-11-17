<nav class="game-navbar" layout="gamenavbar" x-data>
    <?php
        $truceEnabled = \App\Models\Server\ServerConfig::get('truce_enabled');
        $truceMessage = \App\Models\Server\ServerConfig::get('truce_message');
    ?>
    <!--[if BLOCK]><![endif]--><?php if($truceEnabled): ?>
        <div class="truce-banner" role="status" aria-live="polite">
            <div class="truce-banner__inner">
                <i class="fas fa-handshake"></i>
                <span><?php echo e($truceMessage ?? 'Trêve active: certaines actions sont temporairement désactivées.'); ?></span>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <div class="navbar-container">
        <!-- Navigation Links -->
        <div class="navbar-nav">
            <button x-on:click="$dispatch('toggle-drawer')" class="nav-link">
                <i class="fas fa-bars"></i>
            </button>

            <a href="<?php echo e(route('game.index')); ?>" wire:navigate.hover class="nav-link <?php echo e(request()->routeIs('game.index') ? 'active' : ''); ?>">
                <i class="fas fa-home"></i>
                <span>Accueil</span>
            </a>

            <a href="<?php echo e(route('game.mission.index')); ?>" wire:navigate.hover class="nav-link <?php echo e(request()->routeIs('game.mission.index') ? 'active' : ''); ?>">
                <i class="fas fa-bullseye"></i>
                <span>Mission</span>
            </a>

            <a href="<?php echo e(route('game.construction.type', ['type' => 'building'])); ?>" wire:navigate.hover class="nav-link <?php echo e(request()->routeIs('game.construction.type') ? 'active' : ''); ?>">
                <i class="fas fa-hammer"></i>
                <span>Construction</span>
            </a>

            <a href="<?php echo e(route('game.technology')); ?>" wire:navigate.hover class="nav-link <?php echo e(request()->routeIs('game.technology') ? 'active' : ''); ?>">
                <i class="fas fa-flask"></i>
                <span>Technologies</span>
            </a>

            <a href="<?php echo e(route('game.galaxy')); ?>" wire:navigate.hover class="nav-link <?php echo e(request()->routeIs('game.galaxy') ? 'active' : ''); ?>">
                <i class="fas fa-star"></i>
                <span>Galaxie</span>
            </a>

            <a href="<?php echo e(route('game.empire')); ?>" wire:navigate.hover class="nav-link <?php echo e(request()->routeIs('game.empire') ? 'active' : ''); ?>">
                <i class="fas fa-globe"></i>
                <span>Empire</span>
            </a>

            <a href="<?php echo e(route('game.relations')); ?>" wire:navigate.hover class="nav-link <?php echo e(request()->routeIs('game.relations') ? 'active' : ''); ?>">
                <i class="fas fa-handshake"></i>
                <span>Relations</span>
            </a>

            <a href="<?php echo e(route('game.ranking')); ?>" wire:navigate.hover class="nav-link <?php echo e(request()->routeIs('game.ranking') ? 'active' : ''); ?>">
                <i class="fas fa-trophy"></i>
                <span>Classement</span>
            </a>

            <a href="<?php echo e(route('game.alliance.overview')); ?>" wire:navigate.hover class="nav-link <?php echo e(request()->routeIs('game.alliance.overview') ? 'active' : ''); ?>">
                <i class="fas fa-shield-alt"></i>
                <span>Alliance</span>
            </a>

            <a href="<?php echo e(route('game.bunker')); ?>" wire:navigate.hover class="nav-link <?php echo e(request()->routeIs('game.bunker') ? 'active' : ''); ?>">
                <i class="fas fa-warehouse"></i>
                <span>Bunker</span>
            </a>

            <a href="<?php echo e(route('game.chatbox')); ?>" wire:navigate.hover class="nav-link <?php echo e(request()->routeIs('game.chatbox') ? 'active' : ''); ?>">
                <i class="fas fa-comments"></i>
                <span>Chat <!--[if BLOCK]><![endif]--><?php if(($unreadChatCount ?? 0) > 0): ?><span class="message-count"><?php echo e(($unreadChatCount ?? 0) > 9 ? '9+' : ($unreadChatCount ?? 0)); ?></span><?php endif; ?><!--[if ENDBLOCK]><![endif]--></span>
            </a>

            <a href="<?php echo e(route('game.private')); ?>" wire:navigate.hover class="nav-link <?php echo e(request()->routeIs('game.private') ? 'active' : ''); ?>">
                <i class="fas fa-envelope"></i>
                <span>Messages <!--[if BLOCK]><![endif]--><?php if($unreadMessagesCount > 0): ?><span class="message-count"><?php echo e($unreadMessagesCount); ?></span><?php endif; ?><!--[if ENDBLOCK]><![endif]--></span>
            </a>

            <a href="<?php echo e(route('game.forum')); ?>" wire:navigate.hover class="nav-link <?php echo e(request()->routeIs('game.forum') ? 'active' : ''); ?>">
                <i class="fas fa-comments"></i>
                <span>Forum</span>
            </a>

            <!-- Lien Discord (ouvre dans un nouvel onglet) -->
            <a href="https://discord.gg/UpBp2x6VPV" class="nav-link" target="_blank" rel="noopener">
                <i class="fab fa-discord"></i>
                <span>Discord</span>
            </a>
        </div>
    </div>
</nav><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/navbar.blade.php ENDPATH**/ ?>