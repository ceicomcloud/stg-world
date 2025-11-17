<div layout="server_news_banner">
    <div class="server-event-banner" role="status" aria-live="polite">
        <!--[if BLOCK]><![endif]--><?php if(count($newsItems) > 0): ?>
            <div class="server-event-inner" x-data="{ autoScroll: <?php if ((object) ('autoScroll') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('autoScroll'->value()); ?>')<?php echo e('autoScroll'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('autoScroll'); ?>')<?php endif; ?> }" x-init="setInterval(() => { if (autoScroll) Livewire.dispatch('autoNextNews') }, 8000)">
                <!-- Compact controls for navigation -->
                <div class="news-controls">
                    <button wire:click="previousNews" class="news-btn news-prev" aria-label="Actualité précédente">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button wire:click="toggleAutoScroll" class="news-btn news-auto <?php echo e($autoScroll ? 'active' : ''); ?>" aria-label="Lecture automatique">
                        <i class="fas fa-<?php echo e($autoScroll ? 'pause' : 'play'); ?>"></i>
                    </button>
                    <button wire:click="nextNews" class="news-btn news-next" aria-label="Actualité suivante">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                <!-- News content aligned like server-event-banner -->
                <!--[if BLOCK]><![endif]--><?php if(isset($newsItems[$currentIndex])): ?>
                    <?php $currentNews = $newsItems[$currentIndex]; ?>
                    <div class="event-icon" aria-hidden="true">
                        <i class="fas fa-<?php echo e($currentNews['icon']); ?>"></i>
                    </div>

                    <div class="event-text">
                        <span class="news-message"><?php echo e($currentNews['text']); ?></span>
                        <span class="event-time"><?php echo e($currentNews['time']); ?></span>
                    </div>

                    <div class="event-badge">
                        <!--[if BLOCK]><![endif]--><?php switch($currentNews['type']):
                            case ('news'): ?>
                                <span class="badge badge-info">Actualité</span>
                                <?php break; ?>
                            <?php case ('colonization'): ?>
                                <span class="badge badge-success">Colonisation</span>
                                <?php break; ?>
                            <?php case ('registration'): ?>
                                <span class="badge badge-primary">Nouveau joueur</span>
                                <?php break; ?>
                        <?php endswitch; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="news-indicator" aria-label="Index de l’actualité">
                        <span class="current-index"><?php echo e($currentIndex + 1); ?></span>
                        <span class="separator">/</span>
                        <span class="total-count"><?php echo e(count($newsItems)); ?></span>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        <?php else: ?>
            <div class="server-event-inner">
                <div class="event-icon" aria-hidden="true">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="event-text">
                    <span class="news-message">Bienvenue sur <?php echo e(config('app.name')); ?> ! Aucune actualité récente.</span>
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/server-news-banner.blade.php ENDPATH**/ ?>