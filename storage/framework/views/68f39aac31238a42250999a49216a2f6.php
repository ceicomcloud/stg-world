<div class="admin-settings">
    <div class="admin-page-header">
        <h1>Paramètres du serveur</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button <?php echo e($activeTab === 'list' ? 'active' : ''); ?>" wire:click="setActiveTab('list')">
                Liste des paramètres
            </button>
            <button class="admin-tab-button <?php echo e($activeTab === 'create' ? 'active' : ''); ?>" wire:click="setActiveTab('create')">
                Créer un paramètre
            </button>
            <?php if($selectedConfig): ?>
                <button class="admin-tab-button <?php echo e($activeTab === 'edit' ? 'active' : ''); ?>" wire:click="setActiveTab('edit')">
                Modifier le paramètre
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-card-icon">
                <i class="fas fa-cogs"></i>
            </div>
            <div class="admin-stat-card-content">
                <div class="admin-stat-value"><?php echo e($configStats['total']); ?></div>
                <div class="admin-stat-label">Paramètres au total</div>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="admin-stat-card-content">
                <div class="admin-stat-value"><?php echo e($configStats['active']); ?></div>
                <div class="admin-stat-label">Paramètres actifs</div>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-card-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="admin-stat-card-content">
                <div class="admin-stat-value"><?php echo e($configStats['inactive']); ?></div>
                <div class="admin-stat-label">Paramètres inactifs</div>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-card-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="admin-stat-card-content">
                <div class="admin-stat-value"><?php echo e(count($configCategories)); ?></div>
                <div class="admin-stat-label">Catégories</div>
            </div>
        </div>
    </div>

    <!-- Liste des paramètres -->
    <?php if($activeTab === 'list'): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Liste des paramètres</h2>
                <div class="admin-card-tools">
                    <div class="admin-search">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="admin-input">
                    </div>
                </div>
            </div>
            <div class="admin-card-body">
                <div class="admin-filters">
                    <div class="admin-filter">
                        <label for="filterCategory" class="admin-filter-label">Catégorie</label>
                        <select id="filterCategory" wire:model.live="filterCategory" class="admin-select">
                            <option value="">Toutes les catégories</option>
                            <?php $__currentLoopData = $configCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="admin-filter">
                        <label for="filterType" class="admin-filter-label">Type</label>
                        <select id="filterType" wire:model.live="filterType" class="admin-select">
                            <option value="">Tous les types</option>
                            <?php $__currentLoopData = $configTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="admin-filter">
                        <label for="filterActive" class="admin-filter-label">Statut</label>
                        <select id="filterActive" wire:model.live="filterActive" class="admin-select">
                            <option value="">Tous les statuts</option>
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                        </select>
                    </div>
                    <div class="admin-filter">
                        <label for="perPage" class="admin-filter-label">Par page</label>
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
                                <th wire:click="sortBy('key')" class="admin-table-sortable">
                                    Clé
                                    <?php if($sortField === 'key'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </th>
                                <th>Valeur</th>
                                <th wire:click="sortBy('type')" class="admin-table-sortable">
                                    Type
                                    <?php if($sortField === 'type'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </th>
                                <th wire:click="sortBy('category')" class="admin-table-sortable">
                                    Catégorie
                                    <?php if($sortField === 'category'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </th>
                                <th wire:click="sortBy('is_active')" class="admin-table-sortable">
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
                            <?php $__empty_1 = true; $__currentLoopData = $configs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $config): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($config->key); ?></td>
                                    <td>
                                        <?php if($config->type === 'boolean'): ?>
                                            <?php echo e($config->value ? 'Vrai' : 'Faux'); ?>

                                        <?php elseif($config->type === 'json'): ?>
                                            <span class="admin-badge admin-badge-info">JSON</span>
                                        <?php else: ?>
                                            <?php echo e(Str::limit($config->value, 50)); ?>

                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="admin-badge">
                                            <?php echo e($configTypes[$config->type] ?? $config->type); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <span class="admin-badge admin-badge-primary">
                                            <?php echo e($configCategories[$config->category] ?? $config->category); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <?php if($config->is_active): ?>
                                            <span class="admin-badge admin-badge-success">Actif</span>
                                        <?php else: ?>
                                            <span class="admin-badge admin-badge-danger">Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="admin-table-actions">
                                            <button class="admin-btn admin-btn-primary admin-btn-sm" wire:click="selectConfig(<?php echo e($config->id); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="admin-table-empty">Aucun paramètre trouvé</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="admin-pagination">
                    <?php echo e($configs->links()); ?>

                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Formulaire de création de paramètre -->
    <?php if($activeTab === 'create'): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Créer un nouveau paramètre</h2>
            </div>
            <div class="admin-card-body">
                <form wire:submit.prevent="createConfig" class="admin-form">
                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <label for="key" class="admin-form-label">Clé</label>
                            <input type="text" id="key" wire:model="configForm.key" class="admin-form-input" placeholder="server_name">
                            <?php $__errorArgs = ['configForm.key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="type" class="admin-form-label">Type</label>
                            <select id="type" wire:model="configForm.type" class="admin-form-select">
                                <?php $__currentLoopData = $configTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['configForm.type'];
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
                        <label for="value" class="admin-form-label">Valeur</label>
                        <?php if($configForm['type'] === 'boolean'): ?>
                            <select id="value" wire:model="configForm.value" class="admin-form-select">
                                <option value="1">Vrai</option>
                                <option value="0">Faux</option>
                            </select>
                        <?php elseif($configForm['type'] === 'json'): ?>
                            <textarea id="value" wire:model="configForm.value" class="admin-form-textarea" placeholder='{"key": "value"}'></textarea>
                        <?php else: ?>
                            <input type="text" id="value" wire:model="configForm.value" class="admin-form-input" placeholder="Valeur du paramètre">
                        <?php endif; ?>
                        <?php $__errorArgs = ['configForm.value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="admin-form-group">
                        <label for="description" class="admin-form-label">Description</label>
                        <textarea id="description" wire:model="configForm.description" class="admin-form-textarea" placeholder="Description du paramètre"></textarea>
                        <?php $__errorArgs = ['configForm.description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <label for="category" class="admin-form-label">Catégorie</label>
                            <select id="category" wire:model="configForm.category" class="admin-form-select">
                                <?php $__currentLoopData = $configCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['configForm.category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="is_active" class="admin-form-label">Statut</label>
                            <select id="is_active" wire:model="configForm.is_active" class="admin-form-select">
                                <option value="1">Actif</option>
                                <option value="0">Inactif</option>
                            </select>
                            <?php $__errorArgs = ['configForm.is_active'];
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
                        <button type="button" class="admin-btn admin-btn-outline" wire:click="resetConfigForm">Réinitialiser</button>
                        <button type="submit" class="admin-btn admin-btn-primary">Créer le paramètre</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Formulaire d'édition de paramètre -->
    <?php if($activeTab === 'edit' && $selectedConfig): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Modifier le paramètre</h2>
            </div>
            <div class="admin-card-body">
                <form wire:submit.prevent="updateConfig" class="admin-form">
                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <label for="edit_key" class="admin-form-label">Clé</label>
                            <input type="text" id="edit_key" wire:model="configForm.key" class="admin-form-input" placeholder="server_name">
                            <?php $__errorArgs = ['configForm.key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="edit_type" class="admin-form-label">Type</label>
                            <select id="edit_type" wire:model="configForm.type" class="admin-form-select">
                                <?php $__currentLoopData = $configTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['configForm.type'];
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
                        <label for="edit_value" class="admin-form-label">Valeur</label>
                        <?php if($configForm['type'] === 'boolean'): ?>
                            <select id="edit_value" wire:model="configForm.value" class="admin-form-select">
                                <option value="1">Vrai</option>
                                <option value="0">Faux</option>
                            </select>
                        <?php elseif($configForm['type'] === 'json'): ?>
                            <textarea id="edit_value" wire:model="configForm.value" class="admin-form-textarea" placeholder='{"key": "value"}'></textarea>
                        <?php else: ?>
                            <input type="text" id="edit_value" wire:model="configForm.value" class="admin-form-input" placeholder="Valeur du paramètre">
                        <?php endif; ?>
                        <?php $__errorArgs = ['configForm.value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="admin-form-group">
                        <label for="edit_description" class="admin-form-label">Description</label>
                        <textarea id="edit_description" wire:model="configForm.description" class="admin-form-textarea" placeholder="Description du paramètre"></textarea>
                        <?php $__errorArgs = ['configForm.description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <label for="edit_category" class="admin-form-label">Catégorie</label>
                            <select id="edit_category" wire:model="configForm.category" class="admin-form-select">
                                <?php $__currentLoopData = $configCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['configForm.category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="edit_is_active" class="admin-form-label">Statut</label>
                            <select id="edit_is_active" wire:model="configForm.is_active" class="admin-form-select">
                                <option value="1">Actif</option>
                                <option value="0">Inactif</option>
                            </select>
                            <?php $__errorArgs = ['configForm.is_active'];
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
                        <button type="button" class="admin-btn admin-btn-danger" wire:click="deleteConfig" wire:confirm="Êtes-vous sûr de vouloir supprimer ce paramètre ?">Supprimer</button>
                        <button type="submit" class="admin-btn admin-btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/admin/settings.blade.php ENDPATH**/ ?>