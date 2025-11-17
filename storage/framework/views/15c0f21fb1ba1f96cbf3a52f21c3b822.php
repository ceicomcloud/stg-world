<div class="admin-factions">
    <div class="admin-page-header">
        <h1>Gestion des factions</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button <?php echo e($activeTab === 'list' ? 'active' : ''); ?>" wire:click="setActiveTab('list')">
                <i class="fas fa-flag"></i> Liste des factions
            </button>
            <button class="admin-tab-button <?php echo e($activeTab === 'create' ? 'active' : ''); ?>" wire:click="setActiveTab('create')">
                <i class="fas fa-plus"></i> Créer une faction
            </button>
            <?php if($activeTab === 'edit'): ?>
                <button class="admin-tab-button active">
                    <i class="fas fa-edit"></i> <?php echo e($selectedFaction->name); ?>

                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-content-body">
        <!-- Liste des factions -->
        <?php if($activeTab === 'list'): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Liste des factions</h2>
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
                            <label for="filterActive">Statut:</label>
                            <select id="filterActive" wire:model.live="filterActive" class="admin-select">
                                <option value="">Tous</option>
                                <option value="active">Actives</option>
                                <option value="inactive">Inactives</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="admin-table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th wire:click="sortBy('sort_order')" class="admin-sortable-column">
                                        Ordre
                                        <?php if($sortField === 'sort_order'): ?>
                                            <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th wire:click="sortBy('name')" class="admin-sortable-column">
                                        Nom
                                        <?php if($sortField === 'name'): ?>
                                            <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th>Icône</th>
                                    <th>Couleur</th>
                                    <th>Bonus principaux</th>
                                    <th>Utilisateurs</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $factions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($faction->sort_order); ?></td>
                                        <td><?php echo e($faction->name); ?></td>
                                        <td>
                                            <?php if($faction->icon): ?>
                                                <i class="<?php echo e($faction->icon); ?>" style="color: <?php echo e($faction->color_code); ?>;"></i>
                                            <?php else: ?>
                                                <i class="fas fa-flag" style="color: <?php echo e($faction->color_code); ?>;"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="admin-color-preview" style="background-color: <?php echo e($faction->color_code); ?>;"></div>
                                            <?php echo e($faction->color_code); ?>

                                        </td>
                                        <td>
                                            <div class="admin-badges-container">
                                                <?php if($faction->getBonusResourceProduction() != 0): ?>
                                                    <span class="admin-badge <?php echo e($faction->getBonusResourceProduction() > 0 ? 'success' : 'danger'); ?>">
                                                        Production: <?php echo e($faction->getBonusResourceProduction() > 0 ? '+' : ''); ?><?php echo e($faction->getBonusResourceProduction()); ?>%
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if($faction->getBonusAttackPower() != 0): ?>
                                                    <span class="admin-badge <?php echo e($faction->getBonusAttackPower() > 0 ? 'success' : 'danger'); ?>">
                                                        Attaque: <?php echo e($faction->getBonusAttackPower() > 0 ? '+' : ''); ?><?php echo e($faction->getBonusAttackPower()); ?>%
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if($faction->getBonusDefensePower() != 0): ?>
                                                    <span class="admin-badge <?php echo e($faction->getBonusDefensePower() > 0 ? 'success' : 'danger'); ?>">
                                                        Défense: <?php echo e($faction->getBonusDefensePower() > 0 ? '+' : ''); ?><?php echo e($faction->getBonusDefensePower()); ?>%
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="admin-badge info">
                                                <?php echo e($userCounts[$faction->id] ?? 0); ?> utilisateur(s)
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($faction->is_active): ?>
                                                <span class="admin-badge success">Active</span>
                                            <?php else: ?>
                                                <span class="admin-badge danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="admin-actions">
                                                <button class="admin-action-button admin-action-info" wire:click="selectFaction(<?php echo e($faction->id); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="admin-table-empty">Aucune faction trouvée</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="admin-pagination">
                        <?php echo e($factions->links()); ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Créer une faction -->
        <?php if($activeTab === 'create'): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Créer une nouvelle faction</h2>
                </div>
                <div class="admin-card-body">
                    <form wire:submit.prevent="createFaction">
                        <div class="admin-form-grid-2">
                            <div class="admin-form-group">
                                <label for="name">Nom de la faction</label>
                                <input type="text" id="name" wire:model="factionForm.name" wire:change="generateSlug" class="admin-input" required>
                                <?php $__errorArgs = ['factionForm.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="slug">Slug</label>
                                <input type="text" id="slug" wire:model="factionForm.slug" class="admin-input" required>
                                <?php $__errorArgs = ['factionForm.slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        
                        <div class="admin-form-grid-2">
                            <div class="admin-form-group">
                                <label for="icon">Icône (classe FontAwesome)</label>
                                <input type="text" id="icon" wire:model="factionForm.icon" class="admin-input" placeholder="fas fa-flag">
                                <?php $__errorArgs = ['factionForm.icon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="color_code">Couleur</label>
                                <input type="color" id="color_code" wire:model="factionForm.color_code" class="admin-input">
                                <?php $__errorArgs = ['factionForm.color_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        
                        <div class="admin-form-grid-2">
                            <div class="admin-form-group">
                                <label for="banner">Bannière (URL)</label>
                                <input type="text" id="banner" wire:model="factionForm.banner" class="admin-input" placeholder="URL de l'image">
                                <?php $__errorArgs = ['factionForm.banner'];
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
                                <input type="number" id="sort_order" wire:model="factionForm.sort_order" class="admin-input" min="0">
                                <?php $__errorArgs = ['factionForm.sort_order'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="description">Description</label>
                            <textarea id="description" wire:model="factionForm.description" class="admin-textarea" rows="4"></textarea>
                            <?php $__errorArgs = ['factionForm.description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="admin-form-group">
                            <label>Bonus de faction</label>
                            <div class="admin-form-grid-2">
                                <div class="admin-form-group">
                                    <label for="resource_production">Production de ressources (%)</label>
                                    <input type="number" id="resource_production" wire:model="factionForm.bonuses.resource_production" class="admin-input" step="1">
                                    <?php $__errorArgs = ['factionForm.bonuses.resource_production'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="building_cost">Coût des bâtiments (%)</label>
                                    <input type="number" id="building_cost" wire:model="factionForm.bonuses.building_cost" class="admin-input" step="1">
                                    <?php $__errorArgs = ['factionForm.bonuses.building_cost'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                            
                            <div class="admin-form-grid-2">
                                <div class="admin-form-group">
                                    <label for="technology_cost">Coût des technologies (%)</label>
                                    <input type="number" id="technology_cost" wire:model="factionForm.bonuses.technology_cost" class="admin-input" step="1">
                                    <?php $__errorArgs = ['factionForm.bonuses.technology_cost'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="ship_speed">Vitesse des vaisseaux (%)</label>
                                    <input type="number" id="ship_speed" wire:model="factionForm.bonuses.ship_speed" class="admin-input" step="1">
                                    <?php $__errorArgs = ['factionForm.bonuses.ship_speed'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                            
                            <div class="admin-form-grid-2">
                                <div class="admin-form-group">
                                    <label for="attack_power">Puissance d'attaque (%)</label>
                                    <input type="number" id="attack_power" wire:model="factionForm.bonuses.attack_power" class="admin-input" step="1">
                                    <?php $__errorArgs = ['factionForm.bonuses.attack_power'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="defense_power">Puissance de défense (%)</label>
                                    <input type="number" id="defense_power" wire:model="factionForm.bonuses.defense_power" class="admin-input" step="1">
                                    <?php $__errorArgs = ['factionForm.bonuses.defense_power'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                            
                            <div class="admin-form-grid-2">
                                <div class="admin-form-group">
                                    <label for="ship_capacity">Capacité des vaisseaux (%)</label>
                                    <input type="number" id="ship_capacity" wire:model="factionForm.bonuses.ship_capacity" class="admin-input" step="1">
                                    <?php $__errorArgs = ['factionForm.bonuses.ship_capacity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="building_speed">Vitesse de construction (%)</label>
                                    <input type="number" id="building_speed" wire:model="factionForm.bonuses.building_speed" class="admin-input" step="1">
                                    <?php $__errorArgs = ['factionForm.bonuses.building_speed'];
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
                        
                        <div class="admin-form-group">
                            <label class="admin-checkbox-container">
                                <input type="checkbox" wire:model="factionForm.is_active">
                                <span class="admin-checkbox-label">Faction active</span>
                            </label>
                            <?php $__errorArgs = ['factionForm.is_active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="admin-form-actions">
                            <button type="button" class="admin-button admin-button-secondary" wire:click="setActiveTab('list')">Annuler</button>
                            <button type="submit" class="admin-button admin-button-primary">Créer la faction</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Éditer une faction -->
        <?php if($activeTab === 'edit' && $selectedFaction): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Modifier la faction: <?php echo e($selectedFaction->name); ?></h2>
                </div>
                <div class="admin-card-body">
                    <form wire:submit.prevent="updateFaction">
                        <div class="admin-form-grid-2">
                            <div class="admin-form-group">
                                <label for="edit_name">Nom de la faction</label>
                                <input type="text" id="edit_name" wire:model="factionForm.name" wire:change="generateSlug" class="admin-input" required>
                                <?php $__errorArgs = ['factionForm.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_slug">Slug</label>
                                <input type="text" id="edit_slug" wire:model="factionForm.slug" class="admin-input" required>
                                <?php $__errorArgs = ['factionForm.slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        
                        <div class="admin-form-grid-2">
                            <div class="admin-form-group">
                                <label for="edit_icon">Icône (classe FontAwesome)</label>
                                <input type="text" id="edit_icon" wire:model="factionForm.icon" class="admin-input" placeholder="fas fa-flag">
                                <?php $__errorArgs = ['factionForm.icon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_color_code">Couleur</label>
                                <input type="color" id="edit_color_code" wire:model="factionForm.color_code" class="admin-input">
                                <?php $__errorArgs = ['factionForm.color_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        
                        <div class="admin-form-grid-2">
                            <div class="admin-form-group">
                                <label for="edit_banner">Bannière (URL)</label>
                                <input type="text" id="edit_banner" wire:model="factionForm.banner" class="admin-input" placeholder="URL de l'image">
                                <?php $__errorArgs = ['factionForm.banner'];
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
                                <input type="number" id="edit_sort_order" wire:model="factionForm.sort_order" class="admin-input" min="0">
                                <?php $__errorArgs = ['factionForm.sort_order'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="edit_description">Description</label>
                            <textarea id="edit_description" wire:model="factionForm.description" class="admin-textarea" rows="4"></textarea>
                            <?php $__errorArgs = ['factionForm.description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="admin-form-group">
                            <label>Bonus de faction</label>
                            <div class="admin-form-grid-2">
                                <div class="admin-form-group">
                                    <label for="edit_resource_production">Production de ressources (%)</label>
                                    <input type="number" id="edit_resource_production" wire:model="factionForm.bonuses.resource_production" class="admin-input" step="1">
                                    <?php $__errorArgs = ['factionForm.bonuses.resource_production'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="edit_building_cost">Coût des bâtiments (%)</label>
                                    <input type="number" id="edit_building_cost" wire:model="factionForm.bonuses.building_cost" class="admin-input" step="1">
                                    <?php $__errorArgs = ['factionForm.bonuses.building_cost'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                            
                            <div class="admin-form-grid-2">
                                <div class="admin-form-group">
                                    <label for="edit_technology_cost">Coût des technologies (%)</label>
                                    <input type="number" id="edit_technology_cost" wire:model="factionForm.bonuses.technology_cost" class="admin-input" step="1">
                                    <?php $__errorArgs = ['factionForm.bonuses.technology_cost'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="edit_ship_speed">Vitesse des vaisseaux (%)</label>
                                    <input type="number" id="edit_ship_speed" wire:model="factionForm.bonuses.ship_speed" class="admin-input" step="1">
                                    <?php $__errorArgs = ['factionForm.bonuses.ship_speed'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                            
                            <div class="admin-form-grid-2">
                                <div class="admin-form-group">
                                    <label for="edit_attack_power">Puissance d'attaque (%)</label>
                                    <input type="number" id="edit_attack_power" wire:model="factionForm.bonuses.attack_power" class="admin-input" step="1">
                                    <?php $__errorArgs = ['factionForm.bonuses.attack_power'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="edit_defense_power">Puissance de défense (%)</label>
                                    <input type="number" id="edit_defense_power" wire:model="factionForm.bonuses.defense_power" class="admin-input" step="1">
                                    <?php $__errorArgs = ['factionForm.bonuses.defense_power'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                            
                            <div class="admin-form-grid-2">
                                <div class="admin-form-group">
                                    <label for="edit_ship_capacity">Capacité des vaisseaux (%)</label>
                                    <input type="number" id="edit_ship_capacity" wire:model="factionForm.bonuses.ship_capacity" class="admin-input" step="1">
                                    <?php $__errorArgs = ['factionForm.bonuses.ship_capacity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="edit_building_speed">Vitesse de construction (%)</label>
                                    <input type="number" id="edit_building_speed" wire:model="factionForm.bonuses.building_speed" class="admin-input" step="1">
                                    <?php $__errorArgs = ['factionForm.bonuses.building_speed'];
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
                        
                        <div class="admin-form-group">
                            <label class="admin-checkbox-container">
                                <input type="checkbox" wire:model="factionForm.is_active">
                                <span class="admin-checkbox-label">Faction active</span>
                            </label>
                            <?php $__errorArgs = ['factionForm.is_active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="admin-form-actions">
                            <button type="button" class="admin-button admin-button-secondary" wire:click="setActiveTab('list')">Annuler</button>
                            <button type="submit" class="admin-button admin-button-primary">Mettre à jour la faction</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/admin/factions.blade.php ENDPATH**/ ?>