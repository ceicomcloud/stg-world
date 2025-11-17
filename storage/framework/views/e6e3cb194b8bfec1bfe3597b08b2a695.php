<div class="admin-content">
    <div class="admin-page-header">
        <h1>Gestion des planètes templates</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button <?php echo e($activeTab === 'list' ? 'active' : ''); ?>" wire:click="setActiveTab('list')">
                Liste des planètes
            </button>
            <button class="admin-tab-button <?php echo e($activeTab === 'create' ? 'active' : ''); ?>" wire:click="setActiveTab('create')">
                Créer une planète
            </button>
            <?php if($selectedPlanet): ?>
                <button class="admin-tab-button active">
                    <?php echo e($selectedPlanet->name); ?>

                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fas fa-globe"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value"><?php echo e($freePlanetsCount); ?></div>
                <div class="admin-stat-label">Planètes libres</div>
            </div>
        </div>
        
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fas fa-user-astronaut"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value"><?php echo e($occupiedPlanetsCount); ?></div>
                <div class="admin-stat-label">Planètes occupées</div>
            </div>
        </div>
        
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fas fa-flag"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value"><?php echo e($colonizablePlanetsCount); ?></div>
                <div class="admin-stat-label">Planètes colonisables</div>
            </div>
        </div>
        
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fas fa-meteor"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value"><?php echo e(array_sum($planetTypeCounts)); ?></div>
                <div class="admin-stat-label">Total des planètes</div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Liste des planètes</h2>
        </div>
        <div class="admin-card-body">            
            <!-- Onglet Liste des planètes -->
            <?php if($activeTab === 'list'): ?>
                <div class="admin-filters">
                    <div class="admin-filter-group">
                        <input type="text" wire:model.live="search" placeholder="Rechercher par nom ou coordonnées..." class="admin-input">
                    </div>
                    
                    <div class="admin-filter-group">
                        <select wire:model.live="filterGalaxy" class="admin-select">
                            <option value="">Toutes les galaxies</option>
                            <?php $__currentLoopData = $galaxies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $galaxy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($galaxy); ?>">Galaxie <?php echo e($galaxy); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    
                    <div class="admin-filter-group">
                        <select wire:model.live="filterSystem" class="admin-select">
                            <option value="">Tous les systèmes</option>
                            <?php $__currentLoopData = $systems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $system): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($system); ?>">Système <?php echo e($system); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    
                    <div class="admin-filter-group">
                        <select wire:model.live="filterType" class="admin-select">
                            <option value="">Tous les types</option>
                            <?php $__currentLoopData = $planetTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    
                    <div class="admin-filter-group">
                        <select wire:model.live="filterSize" class="admin-select">
                            <option value="">Toutes les tailles</option>
                            <?php $__currentLoopData = $planetSizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    
                    <div class="admin-filter-group">
                        <select wire:model.live="filterOccupied" class="admin-select">
                            <option value="">Occupation</option>
                            <option value="occupied">Occupées</option>
                            <option value="free">Libres</option>
                        </select>
                    </div>
                    
                    <div class="admin-filter-group">
                        <select wire:model.live="filterColonizable" class="admin-select">
                            <option value="">Colonisation</option>
                            <option value="colonizable">Colonisables</option>
                            <option value="not_colonizable">Non colonisables</option>
                        </select>
                    </div>
                    
                    <div class="admin-filter-group">
                        <select wire:model.live="filterActive" class="admin-select">
                            <option value="">Statut</option>
                            <option value="active">Actives</option>
                            <option value="inactive">Inactives</option>
                        </select>
                    </div>
                    
                    <div class="admin-filter-group">
                        <select wire:model.live="perPage" class="admin-select">
                            <option value="15">15 par page</option>
                            <option value="30">30 par page</option>
                            <option value="50">50 par page</option>
                            <option value="100">100 par page</option>
                        </select>
                    </div>
                </div>
                
                <div class="admin-table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th wire:click="sortBy('id')" class="admin-th-sortable">
                                    ID
                                    <?php if($sortField === 'id'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th wire:click="sortBy('name')" class="admin-th-sortable">
                                    Nom
                                    <?php if($sortField === 'name'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th wire:click="sortBy('galaxy')" class="admin-th-sortable">
                                    Coordonnées
                                    <?php if($sortField === 'galaxy'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th wire:click="sortBy('type')" class="admin-th-sortable">
                                    Type
                                    <?php if($sortField === 'type'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th wire:click="sortBy('size')" class="admin-th-sortable">
                                    Taille
                                    <?php if($sortField === 'size'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th wire:click="sortBy('fields')" class="admin-th-sortable">
                                    Champs
                                    <?php if($sortField === 'fields'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th wire:click="sortBy('is_occupied')" class="admin-th-sortable">
                                    Occupation
                                    <?php if($sortField === 'is_occupied'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th wire:click="sortBy('is_colonizable')" class="admin-th-sortable">
                                    Colonisable
                                    <?php if($sortField === 'is_colonizable'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th wire:click="sortBy('is_active')" class="admin-th-sortable">
                                    Statut
                                    <?php if($sortField === 'is_active'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th wire:click="sortBy('created_at')" class="admin-th-sortable">
                                    Création
                                    <?php if($sortField === 'created_at'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
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
                                    <td>[<?php echo e($planet->galaxy); ?>:<?php echo e($planet->system); ?>:<?php echo e($planet->position); ?>]</td>
                                    <td>
                                        <span class="admin-badge admin-badge-<?php echo e($planet->type); ?>">
                                            <?php echo e($planetTypes[$planet->type]); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($planetSizes[$planet->size]); ?></td>
                                    <td><?php echo e($planet->fields); ?></td>
                                    <td>
                                        <?php if($planet->is_occupied): ?>
                                            <span class="admin-badge admin-badge-danger">Occupée</span>
                                        <?php else: ?>
                                            <span class="admin-badge admin-badge-success">Libre</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($planet->is_colonizable): ?>
                                            <span class="admin-badge admin-badge-success">Oui</span>
                                        <?php else: ?>
                                            <span class="admin-badge admin-badge-danger">Non</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($planet->is_active): ?>
                                            <span class="admin-badge admin-badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="admin-badge admin-badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($planet->created_at->format('d/m/Y')); ?></td>
                                    <td>
                                        <div class="admin-actions">
                                            <button wire:click="selectPlanet(<?php echo e($planet->id); ?>)" class="admin-btn admin-btn-primary admin-btn-sm">
                                                <i class="fas fa-edit"></i> Éditer
                                            </button>
                                            <button wire:click="deletePlanet(<?php echo e($planet->id); ?>)" class="admin-btn admin-btn-danger admin-btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette planète ?')">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="11" class="text-center">Aucune planète trouvée</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="admin-pagination">
                    <?php echo e($planets->links()); ?>

                </div>
            <?php endif; ?>
            
            <!-- Onglet Créer une planète -->
            <?php if($activeTab === 'create'): ?>
                <form wire:submit.prevent="createPlanet" class="admin-form">
                    <div class="admin-form-section">
                        <h3 class="admin-form-title">Informations générales</h3>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="name" class="admin-form-label">Nom</label>
                                <div class="admin-input-group">
                                    <input type="text" id="name" wire:model="planetForm.name" class="admin-input" required>
                                    <button type="button" wire:click="generatePlanetName" class="admin-btn admin-btn-secondary">
                                        <i class="fas fa-magic"></i> Générer
                                    </button>
                                </div>
                                <?php $__errorArgs = ['planetForm.name'];
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
                                <label class="admin-form-label">Coordonnées</label>
                                <div class="admin-input-group">
                                    <input type="number" wire:model="planetForm.galaxy" class="admin-input" min="1" placeholder="Galaxie" required>
                                    <span class="admin-input-group-text">:</span>
                                    <input type="number" wire:model="planetForm.system" class="admin-input" min="1" placeholder="Système" required>
                                    <span class="admin-input-group-text">:</span>
                                    <input type="number" wire:model="planetForm.position" class="admin-input" min="1" placeholder="Position" required>
                                </div>
                                <?php $__errorArgs = ['planetForm.galaxy'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <?php $__errorArgs = ['planetForm.system'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <?php $__errorArgs = ['planetForm.position'];
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
                                <label for="type" class="admin-form-label">Type</label>
                                <select id="type" wire:model.live="planetForm.type" class="admin-select" required>
                                    <?php $__currentLoopData = $planetTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['planetForm.type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="size" class="admin-form-label">Taille</label>
                                <select id="size" wire:model.live="planetForm.size" class="admin-select" required>
                                    <?php $__currentLoopData = $planetSizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['planetForm.size'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="" class="admin-form-label">&nbsp;</label>
                                <button type="button" wire:click="calculatePlanetProperties" class="admin-btn admin-btn-secondary">
                                    <i class="fas fa-calculator"></i> Calculer les propriétés
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-section">
                        <h3 class="admin-form-title">Propriétés physiques</h3>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="diameter" class="admin-form-label">Diamètre (km)</label>
                                <input type="number" id="diameter" wire:model="planetForm.diameter" class="admin-input" min="1000" required>
                                <?php $__errorArgs = ['planetForm.diameter'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="fields" class="admin-form-label">Champs</label>
                                <input type="number" id="fields" wire:model="planetForm.fields" class="admin-input" min="0" required>
                                <?php $__errorArgs = ['planetForm.fields'];
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
                                <label for="min_temperature" class="admin-form-label">Température min (°C)</label>
                                <input type="number" id="min_temperature" wire:model="planetForm.min_temperature" class="admin-input" required>
                                <?php $__errorArgs = ['planetForm.min_temperature'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="max_temperature" class="admin-form-label">Température max (°C)</label>
                                <input type="number" id="max_temperature" wire:model="planetForm.max_temperature" class="admin-input" required>
                                <?php $__errorArgs = ['planetForm.max_temperature'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-section">
                        <h3 class="admin-form-title">Bonus de ressources</h3>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="metal_bonus" class="admin-form-label">Bonus de métal</label>
                                <input type="number" id="metal_bonus" wire:model="planetForm.metal_bonus" class="admin-input" min="0" step="0.01" required>
                                <?php $__errorArgs = ['planetForm.metal_bonus'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="crystal_bonus" class="admin-form-label">Bonus de cristal</label>
                                <input type="number" id="crystal_bonus" wire:model="planetForm.crystal_bonus" class="admin-input" min="0" step="0.01" required>
                                <?php $__errorArgs = ['planetForm.crystal_bonus'];
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
                                <label for="deuterium_bonus" class="admin-form-label">Bonus de deutérium</label>
                                <input type="number" id="deuterium_bonus" wire:model="planetForm.deuterium_bonus" class="admin-input" min="0" step="0.01" required>
                                <?php $__errorArgs = ['planetForm.deuterium_bonus'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="energy_bonus" class="admin-form-label">Bonus d'énergie</label>
                                <input type="number" id="energy_bonus" wire:model="planetForm.energy_bonus" class="admin-input" min="0" step="0.01" required>
                                <?php $__errorArgs = ['planetForm.energy_bonus'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-section">
                        <h3 class="admin-form-title">Statut</h3>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Colonisable</label>
                                <div class="admin-toggle-switch">
                                    <input type="checkbox" id="is_colonizable" wire:model="planetForm.is_colonizable" class="admin-toggle-input">
                                    <label for="is_colonizable" class="admin-toggle-label"></label>
                                </div>
                                <?php $__errorArgs = ['planetForm.is_colonizable'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Occupée</label>
                                <div class="admin-toggle-switch">
                                    <input type="checkbox" id="is_occupied" wire:model="planetForm.is_occupied" class="admin-toggle-input">
                                    <label for="is_occupied" class="admin-toggle-label"></label>
                                </div>
                                <?php $__errorArgs = ['planetForm.is_occupied'];
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
                                <label class="admin-form-label">Disponible</label>
                                <div class="admin-toggle-switch">
                                    <input type="checkbox" id="is_available" wire:model="planetForm.is_available" class="admin-toggle-input">
                                    <label for="is_available" class="admin-toggle-label"></label>
                                </div>
                                <?php $__errorArgs = ['planetForm.is_available'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Active</label>
                                <div class="admin-toggle-switch">
                                    <input type="checkbox" id="is_active" wire:model="planetForm.is_active" class="admin-toggle-input">
                                    <label for="is_active" class="admin-toggle-label"></label>
                                </div>
                                <?php $__errorArgs = ['planetForm.is_active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-actions">
                        <button type="submit" class="admin-btn admin-btn-primary">
                            <i class="fas fa-save"></i> Créer la planète
                        </button>
                        <button type="button" wire:click="resetPlanetForm" class="admin-btn admin-btn-secondary">
                            <i class="fas fa-undo"></i> Réinitialiser
                        </button>
                    </div>
                </form>
            <?php endif; ?>
            
            <!-- Onglet Édition d'une planète -->
            <?php if($activeTab === 'edit' && $selectedPlanet): ?>
                <form wire:submit.prevent="updatePlanet" class="admin-form">
                    <div class="admin-form-section">
                        <h3 class="admin-form-title">Informations générales</h3>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="edit_name" class="admin-form-label">Nom</label>
                                <div class="admin-input-group">
                                    <input type="text" id="edit_name" wire:model="planetForm.name" class="admin-input" required>
                                    <button type="button" wire:click="generatePlanetName" class="admin-btn admin-btn-secondary">
                                        <i class="fas fa-magic"></i> Générer
                                    </button>
                                </div>
                                <?php $__errorArgs = ['planetForm.name'];
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
                                <label class="admin-form-label">Coordonnées</label>
                                <div class="admin-input-group">
                                    <input type="number" wire:model="planetForm.galaxy" class="admin-input" min="1" placeholder="Galaxie" required>
                                    <span class="admin-input-group-text">:</span>
                                    <input type="number" wire:model="planetForm.system" class="admin-input" min="1" placeholder="Système" required>
                                    <span class="admin-input-group-text">:</span>
                                    <input type="number" wire:model="planetForm.position" class="admin-input" min="1" placeholder="Position" required>
                                </div>
                                <?php $__errorArgs = ['planetForm.galaxy'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <?php $__errorArgs = ['planetForm.system'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <?php $__errorArgs = ['planetForm.position'];
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
                                <label for="edit_type" class="admin-form-label">Type</label>
                                <select id="edit_type" wire:model.live="planetForm.type" class="admin-select" required>
                                    <?php $__currentLoopData = $planetTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['planetForm.type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_size" class="admin-form-label">Taille</label>
                                <select id="edit_size" wire:model.live="planetForm.size" class="admin-select" required>
                                    <?php $__currentLoopData = $planetSizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['planetForm.size'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="" class="admin-form-label">&nbsp;</label>
                                <button type="button" wire:click="calculatePlanetProperties" class="admin-btn admin-btn-secondary">
                                    <i class="fas fa-calculator"></i> Calculer les propriétés
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-section">
                        <h3 class="admin-form-title">Propriétés physiques</h3>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="edit_diameter" class="admin-form-label">Diamètre (km)</label>
                                <input type="number" id="edit_diameter" wire:model="planetForm.diameter" class="admin-input" min="1000" required>
                                <?php $__errorArgs = ['planetForm.diameter'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_fields" class="admin-form-label">Champs</label>
                                <input type="number" id="edit_fields" wire:model="planetForm.fields" class="admin-input" min="0" required>
                                <?php $__errorArgs = ['planetForm.fields'];
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
                                <label for="edit_min_temperature" class="admin-form-label">Température min (°C)</label>
                                <input type="number" id="edit_min_temperature" wire:model="planetForm.min_temperature" class="admin-input" required>
                                <?php $__errorArgs = ['planetForm.min_temperature'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_max_temperature" class="admin-form-label">Température max (°C)</label>
                                <input type="number" id="edit_max_temperature" wire:model="planetForm.max_temperature" class="admin-input" required>
                                <?php $__errorArgs = ['planetForm.max_temperature'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-section">
                        <h3 class="admin-form-title">Bonus de ressources</h3>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="edit_metal_bonus" class="admin-form-label">Bonus de métal</label>
                                <input type="number" id="edit_metal_bonus" wire:model="planetForm.metal_bonus" class="admin-input" min="0" step="0.01" required>
                                <?php $__errorArgs = ['planetForm.metal_bonus'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_crystal_bonus" class="admin-form-label">Bonus de cristal</label>
                                <input type="number" id="edit_crystal_bonus" wire:model="planetForm.crystal_bonus" class="admin-input" min="0" step="0.01" required>
                                <?php $__errorArgs = ['planetForm.crystal_bonus'];
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
                                <label for="edit_deuterium_bonus" class="admin-form-label">Bonus de deutérium</label>
                                <input type="number" id="edit_deuterium_bonus" wire:model="planetForm.deuterium_bonus" class="admin-input" min="0" step="0.01" required>
                                <?php $__errorArgs = ['planetForm.deuterium_bonus'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_energy_bonus" class="admin-form-label">Bonus d'énergie</label>
                                <input type="number" id="edit_energy_bonus" wire:model="planetForm.energy_bonus" class="admin-input" min="0" step="0.01" required>
                                <?php $__errorArgs = ['planetForm.energy_bonus'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-section">
                        <h3 class="admin-form-title">Statut</h3>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Colonisable</label>
                                <div class="admin-toggle-switch">
                                    <input type="checkbox" id="edit_is_colonizable" wire:model="planetForm.is_colonizable" class="admin-toggle-input">
                                    <label for="edit_is_colonizable" class="admin-toggle-label"></label>
                                </div>
                                <?php $__errorArgs = ['planetForm.is_colonizable'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Occupée</label>
                                <div class="admin-toggle-switch">
                                    <input type="checkbox" id="edit_is_occupied" wire:model="planetForm.is_occupied" class="admin-toggle-input">
                                    <label for="edit_is_occupied" class="admin-toggle-label"></label>
                                </div>
                                <?php $__errorArgs = ['planetForm.is_occupied'];
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
                                <label class="admin-form-label">Disponible</label>
                                <div class="admin-toggle-switch">
                                    <input type="checkbox" id="edit_is_available" wire:model="planetForm.is_available" class="admin-toggle-input">
                                    <label for="edit_is_available" class="admin-toggle-label"></label>
                                </div>
                                <?php $__errorArgs = ['planetForm.is_available'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Active</label>
                                <div class="admin-toggle-switch">
                                    <input type="checkbox" id="edit_is_active" wire:model="planetForm.is_active" class="admin-toggle-input">
                                    <label for="edit_is_active" class="admin-toggle-label"></label>
                                </div>
                                <?php $__errorArgs = ['planetForm.is_active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-actions">
                        <button type="submit" class="admin-btn admin-btn-primary">
                            <i class="fas fa-save"></i> Mettre à jour la planète
                        </button>
                        <button type="button" wire:click="setActiveTab('list')" class="admin-btn admin-btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/admin/template/planets.blade.php ENDPATH**/ ?>