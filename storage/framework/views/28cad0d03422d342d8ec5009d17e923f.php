<div class="admin-logs">
    <div class="admin-page-header">
        <h1>Gestion des logs système</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button <?php echo e($activeTab === 'user_logs' ? 'active' : ''); ?>" wire:click="setActiveTab('user_logs')">
                <i class="fas fa-clipboard-list"></i> Logs utilisateurs
            </button>
            <button class="admin-tab-button <?php echo e($activeTab === 'attack_logs' ? 'active' : ''); ?>" wire:click="setActiveTab('attack_logs')">
                <i class="fas fa-fighter-jet"></i> Logs d'attaques
            </button>
        </div>
    </div>

    <div class="admin-content-body">
        <!-- Onglet des logs utilisateurs -->
        <?php if($activeTab === 'user_logs'): ?>
            <!-- Statistiques des logs utilisateurs -->
            <div class="admin-stats-grid">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-title">Total des logs</div>
                        <div class="admin-stat-value"><?php echo e($userLogStats['total']); ?></div>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-info">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-title">Logs d'information</div>
                        <div class="admin-stat-value"><?php echo e($userLogStats['by_severity']['info'] ?? 0); ?></div>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-title">Logs d'avertissement</div>
                        <div class="admin-stat-value"><?php echo e($userLogStats['by_severity']['warning'] ?? 0); ?></div>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-danger">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-title">Logs d'erreur</div>
                        <div class="admin-stat-value"><?php echo e($userLogStats['by_severity']['error'] ?? 0); ?></div>
                    </div>
                </div>
            </div>

            <!-- Filtres et actions pour les logs utilisateurs -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Liste des logs utilisateurs</h2>
                    <div class="admin-card-actions">
                        <button class="admin-btn admin-btn-danger" wire:click="deleteSelectedUserLogs" wire:confirm="Êtes-vous sûr de vouloir supprimer les logs sélectionnés ?">
                            <i class="fas fa-trash"></i> Supprimer sélection
                        </button>
                        <button class="admin-btn admin-btn-danger" wire:click="deleteAllUserLogs" wire:confirm="Êtes-vous sûr de vouloir supprimer TOUS les logs utilisateurs ? Cette action est irréversible.">
                            <i class="fas fa-trash-alt"></i> Tout supprimer
                        </button>
                    </div>
                </div>
                <div class="admin-card-body">
                    <!-- Filtres -->
                    <div class="admin-filters">
                        <div class="admin-search-container">
                            <input type="text" class="admin-search-input" placeholder="Rechercher..." wire:model.live.debounce.300ms="userLogSearch">
                            <i class="fas fa-search admin-search-icon"></i>
                        </div>
                        <div class="admin-filter-group">
                            <select class="admin-select" wire:model.live="userLogActionType">
                                <option value="">Type d'action</option>
                                <?php $__currentLoopData = $userLogActionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($type); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <select class="admin-select" wire:model.live="userLogCategory">
                                <option value="">Catégorie</option>
                                <?php $__currentLoopData = $userLogCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <select class="admin-select" wire:model.live="userLogSeverity">
                                <option value="">Sévérité</option>
                                <?php $__currentLoopData = $userLogSeverities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $severity => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($severity); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <select class="admin-select" wire:model.live="userLogPerPage">
                                <option value="15">15 par page</option>
                                <option value="25">25 par page</option>
                                <option value="50">50 par page</option>
                                <option value="100">100 par page</option>
                            </select>
                        </div>
                        <button class="admin-btn admin-btn-outline" wire:click="resetUserLogFilters">
                            <i class="fas fa-redo"></i> Réinitialiser
                        </button>
                    </div>

                    <!-- Tableau des logs utilisateurs -->
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th class="admin-table-checkbox">
                                        <input type="checkbox" wire:model.live="selectAllUserLogs" wire:click="toggleSelectAllUserLogs">
                                    </th>
                                    <th class="admin-sortable" wire:click="sortUserLogs('created_at')">
                                        Date
                                        <?php if($userLogSortField === 'created_at'): ?>
                                            <i class="fas fa-sort-<?php echo e($userLogSortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th class="admin-sortable" wire:click="sortUserLogs('user_id')">
                                        Utilisateur
                                        <?php if($userLogSortField === 'user_id'): ?>
                                            <i class="fas fa-sort-<?php echo e($userLogSortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th class="admin-sortable" wire:click="sortUserLogs('action_type')">
                                        Action
                                        <?php if($userLogSortField === 'action_type'): ?>
                                            <i class="fas fa-sort-<?php echo e($userLogSortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th class="admin-sortable" wire:click="sortUserLogs('action_category')">
                                        Catégorie
                                        <?php if($userLogSortField === 'action_category'): ?>
                                            <i class="fas fa-sort-<?php echo e($userLogSortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th>Description</th>
                                    <th class="admin-sortable" wire:click="sortUserLogs('severity')">
                                        Sévérité
                                        <?php if($userLogSortField === 'severity'): ?>
                                            <i class="fas fa-sort-<?php echo e($userLogSortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $userLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="admin-table-checkbox">
                                            <input type="checkbox" value="<?php echo e($log->id); ?>" wire:model.live="selectedUserLogs">
                                        </td>
                                        <td><?php echo e($log->created_at->format('d/m/Y H:i:s')); ?></td>
                                        <td>
                                            <?php if($log->user): ?>
                                                <?php echo e($log->user->name); ?>

                                            <?php else: ?>
                                                Utilisateur supprimé
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($userLogActionTypes[$log->action_type] ?? $log->action_type); ?></td>
                                        <td><?php echo e($userLogCategories[$log->action_category] ?? $log->action_category); ?></td>
                                        <td class="admin-truncated-text" title="<?php echo e($log->formatted_description); ?>"><?php echo e($log->formatted_description); ?></td>
                                        <td>
                                            <span class="admin-badge admin-badge-<?php echo e($log->severity); ?>">
                                                <?php echo e($userLogSeverities[$log->severity] ?? $log->severity); ?>

                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="admin-table-empty">Aucun log utilisateur trouvé</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="admin-pagination">
                        <?php echo e($userLogs->links()); ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Onglet des logs d'attaque -->
        <?php if($activeTab === 'attack_logs'): ?>
            <!-- Statistiques des logs d'attaque -->
            <div class="admin-stats-grid">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon">
                        <i class="fas fa-fighter-jet"></i>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-title">Total des attaques</div>
                        <div class="admin-stat-value"><?php echo e($attackLogStats['total']); ?></div>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-success">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-title">Victoires</div>
                        <div class="admin-stat-value"><?php echo e($attackLogStats['victories']); ?></div>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-danger">
                        <i class="fas fa-skull-crossbones"></i>
                    </div>
                    <div class="admin-stat-content">
                        <div class="admin-stat-title">Défaites</div>
                        <div class="admin-stat-value"><?php echo e($attackLogStats['defeats']); ?></div>
                    </div>
                </div>
                <?php $__currentLoopData = $attackLogStats['by_type'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon admin-stat-icon-info">
                            <i class="fas fa-crosshairs"></i>
                        </div>
                        <div class="admin-stat-content">
                            <div class="admin-stat-title"><?php echo e($type); ?></div>
                            <div class="admin-stat-value"><?php echo e($count); ?></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Filtres et actions pour les logs d'attaque -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Liste des logs d'attaque</h2>
                    <div class="admin-card-actions">
                        <button class="admin-btn admin-btn-danger" wire:click="deleteSelectedAttackLogs" wire:confirm="Êtes-vous sûr de vouloir supprimer les logs d'attaque sélectionnés ?">
                            <i class="fas fa-trash"></i> Supprimer sélection
                        </button>
                        <button class="admin-btn admin-btn-danger" wire:click="deleteAllAttackLogs" wire:confirm="Êtes-vous sûr de vouloir supprimer TOUS les logs d'attaque ? Cette action est irréversible.">
                            <i class="fas fa-trash-alt"></i> Tout supprimer
                        </button>
                    </div>
                </div>
                <div class="admin-card-body">
                    <!-- Filtres -->
                    <div class="admin-filters">
                        <div class="admin-search-container">
                            <input type="text" class="admin-search-input" placeholder="Rechercher..." wire:model.live.debounce.300ms="attackLogSearch">
                            <i class="fas fa-search admin-search-icon"></i>
                        </div>
                        <div class="admin-filter-group">
                            <select class="admin-select" wire:model.live="attackLogType">
                                <option value="">Type d'attaque</option>
                                <?php $__currentLoopData = $attackLogTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($type); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <select class="admin-select" wire:model.live="attackLogWon">
                                <option value="">Résultat</option>
                                <option value="1">Victoire</option>
                                <option value="0">Défaite</option>
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <select class="admin-select" wire:model.live="attackLogPerPage">
                                <option value="15">15 par page</option>
                                <option value="25">25 par page</option>
                                <option value="50">50 par page</option>
                                <option value="100">100 par page</option>
                            </select>
                        </div>
                        <button class="admin-btn admin-btn-outline" wire:click="resetAttackLogFilters">
                            <i class="fas fa-redo"></i> Réinitialiser
                        </button>
                    </div>

                    <!-- Tableau des logs d'attaque -->
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th class="admin-table-checkbox">
                                        <input type="checkbox" wire:model.live="selectAllAttackLogs" wire:click="toggleSelectAllAttackLogs">
                                    </th>
                                    <th class="admin-sortable" wire:click="sortAttackLogs('attacked_at')">
                                        Date
                                        <?php if($attackLogSortField === 'attacked_at'): ?>
                                            <i class="fas fa-sort-<?php echo e($attackLogSortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th class="admin-sortable" wire:click="sortAttackLogs('attacker_user_id')">
                                        Attaquant
                                        <?php if($attackLogSortField === 'attacker_user_id'): ?>
                                            <i class="fas fa-sort-<?php echo e($attackLogSortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th class="admin-sortable" wire:click="sortAttackLogs('defender_user_id')">
                                        Défenseur
                                        <?php if($attackLogSortField === 'defender_user_id'): ?>
                                            <i class="fas fa-sort-<?php echo e($attackLogSortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th class="admin-sortable" wire:click="sortAttackLogs('attack_type')">
                                        Type
                                        <?php if($attackLogSortField === 'attack_type'): ?>
                                            <i class="fas fa-sort-<?php echo e($attackLogSortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th class="admin-sortable" wire:click="sortAttackLogs('attacker_won')">
                                        Résultat
                                        <?php if($attackLogSortField === 'attacker_won'): ?>
                                            <i class="fas fa-sort-<?php echo e($attackLogSortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th class="admin-sortable" wire:click="sortAttackLogs('points_gained')">
                                        Points
                                        <?php if($attackLogSortField === 'points_gained'): ?>
                                            <i class="fas fa-sort-<?php echo e($attackLogSortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th>Ressources pillées</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $attackLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="admin-table-checkbox">
                                            <input type="checkbox" value="<?php echo e($log->id); ?>" wire:model.live="selectedAttackLogs">
                                        </td>
                                        <td><?php echo e($log->attacked_at->format('d/m/Y H:i:s')); ?></td>
                                        <td>
                                            <?php if($log->attacker): ?>
                                                <?php echo e($log->attacker->name); ?>

                                            <?php else: ?>
                                                Utilisateur supprimé
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($log->defender): ?>
                                                <?php echo e($log->defender->name); ?>

                                            <?php else: ?>
                                                Utilisateur supprimé
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($attackLogTypes[$log->attack_type] ?? $log->attack_type); ?></td>
                                        <td>
                                            <span class="admin-badge <?php echo e($log->attacker_won ? 'admin-badge-success' : 'admin-badge-danger'); ?>">
                                                <?php echo e($log->attacker_won ? 'Victoire' : 'Défaite'); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($log->points_gained); ?></td>
                                        <td>
                                            <?php if(!empty($log->resources_pillaged)): ?>
                                                <div class="admin-resources-list">
                                                    <?php $__currentLoopData = $log->resources_pillaged; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resource => $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <span class="admin-resource-item"><?php echo e($resource); ?>: <?php echo e($amount); ?></span>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="admin-text-muted">Aucune</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="admin-table-empty">Aucun log d'attaque trouvé</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="admin-pagination">
                        <?php echo e($attackLogs->links()); ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/admin/logs.blade.php ENDPATH**/ ?>