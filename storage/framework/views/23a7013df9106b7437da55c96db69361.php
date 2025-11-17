<div class="admin-badges">
    <div class="admin-page-header">
        <h1>Gestion des badges</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button <?php echo e($activeTab === 'list' ? 'active' : ''); ?>" wire:click="setActiveTab('list')">
                <i class="fas fa-award"></i> Liste des badges
            </button>
            <button class="admin-tab-button <?php echo e($activeTab === 'create' ? 'active' : ''); ?>" wire:click="setActiveTab('create')">
                <i class="fas fa-plus-circle"></i> Créer un badge
            </button>
            <?php if($selectedBadge): ?>
                <button class="admin-tab-button <?php echo e($activeTab === 'edit' ? 'active' : ''); ?>" wire:click="setActiveTab('edit')">
                    <i class="fas fa-edit"></i> <?php echo e($selectedBadge->name); ?>

                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-icon admin-stat-icon-primary">
                <i class="fas fa-award"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value"><?php echo e($badgeStats['total']); ?></div>
                <div class="admin-stat-label">Total des badges</div>
            </div>
        </div>
        
        <div class="admin-stat-card">
            <div class="admin-stat-icon admin-stat-icon-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value"><?php echo e($badgeStats['active']); ?></div>
                <div class="admin-stat-label">Badges actifs</div>
            </div>
        </div>
        
        <div class="admin-stat-card">
            <div class="admin-stat-icon admin-stat-icon-danger">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value"><?php echo e($badgeStats['inactive']); ?></div>
                <div class="admin-stat-label">Badges inactifs</div>
            </div>
        </div>
        
        <div class="admin-stat-card">
            <div class="admin-stat-icon admin-stat-icon-warning">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value"><?php echo e($mostAwardedBadges->isNotEmpty() ? $mostAwardedBadges->first()['count'] : 0); ?></div>
                <div class="admin-stat-label">Badge le plus attribué</div>
            </div>
        </div>
    </div>

    <!-- Onglet Liste des badges -->
    <?php if($activeTab === 'list'): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Liste des badges</h2>
                <div class="admin-card-tools">
                    <div class="admin-search-box">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="admin-search-input">
                        <i class="fas fa-search admin-search-icon"></i>
                    </div>
                </div>
            </div>
            <div class="admin-card-body">
                <div class="admin-filters">
                    <div class="admin-filter-group">
                        <label for="filterType">Type:</label>
                        <select id="filterType" wire:model.live="filterType" class="admin-select">
                            <option value="">Tous</option>
                            <?php $__currentLoopData = $badgeTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="admin-filter-group">
                        <label for="filterRarity">Rareté:</label>
                        <select id="filterRarity" wire:model.live="filterRarity" class="admin-select">
                            <option value="">Toutes</option>
                            <?php $__currentLoopData = $badgeRarities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="admin-filter-group">
                        <label for="filterActive">Statut:</label>
                        <select id="filterActive" wire:model.live="filterActive" class="admin-select">
                            <option value="">Tous</option>
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                        </select>
                    </div>
                    <div class="admin-filter-group">
                        <label for="perPage">Par page:</label>
                        <select id="perPage" wire:model.live="perPage" class="admin-select">
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
                
                <div class="admin-table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th wire:click="sortBy('id')" class="admin-sortable">
                                    ID
                                    <?php if($sortField === 'id'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </th>
                                <th>Icône</th>
                                <th wire:click="sortBy('name')" class="admin-sortable">
                                    Nom
                                    <?php if($sortField === 'name'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </th>
                                <th wire:click="sortBy('type')" class="admin-sortable">
                                    Type
                                    <?php if($sortField === 'type'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </th>
                                <th wire:click="sortBy('rarity')" class="admin-sortable">
                                    Rareté
                                    <?php if($sortField === 'rarity'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </th>
                                <th wire:click="sortBy('points_reward')" class="admin-sortable">
                                    Points
                                    <?php if($sortField === 'points_reward'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </th>
                                <th>Utilisateurs</th>
                                <th wire:click="sortBy('is_active')" class="admin-sortable">
                                    Statut
                                    <?php if($sortField === 'is_active'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $badges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($badge->id); ?></td>
                                    <td>
                                        <div class="admin-badge-icon-small admin-badge-<?php echo e($badge->rarity); ?>">
                                            <i class="fas <?php echo e($badge->icon); ?>"></i>
                                        </div>
                                    </td>
                                    <td><?php echo e($badge->name); ?></td>
                                    <td>
                                        <span class="admin-badge admin-badge-<?php echo e($badge->type); ?>">
                                            <?php echo e($badgeTypes[$badge->type] ?? $badge->type); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <span class="admin-badge admin-badge-<?php echo e($badge->rarity); ?>">
                                            <?php echo e($badgeRarities[$badge->rarity] ?? $badge->rarity); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($badge->points_reward); ?></td>
                                    <td><?php echo e($this->getBadgeUserCount($badge->id)); ?></td>
                                    <td>
                                        <span class="admin-badge <?php echo e($badge->is_active ? 'admin-badge-success' : 'admin-badge-danger'); ?>">
                                            <?php echo e($badge->is_active ? 'Actif' : 'Inactif'); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <div class="admin-table-actions">
                                            <button class="admin-btn admin-btn-icon admin-btn-primary" wire:click="selectBadge(<?php echo e($badge->id); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="admin-btn admin-btn-icon admin-btn-danger" wire:click="deleteBadge(<?php echo e($badge->id); ?>)" wire:confirm="Êtes-vous sûr de vouloir supprimer ce badge ?">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="9" class="admin-table-empty">Aucun badge trouvé</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="admin-pagination">
                    <?php echo e($badges->links()); ?>

                </div>
            </div>
        </div>

        <!-- Statistiques des badges -->
        <div class="admin-grid-2">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Badges les plus attribués</h2>
                </div>
                <div class="admin-card-body">
                    <?php if($mostAwardedBadges->isNotEmpty()): ?>
                        <div class="admin-badges-grid">
                            <?php $__currentLoopData = $mostAwardedBadges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="admin-badge-card">
                                    <div class="admin-badge-icon admin-badge-<?php echo e($item['badge']->rarity); ?>">
                                        <i class="fas <?php echo e($item['badge']->icon); ?>"></i>
                                    </div>
                                    <div class="admin-badge-info">
                                        <h4><?php echo e($item['badge']->name); ?></h4>
                                        <p><?php echo e(Str::limit($item['badge']->description, 100)); ?></p>
                                        <div class="admin-badge-meta">
                                            <span class="admin-badge admin-badge-<?php echo e($item['badge']->rarity); ?>">
                                                <?php echo e($badgeRarities[$item['badge']->rarity] ?? $item['badge']->rarity); ?>

                                            </span>
                                            <span class="admin-badge-count">
                                                <i class="fas fa-users"></i> <?php echo e($item['count']); ?> utilisateurs
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="admin-empty-state">
                            <i class="fas fa-award"></i>
                            <p>Aucun badge n'a encore été attribué</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Badges récemment attribués</h2>
                </div>
                <div class="admin-card-body">
                    <?php if($recentlyAwardedBadges->isNotEmpty()): ?>
                        <div class="admin-table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Badge</th>
                                        <th>Utilisateur</th>
                                        <th>Date d'obtention</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $recentlyAwardedBadges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userBadge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <div class="admin-badge-row">
                                                    <div class="admin-badge-icon-small admin-badge-<?php echo e($userBadge->badge->rarity); ?>">
                                                        <i class="fas <?php echo e($userBadge->badge->icon); ?>"></i>
                                                    </div>
                                                    <span><?php echo e($userBadge->badge->name); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo e($userBadge->user->name); ?></td>
                                            <td><?php echo e($userBadge->earned_at->format('d/m/Y H:i')); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="admin-empty-state">
                            <i class="fas fa-trophy"></i>
                            <p>Aucun badge n'a encore été attribué</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Onglet Créer un badge -->
    <?php if($activeTab === 'create'): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Créer un nouveau badge</h2>
            </div>
            <div class="admin-card-body">
                <form wire:submit.prevent="createBadge">
                    <div class="admin-form-section">
                        <h3>Informations générales</h3>
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="badgeName" class="admin-form-label">Nom du badge</label>
                                <input type="text" id="badgeName" wire:model="badgeForm.name" class="admin-input" placeholder="Nom du badge">
                                <?php $__errorArgs = ['badgeForm.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="badgeIcon" class="admin-form-label">Icône (classe Font Awesome)</label>
                                <div class="admin-input-group">
                                    <span class="admin-input-group-text"><i class="fas <?php echo e($badgeForm['icon'] ?: 'fa-award'); ?>"></i></span>
                                    <input type="text" id="badgeIcon" wire:model="badgeForm.icon" class="admin-input" placeholder="fa-award">
                                </div>
                                <?php $__errorArgs = ['badgeForm.icon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="badgeDescription" class="admin-form-label">Description</label>
                            <textarea id="badgeDescription" wire:model="badgeForm.description" class="admin-textarea" rows="3" placeholder="Description du badge"></textarea>
                            <?php $__errorArgs = ['badgeForm.description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    
                    <div class="admin-form-section">
                        <h3>Propriétés du badge</h3>
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="badgeType" class="admin-form-label">Type de badge</label>
                                <select id="badgeType" wire:model="badgeForm.type" class="admin-select">
                                    <option value="">Sélectionner un type</option>
                                    <?php $__currentLoopData = $badgeTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['badgeForm.type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="badgeRarity" class="admin-form-label">Rareté</label>
                                <select id="badgeRarity" wire:model="badgeForm.rarity" class="admin-select">
                                    <option value="">Sélectionner une rareté</option>
                                    <?php $__currentLoopData = $badgeRarities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['badgeForm.rarity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="badgeRequirementType" class="admin-form-label">Type de condition</label>
                                <select id="badgeRequirementType" wire:model="badgeForm.requirement_type" class="admin-select">
                                    <option value="">Sélectionner une condition</option>
                                    <?php $__currentLoopData = $requirementTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['badgeForm.requirement_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="badgeRequirementValue" class="admin-form-label">Valeur requise</label>
                                <input type="number" id="badgeRequirementValue" wire:model="badgeForm.requirement_value" class="admin-input" min="0">
                                <?php $__errorArgs = ['badgeForm.requirement_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="badgePointsReward" class="admin-form-label">Points de récompense</label>
                                <input type="number" id="badgePointsReward" wire:model="badgeForm.points_reward" class="admin-input" min="0">
                                <?php $__errorArgs = ['badgeForm.points_reward'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Statut</label>
                                <div class="admin-toggle-container">
                                    <label class="admin-toggle">
                                        <input type="checkbox" wire:model="badgeForm.is_active">
                                        <span class="admin-toggle-slider"></span>
                                    </label>
                                    <span><?php echo e($badgeForm['is_active'] ? 'Actif' : 'Inactif'); ?></span>
                                </div>
                                <?php $__errorArgs = ['badgeForm.is_active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-actions">
                        <button type="button" class="admin-btn admin-btn-secondary" wire:click="resetBadgeForm">Réinitialiser</button>
                        <button type="submit" class="admin-btn admin-btn-primary">Créer le badge</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Onglet Éditer un badge -->
    <?php if($activeTab === 'edit' && $selectedBadge): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Éditer le badge: <?php echo e($selectedBadge->name); ?></h2>
            </div>
            <div class="admin-card-body">
                <form wire:submit.prevent="updateBadge">
                    <div class="admin-form-section">
                        <h3>Informations générales</h3>
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="editBadgeName" class="admin-form-label">Nom du badge</label>
                                <input type="text" id="editBadgeName" wire:model="badgeForm.name" class="admin-input" placeholder="Nom du badge">
                                <?php $__errorArgs = ['badgeForm.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="editBadgeIcon" class="admin-form-label">Icône (classe Font Awesome)</label>
                                <div class="admin-input-group">
                                    <span class="admin-input-group-text"><i class="fas <?php echo e($badgeForm['icon'] ?: 'fa-award'); ?>"></i></span>
                                    <input type="text" id="editBadgeIcon" wire:model="badgeForm.icon" class="admin-input" placeholder="fa-award">
                                </div>
                                <?php $__errorArgs = ['badgeForm.icon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="editBadgeDescription" class="admin-form-label">Description</label>
                            <textarea id="editBadgeDescription" wire:model="badgeForm.description" class="admin-textarea" rows="3" placeholder="Description du badge"></textarea>
                            <?php $__errorArgs = ['badgeForm.description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    
                    <div class="admin-form-section">
                        <h3>Propriétés du badge</h3>
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="editBadgeType" class="admin-form-label">Type de badge</label>
                                <select id="editBadgeType" wire:model="badgeForm.type" class="admin-select">
                                    <option value="">Sélectionner un type</option>
                                    <?php $__currentLoopData = $badgeTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['badgeForm.type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="editBadgeRarity" class="admin-form-label">Rareté</label>
                                <select id="editBadgeRarity" wire:model="badgeForm.rarity" class="admin-select">
                                    <option value="">Sélectionner une rareté</option>
                                    <?php $__currentLoopData = $badgeRarities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['badgeForm.rarity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="editBadgeRequirementType" class="admin-form-label">Type de condition</label>
                                <select id="editBadgeRequirementType" wire:model="badgeForm.requirement_type" class="admin-select">
                                    <option value="">Sélectionner une condition</option>
                                    <?php $__currentLoopData = $requirementTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['badgeForm.requirement_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="editBadgeRequirementValue" class="admin-form-label">Valeur requise</label>
                                <input type="number" id="editBadgeRequirementValue" wire:model="badgeForm.requirement_value" class="admin-input" min="0">
                                <?php $__errorArgs = ['badgeForm.requirement_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="editBadgePointsReward" class="admin-form-label">Points de récompense</label>
                                <input type="number" id="editBadgePointsReward" wire:model="badgeForm.points_reward" class="admin-input" min="0">
                                <?php $__errorArgs = ['badgeForm.points_reward'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Statut</label>
                                <div class="admin-toggle-container">
                                    <label class="admin-toggle">
                                        <input type="checkbox" wire:model="badgeForm.is_active">
                                        <span class="admin-toggle-slider"></span>
                                    </label>
                                    <span><?php echo e($badgeForm['is_active'] ? 'Actif' : 'Inactif'); ?></span>
                                </div>
                                <?php $__errorArgs = ['badgeForm.is_active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-section">
                        <h3>Statistiques du badge</h3>
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Nombre d'utilisateurs</label>
                                <div class="admin-input-static">
                                    <?php echo e($this->getBadgeUserCount($selectedBadge->id)); ?> utilisateur(s)
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-actions">
                        <button type="button" class="admin-btn admin-btn-danger" wire:click="deleteBadge(<?php echo e($selectedBadge->id); ?>)" wire:confirm="Êtes-vous sûr de vouloir supprimer ce badge ?">Supprimer</button>
                        <button type="submit" class="admin-btn admin-btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/admin/template/badges.blade.php ENDPATH**/ ?>