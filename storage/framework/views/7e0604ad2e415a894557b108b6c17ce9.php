<div class="admin-buildings">
    <div class="admin-page-header">
        <h1>Gestion des bâtiments</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button <?php echo e($activeTab === 'list' ? 'active' : ''); ?>" wire:click="setActiveTab('list')">
                <i class="fas fa-building"></i> Liste des bâtiments
            </button>
            <button class="admin-tab-button <?php echo e($activeTab === 'create' ? 'active' : ''); ?>" wire:click="setActiveTab('create')">
                <i class="fas fa-plus-circle"></i> Créer un bâtiment
            </button>
            <?php if($selectedBuild): ?>
                <button class="admin-tab-button <?php echo e($activeTab === 'edit' ? 'active' : ''); ?>" wire:click="setActiveTab('edit')">
                    <i class="fas fa-edit"></i> <?php echo e($selectedBuild->name); ?>

                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-content-body">

        <!-- Liste des bâtiments -->
        <?php if($activeTab === 'list'): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Liste des bâtiments</h2>
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
                                <?php $__currentLoopData = $buildTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <label for="filterCategory">Catégorie:</label>
                            <select id="filterCategory" wire:model.live="filterCategory" class="admin-select">
                                <option value="">Toutes</option>
                                <?php $__currentLoopData = $buildCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
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
                                    <th wire:click="sortBy('name')" class="admin-sortable">
                                        Nom
                                        <?php if($sortField === 'name'): ?>
                                            <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th wire:click="sortBy('label')" class="admin-sortable">
                                        Label
                                        <?php if($sortField === 'label'): ?>
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
                                    <th wire:click="sortBy('category')" class="admin-sortable">
                                        Catégorie
                                        <?php if($sortField === 'category'): ?>
                                            <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th wire:click="sortBy('max_level')" class="admin-sortable">
                                        Niveau Max
                                        <?php if($sortField === 'max_level'): ?>
                                            <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </th>
                                    <th>Utilisation</th>
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
                                <?php $__currentLoopData = $builds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $build): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($build->id); ?></td>
                                        <td><?php echo e($build->name); ?></td>
                                        <td><?php echo e($build->label); ?></td>
                                        <td><?php echo e($buildTypes[$build->type] ?? $build->type); ?></td>
                                        <td><?php echo e($buildCategories[$build->category] ?? $build->category); ?></td>
                                        <td><?php echo e($build->max_level); ?></td>
                                        <td><?php echo e($planetCounts[$build->id] ?? 0); ?></td>
                                        <td>
                                            <?php if($build->is_active): ?>
                                                <span class="admin-badge admin-badge-success">Actif</span>
                                            <?php else: ?>
                                                <span class="admin-badge admin-badge-danger">Inactif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="selectBuild(<?php echo e($build->id); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                
                                <?php if($builds->count() === 0): ?>
                                    <tr>
                                        <td colspan="9" class="admin-table-empty">Aucun bâtiment trouvé</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="admin-pagination">
                        <?php echo e($builds->links()); ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>
                
        <!-- Créer un bâtiment -->
        <?php if($activeTab === 'create'): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Créer un bâtiment</h2>
                </div>
                <div class="admin-card-body">
                    <form wire:submit.prevent="createBuild">
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="name">Nom <span class="admin-required">*</span></label>
                                <input type="text" id="name" wire:model="buildForm.name" class="admin-input">
                                <?php $__errorArgs = ['buildForm.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="admin-form-group">
                                <label for="label">Label <span class="admin-required">*</span></label>
                                <input type="text" id="label" wire:model="buildForm.label" class="admin-input">
                                <?php $__errorArgs = ['buildForm.label'];
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
                                <label for="type">Type <span class="admin-required">*</span></label>
                                <select id="type" wire:model="buildForm.type" class="admin-select">
                                    <option value="">Sélectionner un type</option>
                                    <?php $__currentLoopData = $buildTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['buildForm.type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="admin-form-group">
                                <label for="category">Catégorie <span class="admin-required">*</span></label>
                                <select id="category" wire:model="buildForm.category" class="admin-select">
                                    <option value="">Sélectionner une catégorie</option>
                                    <?php $__currentLoopData = $buildCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['buildForm.category'];
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
                                <label for="icon">Icône</label>
                                <input type="text" id="icon" wire:model="buildForm.icon" class="admin-input">
                                <?php $__errorArgs = ['buildForm.icon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <?php if($buildForm['icon']): ?>
                                    <div class="admin-icon-preview">
                                        <i class="<?php echo e($buildForm['icon']); ?> fa-2x"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="admin-form-group">
                                <label for="max_level">Niveau Max <span class="admin-required">*</span></label>
                                <input type="number" id="max_level" wire:model="buildForm.max_level" min="1" class="admin-input">
                                <?php $__errorArgs = ['buildForm.max_level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="admin-form-group">
                                <label for="base_build_time">Temps de construction de base <span class="admin-required">*</span></label>
                                <input type="number" id="base_build_time" wire:model="buildForm.base_build_time" min="0" class="admin-input">
                                <?php $__errorArgs = ['buildForm.base_build_time'];
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
                            <div class="admin-switch">
                                <input type="checkbox" id="is_active" wire:model="buildForm.is_active">
                                <label for="is_active">Actif</label>
                            </div>
                            <?php $__errorArgs = ['buildForm.is_active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="description">Description</label>
                            <textarea id="description" wire:model="buildForm.description" rows="3" class="admin-textarea"></textarea>
                            <?php $__errorArgs = ['buildForm.description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="admin-form-actions">
                            <button type="button" class="admin-btn admin-btn-secondary" wire:click="setActiveTab('list')">Annuler</button>
                            <button type="submit" class="admin-btn admin-btn-primary">Créer</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
                
                <!-- Éditer un bâtiment -->
                <div class="tab-pane fade <?php echo e($activeTab === 'edit' && $selectedBuild ? 'show active' : ''); ?>">
                    <?php if($selectedBuild): ?>
                        <div x-data="{ activeTab: 'details' }">
                            <div class="admin-tabs">
                                <ul class="admin-tabs-nav" role="tablist">
                                    <li class="admin-tabs-item" role="presentation">
                                        <button class="admin-tabs-link" :class="{ 'active': activeTab === 'details' }" @click="activeTab = 'details'" type="button">
                                            Détails
                                        </button>
                                    </li>
                                    <li class="admin-tabs-item" role="presentation">
                                        <button class="admin-tabs-link" :class="{ 'active': activeTab === 'costs' }" @click="activeTab = 'costs'" type="button">
                                            Coûts
                                        </button>
                                    </li>
                                    <li class="admin-tabs-item" role="presentation">
                                        <button class="admin-tabs-link" :class="{ 'active': activeTab === 'requirements' }" @click="activeTab = 'requirements'" type="button">
                                            Prérequis
                                        </button>
                                    </li>
                                    <li class="admin-tabs-item" role="presentation">
                                        <button class="admin-tabs-link" :class="{ 'active': activeTab === 'advantages' }" @click="activeTab = 'advantages'" type="button">
                                            Avantages
                                        </button>
                                    </li>
                                    <li class="admin-tabs-item" role="presentation">
                                        <button class="admin-tabs-link" :class="{ 'active': activeTab === 'disadvantages' }" @click="activeTab = 'disadvantages'" type="button">
                                            Désavantages
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="tab-content">
                            <!-- Détails du bâtiment -->
                            <div x-show="activeTab === 'details'">
                                <div class="admin-card">
                                    <div class="admin-card-header">
                                        <h2>Détails du bâtiment</h2>
                                    </div>
                                    <div class="admin-card-body">
                                        <form wire:submit.prevent="updateBuild">
                                            <div class="admin-form-row">
                                                <div class="admin-form-group">
                                                    <label for="edit_name">Nom <span class="admin-required">*</span></label>
                                                    <input type="text" id="edit_name" wire:model="buildForm.name" class="admin-input">
                                                    <?php $__errorArgs = ['buildForm.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="edit_label">Label <span class="admin-required">*</span></label>
                                                    <input type="text" id="edit_label" wire:model="buildForm.label" class="admin-input">
                                                    <?php $__errorArgs = ['buildForm.label'];
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
                                                    <label for="edit_type">Type <span class="admin-required">*</span></label>
                                                    <select id="edit_type" wire:model="buildForm.type" class="admin-select">
                                                        <option value="">Sélectionner un type</option>
                                                        <?php $__currentLoopData = $buildTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                    <?php $__errorArgs = ['buildForm.type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="edit_category">Catégorie <span class="admin-required">*</span></label>
                                                    <select id="edit_category" wire:model="buildForm.category" class="admin-select">
                                                        <option value="">Sélectionner une catégorie</option>
                                                        <?php $__currentLoopData = $buildCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                    <?php $__errorArgs = ['buildForm.category'];
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
                                                    <label for="edit_icon">Icône</label>
                                                    <input type="text" id="edit_icon" wire:model="buildForm.icon" class="admin-input">
                                                    <?php $__errorArgs = ['buildForm.icon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                    <?php if($buildForm['icon']): ?>
                                                        <div class="admin-icon-preview">
                                                            <i class="<?php echo e($buildForm['icon']); ?> fa-2x"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="edit_max_level">Niveau Max <span class="admin-required">*</span></label>
                                                    <input type="number" id="edit_max_level" wire:model="buildForm.max_level" min="1" class="admin-input">
                                                    <?php $__errorArgs = ['buildForm.max_level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="edit_base_build_time">Temps de construction de base <span class="admin-required">*</span></label>
                                                    <input type="number" id="edit_base_build_time" wire:model="buildForm.base_build_time" min="0" class="admin-input">
                                                    <?php $__errorArgs = ['buildForm.base_build_time'];
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
                                                <div class="admin-switch">
                                                    <input type="checkbox" id="edit_is_active" wire:model="buildForm.is_active">
                                                    <label for="edit_is_active">Actif</label>
                                                </div>
                                                <?php $__errorArgs = ['buildForm.is_active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                            
                                            <div class="admin-form-group">
                                                <label for="edit_description">Description</label>
                                                <textarea id="edit_description" wire:model="buildForm.description" rows="3" class="admin-textarea"></textarea>
                                                <?php $__errorArgs = ['buildForm.description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                            
                                            <div class="admin-form-actions">
                                                <button type="button" class="admin-btn admin-btn-secondary" wire:click="setActiveTab('list')">Retour à la liste</button>
                                                <button type="submit" class="admin-btn admin-btn-primary">Mettre à jour</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Coûts du bâtiment -->
                            <div x-show="activeTab === 'costs'">
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="admin-card">
                                            <div class="admin-card-header">
                                                <h2>Ajouter un coût</h2>
                                            </div>
                                            <div class="admin-card-body">
                                                <form wire:submit.prevent="addCost">
                                                    <div class="admin-form-row">
                                                        <div class="admin-form-group">
                                                            <label for="cost_resource_id">Ressource <span class="admin-required">*</span></label>
                                                            <select class="admin-select" id="cost_resource_id" wire:model="newCost.resource_id">
                                                                <option value="">Sélectionner une ressource</option>
                                                                <?php $__currentLoopData = $resources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <option value="<?php echo e($resource->id); ?>"><?php echo e($resource->label); ?></option>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </select>
                                                            <?php $__errorArgs = ['newCost.resource_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                        </div>
                                                        <div class="admin-form-group">
                                                            <label for="cost_base_cost">Coût de base <span class="admin-required">*</span></label>
                                                            <input type="number" class="admin-input" id="cost_base_cost" wire:model="newCost.base_cost" min="0">
                                                            <?php $__errorArgs = ['newCost.base_cost'];
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
                                                            <label for="cost_multiplier">Multiplicateur <span class="admin-required">*</span></label>
                                                            <input type="number" class="admin-input" id="cost_multiplier" wire:model="newCost.cost_multiplier" min="1" step="0.1">
                                                            <?php $__errorArgs = ['newCost.cost_multiplier'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                        </div>
                                                        <div class="admin-form-group">
                                                            <label for="cost_level">Niveau <span class="admin-required">*</span></label>
                                                            <input type="number" class="admin-input" id="cost_level" wire:model="newCost.level" min="1">
                                                            <?php $__errorArgs = ['newCost.level'];
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
                                                        <button type="submit" class="admin-btn admin-btn-primary">Ajouter</button>
                                                    </div>
                                                </form>
                                            
                                                <br />
                                
                                                <div class="admin-table-container">
                                                    <table class="admin-table">
                                                        <thead>
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>Ressource</th>
                                                                <th>Coût de base</th>
                                                                <th>Multiplicateur</th>
                                                                <th>Niveau</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php $__empty_1 = true; $__currentLoopData = $costs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cost): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                <tr>
                                                                    <td><?php echo e($cost['id']); ?></td>
                                                                    <td>
                                                                        <?php
                                                                            $resource = $resources->firstWhere('id', $cost['resource_id']);
                                                                        ?>
                                                                        <?php echo e($resource ? $resource->label : 'Ressource inconnue'); ?>

                                                                    </td>
                                                                    <td><?php echo e($cost['base_cost']); ?></td>
                                                                    <td><?php echo e($cost['cost_multiplier']); ?></td>
                                                                    <td><?php echo e($cost['level']); ?></td>
                                                                    <td>
                                                                        <button class="admin-btn admin-btn-danger admin-btn-sm" wire:click="deleteCost(<?php echo e($cost['id']); ?>)" wire:confirm="Êtes-vous sûr de vouloir supprimer ce coût ?">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                                <tr>
                                                                    <td colspan="6" class="admin-table-empty">Aucun coût défini</td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Prérequis du bâtiment -->
                            <div x-show="activeTab === 'requirements'">
                                <div class="admin-card">
                                    <div class="admin-card-header">
                                        <h2>Ajouter un prérequis</h2>
                                    </div>
                                    <div class="admin-card-body">
                                        <form wire:submit.prevent="addRequirement">
                                            <div class="admin-form-row">
                                                <div class="admin-form-group">
                                                    <label for="required_build_id">Bâtiment requis <span class="admin-required">*</span></label>
                                                    <select class="admin-select" id="required_build_id" wire:model="newRequirement.required_build_id">
                                                        <option value="">Sélectionner un bâtiment</option>
                                                        <?php $__currentLoopData = $availableBuilds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $build): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($build->id); ?>"><?php echo e($build->label); ?> (<?php echo e($buildTypes[$build->type] ?? $build->type); ?>)</option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                    <?php $__errorArgs = ['newRequirement.required_build_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="required_level">Niveau requis <span class="admin-required">*</span></label>
                                                    <input type="number" class="admin-input" id="required_level" wire:model="newRequirement.required_level" min="1">
                                                    <?php $__errorArgs = ['newRequirement.required_level'];
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
                                                <div class="admin-form-group admin-form-checkbox-group">
                                                    <div class="admin-checkbox-container">
                                                        <input type="checkbox" id="requirement_is_active" wire:model="newRequirement.is_active" class="admin-checkbox">
                                                        <label for="requirement_is_active">Actif</label>
                                                    </div>
                                                    <?php $__errorArgs = ['newRequirement.is_active'];
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
                                                <button type="submit" class="admin-btn admin-btn-primary">Ajouter</button>
                                            </div>
                                        </form>

                                        <br />
                                
                                        <div class="admin-table-container">
                                            <table class="admin-table">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Bâtiment requis</th>
                                                        <th>Niveau requis</th>
                                                        <th>Statut</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__empty_1 = true; $__currentLoopData = $requirements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $requirement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                        <tr>
                                                            <td><?php echo e($requirement['id']); ?></td>
                                                            <td>
                                                                <?php
                                                                    $requiredBuild = $availableBuilds->firstWhere('id', $requirement['required_build_id']);
                                                                ?>
                                                                <?php echo e($requiredBuild ? $requiredBuild->label : 'Bâtiment inconnu'); ?>

                                                                <?php if($requiredBuild): ?>
                                                                    (<?php echo e($buildTypes[$requiredBuild->type] ?? $requiredBuild->type); ?>)
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo e($requirement['required_level']); ?></td>
                                                            <td>
                                                                <span class="admin-badge <?php echo e($requirement['is_active'] ? 'admin-badge-success' : 'admin-badge-danger'); ?>">
                                                                    <?php echo e($requirement['is_active'] ? 'Actif' : 'Inactif'); ?>

                                                                </span>
                                                            </td>
                                                            <td>
                                                                <button class="admin-btn admin-btn-danger admin-btn-sm" wire:click="deleteRequirement(<?php echo e($requirement['id']); ?>)" wire:confirm="Êtes-vous sûr de vouloir supprimer ce prérequis ?">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                        <tr>
                                                            <td colspan="5" class="admin-table-empty">Aucun prérequis défini</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Avantages du bâtiment -->
                            <div x-show="activeTab === 'advantages'">
                                <div class="admin-card">
                                    <div class="admin-card-header">
                                        <h2>Ajouter un avantage</h2>
                                    </div>
                                    <div class="admin-card-body">
                                        <form wire:submit.prevent="addAdvantage">
                                            <div class="admin-form-row">
                                                <div class="admin-form-group">
                                                    <label for="advantage_type">Type d'avantage <span class="admin-required">*</span></label>
                                                    <select class="admin-select" id="advantage_type" wire:model="newAdvantage.advantage_type">
                                                        <option value="">Sélectionner un type</option>
                                                        <?php $__currentLoopData = $advantageTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                    <?php $__errorArgs = ['newAdvantage.advantage_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="advantage_target_type">Type de cible <span class="admin-required">*</span></label>
                                                    <select class="admin-select" id="advantage_target_type" wire:model="newAdvantage.target_type">
                                                        <option value="">Sélectionner un type de cible</option>
                                                        <?php $__currentLoopData = $targetTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                    <?php $__errorArgs = ['newAdvantage.target_type'];
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
                                                    <label for="advantage_resource_id">Ressource (optionnel)</label>
                                                    <select class="admin-select" id="advantage_resource_id" wire:model="newAdvantage.resource_id">
                                                        <option value="">Aucune ressource spécifique</option>
                                                        <?php $__currentLoopData = $resources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($resource->id); ?>"><?php echo e($resource->label); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                    <?php $__errorArgs = ['newAdvantage.resource_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="advantage_calculation_type">Type de calcul <span class="admin-required">*</span></label>
                                                    <select class="admin-select" id="advantage_calculation_type" wire:model="newAdvantage.calculation_type">
                                                        <option value="">Sélectionner un type de calcul</option>
                                                        <?php $__currentLoopData = $calculationTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                    <?php $__errorArgs = ['newAdvantage.calculation_type'];
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
                                                    <label for="advantage_base_value">Valeur de base <span class="admin-required">*</span></label>
                                                    <input type="number" class="admin-input" id="advantage_base_value" wire:model="newAdvantage.base_value" min="0" step="0.01">
                                                    <?php $__errorArgs = ['newAdvantage.base_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="advantage_value_per_level">Valeur par niveau <span class="admin-required">*</span></label>
                                                    <input type="number" class="admin-input" id="advantage_value_per_level" wire:model="newAdvantage.value_per_level" min="0" step="0.01">
                                                    <?php $__errorArgs = ['newAdvantage.value_per_level'];
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
                                                <div class="admin-form-group admin-form-checkbox-group">
                                                    <div class="admin-checkbox-container">
                                                        <input type="checkbox" id="advantage_is_percentage" wire:model="newAdvantage.is_percentage" class="admin-checkbox">
                                                        <label for="advantage_is_percentage">Est un pourcentage</label>
                                                    </div>
                                                    <?php $__errorArgs = ['newAdvantage.is_percentage'];
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
                                                        <input type="checkbox" id="advantage_is_active" wire:model="newAdvantage.is_active" class="admin-checkbox">
                                                        <label for="advantage_is_active">Actif</label>
                                                    </div>
                                                    <?php $__errorArgs = ['newAdvantage.is_active'];
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
                                                <button type="submit" class="admin-btn admin-btn-primary">Ajouter</button>
                                            </div>
                                        </form>

                                        <br />
                                        <div class="admin-table-container">
                                            <table class="admin-table">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Type</th>
                                                        <th>Cible</th>
                                                        <th>Ressource</th>
                                                        <th>Valeur de base</th>
                                                        <th>Valeur par niveau</th>
                                                        <th>Type de calcul</th>
                                                        <th>Pourcentage</th>
                                                        <th>Statut</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__empty_1 = true; $__currentLoopData = $advantages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $advantage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                        <tr>
                                                            <td><?php echo e($advantage['id']); ?></td>
                                                            <td><?php echo e($advantageTypes[$advantage['advantage_type']] ?? $advantage['advantage_type']); ?></td>
                                                            <td><?php echo e($targetTypes[$advantage['target_type']] ?? $advantage['target_type']); ?></td>
                                                            <td>
                                                                <?php if($advantage['resource_id']): ?>
                                                                    <?php
                                                                        $resource = $resources->firstWhere('id', $advantage['resource_id']);
                                                                    ?>
                                                                    <?php echo e($resource ? $resource->label : 'Ressource inconnue'); ?>

                                                                <?php else: ?>
                                                                    -
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo e($advantage['base_value']); ?><?php echo e($advantage['is_percentage'] ? '%' : ''); ?></td>
                                                            <td><?php echo e($advantage['value_per_level']); ?><?php echo e($advantage['is_percentage'] ? '%' : ''); ?></td>
                                                            <td><?php echo e($calculationTypes[$advantage['calculation_type']] ?? $advantage['calculation_type']); ?></td>
                                                            <td>
                                                                <span class="admin-badge <?php echo e($advantage['is_percentage'] ? 'admin-badge-info' : 'admin-badge-secondary'); ?>">
                                                                    <?php echo e($advantage['is_percentage'] ? 'Oui' : 'Non'); ?>

                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="admin-badge <?php echo e($advantage['is_active'] ? 'admin-badge-success' : 'admin-badge-danger'); ?>">
                                                                    <?php echo e($advantage['is_active'] ? 'Actif' : 'Inactif'); ?>

                                                                </span>
                                                            </td>
                                                            <td>
                                                                <button class="admin-btn admin-btn-danger admin-btn-sm" wire:click="deleteAdvantage(<?php echo e($advantage['id']); ?>)" wire:confirm="Êtes-vous sûr de vouloir supprimer cet avantage ?">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                        <tr>
                                                            <td colspan="10" class="admin-table-empty">Aucun avantage défini</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Désavantages du bâtiment -->
                            <div x-show="activeTab === 'disadvantages'">
                                <div class="admin-card">
                                    <div class="admin-card-header">
                                        <h2>Ajouter un désavantage</h2>
                                    </div>
                                    <div class="admin-card-body">
                                        <form wire:submit.prevent="addDisadvantage">
                                            <div class="admin-form-row">
                                                <div class="admin-form-group">
                                                    <label for="disadvantage_type">Type de désavantage <span class="admin-required">*</span></label>
                                                    <select class="admin-select" id="disadvantage_type" wire:model="newDisadvantage.disadvantage_type">
                                                        <option value="">Sélectionner un type</option>
                                                        <?php $__currentLoopData = $disadvantageTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                    <?php $__errorArgs = ['newDisadvantage.disadvantage_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="disadvantage_target_type">Type de cible <span class="admin-required">*</span></label>
                                                    <select class="admin-select" id="disadvantage_target_type" wire:model="newDisadvantage.target_type">
                                                        <option value="">Sélectionner un type de cible</option>
                                                        <?php $__currentLoopData = $targetTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                                <?php $__errorArgs = ['newDisadvantage.target_type'];
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
                                                    <label for="disadvantage_resource_id">Ressource (optionnel)</label>
                                                    <select class="admin-select" id="disadvantage_resource_id" wire:model="newDisadvantage.resource_id">
                                                        <option value="">Aucune ressource spécifique</option>
                                                        <?php $__currentLoopData = $resources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($resource->id); ?>"><?php echo e($resource->label); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                    <?php $__errorArgs = ['newDisadvantage.resource_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="disadvantage_calculation_type">Type de calcul <span class="admin-required">*</span></label>
                                                    <select class="admin-select" id="disadvantage_calculation_type" wire:model="newDisadvantage.calculation_type">
                                                        <option value="">Sélectionner un type de calcul</option>
                                                        <?php $__currentLoopData = $calculationTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                    <?php $__errorArgs = ['newDisadvantage.calculation_type'];
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
                                                    <label for="disadvantage_base_value">Valeur de base <span class="admin-required">*</span></label>
                                                    <input type="number" class="admin-input" id="disadvantage_base_value" wire:model="newDisadvantage.base_value" min="0" step="0.01">
                                                    <?php $__errorArgs = ['newDisadvantage.base_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="disadvantage_value_per_level">Valeur par niveau <span class="admin-required">*</span></label>
                                                    <input type="number" class="admin-input" id="disadvantage_value_per_level" wire:model="newDisadvantage.value_per_level" min="0" step="0.01">
                                                    <?php $__errorArgs = ['newDisadvantage.value_per_level'];
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
                                                <div class="admin-form-group admin-form-checkbox-group">
                                                    <div class="admin-checkbox-container">
                                                        <input type="checkbox" id="disadvantage_is_percentage" wire:model="newDisadvantage.is_percentage" class="admin-checkbox">
                                                        <label for="disadvantage_is_percentage">Est un pourcentage</label>
                                                    </div>
                                                    <?php $__errorArgs = ['newDisadvantage.is_percentage'];
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
                                                        <input type="checkbox" id="disadvantage_is_active" wire:model="newDisadvantage.is_active" class="admin-checkbox">
                                                        <label for="disadvantage_is_active">Actif</label>
                                                    </div>
                                                    <?php $__errorArgs = ['newDisadvantage.is_active'];
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
                                                <button type="submit" class="admin-btn admin-btn-primary">Ajouter</button>
                                            </div>
                                        </form>

                                        <br />
                                
                                        <div class="admin-table-container">
                                            <table class="admin-table">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Type</th>
                                                        <th>Cible</th>
                                                        <th>Ressource</th>
                                                        <th>Valeur de base</th>
                                                        <th>Valeur par niveau</th>
                                                        <th>Type de calcul</th>
                                                        <th>Pourcentage</th>
                                                        <th>Statut</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__empty_1 = true; $__currentLoopData = $disadvantages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $disadvantage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                        <tr>
                                                            <td><?php echo e($disadvantage['id']); ?></td>
                                                            <td><?php echo e($disadvantageTypes[$disadvantage['disadvantage_type']] ?? $disadvantage['disadvantage_type']); ?></td>
                                                            <td><?php echo e($targetTypes[$disadvantage['target_type']] ?? $disadvantage['target_type']); ?></td>
                                                            <td>
                                                                <?php if($disadvantage['resource_id']): ?>
                                                                    <?php
                                                                        $resource = $resources->firstWhere('id', $disadvantage['resource_id']);
                                                                    ?>
                                                                    <?php echo e($resource ? $resource->label : 'Ressource inconnue'); ?>

                                                                <?php else: ?>
                                                                    -
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo e($disadvantage['base_value']); ?><?php echo e($disadvantage['is_percentage'] ? '%' : ''); ?></td>
                                                            <td><?php echo e($disadvantage['value_per_level']); ?><?php echo e($disadvantage['is_percentage'] ? '%' : ''); ?></td>
                                                            <td><?php echo e($calculationTypes[$disadvantage['calculation_type']] ?? $disadvantage['calculation_type']); ?></td>
                                                            <td>
                                                                <span class="admin-badge <?php echo e($disadvantage['is_percentage'] ? 'admin-badge-info' : 'admin-badge-secondary'); ?>">
                                                                    <?php echo e($disadvantage['is_percentage'] ? 'Oui' : 'Non'); ?>

                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="admin-badge <?php echo e($disadvantage['is_active'] ? 'admin-badge-success' : 'admin-badge-danger'); ?>">
                                                                    <?php echo e($disadvantage['is_active'] ? 'Actif' : 'Inactif'); ?>

                                                                </span>
                                                            </td>
                                                            <td>
                                                                <button class="admin-btn admin-btn-sm admin-btn-danger" wire:click="deleteDisadvantage(<?php echo e($disadvantage['id']); ?>)" wire:confirm="Êtes-vous sûr de vouloir supprimer ce désavantage ?">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                        <tr>
                                                            <td colspan="10" class="admin-table-empty">Aucun désavantage défini</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/admin/template/buildings.blade.php ENDPATH**/ ?>