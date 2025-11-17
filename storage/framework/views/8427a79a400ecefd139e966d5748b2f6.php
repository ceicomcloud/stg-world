<div class="admin-alliances">
    <div class="admin-page-header">
        <h1>Gestion des alliances</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button <?php echo e($activeTab === 'list' ? 'active' : ''); ?>" wire:click="setActiveTab('list')">
                <i class="fas fa-users"></i> Liste des alliances
            </button>
            <?php if($activeTab === 'detail'): ?>
                <button class="admin-tab-button active">
                    <i class="fas fa-user-shield"></i> <?php echo e($selectedAlliance->name); ?>

                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-content-body">
        <!-- Liste des alliances -->
        <?php if($activeTab === 'list'): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Liste des alliances</h2>
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
                                    <th wire:click="sortBy('name')" class="admin-sortable">
                                        Nom
                                        <?php if($sortField === 'name'): ?>
                                            <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th wire:click="sortBy('tag')" class="admin-sortable">
                                        Tag
                                        <?php if($sortField === 'tag'): ?>
                                            <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th>Leader</th>
                                    <th wire:click="sortBy('max_members')" class="admin-sortable">
                                        Membres
                                        <?php if($sortField === 'max_members'): ?>
                                            <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th wire:click="sortBy('deuterium_bank')" class="admin-sortable">
                                        Banque
                                        <?php if($sortField === 'deuterium_bank'): ?>
                                            <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th wire:click="sortBy('created_at')" class="admin-sortable">
                                        Création
                                        <?php if($sortField === 'created_at'): ?>
                                            <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $alliances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alliance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($alliance->id); ?></td>
                                        <td><?php echo e($alliance->name); ?></td>
                                        <td><?php echo e($alliance->tag); ?></td>
                                        <td><?php echo e($alliance->leader->name ?? 'Aucun'); ?></td>
                                        <td><?php echo e($alliance->member_count); ?> / <?php echo e($alliance->max_members); ?></td>
                                        <td><?php echo e(number_format($alliance->deuterium_bank)); ?></td>
                                        <td><?php echo e($alliance->created_at->format('d/m/Y')); ?></td>
                                        <td>
                                            <div class="admin-actions">
                                                <button wire:click="selectAlliance(<?php echo e($alliance->id); ?>)" class="admin-action-button admin-action-info" title="Voir les détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="admin-table-empty">Aucune alliance trouvée</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="admin-pagination">
                        <?php echo e($alliances->links()); ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Détails de l'alliance -->
        <?php if($activeTab === 'detail' && $selectedAlliance): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Alliance: <?php echo e($selectedAlliance->name); ?> [<?php echo e($selectedAlliance->tag); ?>]</h2>
                    <div class="admin-card-tabs">
                        <button class="admin-tab-button <?php echo e($allianceDetailTab === 'info' ? 'active' : ''); ?>" wire:click="setAllianceDetailTab('info')">
                            <i class="fas fa-info-circle"></i> Informations
                        </button>
                        <button class="admin-tab-button <?php echo e($allianceDetailTab === 'members' ? 'active' : ''); ?>" wire:click="setAllianceDetailTab('members')">
                            <i class="fas fa-users"></i> Membres
                        </button>
                        <button class="admin-tab-button <?php echo e($allianceDetailTab === 'ranks' ? 'active' : ''); ?>" wire:click="setAllianceDetailTab('ranks')">
                            <i class="fas fa-user-tag"></i> Rangs
                        </button>
                        <button class="admin-tab-button <?php echo e($allianceDetailTab === 'technologies' ? 'active' : ''); ?>" wire:click="setAllianceDetailTab('technologies')">
                            <i class="fas fa-flask"></i> Technologies
                        </button>
                        <button class="admin-tab-button <?php echo e($allianceDetailTab === 'bank' ? 'active' : ''); ?>" wire:click="setAllianceDetailTab('bank')">
                            <i class="fas fa-university"></i> Banque
                        </button>
                        <button class="admin-tab-button <?php echo e($allianceDetailTab === 'applications' ? 'active' : ''); ?>" wire:click="setAllianceDetailTab('applications')">
                            <i class="fas fa-clipboard-list"></i> Candidatures
                        </button>
                        <button class="admin-tab-button <?php echo e($allianceDetailTab === 'wars' ? 'active' : ''); ?>" wire:click="setAllianceDetailTab('wars')">
                            <i class="fas fa-fighter-jet"></i> Guerres
                        </button>
                    </div>
                </div>
                <div class="admin-card-body">
                    <!-- Informations générales -->
                    <?php if($allianceDetailTab === 'info'): ?>
                        <div class="admin-profile-header">
                            <div class="admin-profile-avatar">
                                <?php if($selectedAlliance->logo_url): ?>
                                    <img src="<?php echo e($selectedAlliance->logo_url); ?>" alt="<?php echo e($selectedAlliance->name); ?>" class="admin-alliance-logo">
                                <?php else: ?>
                                    <i class="fas fa-user-shield"></i>
                                <?php endif; ?>
                            </div>
                            <div class="admin-profile-info">
                                <h3><?php echo e($selectedAlliance->name); ?> [<?php echo e($selectedAlliance->tag); ?>]</h3>
                                <p>Leader: <?php echo e($selectedAlliance->leader->name ?? 'Aucun'); ?></p>
                                <div class="admin-profile-badges">
                                    <span class="admin-badge admin-badge-primary"><?php echo e($selectedAlliance->member_count); ?> / <?php echo e($selectedAlliance->max_members); ?> membres</span>
                                    <?php if($selectedAlliance->open_recruitment): ?>
                                        <span class="admin-badge admin-badge-success">Recrutement ouvert</span>
                                    <?php else: ?>
                                        <span class="admin-badge admin-badge-danger">Recrutement fermé</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="admin-profile-details">
                            <div class="admin-profile-section">
                                <h4>Informations générales</h4>
                                <div class="admin-profile-grid">
                                    <div class="admin-profile-item">
                                        <span class="admin-profile-label">ID</span>
                                        <span class="admin-profile-value"><?php echo e($selectedAlliance->id); ?></span>
                                    </div>
                                    <div class="admin-profile-item">
                                        <span class="admin-profile-label">Nom</span>
                                        <span class="admin-profile-value"><?php echo e($selectedAlliance->name); ?></span>
                                    </div>
                                    <div class="admin-profile-item">
                                        <span class="admin-profile-label">Tag</span>
                                        <span class="admin-profile-value"><?php echo e($selectedAlliance->tag); ?></span>
                                    </div>
                                    <div class="admin-profile-item">
                                        <span class="admin-profile-label">Date de création</span>
                                        <span class="admin-profile-value"><?php echo e($selectedAlliance->created_at->format('d/m/Y H:i')); ?></span>
                                    </div>
                                    <div class="admin-profile-item">
                                        <span class="admin-profile-label">Membres</span>
                                        <span class="admin-profile-value"><?php echo e($selectedAlliance->member_count); ?> / <?php echo e($selectedAlliance->max_members); ?></span>
                                    </div>
                                    <div class="admin-profile-item">
                                        <span class="admin-profile-label">Banque de deuterium</span>
                                        <span class="admin-profile-value"><?php echo e(number_format($selectedAlliance->deuterium_bank)); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="admin-profile-section">
                                <h4>Description externe</h4>
                                <div class="admin-profile-detail">
                                    <div class="admin-profile-text">
                                        <?php echo nl2br(e($selectedAlliance->external_description)); ?>

                                    </div>
                                </div>
                            </div>

                            <div class="admin-profile-section">
                                <h4>Description interne</h4>
                                <div class="admin-profile-detail">
                                    <div class="admin-profile-text">
                                        <?php echo nl2br(e($selectedAlliance->internal_description)); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Membres -->
                    <?php if($allianceDetailTab === 'members'): ?>
                        <div class="admin-table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Rang</th>
                                        <th>Date d'adhésion</th>
                                        <th>Contribution (deuterium)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $allianceMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($member->user->id); ?></td>
                                            <td><?php echo e($member->user->name); ?></td>
                                            <td>
                                                <?php if($member->rank): ?>
                                                    <span class="admin-badge admin-badge-primary"><?php echo e($member->rank->name); ?></span>
                                                <?php else: ?>
                                                    <span class="admin-badge admin-badge-secondary">Membre</span>
                                                <?php endif; ?>
                                                <?php if($member->isLeader()): ?>
                                                    <span class="admin-badge admin-badge-success">Leader</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($member->joined_at->format('d/m/Y')); ?></td>
                                            <td><?php echo e(number_format($member->contributed_deuterium)); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="5" class="admin-table-empty">Aucun membre trouvé</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- Rangs -->
                    <?php if($allianceDetailTab === 'ranks'): ?>
                        <div class="admin-table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Niveau</th>
                                        <th>Permissions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $allianceRanks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($rank->id); ?></td>
                                            <td><?php echo e($rank->name); ?></td>
                                            <td>
                                                <span class="admin-badge admin-badge-primary"><?php echo e($rank->level_name); ?></span>
                                            </td>
                                            <td>
                                                <div class="admin-badges-container">
                                                    <?php $__currentLoopData = $rank->getFormattedPermissions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <span class="admin-badge admin-badge-info"><?php echo e($label); ?></span>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="4" class="admin-table-empty">Aucun rang trouvé</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- Technologies -->
                    <?php if($allianceDetailTab === 'technologies'): ?>
                        <div class="admin-form-grid admin-form-grid-2">
                            <?php $__empty_1 = true; $__currentLoopData = $allianceTechnologies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $technology): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="admin-card">
                                    <div class="admin-card-header">
                                        <h3><?php echo e($technology->getName()); ?></h3>
                                    </div>
                                    <div class="admin-card-body">
                                        <div class="admin-tech-info">
                                            <p><?php echo e($technology->getDescription()); ?></p>
                                            <div class="admin-tech-level">
                                                <span class="admin-tech-level-label">Niveau:</span>
                                                <span class="admin-tech-level-value"><?php echo e($technology->level); ?> / <?php echo e($technology->max_level); ?></span>
                                            </div>
                                            <div class="admin-tech-bonus">
                                                <span class="admin-tech-bonus-label">Bonus actuel:</span>
                                                <span class="admin-tech-bonus-value">
                                                    <?php if($technology->technology_type === \App\Models\Alliance\AllianceTechnology::TYPE_MEMBERS): ?>
                                                        +<?php echo e($technology->getBonus()); ?> membres
                                                    <?php elseif($technology->technology_type === \App\Models\Alliance\AllianceTechnology::TYPE_BANK): ?>
                                                        +<?php echo e(number_format($technology->getBonus())); ?> deuterium
                                                    <?php else: ?>
                                                        <?php echo e($technology->getBonus()); ?>

                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                            <?php if($technology->canUpgrade()): ?>
                                                <div class="admin-tech-upgrade">
                                                    <div class="admin-tech-cost">
                                                        <span class="admin-tech-cost-label">Coût d'amélioration:</span>
                                                        <span class="admin-tech-cost-value"><?php echo e(number_format($technology->getUpgradeCost())); ?> deuterium</span>
                                                    </div>
                                                    <div class="admin-tech-next-bonus">
                                                        <span class="admin-tech-next-bonus-label">Bonus au niveau suivant:</span>
                                                        <span class="admin-tech-next-bonus-value">
                                                            <?php if($technology->technology_type === \App\Models\Alliance\AllianceTechnology::TYPE_MEMBERS): ?>
                                                                +<?php echo e($technology->getNextLevelBonus()); ?> membres
                                                            <?php elseif($technology->technology_type === \App\Models\Alliance\AllianceTechnology::TYPE_BANK): ?>
                                                                +<?php echo e(number_format($technology->getNextLevelBonus())); ?> deuterium
                                                            <?php else: ?>
                                                                <?php echo e($technology->getNextLevelBonus()); ?>

                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                    <button wire:click="upgradeTechnology(<?php echo e($technology->id); ?>)" class="admin-button admin-button-primary admin-button-sm">
                                                        <i class="fas fa-arrow-up"></i> Améliorer
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <div class="admin-tech-max-level">
                                                    <span class="admin-badge admin-badge-success">Niveau maximum atteint</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="admin-alert admin-alert-info">
                                    <i class="fas fa-info-circle"></i> Aucune technologie trouvée
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Banque -->
                    <?php if($allianceDetailTab === 'bank'): ?>
                        <div class="admin-form-grid admin-form-grid-2">
                            <div class="admin-card">
                                <div class="admin-card-header">
                                    <h3>État de la banque</h3>
                                </div>
                                <div class="admin-card-body">
                                    <div class="admin-bank-info">
                                        <div class="admin-bank-balance">
                                            <span class="admin-bank-balance-label">Solde actuel:</span>
                                            <span class="admin-bank-balance-value"><?php echo e(number_format($selectedAlliance->deuterium_bank)); ?> deuterium</span>
                                        </div>
                                        <div class="admin-bank-capacity">
                                            <span class="admin-bank-capacity-label">Capacité maximale:</span>
                                            <span class="admin-bank-capacity-value"><?php echo e(number_format($selectedAlliance->getMaxDeuteriumStorage())); ?> deuterium</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="admin-card">
                                <div class="admin-card-header">
                                    <h3>Opérations bancaires</h3>
                                </div>
                                <div class="admin-card-body">
                                    <form wire:submit.prevent="manageBankOperation" class="admin-form">
                                        <div class="admin-form-group">
                                            <label for="bankOperation">Opération</label>
                                            <select id="bankOperation" wire:model="bankForm.operation" class="admin-select">
                                                <option value="add">Ajouter du deuterium</option>
                                                <option value="withdraw">Retirer du deuterium</option>
                                            </select>
                                        </div>
                                        
                                        <div class="admin-form-group">
                                            <label for="bankAmount">Montant</label>
                                            <input type="number" id="bankAmount" wire:model="bankForm.amount" class="admin-input" min="1">
                                            <?php $__errorArgs = ['bankForm.amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        
                                        <div class="admin-form-actions">
                                            <button type="submit" class="admin-button admin-button-primary">
                                                <i class="fas fa-check"></i> Exécuter l'opération
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Candidatures -->
                    <?php if($allianceDetailTab === 'applications'): ?>
                        <div class="admin-table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Utilisateur</th>
                                        <th>Message</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Examiné par</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $allianceApplications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $application): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($application->id); ?></td>
                                            <td><?php echo e($application->user->name); ?></td>
                                            <td>
                                                <div class="admin-truncated-text">
                                                    <?php echo e($application->message); ?>

                                                </div>
                                            </td>
                                            <td>
                                                <?php if($application->isPending()): ?>
                                                    <span class="admin-badge admin-badge-warning">En attente</span>
                                                <?php elseif($application->isAccepted()): ?>
                                                    <span class="admin-badge admin-badge-success">Acceptée</span>
                                                <?php elseif($application->isRejected()): ?>
                                                    <span class="admin-badge admin-badge-danger">Rejetée</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($application->created_at->format('d/m/Y H:i')); ?></td>
                                            <td>
                                                <?php if($application->reviewer): ?>
                                                    <?php echo e($application->reviewer->name); ?>

                                                    <br>
                                                    <small><?php echo e($application->reviewed_at->format('d/m/Y H:i')); ?></small>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="6" class="admin-table-empty">Aucune candidature trouvée</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- Guerres -->
                    <?php if($allianceDetailTab === 'wars'): ?>
                        <div class="admin-table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Alliance adverse</th>
                                        <th>Rôle</th>
                                        <th>Statut</th>
                                        <th>Raison</th>
                                        <th>Déclarée par</th>
                                        <th>Date de début</th>
                                        <th>Date de fin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $allianceWars; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $war): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($war->id); ?></td>
                                            <td>
                                                <?php if($war->attacker_alliance_id === $selectedAllianceId): ?>
                                                    <?php echo e($war->defenderAlliance->name); ?> [<?php echo e($war->defenderAlliance->tag); ?>]
                                                <?php else: ?>
                                                    <?php echo e($war->attackerAlliance->name); ?> [<?php echo e($war->attackerAlliance->tag); ?>]
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($war->attacker_alliance_id === $selectedAllianceId): ?>
                                                    <span class="admin-badge admin-badge-danger">Attaquant</span>
                                                <?php else: ?>
                                                    <span class="admin-badge admin-badge-warning">Défenseur</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($war->isDeclared()): ?>
                                                    <span class="admin-badge admin-badge-warning">Déclarée</span>
                                                <?php elseif($war->isActive()): ?>
                                                    <span class="admin-badge admin-badge-danger">Active</span>
                                                <?php elseif($war->isEnded()): ?>
                                                    <span class="admin-badge admin-badge-success">Terminée</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="admin-truncated-text">
                                                    <?php echo e($war->reason); ?>

                                                </div>
                                            </td>
                                            <td><?php echo e($war->declaredBy->name); ?></td>
                                            <td><?php echo e($war->started_at ? $war->started_at->format('d/m/Y H:i') : '-'); ?></td>
                                            <td><?php echo e($war->ended_at ? $war->ended_at->format('d/m/Y H:i') : '-'); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="8" class="admin-table-empty">Aucune guerre trouvée</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/admin/alliances.blade.php ENDPATH**/ ?>