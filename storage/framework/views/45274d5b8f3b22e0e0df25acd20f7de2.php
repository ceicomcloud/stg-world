<div page="forum">
    <div class="forum-container">
        
        <nav class="breadcrumb-nav mb-4">
            <a href="<?php echo e(route('game.forum')); ?>" class="breadcrumb-item">
                <i class="fas fa-home"></i> Forum
            </a>
            
            <span class="breadcrumb-separator">></span>
            <a href="<?php echo e(route('game.forum')); ?>" class="breadcrumb-item">
                <?php echo e($category->name ?? 'Catégorie'); ?>

            </a>
            
            <span class="breadcrumb-separator">></span>
            <span class="breadcrumb-current"><?php echo e($forum->name ?? 'Forum'); ?></span>
        </nav>

        
        <div class="topics-header">
            <div class="forum-info">
                <?php if($forum->icon): ?>
                    <i class="<?php echo e($forum->icon); ?> forum-icon"></i>
                <?php endif; ?>
                <div>
                    <h2 class="forum-title"><?php echo e($forum->name); ?></h2>
                    <?php if($forum->description): ?>
                        <p class="forum-description"><?php echo e($forum->description); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if(!$forum->is_locked || auth()->user()->hasModeratorRights() || auth()->user()->hasAdminRights()): ?>
                <button wire:click="toggleNewTopicForm" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouveau Sujet
                </button>
            <?php endif; ?>
        </div>

        
        <?php if($forum->children && $forum->children->count() > 0): ?>
            <div class="subforums-section mb-2">
                <h4 class="subforums-title">
                    <i class="fas fa-folder-open"></i> Sous-forums
                </h4>
                <div class="subforums-grid">
                    <?php $__currentLoopData = $forum->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subforum): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('game.forum.topics', ['categoryId' => $categoryId, 'forumId' => $subforum->slug])); ?>" class="subforum-card">
                            <div class="subforum-header">
                                <?php if($subforum->icon): ?>
                                    <i class="<?php echo e($subforum->icon); ?> subforum-icon"></i>
                                <?php else: ?>
                                    <i class="fas fa-comments subforum-icon"></i>
                                <?php endif; ?>
                                <div class="subforum-info">
                                    <h5 class="subforum-name">
                                        <?php echo e($subforum->name); ?>

                                        <?php if($subforum->is_locked): ?>
                                            <i class="fas fa-lock text-warning"></i>
                                        <?php endif; ?>
                                    </h5>
                                    <?php if($subforum->description): ?>
                                        <p class="subforum-description"><?php echo e($subforum->description); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="subforum-stats">
                                <span class="subforum-stat">
                                    <i class="fas fa-comments"></i> <?php echo e($subforum->topics_count()); ?>

                                </span>
                                <span class="subforum-stat">
                                    <i class="fas fa-comment"></i> <?php echo e($subforum->posts_count()); ?>

                                </span>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if($showNewTopicForm): ?>
            <div class="new-topic-form">
                <div class="form-header">
                    <h4><i class="fas fa-plus-circle"></i> Créer un Nouveau Sujet</h4>
                    <button wire:click="toggleNewTopicForm" class="btn-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form wire:submit.prevent="createTopic">
                    <div class="form-group">
                        <label for="topicTitle"><i class="fas fa-heading"></i> Titre du sujet</label>
                        <input type="text" id="topicTitle" wire:model="newTopicTitle" class="form-control" placeholder="Entrez le titre du sujet...">
                        <?php $__errorArgs = ['newTopicTitle'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="topicContent"><i class="fas fa-edit"></i> Contenu</label>
                        <?php if (isset($component)) { $__componentOriginalac67a5bc9c14e131fd19e367e45ff063 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac67a5bc9c14e131fd19e367e45ff063 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.tinymce','data' => ['wire:model.live' => 'newTopicContent','placeholder' => 'Rédigez votre message...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.tinymce'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'newTopicContent','placeholder' => 'Rédigez votre message...']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalac67a5bc9c14e131fd19e367e45ff063)): ?>
<?php $attributes = $__attributesOriginalac67a5bc9c14e131fd19e367e45ff063; ?>
<?php unset($__attributesOriginalac67a5bc9c14e131fd19e367e45ff063); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalac67a5bc9c14e131fd19e367e45ff063)): ?>
<?php $component = $__componentOriginalac67a5bc9c14e131fd19e367e45ff063; ?>
<?php unset($__componentOriginalac67a5bc9c14e131fd19e367e45ff063); ?>
<?php endif; ?>
                        <?php $__errorArgs = ['newTopicContent'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Créer le Sujet
                        </button>
                        <button type="button" wire:click="toggleNewTopicForm" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        
        <div class="topics-list">
            <?php if(Auth::check() && Auth::user()->hasAdmin()): ?>
                <div class="bulk-actions-header">
                    <div class="select-all-container">
                        <input type="checkbox" id="selectAll" wire:model.live="selectAll" wire:change="toggleSelectAll">
                        <label for="selectAll">Tout sélectionner</label>
                    </div>
                    
                    <?php if($showBulkActions): ?>
                        <div class="bulk-actions">
                            <button type="button" class="btn btn-warning" wire:click="showMoveTopicsModal">
                                <i class="fas fa-arrows-alt"></i> Déplacer (<?php echo e(count($selectedTopics)); ?>)
                            </button>
                            <button type="button" class="btn btn-danger" wire:click="showDeleteTopicsModal">
                                <i class="fas fa-trash"></i> Supprimer (<?php echo e(count($selectedTopics)); ?>)
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php $__empty_1 = true; $__currentLoopData = $topics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="topic-item <?php echo e($topic->is_pinned ? 'pinned' : ''); ?> <?php echo e($topic->is_locked ? 'locked' : ''); ?>">
                    <?php if(Auth::check() && Auth::user()->hasAdmin()): ?>
                        <div class="topic-checkbox">
                            <input type="checkbox" 
                                id="topic_<?php echo e($topic->id); ?>" 
                                value="<?php echo e($topic->id); ?>" 
                                wire:change="toggleTopicSelection(<?php echo e($topic->id); ?>)"
                                <?php if(in_array($topic->id, $selectedTopics)): ?> checked <?php endif; ?>>
                        </div>
                    <?php endif; ?>
                    <div class="topic-content">
                        <div class="topic-header">
                            <h4 class="topic-title">
                                <a href="<?php echo e(route('game.forum.topic', ['categoryId' => $categoryId, 'forumId' => $forumId, 'topicId' => $topic->slug])); ?>">
                                    <?php echo e($topic->title); ?>

                                </a>
                            </h4>
                            <div class="topic-badges">
                                <?php if($topic->is_pinned): ?>
                                    <span class="badge badge-pinned">
                                        <i class="fas fa-thumbtack"></i> Épinglé
                                    </span>
                                <?php endif; ?>
                                <?php if($topic->is_locked): ?>
                                    <span class="badge badge-locked">
                                        <i class="fas fa-lock"></i> Verrouillé
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="topic-meta">
                            <span class="topic-author">
                                <i class="fas fa-user"></i> <?php echo e($topic->user->name); ?>

                            </span>
                            <span class="topic-date">
                                <i class="fas fa-calendar"></i> <?php echo e($topic->created_at->format('d/m/Y H:i')); ?>

                            </span>
                        </div>
                    </div>
                    
                    <div class="topic-stats">
                        <div class="topic-stat">
                            <span class="topic-stat-number"><?php echo e($topic->posts_count()); ?></span>
                            <span class="topic-stat-label">Réponses</span>
                        </div>
                        <div class="topic-stat">
                            <span class="topic-stat-number"><?php echo e($topic->views_count); ?></span>
                            <span class="topic-stat-label">Vues</span>
                        </div>
                    </div>
                    
                    <?php if($topic->lastPost): ?>
                        <div class="topic-last-post">
                            <div class="topic-last-post-date"><?php echo e($topic->last_post_at->format('d/m/Y H:i')); ?></div>
                            <div class="topic-last-post-user">par <?php echo e($topic->lastPost->user->name); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3 class="empty-title">Aucun sujet</h3>
                    <p class="empty-description">Il n'y a pas encore de sujets dans ce forum.</p>
                    <?php if(!$forum->is_locked): ?>
                        <button wire:click="toggleNewTopicForm" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Créer le premier sujet
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        
        <?php if($topics->hasPages()): ?>
            <div class="pagination-wrapper">
                <?php echo e($topics->links()); ?>

            </div>
        <?php endif; ?>

        
        <?php if($showMoveModal): ?>
            <div class="modal-overlay" wire:click="cancelMove">
                <div class="modal-content" wire:click.stop>
                    <div class="modal-header">
                        <h3 class="modal-title">Déplacer les topics sélectionnés</h3>
                        <button type="button" class="btn-close" wire:click="cancelMove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Vous êtes sur le point de déplacer <?php echo e(count($selectedTopics)); ?> topic(s) vers un autre forum.
                        </p>
                        
                        <div class="form-group">
                            <label for="targetForum">Forum de destination :</label>
                            <select id="targetForum" wire:model="targetForumId" class="form-control">
                                <option value="">Sélectionnez un forum</option>
                                <?php $__currentLoopData = $availableForums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $availableForum): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($availableForum->id); ?>">
                                        <?php echo e($availableForum->category->name); ?> > <?php echo e($availableForum->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelMove">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button type="button" class="btn btn-warning" wire:click="moveSelectedTopics">
                            <i class="fas fa-arrows-alt"></i> Déplacer
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if($showDeleteModal): ?>
            <div class="modal-overlay" wire:click="cancelDelete">
                <div class="modal-content" wire:click.stop>
                    <div class="modal-header">
                        <h3 class="modal-title">Supprimer les topics sélectionnés</h3>
                        <button type="button" class="btn-close" wire:click="cancelDelete">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Attention !</strong> Vous êtes sur le point de supprimer définitivement <?php echo e(count($selectedTopics)); ?> topic(s) et tous leurs messages.
                            Cette action est irréversible.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDelete">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deleteSelectedTopics">
                            <i class="fas fa-trash"></i> Supprimer définitivement
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/forum-topic.blade.php ENDPATH**/ ?>