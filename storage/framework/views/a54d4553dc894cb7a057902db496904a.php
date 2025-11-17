<div class="admin-news">
    <div class="admin-page-header">
        <h1>Gestion des actualités</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button <?php echo e($activeTab === 'list' ? 'active' : ''); ?>" wire:click="setActiveTab('list')">
                Liste des actualités
            </button>
            <button class="admin-tab-button <?php echo e($activeTab === 'create' ? 'active' : ''); ?>" wire:click="setActiveTab('create')">
                Créer une actualité
            </button>
            <?php if($selectedNews): ?>
                <button class="admin-tab-button <?php echo e($activeTab === 'edit' ? 'active' : ''); ?>" wire:click="setActiveTab('edit')">
                Modifier l'actualité
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Statistiques des actualités -->
    <?php if($activeTab === 'list'): ?>
    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-card-icon">
                <i class="fas fa-newspaper"></i>
            </div>
            <div class="admin-stat-card-content">
                <div class="admin-stat-value"><?php echo e($newsStats['total']); ?></div>
                <div class="admin-stat-label">Actualités au total</div>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="admin-stat-card-content">
                <div class="admin-stat-value"><?php echo e($newsStats['published']); ?></div>
                <div class="admin-stat-label">Actualités publiées</div>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-card-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="admin-stat-card-content">
                <div class="admin-stat-value"><?php echo e($newsStats['scheduled']); ?></div>
                <div class="admin-stat-label">Actualités programmées</div>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-card-icon">
                <i class="fas fa-thumbtack"></i>
            </div>
            <div class="admin-stat-card-content">
                <div class="admin-stat-value"><?php echo e($newsStats['pinned']); ?></div>
                <div class="admin-stat-label">Actualités épinglées</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Liste des actualités -->
    <?php if($activeTab === 'list'): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Liste des actualités</h2>
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
                            <?php $__currentLoopData = $newsCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="admin-filter">
                        <label for="filterPriority" class="admin-filter-label">Priorité</label>
                        <select id="filterPriority" wire:model.live="filterPriority" class="admin-select">
                            <option value="">Toutes les priorités</option>
                            <?php $__currentLoopData = $newsPriorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="admin-filter">
                        <label for="filterStatus" class="admin-filter-label">Statut</label>
                        <select id="filterStatus" wire:model.live="filterStatus" class="admin-select">
                            <option value="">Tous les statuts</option>
                            <option value="published">Publiées</option>
                            <option value="draft">Brouillons</option>
                            <option value="scheduled">Programmées</option>
                            <option value="expired">Expirées</option>
                            <option value="active">Actives</option>
                            <option value="inactive">Inactives</option>
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
                                <th wire:click="sortBy('title')" class="admin-table-sortable">
                                    Titre
                                    <?php if($sortField === 'title'): ?>
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
                                <th wire:click="sortBy('priority')" class="admin-table-sortable">
                                    Priorité
                                    <?php if($sortField === 'priority'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </th>
                                <th wire:click="sortBy('published_at')" class="admin-table-sortable">
                                    Date de publication
                                    <?php if($sortField === 'published_at'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $news; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <div class="admin-news-title">
                                            <?php echo e($item->title); ?>

                                            <?php if($item->is_pinned): ?>
                                                <i class="fas fa-thumbtack text-primary ml-2" title="Épinglée"></i>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="admin-badge admin-badge-primary">
                                            <?php echo e($newsCategories[$item->category] ?? $item->category); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <span class="admin-badge" style="background-color: <?php echo e($item->getPriorityColor()); ?>">
                                            <?php echo e($newsPriorities[$item->priority] ?? $item->priority); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <?php if($item->published_at): ?>
                                            <?php echo e($item->published_at->format('d/m/Y H:i')); ?>

                                        <?php else: ?>
                                            <span class="admin-badge admin-badge-secondary">Non publiée</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(!$item->is_active): ?>
                                            <span class="admin-badge admin-badge-danger">Inactive</span>
                                        <?php elseif($item->isScheduled()): ?>
                                            <span class="admin-badge admin-badge-warning">Programmée</span>
                                        <?php elseif($item->isExpired()): ?>
                                            <span class="admin-badge admin-badge-danger">Expirée</span>
                                        <?php elseif($item->is_published): ?>
                                            <span class="admin-badge admin-badge-success">Publiée</span>
                                        <?php else: ?>
                                            <span class="admin-badge admin-badge-secondary">Brouillon</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="admin-table-actions">
                                            <button class="admin-btn admin-btn-primary admin-btn-sm" wire:click="selectNews(<?php echo e($item->id); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="admin-table-empty">Aucune actualité trouvée</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="admin-pagination">
                    <?php echo e($news->links()); ?>

                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Formulaire de création d'actualité -->
    <?php if($activeTab === 'create'): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Créer une nouvelle actualité</h2>
            </div>
            <div class="admin-card-body">
                <form wire:submit.prevent="createNews" class="admin-form">
                    <div class="admin-form-group">
                        <label for="title" class="admin-form-label">Titre</label>
                        <input type="text" id="title" wire:model="newsForm.title" class="admin-form-input" placeholder="Titre de l'actualité">
                        <?php $__errorArgs = ['newsForm.title'];
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
                            <select id="category" wire:model="newsForm.category" class="admin-form-select">
                                <?php $__currentLoopData = $newsCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['newsForm.category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="priority" class="admin-form-label">Priorité</label>
                            <select id="priority" wire:model="newsForm.priority" class="admin-form-select">
                                <?php $__currentLoopData = $newsPriorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['newsForm.priority'];
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
                        <label for="content" class="admin-form-label">Contenu</label>
                        <textarea id="content" wire:model="newsForm.content" class="admin-form-textarea" rows="10" placeholder="Contenu de l'actualité"></textarea>
                        <?php $__errorArgs = ['newsForm.content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="admin-form-group">
                        <label for="excerpt" class="admin-form-label">Extrait (optionnel)</label>
                        <textarea id="excerpt" wire:model="newsForm.excerpt" class="admin-form-textarea" rows="3" placeholder="Extrait court de l'actualité"></textarea>
                        <?php $__errorArgs = ['newsForm.excerpt'];
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
                            <label for="image_url" class="admin-form-label">URL de l'image (optionnel)</label>
                            <input type="text" id="image_url" wire:model="newsForm.image_url" class="admin-form-input" placeholder="https://exemple.com/image.jpg">
                            <?php $__errorArgs = ['newsForm.image_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="external_url" class="admin-form-label">URL externe (optionnel)</label>
                            <input type="text" id="external_url" wire:model="newsForm.external_url" class="admin-form-input" placeholder="https://exemple.com">
                            <?php $__errorArgs = ['newsForm.external_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <label for="published_at" class="admin-form-label">Date de publication</label>
                            <input type="datetime-local" id="published_at" wire:model="newsForm.published_at" class="admin-form-input">
                            <?php $__errorArgs = ['newsForm.published_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="expires_at" class="admin-form-label">Date d'expiration (optionnel)</label>
                            <input type="datetime-local" id="expires_at" wire:model="newsForm.expires_at" class="admin-form-input">
                            <?php $__errorArgs = ['newsForm.expires_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <div class="admin-form-checkbox-group">
                                <input type="checkbox" id="is_published" wire:model="newsForm.is_published" class="admin-form-checkbox">
                                <label for="is_published" class="admin-form-checkbox-label">Publier</label>
                            </div>
                            <?php $__errorArgs = ['newsForm.is_published'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="admin-form-group">
                            <div class="admin-form-checkbox-group">
                                <input type="checkbox" id="is_pinned" wire:model="newsForm.is_pinned" class="admin-form-checkbox">
                                <label for="is_pinned" class="admin-form-checkbox-label">Épingler</label>
                            </div>
                            <?php $__errorArgs = ['newsForm.is_pinned'];
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
                        <div class="admin-form-checkbox-group">
                            <input type="checkbox" id="is_active" wire:model="newsForm.is_active" class="admin-form-checkbox">
                            <label for="is_active" class="admin-form-checkbox-label">Actif</label>
                        </div>
                        <?php $__errorArgs = ['newsForm.is_active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="admin-form-actions">
                        <button type="button" class="admin-btn admin-btn-outline" wire:click="resetNewsForm">Réinitialiser</button>
                        <button type="button" class="admin-btn admin-btn-secondary" wire:click="publishNow">Publier maintenant</button>
                        <button type="submit" class="admin-btn admin-btn-primary">Créer l'actualité</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Formulaire d'édition d'actualité -->
    <?php if($activeTab === 'edit' && $selectedNews): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Modifier l'actualité</h2>
            </div>
            <div class="admin-card-body">
                <form wire:submit.prevent="updateNews" class="admin-form">
                    <div class="admin-form-group">
                        <label for="edit_title" class="admin-form-label">Titre</label>
                        <input type="text" id="edit_title" wire:model="newsForm.title" class="admin-form-input" placeholder="Titre de l'actualité">
                        <?php $__errorArgs = ['newsForm.title'];
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
                            <select id="edit_category" wire:model="newsForm.category" class="admin-form-select">
                                <?php $__currentLoopData = $newsCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['newsForm.category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="edit_priority" class="admin-form-label">Priorité</label>
                            <select id="edit_priority" wire:model="newsForm.priority" class="admin-form-select">
                                <?php $__currentLoopData = $newsPriorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['newsForm.priority'];
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
                        <label for="edit_content" class="admin-form-label">Contenu</label>
                        <textarea id="edit_content" wire:model="newsForm.content" class="admin-form-textarea" rows="10" placeholder="Contenu de l'actualité"></textarea>
                        <?php $__errorArgs = ['newsForm.content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="admin-form-group">
                        <label for="edit_excerpt" class="admin-form-label">Extrait (optionnel)</label>
                        <textarea id="edit_excerpt" wire:model="newsForm.excerpt" class="admin-form-textarea" rows="3" placeholder="Extrait court de l'actualité"></textarea>
                        <?php $__errorArgs = ['newsForm.excerpt'];
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
                            <label for="edit_image_url" class="admin-form-label">URL de l'image (optionnel)</label>
                            <input type="text" id="edit_image_url" wire:model="newsForm.image_url" class="admin-form-input" placeholder="https://exemple.com/image.jpg">
                            <?php $__errorArgs = ['newsForm.image_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="edit_external_url" class="admin-form-label">URL externe (optionnel)</label>
                            <input type="text" id="edit_external_url" wire:model="newsForm.external_url" class="admin-form-input" placeholder="https://exemple.com">
                            <?php $__errorArgs = ['newsForm.external_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <label for="edit_published_at" class="admin-form-label">Date de publication</label>
                            <input type="datetime-local" id="edit_published_at" wire:model="newsForm.published_at" class="admin-form-input">
                            <?php $__errorArgs = ['newsForm.published_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="edit_expires_at" class="admin-form-label">Date d'expiration (optionnel)</label>
                            <input type="datetime-local" id="edit_expires_at" wire:model="newsForm.expires_at" class="admin-form-input">
                            <?php $__errorArgs = ['newsForm.expires_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <div class="admin-form-checkbox-group">
                                <input type="checkbox" id="edit_is_published" wire:model="newsForm.is_published" class="admin-form-checkbox">
                                <label for="edit_is_published" class="admin-form-checkbox-label">Publier</label>
                            </div>
                            <?php $__errorArgs = ['newsForm.is_published'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="admin-form-group">
                            <div class="admin-form-checkbox-group">
                                <input type="checkbox" id="edit_is_pinned" wire:model="newsForm.is_pinned" class="admin-form-checkbox">
                                <label for="edit_is_pinned" class="admin-form-checkbox-label">Épingler</label>
                            </div>
                            <?php $__errorArgs = ['newsForm.is_pinned'];
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
                        <div class="admin-form-checkbox-group">
                            <input type="checkbox" id="edit_is_active" wire:model="newsForm.is_active" class="admin-form-checkbox">
                            <label for="edit_is_active" class="admin-form-checkbox-label">Actif</label>
                        </div>
                        <?php $__errorArgs = ['newsForm.is_active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="admin-form-actions">
                        <button type="button" class="admin-btn admin-btn-danger" wire:click="deleteNews" wire:confirm="Êtes-vous sûr de vouloir supprimer cette actualité ?">Supprimer</button>
                        <button type="button" class="admin-btn admin-btn-secondary" wire:click="publishNow">Publier maintenant</button>
                        <button type="submit" class="admin-btn admin-btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/admin/news.blade.php ENDPATH**/ ?>