<div class="admin-dashboard">
    <div class="admin-page-header">
        <h1>Tableau de bord</h1>
        <div class="admin-page-actions">
            <span class="admin-date"><?php echo e(now()->format('d/m/Y H:i')); ?></span>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="admin-stats-grid">
        <!-- Utilisateurs -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-title">Utilisateurs</div>
                <div class="admin-stat-value"><?php echo e($gameStats['users']['total']); ?></div>
                <div class="admin-stat-details">
                    <span class="admin-stat-detail">
                        <i class="fas fa-user-check"></i> <?php echo e($gameStats['users']['active']); ?> actifs
                    </span>
                    <span class="admin-stat-detail">
                        <i class="fas fa-user-plus"></i> <?php echo e($gameStats['users']['new']); ?> nouveaux
                    </span>
                    <span class="admin-stat-detail">
                        <i class="fas fa-umbrella-beach"></i> <?php echo e($gameStats['users']['in_vacation']); ?> en vacances
                    </span>
                </div>
            </div>
        </div>

        <!-- Planètes -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fas fa-globe"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-title">Planètes</div>
                <div class="admin-stat-value"><?php echo e($gameStats['planets']['total']); ?></div>
                <div class="admin-stat-details">
                    <span class="admin-stat-detail">
                        <i class="fas fa-flag"></i> <?php echo e($gameStats['planets']['colonized']); ?> colonisées
                    </span>
                    <span class="admin-stat-detail">
                        <i class="fas fa-globe-americas"></i> <?php echo e($gameStats['planets']['free']); ?> disponibles
                    </span>
                </div>
            </div>
        </div>

        <!-- Alliances -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fas fa-handshake"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-title">Alliances</div>
                <div class="admin-stat-value"><?php echo e($gameStats['alliances']['total']); ?></div>
                <div class="admin-stat-details">
                    <span class="admin-stat-detail">
                        <i class="fas fa-users"></i> <?php echo e($gameStats['alliances']['members']); ?> membres
                    </span>
                </div>
            </div>
        </div>

        <!-- Templates -->
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fas fa-cubes"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-title">Templates</div>
                <div class="admin-stat-value"><?php echo e(array_sum($gameStats['templates'])); ?></div>
                <div class="admin-stat-details">
                    <span class="admin-stat-detail">
                        <i class="fas fa-building"></i> <?php echo e($gameStats['templates']['buildings']); ?> bâtiments
                    </span>
                    <span class="admin-stat-detail">
                        <i class="fas fa-user-astronaut"></i> <?php echo e($gameStats['templates']['units']); ?> unités
                    </span>
                    <span class="admin-stat-detail">
                        <i class="fas fa-shield-alt"></i> <?php echo e($gameStats['templates']['defenses']); ?> défenses
                    </span>
                    <span class="admin-stat-detail">
                        <i class="fas fa-rocket"></i> <?php echo e($gameStats['templates']['ships']); ?> vaisseaux
                    </span>
                    <span class="admin-stat-detail">
                        <i class="fas fa-atom"></i> <?php echo e($gameStats['templates']['technologies']); ?> technologies
                    </span>
                    <span class="admin-stat-detail">
                        <i class="fas fa-coins"></i> <?php echo e($gameStats['templates']['resources']); ?> ressources
                    </span>
                    <span class="admin-stat-detail">
                        <i class="fas fa-globe-europe"></i> <?php echo e($gameStats['templates']['planets']); ?> planètes
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribution des factions -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Distribution des factions</h2>
        </div>
        <div class="admin-card-body">
            <div class="admin-faction-distribution">
                <?php $__currentLoopData = $gameStats['factions']['distribution']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faction => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="admin-faction-item">
                        <div class="admin-faction-name"><?php echo e($faction); ?></div>
                        <div class="admin-faction-bar">
                            <div class="admin-faction-progress" style="width: <?php echo e(($count / max(1, $gameStats['users']['total'])) * 100); ?>%"></div>
                        </div>
                        <div class="admin-faction-count"><?php echo e($count); ?></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    <!-- Derniers logs utilisateur -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Derniers logs utilisateur</h2>
        </div>
        <div class="admin-card-body">
            <div class="admin-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Planète</th>
                            <th>Cible</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $recentLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($log['user']); ?></td>
                                <td>
                                    <span class="admin-badge <?php echo e($this->getLogSeverityClass($log['severity'])); ?>">
                                        <i class="fas fa-<?php echo e($this->getLogCategoryIcon($log['action_category'])); ?>"></i>
                                         <?php echo e($log['action_type']); ?>

                                    </span>
                                </td>
                                <td><?php echo e($log['description']); ?></td>
                                <td><?php echo e($log['planet'] ?? '-'); ?></td>
                                <td><?php echo e($log['target_user'] ?? '-'); ?></td>
                                <td><?php echo e($log['created_at']); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="admin-table-empty">Aucun log disponible</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/admin/dashboard.blade.php ENDPATH**/ ?>