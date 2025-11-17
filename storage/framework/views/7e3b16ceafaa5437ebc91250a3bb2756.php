<div class="admin-resoureces">
    <div class="admin-page-header">
        <h1>Gestion des ressources</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button <?php echo e($activeTab === 'list' ? 'active' : ''); ?>" wire:click="setActiveTab('list')">
                <i class="fas fa-coins"></i> Liste des ressources
            </button>
            <button class="admin-tab-button <?php echo e($activeTab === 'create' ? 'active' : ''); ?>" wire:click="setActiveTab('create')">
                <i class="fas fa-plus-circle"></i> Créer une ressource
            </button>
            <?php if($activeTab === 'edit'): ?>
                <button class="admin-tab-button active">
                    <i class="fas fa-edit"></i> <?php echo e($selectedResource->display_name); ?>

                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-content-body">
        <!-- Liste des ressources -->
        <?php if($activeTab === 'list'): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Liste des ressources</h2>
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
                                <?php $__currentLoopData = $resourceTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                                    <th wire:click="sortBy('sort_order')" class="admin-sortable">
                                        Ordre
                                        <?php if($sortField === 'sort_order'): ?>
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
                                    <th wire:click="sortBy('display_name')" class="admin-sortable">
                                        Nom affiché
                                        <?php if($sortField === 'display_name'): ?>
                                            <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th>Icône</th>
                                    <th wire:click="sortBy('type')" class="admin-sortable">
                                        Type
                                        <?php if($sortField === 'type'): ?>
                                            <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th wire:click="sortBy('base_production')" class="admin-sortable">
                                        Production
                                        <?php if($sortField === 'base_production'): ?>
                                            <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th wire:click="sortBy('base_storage')" class="admin-sortable">
                                        Stockage
                                        <?php if($sortField === 'base_storage'): ?>
                                            <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th>Planètes</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $resources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($resource->sort_order); ?></td>
                                        <td><?php echo e($resource->name); ?></td>
                                        <td><?php echo e($resource->display_name); ?></td>
                                        <td>
                                            <?php if($resource->icon): ?>
                                                <i class="fas fa-<?php echo e($resource->icon); ?>" style="color: <?php echo e($resource->color); ?>;"></i>
                                            <?php else: ?>
                                                <i class="fas fa-coins" style="color: <?php echo e($resource->color); ?>;"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($resourceTypes[$resource->type] ?? $resource->type); ?></td>
                                        <td><?php echo e($resource->base_production); ?></td>
                                        <td><?php echo e($resource->base_storage); ?></td>
                                        <td><?php echo e($planetCounts[$resource->id] ?? 0); ?></td>
                                        <td>
                                            <?php if($resource->is_active): ?>
                                                <span class="admin-badge admin-badge-success">Actif</span>
                                            <?php else: ?>
                                                <span class="admin-badge admin-badge-danger">Inactif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="selectResource(<?php echo e($resource->id); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                
                                <?php if($resources->count() === 0): ?>
                                    <tr>
                                        <td colspan="10" class="admin-table-empty">Aucune ressource trouvée</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="admin-pagination">
                        <?php echo e($resources->links()); ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Créer une ressource -->
        <?php if($activeTab === 'create'): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Créer une nouvelle ressource</h2>
                </div>
                <div class="admin-card-body">
                    <form wire:submit.prevent="createResource">
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="name">Nom (identifiant)</label>
                                <input type="text" id="name" wire:model="resourceForm.name" class="admin-input" required>
                                <?php $__errorArgs = ['resourceForm.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="display_name">Nom affiché</label>
                                <input type="text" id="display_name" wire:model="resourceForm.display_name" class="admin-input" required>
                                <?php $__errorArgs = ['resourceForm.display_name'];
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
                                <label for="type">Type de ressource</label>
                                <select id="type" wire:model="resourceForm.type" class="admin-select" required>
                                    <option value="">Sélectionner un type</option>
                                    <?php $__currentLoopData = $resourceTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['resourceForm.type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="sort_order">Ordre d'affichage</label>
                                <input type="number" id="sort_order" wire:model="resourceForm.sort_order" class="admin-input" min="0" required>
                                <?php $__errorArgs = ['resourceForm.sort_order'];
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
                                <label for="icon">Icône (classe FontAwesome)</label>
                                <input type="text" id="icon" wire:model="resourceForm.icon" class="admin-input" placeholder="ex: coins, atom, etc.">
                                <?php $__errorArgs = ['resourceForm.icon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <div class="admin-form-help">Prévisualisation: 
                                    <i class="fas fa-<?php echo e($resourceForm['icon'] ?: 'coins'); ?>" style="color: <?php echo e($resourceForm['color']); ?>;"></i>
                                </div>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="color">Couleur</label>
                                <input type="color" id="color" wire:model="resourceForm.color" class="admin-input">
                                <?php $__errorArgs = ['resourceForm.color'];
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
                                <label for="base_production">Production de base</label>
                                <input type="number" id="base_production" wire:model="resourceForm.base_production" class="admin-input" min="0" step="0.01" required>
                                <?php $__errorArgs = ['resourceForm.base_production'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="base_storage">Stockage de base</label>
                                <input type="number" id="base_storage" wire:model="resourceForm.base_storage" class="admin-input" min="0" required>
                                <?php $__errorArgs = ['resourceForm.base_storage'];
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
                                <label for="trade_rate">Taux d'échange</label>
                                <input type="number" id="trade_rate" wire:model="resourceForm.trade_rate" class="admin-input" min="0" step="0.01" required>
                                <?php $__errorArgs = ['resourceForm.trade_rate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group admin-form-checkbox-group">
                                <div class="admin-checkbox-container">
                                    <input type="checkbox" id="is_tradeable" wire:model="resourceForm.is_tradeable" class="admin-checkbox">
                                    <label for="is_tradeable">Échangeable</label>
                                </div>
                                <?php $__errorArgs = ['resourceForm.is_tradeable'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                
                                <div class="admin-checkbox-container">
                                    <input type="checkbox" id="is_active" wire:model="resourceForm.is_active" class="admin-checkbox">
                                    <label for="is_active">Actif</label>
                                </div>
                                <?php $__errorArgs = ['resourceForm.is_active'];
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
                            <div class="admin-form-group admin-form-group-full">
                                <label for="description">Description</label>
                                <textarea id="description" wire:model="resourceForm.description" class="admin-textarea" rows="4"></textarea>
                                <?php $__errorArgs = ['resourceForm.description'];
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
                            <button type="button" class="admin-btn admin-btn-secondary" wire:click="setActiveTab('list')">
                                <i class="fas fa-times"></i> Annuler
                            </button>
                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="fas fa-save"></i> Créer la ressource
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Éditer une ressource -->
        <?php if($activeTab === 'edit' && $selectedResource): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Modifier la ressource: <?php echo e($selectedResource->display_name); ?></h2>
                </div>
                <div class="admin-card-body">
                    <form wire:submit.prevent="updateResource">
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="edit_name">Nom (identifiant)</label>
                                <input type="text" id="edit_name" wire:model="resourceForm.name" class="admin-input" required>
                                <?php $__errorArgs = ['resourceForm.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_display_name">Nom affiché</label>
                                <input type="text" id="edit_display_name" wire:model="resourceForm.display_name" class="admin-input" required>
                                <?php $__errorArgs = ['resourceForm.display_name'];
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
                                <label for="edit_type">Type de ressource</label>
                                <select id="edit_type" wire:model="resourceForm.type" class="admin-select" required>
                                    <option value="">Sélectionner un type</option>
                                    <?php $__currentLoopData = $resourceTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['resourceForm.type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_sort_order">Ordre d'affichage</label>
                                <input type="number" id="edit_sort_order" wire:model="resourceForm.sort_order" class="admin-input" min="0" required>
                                <?php $__errorArgs = ['resourceForm.sort_order'];
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
                                <label for="edit_icon">Icône (classe FontAwesome)</label>
                                <input type="text" id="edit_icon" wire:model="resourceForm.icon" class="admin-input" placeholder="ex: coins, atom, etc.">
                                <?php $__errorArgs = ['resourceForm.icon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <div class="admin-form-help">Prévisualisation: 
                                    <i class="fas fa-<?php echo e($resourceForm['icon'] ?: 'coins'); ?>" style="color: <?php echo e($resourceForm['color']); ?>;"></i>
                                </div>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_color">Couleur</label>
                                <input type="color" id="edit_color" wire:model="resourceForm.color" class="admin-input">
                                <?php $__errorArgs = ['resourceForm.color'];
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
                                <label for="edit_base_production">Production de base</label>
                                <input type="number" id="edit_base_production" wire:model="resourceForm.base_production" class="admin-input" min="0" step="0.01" required>
                                <?php $__errorArgs = ['resourceForm.base_production'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_base_storage">Stockage de base</label>
                                <input type="number" id="edit_base_storage" wire:model="resourceForm.base_storage" class="admin-input" min="0" required>
                                <?php $__errorArgs = ['resourceForm.base_storage'];
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
                                <label for="edit_trade_rate">Taux d'échange</label>
                                <input type="number" id="edit_trade_rate" wire:model="resourceForm.trade_rate" class="admin-input" min="0" step="0.01" required>
                                <?php $__errorArgs = ['resourceForm.trade_rate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group admin-form-checkbox-group">
                                <div class="admin-checkbox-container">
                                    <input type="checkbox" id="edit_is_tradeable" wire:model="resourceForm.is_tradeable" class="admin-checkbox">
                                    <label for="edit_is_tradeable">Échangeable</label>
                                </div>
                                <?php $__errorArgs = ['resourceForm.is_tradeable'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                
                                <div class="admin-checkbox-container">
                                    <input type="checkbox" id="edit_is_active" wire:model="resourceForm.is_active" class="admin-checkbox">
                                    <label for="edit_is_active">Actif</label>
                                </div>
                                <?php $__errorArgs = ['resourceForm.is_active'];
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
                            <div class="admin-form-group admin-form-group-full">
                                <label for="edit_description">Description</label>
                                <textarea id="edit_description" wire:model="resourceForm.description" class="admin-textarea" rows="4"></textarea>
                                <?php $__errorArgs = ['resourceForm.description'];
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
                            <button type="button" class="admin-btn admin-btn-secondary" wire:click="setActiveTab('list')">
                                <i class="fas fa-times"></i> Annuler
                            </button>
                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="fas fa-save"></i> Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/admin/template/resources.blade.php ENDPATH**/ ?>