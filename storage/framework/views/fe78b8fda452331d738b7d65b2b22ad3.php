<div page="ranking" 
     wire:keydown.arrow-left="previousPage" 
     wire:keydown.arrow-right="nextPage" 
     tabindex="0">
    <div class="ranking-container">
        <!-- Barre de recherche -->        
        <div class="ranking-search-container">
            <div class="search-input-container">
                <input type="text" 
                       wire:model.live.debounce.300ms="searchQuery" 
                       placeholder="Rechercher un joueur..." 
                       class="search-input">
                <?php if($searchQuery): ?>
                    <button class="search-reset-btn" wire:click="resetSearch">
                        <i class="fas fa-times"></i>
                    </button>
                <?php endif; ?>
            </div>
            <?php if($compareUserData): ?>
                <div class="compare-info">
                    <span>Comparaison avec: <strong><?php echo e($compareUserData['name']); ?></strong></span>
                    <button class="compare-cancel-btn" wire:click="cancelComparison">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Onglets de catégories -->
        <div class="ranking-tabs">
            <div class="ranking-tab <?php echo e($activeCategory === 'total' ? 'active' : ''); ?>" 
                 wire:click="switchCategory('total')">
                <i class="fas fa-trophy"></i>
                Total
            </div>
            <div class="ranking-tab <?php echo e($activeCategory === 'event' ? 'active' : ''); ?>" 
                 wire:click="switchCategory('event')">
                <i class="fas fa-flag"></i>
                Événement
            </div>
            <div class="ranking-tab <?php echo e($activeCategory === 'buildings' ? 'active' : ''); ?>" 
                 wire:click="switchCategory('buildings')">
                <i class="fas fa-building"></i>
                Bâtiments
            </div>
            <div class="ranking-tab <?php echo e($activeCategory === 'units' ? 'active' : ''); ?>" 
                 wire:click="switchCategory('units')">
                <i class="fas fa-users"></i>
                Unités
            </div>
            <div class="ranking-tab <?php echo e($activeCategory === 'defense' ? 'active' : ''); ?>" 
                 wire:click="switchCategory('defense')">
                <i class="fas fa-shield-alt"></i>
                Défenses
            </div>
            <div class="ranking-tab <?php echo e($activeCategory === 'ships' ? 'active' : ''); ?>" 
                 wire:click="switchCategory('ships')">
                <i class="fas fa-rocket"></i>
                Vaisseaux
            </div>
            <div class="ranking-tab <?php echo e($activeCategory === 'technology' ? 'active' : ''); ?>" 
                 wire:click="switchCategory('technology')">
                <i class="fas fa-flask"></i>
                Technologies
            </div>
        </div>

        <!-- Info: Limites Fort/Faible (Attaque & Espionnage) -->
        <div class="ranking-info-banner">
            <div class="banner-header">
                <i class="fas fa-shield-alt"></i>
                <div class="banner-title">
                    <strong>Système Fort/Faible</strong>
                </div>
            </div>

            <?php if($spyEnabled || $atkEnabled): ?>
            <div class="banner-grid">
                <?php if($atkEnabled): ?>
                    <div class="banner-section attack">
                        <div class="section-title"><i class="fas fa-crosshairs"></i> Attaques</div>
                        <?php if(!is_null($atkExampleBase)): ?>
                            <div class="band-visual">
                                <div class="band-range attack">
                                    <span class="band-marker min"><?php echo e(number_format($atkMin)); ?></span>
                                    <span class="band-center"><?php echo e(number_format($atkExampleBase)); ?></span>
                                    <span class="band-marker max"><?php echo e(number_format($atkMax)); ?></span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="band-legend muted">Limites d'attaque désactivées.</div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if($spyEnabled): ?>
                    <div class="banner-section spy">
                        <div class="section-title"><i class="fas fa-eye"></i> Espionnage</div>
                        <?php if(!is_null($spyExampleBase)): ?>
                            <div class="band-visual">
                                <div class="band-range spy">
                                    <span class="band-marker min"><?php echo e(number_format($spyMin)); ?></span>
                                    <span class="band-center"><?php echo e(number_format($spyExampleBase)); ?></span>
                                    <span class="band-marker max"><?php echo e(number_format($spyMax)); ?></span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="band-legend muted">Limites d'espionnage désactivées.</div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
                <div class="band-legend muted">Les limitations Fort/Faible sont désactivées par l'administrateur.</div>
            <?php endif; ?>
        </div>

        <?php if($activeCategory === 'event'): ?>
            <!-- Cadre d'information Événement -->
            <div class="event-info-frame">
                <div class="event-info-header">
                    <span>Événement en cours</span>
                </div>

                <?php if($eventActive): ?>
                    <div class="event-user-reward">
                        <span class="label">Votre gain estimé</span>
                        <?php if(!is_null($eventUserReward)): ?>
                            <span class="value"><?php echo e($eventUserRewardText); ?></span>
                            <span class="sub">avec <?php echo e(number_format($eventUserPoints)); ?> points</span>
                        <?php else: ?>
                            <span class="value muted">Aucun point pour l'instant</span>
                            <span class="sub">Gagnez des points pour débloquer une récompense</span>
                        <?php endif; ?>
                    </div>
                    <div class="event-info-grid">
                        <div class="event-info-item">
                            <span class="label">Type</span>
                            <span class="value"><?php echo e($eventTypeLabel); ?></span>
                        </div>
                        <div class="event-info-item">
                            <span class="label">Durée</span>
                            <span class="value"><?php echo e($eventDurationDays ? $eventDurationDays . ' jours' : 'N/A'); ?></span>
                        </div>
                        <div class="event-info-item">
                            <span class="label">Récompense</span>
                            <span class="value"><?php echo e($rewardTypeLabel); ?> (base <?php echo e(number_format($baseReward)); ?>, x<?php echo e($pointsMultiplier); ?>)</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="band-legend muted">Aucun événement actif actuellement.</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Tableau de classement -->
        <?php if(count($rankings) > 0): ?>
            <div class="ranking-table-container">
                <table class="ranking-table">
                    <thead>
                        <tr>
                            <th>Rang</th>
                            <th>Joueur</th>
                            <th>Alliance</th>
                            <th>Points <?php echo e($this->getCategoryLabel($activeCategory)); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $rankings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ranking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="<?php echo e($ranking->id === $user->id ? 'current-user-row' : ($ranking->isAllied ? 'ally-row' : ($ranking->isEnemy ? 'enemy-row' : ''))); ?>">
                                <td>
                                    <div class="ranking-position-container">
                                        <div class="ranking-position <?php echo e($ranking->rank <= 3 ? ($ranking->rank === 1 ? 'gold' : ($ranking->rank === 2 ? 'silver' : 'bronze')) : 'regular'); ?>">
                                            <?php echo e($ranking->rank); ?>

                                        </div>
                                        <?php if(!empty($ranking->changeIndicator) && ($ranking->changeIndicator['change'] ?? 0) !== 0): ?>
                                            <div class="ranking-change-indicator <?php echo e($ranking->changeIndicator['class'] ?? ''); ?>">
                                                <i class="<?php echo e($ranking->changeIndicator['icon'] ?? ''); ?> icon"></i>
                                                <span class="text"><?php echo e($ranking->changeIndicator['text'] ?? ''); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="player-actions">
                                        <div class="player-name <?php echo e($ranking->id === $user->id ? 'current-user' : ''); ?> <?php echo e($ranking->bandClass ?? ''); ?>" 
                                             wire:click="openUserProfile(<?php echo e($ranking->id); ?>)" 
                                             style="cursor: pointer;">
                                            <?php echo e($ranking->name); ?>

                                            <i class="fas fa-eye player-info-icon"></i>
                                            <span class="relation-badges">
                                                <?php if($ranking->id === $user->id): ?>
                                                    <span class="relation-badge me">
                                                        <i class="fas fa-user icon"></i> Moi
                                                    </span>
                                                <?php elseif(!empty($ranking->isAllied)): ?>
                                                    <span class="relation-badge ally">
                                                        <i class="fas fa-handshake icon"></i> Allié
                                                    </span>
                                                <?php elseif(!empty($ranking->isEnemy)): ?>
                                                    <span class="relation-badge enemy">
                                                        <i class="fas fa-crosshairs icon"></i> Ennemi
                                                    </span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <?php if($ranking->id !== $user->id): ?>
                                            <div class="player-compare-actions">
                                                <?php if($compareUserData && $compareUserData['id'] !== $ranking->id): ?>
                                                    <button class="compare-btn" 
                                                            wire:click="compareUsers(<?php echo e($compareUserData['id']); ?>, <?php echo e($ranking->id); ?>)" 
                                                            title="Comparer avec <?php echo e($compareUserData['name']); ?>">
                                                        <i class="fas fa-balance-scale"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button class="select-compare-btn <?php echo e($compareUserData && $compareUserData['id'] === $ranking->id ? 'active' : ''); ?>" 
                                                        wire:click="selectUserForComparison(<?php echo e($ranking->id); ?>)" 
                                                        title="<?php echo e($compareUserData && $compareUserData['id'] === $ranking->id ? 'Sélectionné pour comparaison' : 'Sélectionner pour comparaison'); ?>">
                                                    <i class="fas fa-<?php echo e($compareUserData && $compareUserData['id'] === $ranking->id ? 'check' : 'user-plus'); ?>"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if($ranking->alliance_id): ?>
                                        <div class="alliance-name" 
                                             wire:click="openAllianceProfile(<?php echo e($ranking->alliance_id); ?>)" 
                                             style="cursor: pointer; color: var(--stargate-primary); font-weight: 600;">
                                            [<?php echo e($ranking->alliance->tag ?? 'N/A'); ?>] <?php echo e($ranking->alliance->name ?? 'Alliance inconnue'); ?>

                                            <i class="fas fa-eye alliance-info-icon" style="margin-left: 0.5rem; font-size: 0.875rem;"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="no-alliance" style="color: var(--stargate-text-secondary); font-style: italic;">
                                            Aucune alliance
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="points-value">
                                        <?php if($activeCategory === 'event'): ?>
                                            <?php echo e($this->getEventPointsForUser($ranking)); ?>

                                        <?php else: ?>
                                            <?php echo e($this->getPointsForCategory($ranking->userStat, $activeCategory)); ?>

                                        <?php endif; ?>
                                    </div>
                                </td>
                        
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if($totalPages > 1): ?>
                <div class="ranking-pagination">
                    <button class="pagination-btn <?php echo e($currentPage <= 1 ? 'disabled' : ''); ?>" 
                            wire:click="previousPage" 
                            <?php echo e($currentPage <= 1 ? 'disabled' : ''); ?>>
                        <i class="fas fa-chevron-left"></i>
                        Précédent
                    </button>
                    
                    <?php if($paginationStart > 1): ?>
                        <button class="pagination-btn" wire:click="goToPage(1)">1</button>
                        <?php if($paginationStart > 2): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for($i = $paginationStart; $i <= $paginationEnd; $i++): ?>
                        <button class="pagination-btn <?php echo e($i === $currentPage ? 'active' : ''); ?>" 
                                wire:click="goToPage(<?php echo e($i); ?>)">
                            <?php echo e($i); ?>

                        </button>
                    <?php endfor; ?>
                    
                    <?php if($paginationEnd < $totalPages): ?>
                        <?php if($paginationEnd < $totalPages - 1): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                        <button class="pagination-btn" wire:click="goToPage(<?php echo e($totalPages); ?>)"><?php echo e($totalPages); ?></button>
                    <?php endif; ?>
                    
                    <button class="pagination-btn <?php echo e($currentPage >= $totalPages ? 'disabled' : ''); ?>" 
                            wire:click="nextPage" 
                            <?php echo e($currentPage >= $totalPages ? 'disabled' : ''); ?>>
                        Suivant
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Position de l'utilisateur actuel -->
            <?php if($activeCategory !== 'event' && $userRanking && $userRanking['rank'] > $perPage): ?>
                <div class="user-ranking-info">
                    <div class="ranking-table-container">
                        <table class="ranking-table">
                            <thead>
                                <tr>
                                    <th colspan="<?php echo e($activeCategory === 'total' ? 4 : 3); ?>">Votre Position</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="current-user-row">
                                    <td>
                                        <div class="ranking-position-container">
                                            <div class="ranking-position regular">
                                                <?php echo e($userRanking['rank'] ?? 'N/A'); ?>

                                            </div>
                                            <?php if(!empty($userChangeIndicator) && ($userChangeIndicator['change'] ?? 0) !== 0): ?>
                                                <div class="ranking-change <?php echo e($userChangeIndicator['class'] ?? ''); ?>">
                                                    <i class="<?php echo e($userChangeIndicator['icon'] ?? ''); ?>"></i>
                                                    <span><?php echo e($userChangeIndicator['text'] ?? ''); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="player-actions">
                                            <div class="player-name current-user <?php echo e(($user->vip_active && ($user->vip_badge_enabled ?? true)) ? 'vip-frame' : ''); ?>" 
                                                 wire:click="openUserProfile(<?php echo e($user->id); ?>)" 
                                                 style="cursor: pointer;">
                                                <?php echo e($user->name); ?>

                                                <i class="fas fa-eye player-info-icon"></i>
                                            </div>
                                            <?php if($compareUserData && $compareUserData['id'] !== $user->id): ?>
                                                <div class="player-compare-actions">
                                                    <button class="compare-btn" 
                                                            wire:click="compareUsers(<?php echo e($compareUserData['id']); ?>, <?php echo e($user->id); ?>)" 
                                                            title="Comparer avec <?php echo e($compareUserData['name']); ?>">
                                                        <i class="fas fa-balance-scale"></i>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="points-value">
                                            <?php echo e(number_format($userRanking['points'])); ?>

                                        </div>
                                    </td>
                                    <?php if($activeCategory === 'total' && $user->userStat): ?>
                                        <td>
                                            <div class="points-breakdown">
                                                <?php if($user->userStat->building_points > 0): ?>
                                                    <div class="points-category buildings">
                                                        <i class="fas fa-building icon"></i>
                                                        <?php echo e(number_format($user->userStat->building_points)); ?>

                                                    </div>
                                                <?php endif; ?>
                                                <?php if($user->userStat->units_points > 0): ?>
                                                    <div class="points-category units">
                                                        <i class="fas fa-users icon"></i>
                                                        <?php echo e(number_format($user->userStat->units_points)); ?>

                                                    </div>
                                                <?php endif; ?>
                                                <?php if($user->userStat->defense_points > 0): ?>
                                                    <div class="points-category defense">
                                                        <i class="fas fa-shield-alt icon"></i>
                                                        <?php echo e(number_format($user->userStat->defense_points)); ?>

                                                    </div>
                                                <?php endif; ?>
                                                <?php if($user->userStat->ship_points > 0): ?>
                                                    <div class="points-category ships">
                                                        <i class="fas fa-rocket icon"></i>
                                                        <?php echo e(number_format($user->userStat->ship_points)); ?>

                                                    </div>
                                                <?php endif; ?>
                                                <?php if($user->userStat->technology_points > 0): ?>
                                                    <div class="points-category technology">
                                                        <i class="fas fa-flask icon"></i>
                                                        <?php echo e(number_format($user->userStat->technology_points)); ?>

                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- État vide -->
            <div class="empty-state">
                <div class="icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="message">Aucun classement disponible</div>
                <div class="submessage">Les points des joueurs n'ont pas encore été calculés.</div>
            </div>
        <?php endif; ?>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/ranking.blade.php ENDPATH**/ ?>