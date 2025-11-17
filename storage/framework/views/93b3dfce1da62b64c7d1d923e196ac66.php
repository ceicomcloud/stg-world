<div page="profile">
    <div class="profile-content">
        <div class="profile-sidebar">
            <div class="profile-header">
                <img src="<?php echo e($this->avatarUrl); ?>" alt="Avatar" class="profile-avatar" />
                <h1 class="profile-name"><?php echo e($user->name); ?></h1>
                <p class="profile-email"><?php echo e($user->email); ?></p>
                <div class="profile-join-date">
                    <i class="fas fa-calendar-alt"></i>
                    Membre depuis <?php echo e($this->formatDate($user->created_at)); ?>

                </div>
            </div>

            <div class="profile-stats">
                <div class="stat-item">
                    <span class="stat-value"><?php echo e($user->getLevel()); ?></span>
                    <span class="stat-label">Niveau</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo e($this->formatNumber($user->getCurrentExperience())); ?></span>
                    <span class="stat-label">Expérience</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo e($this->formatNumber($this->researchPoints)); ?></span>
                    <span class="stat-label">Points de Recherche</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo e($this->badgeCount); ?></span>
                    <span class="stat-label">Badges</span>
                </div>
            </div>
        </div>

        <div class="profile-main">
            <div class="profile-card progression-section">
                <h2 class="section-title"><i class="fas fa-chart-line"></i> Progression</h2>

                <div class="level-info">
                    <span class="current-level">Niveau <?php echo e($user->getLevel()); ?></span>
                    <span class="next-level">Niveau <?php echo e($user->getLevel() + 1); ?></span>
                </div>

                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo e($this->experienceProgress); ?>%"></div>
                </div>

                <div class="progress-text">
                    <?php echo e($this->experienceToNext); ?> / <?php echo e($this->experienceForNext); ?> XP (<?php echo e(round($this->experienceProgress)); ?>%)
                </div>
            </div>

            <div class="profile-card vacation-mode-section">
                <h2 class="section-title"><i class="fas fa-plane"></i> Mode Vacances</h2>
                <div class="vacation-mode-content">
                    <?php if($this->is_in_vacation_mode): ?>
                        <div class="vacation-active">
                            <div class="vacation-status">
                                <i class="fas fa-check-circle"></i> Mode vacances actif
                            </div>
                            <div class="vacation-info">
                                <p>Votre compte est actuellement en mode vacances.</p>
                                <p>Fin du mode vacances: <strong><?php echo e($this->formatDate($this->vacation_mode_end_date)); ?></strong></p>
                                <p>Jours restants: <strong><?php echo e($this->remaining_vacation_days); ?></strong></p>
                            </div>
                            <button class="btn-primary" wire:click="disableVacationMode" wire:loading.attr="disabled">
                                <i class="fas fa-power-off"></i> Désactiver le mode vacances
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="vacation-inactive">
                            <div class="vacation-info">
                                <p>Le mode vacances permet de protéger votre compte pendant votre absence.</p>
                                <p>Pendant cette période:</p>
                                <ul>
                                    <li>Vos missions seront suspendues</li>
                                    <li>Votre production de ressources sera arrêtée</li>
                                    <li>Vous ne pourrez pas être attaqué</li>
                                </ul>
                            </div>
                            <div class="vacation-form">
                                <label for="vacationDays">Durée (en jours):</label>
                                <input type="range" id="vacationDays" wire:model.live="vacationDays" min="<?php echo e($minVacationDays); ?>" max="<?php echo e($maxVacationDays); ?>" step="1" class="vacation-slider">
                                <div class="vacation-days-display">
                                    <span><?php echo e($vacationDays); ?> jours</span>
                                    <span class="vacation-range">(Min: <?php echo e($minVacationDays); ?>, Max: <?php echo e($maxVacationDays); ?>)</span>
                                </div>
                                <button class="btn-primary" wire:click="enableVacationMode" wire:loading.attr="disabled">
                                    <i class="fas fa-plane"></i> Activer le mode vacances
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-card">
                <h2 class="section-title"><i class="fas fa-trophy"></i> Réalisations</h2>
                <div class="achievements-grid">
                    <?php $__currentLoopData = $this->achievements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $achievement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="achievement-card <?php echo e($achievement['unlocked'] ? 'unlocked' : 'locked'); ?> rarity-<?php echo e($achievement['rarity']); ?>">
                            <i class="achievement-icon <?php echo e($achievement['icon']); ?>"></i>
                            <div class="achievement-name"><?php echo e($achievement['name']); ?></div>
                            <div class="achievement-description"><?php echo e($achievement['description']); ?></div>
                            
                            <?php if(!$achievement['unlocked']): ?>
                                    <div class="achievement-progress">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo e($achievement['progress']); ?>%"></div>
                                        </div>
                                        <div class="progress-text"><?php echo e(number_format($achievement['progress'], 1)); ?>%</div>
                                    </div>
                            <?php endif; ?>
                            
                            <div class="achievement-meta">
                                <span class="achievement-type"><?php echo e(ucfirst($achievement['type'])); ?></span>
                                <span class="achievement-rarity"><?php echo e(ucfirst($achievement['rarity'])); ?></span>
                                <?php if($achievement['points_reward'] > 0): ?>
                                    <span class="achievement-reward">+<?php echo e($achievement['points_reward']); ?> PTS</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($achievement['unlocked'] && $achievement['unlocked_at']): ?>
                                <div class="achievement-date">
                                    <i class="fas fa-check-circle"></i> Déverrouillé le <?php echo e($this->formatDate($achievement['unlocked_at'])); ?>

                                </div>
                            <?php elseif(!$achievement['unlocked']): ?>
                                <div class="achievement-requirement">
                                    <i class="fas fa-lock"></i> Non déverrouillé
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/dashboard/profile.blade.php ENDPATH**/ ?>