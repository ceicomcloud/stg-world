<div class="admin-planets">
    <div class="admin-page-header">
        <h1>Gestion des planètes</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button <?php echo e($activeTab === 'list' ? 'active' : ''); ?>" wire:click="setActiveTab('list')">
                <i class="fas fa-globe"></i> Liste des planètes
            </button>
            <button class="admin-tab-button <?php echo e($activeTab === 'assign' ? 'active' : ''); ?>" wire:click="setActiveTab('assign')">
                <i class="fas fa-user-plus"></i> Affecter une planète
            </button>
            <?php if($activeTab === 'detail'): ?>
                <button class="admin-tab-button active">
                    <i class="fas fa-info-circle"></i> <?php echo e($selectedPlanet->name); ?>

                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="admin-content-body">
        <!-- Liste des planètes -->
        <?php if($activeTab === 'list'): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Liste des planètes</h2>
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
                            <label for="filterOccupied">Occupation:</label>
                            <select id="filterOccupied" wire:model.live="filterOccupied" class="admin-select">
                                <option value="">Toutes</option>
                                <option value="occupied">Occupées</option>
                                <option value="free">Libres</option>
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <label for="filterActive">Statut:</label>
                            <select id="filterActive" wire:model.live="filterActive" class="admin-select">
                                <option value="">Tous</option>
                                <option value="active">Actives</option>
                                <option value="inactive">Inactives</option>
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <label for="filterType">Type:</label>
                            <select id="filterType" wire:model.live="filterType" class="admin-select">
                                <option value="">Tous</option>
                                <option value="planet">Planète</option>
                                <option value="moon">Lune</option>
                                <option value="asteroid">Astéroïde</option>
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <label for="perPage">Par page:</label>
                            <select id="perPage" wire:model.live="perPage" class="admin-select">
                                <option value="15">15</option>
                                <option value="30">30</option>
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
                                    <th>Coordonnées</th>
                                    <th>Type</th>
                                    <th>Taille</th>
                                    <th>Utilisateur</th>
                                    <th wire:click="sortBy('is_main_planet')" class="admin-sortable">
                                        Statut
                                        <?php if($sortField === 'is_main_planet'): ?>
                                            <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th wire:click="sortBy('is_active')" class="admin-sortable">
                                        Activité
                                        <?php if($sortField === 'is_active'): ?>
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
                                <?php $__empty_1 = true; $__currentLoopData = $planets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($planet->id); ?></td>
                                        <td><?php echo e($planet->name); ?></td>
                                        <td class="admin-planet-coordinates">
                                            <?php if($planet->templatePlanet): ?>
                                                <?php echo e($planet->templatePlanet->galaxy); ?>:<?php echo e($planet->templatePlanet->system); ?>:<?php echo e($planet->templatePlanet->position); ?>

                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($planet->templatePlanet): ?>
                                                <?php echo e(ucfirst($planet->templatePlanet->type)); ?>

                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($planet->templatePlanet): ?>
                                                <?php echo e($planet->templatePlanet->size); ?> (<?php echo e($planet->used_fields); ?>/<?php echo e($planet->templatePlanet->fields); ?>)
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($planet->user): ?>
                                                <?php echo e($planet->user->name); ?>

                                            <?php else: ?>
                                                <span class="admin-badge admin-badge-warning">Non assignée</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($planet->is_main_planet): ?>
                                                <span class="admin-badge admin-badge-primary">Principale</span>
                                            <?php else: ?>
                                                <span class="admin-badge admin-badge-secondary">Colonie</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($planet->is_active): ?>
                                                <span class="admin-badge admin-badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="admin-badge admin-badge-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($planet->created_at->format('d/m/Y H:i')); ?></td>
                                        <td>
                                            <div class="admin-actions">
                                                <button wire:click="selectPlanet(<?php echo e($planet->id); ?>)" class="admin-action-button admin-action-info" title="Voir les détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="10" class="admin-table-empty">Aucune planète trouvée</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="admin-pagination">
                        <?php echo e($planets->links()); ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulaire d'affectation d'une planète -->
        <?php if($activeTab === 'assign'): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Affecter une planète à un utilisateur</h2>
                </div>
                <div class="admin-card-body">
                    <form wire:submit.prevent="assignPlanetToUser" class="admin-form">
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="user_id">Utilisateur</label>
                                <select wire:model="assignPlanet.user_id" id="user_id" class="admin-select">
                                    <option value="">Sélectionner un utilisateur</option>
                                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['assignPlanet.user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="admin-form-group">
                                <label for="template_planet_id">Planète disponible</label>
                                <select wire:model="assignPlanet.template_planet_id" id="template_planet_id" class="admin-select">
                                    <option value="">Sélectionner une planète</option>
                                    <?php $__currentLoopData = $availableTemplatePlanets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $templatePlanet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($templatePlanet->id); ?>">
                                            <?php echo e($templatePlanet->galaxy); ?>:<?php echo e($templatePlanet->system); ?>:<?php echo e($templatePlanet->position); ?> - 
                                            <?php echo e(ucfirst($templatePlanet->type)); ?> - 
                                            <?php echo e($templatePlanet->size); ?> champs
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['assignPlanet.template_planet_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="name">Nom de la planète</label>
                                <input type="text" wire:model="assignPlanet.name" id="name" class="admin-input">
                                <?php $__errorArgs = ['assignPlanet.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="is_main_planet">Planète principale</label>
                                <div class="admin-toggle-container">
                                    <label class="admin-toggle">
                                        <input type="checkbox" wire:model="assignPlanet.is_main_planet" id="is_main_planet">
                                        <span class="admin-toggle-slider"></span>
                                    </label>
                                    <span class="admin-toggle-label"><?php echo e($assignPlanet['is_main_planet'] ? 'Oui' : 'Non'); ?></span>
                                </div>
                                <?php $__errorArgs = ['assignPlanet.is_main_planet'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="admin-form-group">
                                <label for="is_active">Planète active</label>
                                <div class="admin-toggle-container">
                                    <label class="admin-toggle">
                                        <input type="checkbox" wire:model="assignPlanet.is_active" id="is_active">
                                        <span class="admin-toggle-slider"></span>
                                    </label>
                                    <span class="admin-toggle-label"><?php echo e($assignPlanet['is_active'] ? 'Oui' : 'Non'); ?></span>
                                </div>
                                <?php $__errorArgs = ['assignPlanet.is_active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="admin-form-actions">
                            <button type="button" wire:click="resetAssignPlanet" class="admin-button admin-button-secondary">
                                <i class="fas fa-times"></i> Réinitialiser
                            </button>
                            <button type="submit" class="admin-button admin-button-primary">
                                <i class="fas fa-save"></i> Affecter la planète
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Détail d'une planète -->
        <?php if($activeTab === 'detail' && $selectedPlanet): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Détail de la planète #<?php echo e($selectedPlanet->id); ?></h2>
                    <div class="admin-card-actions">
                        <button class="admin-button admin-button-secondary" wire:click="$set('activeTab', 'list')">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </button>
                    </div>
                </div>
                
                <div class="admin-card-body">
                    <div class="admin-detail-info">
                        <div class="admin-detail-info-grid">
                            <div class="admin-detail-info-item">
                                <span class="admin-detail-label">ID:</span>
                                <span class="admin-detail-value"><?php echo e($selectedPlanet->id); ?></span>
                            </div>
                            <div class="admin-detail-info-item">
                                <span class="admin-detail-label">Coordonnées:</span>
                                <span class="admin-detail-value admin-planet-coordinates">
                                    <?php if($selectedPlanet->templatePlanet): ?>
                                        <?php echo e($selectedPlanet->templatePlanet->galaxy); ?>:<?php echo e($selectedPlanet->templatePlanet->system); ?>:<?php echo e($selectedPlanet->templatePlanet->position); ?>

                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="admin-detail-info-item">
                                <span class="admin-detail-label">Type:</span>
                                <span class="admin-detail-value">
                                    <?php if($selectedPlanet->templatePlanet): ?>
                                        <?php echo e(ucfirst($selectedPlanet->templatePlanet->type)); ?>

                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="admin-detail-info-item">
                                <span class="admin-detail-label">Taille:</span>
                                <span class="admin-detail-value">
                                    <?php if($selectedPlanet->templatePlanet): ?>
                                        <?php echo e($selectedPlanet->templatePlanet->size); ?> (<?php echo e($selectedPlanet->used_fields); ?>/<?php echo e($selectedPlanet->templatePlanet->fields); ?> champs)
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="admin-detail-info-item">
                                <span class="admin-detail-label">Propriétaire:</span>
                                <span class="admin-detail-value">
                                    <?php if($selectedPlanet->user): ?>
                                        <?php echo e($selectedPlanet->user->name); ?>

                                    <?php else: ?>
                                        <span class="admin-badge admin-badge-secondary">Non assignée</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="admin-detail-info-item">
                                <span class="admin-detail-label">Statut:</span>
                                <span class="admin-detail-value">
                                    <?php if($selectedPlanet->is_main_planet): ?>
                                        <span class="admin-badge admin-badge-primary">Principale</span>
                                    <?php else: ?>
                                        <span class="admin-badge admin-badge-info">Colonie</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="admin-detail-info-item">
                                <span class="admin-detail-label">Activité:</span>
                                <span class="admin-detail-value">
                                    <?php if($selectedPlanet->is_active): ?>
                                        <span class="admin-badge admin-badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="admin-badge admin-badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="admin-detail-info-item">
                                <span class="admin-detail-label">Créée le:</span>
                                <span class="admin-detail-value"><?php echo e($selectedPlanet->created_at->format('d/m/Y H:i')); ?></span>
                            </div>
                            <div class="admin-detail-info-item">
                                <span class="admin-detail-label">Mise à jour:</span>
                                <span class="admin-detail-value"><?php echo e($selectedPlanet->last_update ? $selectedPlanet->last_update->format('d/m/Y H:i:s') : 'Aucune'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="admin-tabs">
                <button class="admin-tab <?php echo e($planetDetailTab === 'info' ? 'active' : ''); ?>" wire:click="setPlanetDetailTab('info')">
                    <i class="fas fa-info-circle"></i> Informations
                </button>
                <button class="admin-tab <?php echo e($planetDetailTab === 'resources' ? 'active' : ''); ?>" wire:click="setPlanetDetailTab('resources')">
                    <i class="fas fa-cube"></i> Ressources
                </button>
                <button class="admin-tab <?php echo e($planetDetailTab === 'buildings' ? 'active' : ''); ?>" wire:click="setPlanetDetailTab('buildings')">
                    <i class="fas fa-building"></i> Bâtiments
                </button>
                <button class="admin-tab <?php echo e($planetDetailTab === 'units' ? 'active' : ''); ?>" wire:click="setPlanetDetailTab('units')">
                    <i class="fas fa-user-astronaut"></i> Unités
                </button>
                <button class="admin-tab <?php echo e($planetDetailTab === 'ships' ? 'active' : ''); ?>" wire:click="setPlanetDetailTab('ships')">
                    <i class="fas fa-rocket"></i> Vaisseaux
                </button>
                <button class="admin-tab <?php echo e($planetDetailTab === 'defenses' ? 'active' : ''); ?>" wire:click="setPlanetDetailTab('defenses')">
                    <i class="fas fa-shield-alt"></i> Défenses
                </button>
                <button class="admin-tab <?php echo e($planetDetailTab === 'bunkers' ? 'active' : ''); ?>" wire:click="setPlanetDetailTab('bunkers')">
                    <i class="fas fa-warehouse"></i> Bunker
                </button>
                <button class="admin-tab <?php echo e($planetDetailTab === 'missions' ? 'active' : ''); ?>" wire:click="setPlanetDetailTab('missions')">
                    <i class="fas fa-space-shuttle"></i> Missions
                </button>
            </div>

                <div class="admin-detail-content">
                    <!-- Onglet Informations -->
                    <?php if($planetDetailTab === 'info'): ?>
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Caractéristiques</h3>
                            </div>
                            <div class="admin-card-body">
                                <div class="admin-detail-grid">
                                    <div class="admin-detail-item">
                                        <span class="admin-detail-label">Diamètre:</span>
                                        <span class="admin-detail-value"><?php echo e(number_format($selectedPlanet->templatePlanet->diameter, 0, ',', ' ')); ?> km</span>
                                    </div>
                                    <div class="admin-detail-item">
                                        <span class="admin-detail-label">Température:</span>
                                        <span class="admin-detail-value"><?php echo e($selectedPlanet->templatePlanet->min_temperature); ?>°C à <?php echo e($selectedPlanet->templatePlanet->max_temperature); ?>°C</span>
                                    </div>
                                    <div class="admin-detail-item">
                                        <span class="admin-detail-label">Champs:</span>
                                        <span class="admin-detail-value"><?php echo e($selectedPlanet->used_fields); ?>/<?php echo e($selectedPlanet->templatePlanet->fields); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Bonus de production</h3>
                            </div>
                            <div class="admin-card-body">
                                <div class="admin-detail-grid">
                                    <div class="admin-detail-item">
                                        <span class="admin-detail-label">Métal:</span>
                                        <span class="admin-detail-value"><?php echo e($selectedPlanet->templatePlanet->metal_bonus * 100); ?>%</span>
                                    </div>
                                    <div class="admin-detail-item">
                                        <span class="admin-detail-label">Cristal:</span>
                                        <span class="admin-detail-value"><?php echo e($selectedPlanet->templatePlanet->crystal_bonus * 100); ?>%</span>
                                    </div>
                                    <div class="admin-detail-item">
                                        <span class="admin-detail-label">Deutérium:</span>
                                        <span class="admin-detail-value"><?php echo e($selectedPlanet->templatePlanet->deuterium_bonus * 100); ?>%</span>
                                    </div>
                                    <div class="admin-detail-item">
                                        <span class="admin-detail-label">Énergie:</span>
                                        <span class="admin-detail-value"><?php echo e($selectedPlanet->templatePlanet->energy_bonus * 100); ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Bouclier planétaire</h3>
                            </div>
                            <div class="admin-card-body">
                                <div class="admin-detail-grid">
                                    <div class="admin-detail-item">
                                        <span class="admin-detail-label">Statut:</span>
                                        <span class="admin-detail-value">
                                            <?php if($selectedPlanet->shield_active): ?>
                                                <span class="admin-badge admin-badge-success">Actif</span>
                                                (Expire: <?php echo e($selectedPlanet->shield_end_time->format('d/m/Y H:i')); ?>)
                                            <?php else: ?>
                                                <span class="admin-badge admin-badge-danger">Inactif</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Onglet Ressources -->
                    <?php if($planetDetailTab === 'resources'): ?>
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Ressources de la planète</h3>
                            </div>
                            <div class="admin-card-body">
                                <!-- Formulaire d'ajout/retrait de ressources -->
                                <div class="admin-form-grid admin-form-grid-2">
                                    <div class="admin-form-group">
                                        <label for="resourceForm.resource_id" class="admin-form-label">Ressource</label>
                                        <select wire:model="resourceForm.resource_id" id="resourceForm.resource_id" class="admin-form-select">
                                            <option value="">Sélectionner une ressource</option>
                                            <?php $__currentLoopData = $planetResources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($resource->resource_id); ?>"><?php echo e($resource->resource->display_name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['resourceForm.resource_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="admin-form-group">
                                        <label for="resourceForm.amount" class="admin-form-label">Quantité</label>
                                        <input type="number" wire:model="resourceForm.amount" id="resourceForm.amount" class="admin-form-input" min="1">
                                        <?php $__errorArgs = ['resourceForm.amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="admin-form-actions">
                                    <button type="button" wire:click="addResources" class="admin-button admin-button-success">Ajouter</button>
                                    <button type="button" wire:click="removeResources" class="admin-button admin-button-danger">Retirer</button>
                                </div>

                                <hr />
                                
                                <div class="admin-table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Ressource</th>
                                                <th>Quantité</th>
                                                <th>Production</th>
                                                <th>Dernière mise à jour</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = $planetResources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planetResource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td><?php echo e($planetResource->id); ?></td>
                                                    <td>
                                                        <?php if($planetResource->resource): ?>
                                                            <div class="admin-resource-name">
                                                                <div class="admin-resource-icon <?php echo e(strtolower($planetResource->resource->name)); ?>"></div>
                                                                <?php echo e($planetResource->resource->display_name); ?>

                                                            </div>
                                                        <?php else: ?>
                                                            N/A
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo e(number_format($planetResource->current_amount, 0, ',', ' ')); ?></td>
                                                    <td>
                                                        <?php
                                                            $productionClass = 'neutral';
                                                            if ($planetResource->production_rate > 0) {
                                                                $productionClass = 'positive';
                                                            } elseif ($planetResource->production_rate < 0) {
                                                                $productionClass = 'negative';
                                                            }
                                                        ?>
                                                        <span class="admin-production-indicator admin-production-<?php echo e($productionClass); ?>">
                                                            <?php echo e(number_format($planetResource->production_rate, 2, ',', ' ')); ?> / h
                                                        </span>
                                                    </td>
                                                    <td><?php echo e($planetResource->last_update ? $planetResource->last_update->format('d/m/Y H:i:s') : 'Aucune'); ?></td>
                                                    <td>
                                                        <?php if($planetResource->is_active): ?>
                                                            <span class="admin-badge admin-badge-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="admin-badge admin-badge-danger">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="6" class="admin-table-empty">Aucune ressource trouvée</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Onglet Bâtiments -->
                    <?php if($planetDetailTab === 'buildings'): ?>
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Bâtiments de la planète</h3>
                            </div>
                            <div class="admin-card-body">
                                <!-- Formulaire d'ajout/retrait de niveaux de bâtiments -->
                                <div class="admin-form-grid admin-form-grid-2">
                                    <div class="admin-form-group">
                                        <label for="buildingForm.building_id" class="admin-form-label">Bâtiment</label>
                                        <select wire:model="buildingForm.building_id" id="buildingForm.building_id" class="admin-form-select">
                                            <option value="">Sélectionner un bâtiment</option>
                                            <?php $__currentLoopData = $availableBuildings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $buildTpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($buildTpl->id); ?>"><?php echo e($buildTpl->label ?? $buildTpl->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['buildingForm.building_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="admin-form-group">
                                        <label for="buildingForm.levels" class="admin-form-label">Niveaux</label>
                                        <input type="number" wire:model="buildingForm.levels" id="buildingForm.levels" class="admin-form-input" min="1">
                                        <?php $__errorArgs = ['buildingForm.levels'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="admin-form-actions">
                                    <button type="button" wire:click="addBuildingLevels" class="admin-button admin-button-success">Ajouter niveaux</button>
                                    <button type="button" wire:click="removeBuildingLevels" class="admin-button admin-button-danger">Retirer niveaux</button>
                                </div>

                                <div class="admin-table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Bâtiment</th>
                                                <th>Niveau</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = $planetBuildings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $building): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td><?php echo e($building->id); ?></td>
                                                    <td>
                                                        <?php if($building->building): ?>
                                                            <div class="admin-building-name">
                                                                <div class="admin-building-icon <?php echo e(strtolower(str_replace(' ', '-', $building->building->name))); ?>"></div>
                                                                <?php echo e($building->building->label); ?>

                                                            </div>
                                                        <?php else: ?>
                                                            N/A
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo e($building->level); ?></td>
                                                    <td>
                                                        <?php if($building->is_active): ?>
                                                            <span class="admin-badge admin-badge-success">Actif</span>
                                                        <?php else: ?>
                                                            <span class="admin-badge admin-badge-danger">Inactif</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="4" class="admin-table-empty">Aucun bâtiment trouvé</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Onglet Unités -->
                    <?php if($planetDetailTab === 'units'): ?>
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Unités de la planète</h3>
                            </div>
                            <div class="admin-card-body">
                                <!-- Formulaire d'ajout/retrait d'unités -->
                                <div class="admin-form-grid admin-form-grid-2">
                                    <div class="admin-form-group">
                                        <label for="unitForm.unit_id" class="admin-form-label">Unité</label>
                                        <select wire:model="unitForm.unit_id" id="unitForm.unit_id" class="admin-form-select">
                                            <option value="">Sélectionner une unité</option>
                                            <?php $__currentLoopData = $availableUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unitTpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($unitTpl->id); ?>"><?php echo e($unitTpl->label ?? $unitTpl->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['unitForm.unit_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="admin-form-group">
                                        <label for="unitForm.quantity" class="admin-form-label">Quantité</label>
                                        <input type="number" wire:model="unitForm.quantity" id="unitForm.quantity" class="admin-form-input" min="1">
                                        <?php $__errorArgs = ['unitForm.quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="admin-form-actions">
                                    <button type="button" wire:click="addUnits" class="admin-button admin-button-success">Ajouter</button>
                                    <button type="button" wire:click="removeUnits" class="admin-button admin-button-danger">Retirer</button>
                                </div>

                                <hr />
                                
                                <div class="admin-table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Unité</th>
                                                <th>Quantité</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = $planetUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td><?php echo e($unit->id); ?></td>
                                                    <td>
                                                        <?php if($unit->unit): ?>
                                                            <div class="admin-unit-name">
                                                                <div class="admin-unit-icon <?php echo e(strtolower(str_replace(' ', '-', $unit->unit->name))); ?>"></div>
                                                                <?php echo e($unit->unit->label); ?>

                                                            </div>
                                                        <?php else: ?>
                                                            N/A
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo e(number_format($unit->quantity, 0, ',', ' ')); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="3" class="admin-table-empty">Aucune unité trouvée</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Onglet Vaisseaux -->
                    <?php if($planetDetailTab === 'ships'): ?>
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Vaisseaux de la planète</h3>
                            </div>
                            <div class="admin-card-body">
                                <!-- Formulaire d'ajout/retrait de vaisseaux -->
                                <div class="admin-form-grid admin-form-grid-2">
                                    <div class="admin-form-group">
                                        <label for="shipForm.ship_id" class="admin-form-label">Vaisseau</label>
                                        <select wire:model="shipForm.ship_id" id="shipForm.ship_id" class="admin-form-select">
                                            <option value="">Sélectionner un vaisseau</option>
                                            <?php $__currentLoopData = $availableShips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipTpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($shipTpl->id); ?>"><?php echo e($shipTpl->label ?? $shipTpl->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['shipForm.ship_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="admin-form-group">
                                        <label for="shipForm.quantity" class="admin-form-label">Quantité</label>
                                        <input type="number" wire:model="shipForm.quantity" id="shipForm.quantity" class="admin-form-input" min="1">
                                        <?php $__errorArgs = ['shipForm.quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="admin-form-actions">
                                    <button type="button" wire:click="addShips" class="admin-button admin-button-success">Ajouter</button>
                                    <button type="button" wire:click="removeShips" class="admin-button admin-button-danger">Retirer</button>
                                </div>

                                <hr />
                                
                                <div class="admin-table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Vaisseau</th>
                                                <th>Quantité</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = $planetShips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td><?php echo e($ship->id); ?></td>
                                                    <td>
                                                        <?php if($ship->ship): ?>
                                                            <div class="admin-ship-name">
                                                                <div class="admin-ship-icon <?php echo e(strtolower(str_replace(' ', '-', $ship->ship->name))); ?>"></div>
                                                                <?php echo e($ship->ship->label); ?>

                                                            </div>
                                                        <?php else: ?>
                                                            N/A
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo e(number_format($ship->quantity, 0, ',', ' ')); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="3" class="admin-table-empty">Aucun vaisseau trouvé</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Onglet Défenses -->
                    <?php if($planetDetailTab === 'defenses'): ?>
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Défenses de la planète</h3>
                            </div>
                            <div class="admin-card-body">
                                <!-- Formulaire d'ajout/retrait de défenses -->
                                <div class="admin-form-grid admin-form-grid-2">
                                    <div class="admin-form-group">
                                        <label for="defenseForm.defense_id" class="admin-form-label">Défense</label>
                                        <select wire:model="defenseForm.defense_id" id="defenseForm.defense_id" class="admin-form-select">
                                            <option value="">Sélectionner une défense</option>
                                            <?php $__currentLoopData = $availableDefenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $defTpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($defTpl->id); ?>"><?php echo e($defTpl->label ?? $defTpl->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['defenseForm.defense_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="admin-form-group">
                                        <label for="defenseForm.quantity" class="admin-form-label">Quantité</label>
                                        <input type="number" wire:model="defenseForm.quantity" id="defenseForm.quantity" class="admin-form-input" min="1">
                                        <?php $__errorArgs = ['defenseForm.quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="admin-form-actions">
                                    <button type="button" wire:click="addDefenses" class="admin-button admin-button-success">Ajouter</button>
                                    <button type="button" wire:click="removeDefenses" class="admin-button admin-button-danger">Retirer</button>
                                </div>

                                <hr />
                                
                                <div class="admin-table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Défense</th>
                                                <th>Quantité</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = $planetDefenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $defense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td><?php echo e($defense->id); ?></td>
                                                    <td>
                                                        <?php if($defense->defense): ?>
                                                            <div class="admin-defense-name">
                                                                <div class="admin-defense-icon <?php echo e(strtolower(str_replace(' ', '-', $defense->defense->name))); ?>"></div>
                                                                <?php echo e($defense->defense->name); ?>

                                                            </div>
                                                        <?php else: ?>
                                                            N/A
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo e(number_format($defense->quantity, 0, ',', ' ')); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="3" class="admin-table-empty">Aucune défense trouvée</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Onglet Bunker -->
                    <?php if($planetDetailTab === 'bunkers'): ?>
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Ressources dans le bunker</h3>
                            </div>
                            <div class="admin-card-body">
                                <div class="admin-table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Ressource</th>
                                                <th>Quantité</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = $planetBunkers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planetBunker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td><?php echo e($planetBunker->id); ?></td>
                                                    <td>
                                                        <?php if($planetBunker->resource): ?>
                                                            <div class="admin-resource-name">
                                                                <div class="admin-resource-icon <?php echo e(strtolower(str_replace(' ', '-', $planetBunker->resource->name))); ?>"></div>
                                                                <?php echo e($planetBunker->resource->name); ?>

                                                            </div>
                                                        <?php else: ?>
                                                            N/A
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo e(number_format($planetBunker->amount, 0, ',', ' ')); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="3" class="admin-table-empty">Aucune ressource dans le bunker</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Onglet Missions -->
                    <?php if($planetDetailTab === 'missions'): ?>
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Missions liées à la planète</h3>
                            </div>
                            <div class="admin-card-body">
                                <div class="admin-table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Type</th>
                                                <th>Origine</th>
                                                <th>Destination</th>
                                                <th>Utilisateur</th>
                                                <th>Départ</th>
                                                <th>Arrivée</th>
                                                <th>Retour</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = $planetMissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td><?php echo e($mission->id); ?></td>
                                                    <td><?php echo e($mission->mission_type); ?></td>
                                                    <td>
                                                        <?php if($mission->fromPlanet): ?>
                                                            <div class="admin-planet-coordinates"><?php echo e($mission->fromPlanet->name); ?></div>
                                                        <?php else: ?>
                                                            N/A
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($mission->toPlanet): ?>
                                                            <div class="admin-planet-coordinates"><?php echo e($mission->toPlanet->name); ?></div>
                                                        <?php else: ?>
                                                            N/A
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($mission->user): ?>
                                                            <div class="admin-user-name"><?php echo e($mission->user->name); ?></div>
                                                        <?php else: ?>
                                                            N/A
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($mission->departure_time): ?>
                                                            <?php echo e($mission->departure_time->format('d/m/Y H:i:s')); ?>

                                                        <?php else: ?>
                                                            N/A
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($mission->arrival_time): ?>
                                                            <?php echo e($mission->arrival_time->format('d/m/Y H:i:s')); ?>

                                                        <?php else: ?>
                                                            N/A
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($mission->return_time): ?>
                                                            <?php echo e($mission->return_time->format('d/m/Y H:i:s')); ?>

                                                        <?php else: ?>
                                                            N/A
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($mission->status === 'completed'): ?>
                                                            <span class="admin-badge admin-badge-success">Terminée</span>
                                                        <?php elseif($mission->status === 'returning'): ?>
                                                            <span class="admin-badge admin-badge-info">Retour</span>
                                                        <?php else: ?>
                                                            <span class="admin-badge admin-badge-warning">En cours</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="8" class="admin-table-empty">Aucune mission trouvée</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="admin-pagination">
                                    <?php echo e($planetMissions->links()); ?>

                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/admin/planets.blade.php ENDPATH**/ ?>