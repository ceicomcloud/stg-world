<div class="auth-extra">
    <div class="auth-extra-tabs">
        <button 
            class="auth-extra-tab <?php echo e(!$showGameHistory ? 'active' : ''); ?>" 
            wire:click="$set('showGameHistory', false)"
        >
            <i class="fas fa-newspaper"></i> Actualités
        </button>
        <button 
            class="auth-extra-tab <?php echo e($showGameHistory ? 'active' : ''); ?>" 
            wire:click="$set('showGameHistory', true)"
        >
            <i class="fas fa-book-open"></i> Histoire du jeu
        </button>
    </div>
        
    <div class="auth-extra-content">
        <?php if(!$showGameHistory): ?>
            <!-- Affichage des actualités -->
            <div class="news-banner">
                <?php if(count($newsItems) > 0): ?>
                    <div class="news-container" x-data="{ autoScroll: <?php if ((object) ('autoScroll') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('autoScroll'->value()); ?>')<?php echo e('autoScroll'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('autoScroll'); ?>')<?php endif; ?> }" x-init="setInterval(() => { if (autoScroll) Livewire.dispatch('autoNextNews') }, 8000)">
                        <div class="news-controls">
                            <button wire:click="previousNews" class="news-btn news-prev">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button wire:click="toggleAutoScroll" class="news-btn news-auto <?php echo e($autoScroll ? 'active' : ''); ?>">
                                <i class="fas fa-<?php echo e($autoScroll ? 'pause' : 'play'); ?>"></i>
                            </button>
                            <button wire:click="nextNews" class="news-btn news-next">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        
                        <div class="news-content">
                            
                            <?php if(isset($newsItems[$currentIndex])): ?>
                                <?php $currentNews = $newsItems[$currentIndex]; ?>
                                <div class="news-item priority-<?php echo e($currentNews['priority']); ?>">
                                    <div class="news-icon">
                                        <i class="fas fa-<?php echo e($currentNews['icon']); ?>"></i>
                                    </div>
                                    <div class="news-text">
                                        <span class="news-message"><?php echo e($currentNews['text']); ?></span>
                                        <span class="news-time"><?php echo e($currentNews['time']); ?></span>
                                    </div>
                                    <div class="news-type-badge">
                                        <?php switch($currentNews['type']):
                                            case ('news'): ?>
                                                <span class="badge badge-info">Actualité</span>
                                                <?php break; ?>
                                            <?php case ('info'): ?>
                                                <span class="badge badge-primary">Info</span>
                                                <?php break; ?>
                                            <?php default: ?>
                                                <span class="badge badge-secondary"><?php echo e($currentNews['type']); ?></span>
                                        <?php endswitch; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="news-indicator">
                            <span class="current-index"><?php echo e($currentIndex + 1); ?></span>
                            <span class="separator">/</span>
                            <span class="total-count"><?php echo e(count($newsItems)); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Affichage de l'histoire du jeu -->
            <div class="game-history">
                <h3>L'histoire de <?php echo e(config('app.name')); ?></h3>
                <div class="game-history-content">
                    <p>Dans un futur lointain, l'humanité a découvert un réseau de portes stellaires permettant de voyager instantanément entre différentes planètes de la galaxie.</p>
                    
                    <p>Après des siècles d'exploration et de colonisation, différentes factions se sont formées, chacune cherchant à contrôler les ressources et les technologies des mondes découverts.</p>
                    
                    <p>En tant que commandant d'une base spatiale, votre mission est de développer votre empire, de former des alliances et de défendre vos colonies contre les menaces extraterrestres et les autres joueurs.</p>
                    
                    <p>Explorez la galaxie, recherchez de nouvelles technologies, construisez une flotte puissante et écrivez votre propre histoire dans l'univers de <?php echo e(config('app.name')); ?>!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="server-stats">
        <div class="stat-item">
            <i class="fas fa-users"></i>
            <span class="stat-value"><?php echo e(number_format($serverStats['total_players'])); ?></span>
            <span class="stat-label">Joueurs</span>
        </div>
        <div class="stat-item">
            <i class="fas fa-user-clock"></i>
            <span class="stat-value"><?php echo e(number_format($serverStats['online_players'])); ?></span>
            <span class="stat-label">En ligne</span>
        </div>
        <div class="stat-item">
            <i class="fas fa-globe"></i>
            <span class="stat-value"><?php echo e(number_format($serverStats['total_planets'])); ?></span>
            <span class="stat-label">Planètes</span>
        </div>
        <div class="stat-item">
            <i class="fas fa-chart-bar"></i>
            <span class="stat-value"><?php echo e($serverStats['avg_planets_per_player']); ?></span>
            <span class="stat-label">Moy/joueur</span>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/auth/auth-extra.blade.php ENDPATH**/ ?>