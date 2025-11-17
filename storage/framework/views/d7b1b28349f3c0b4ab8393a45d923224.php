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
            <a href="<?php echo e(route('game.forum.topics', ['categoryId' => $categoryId, 'forumId' => $forumId])); ?>" class="breadcrumb-item">
                <?php echo e($forum->name ?? 'Forum'); ?>

            </a>
            <span class="breadcrumb-separator">></span>
            <span class="breadcrumb-current"><?php echo e($topic->title ?? 'Topic'); ?></span>
        </nav>

        
        <div class="topic-header mb-4">
            <div class="topic-info">
                <h2 class="topic-title">
                    <?php ($authorAvatar = $this->getUserAvatarUrl($topic->user_id, 32)); ?>
                    <?php if(!empty($authorAvatar)): ?>
                        <img src="<?php echo e($authorAvatar); ?>" alt="Avatar de <?php echo e($topic->user->name); ?>" style="width:24px; height:24px; border-radius:50%; object-fit:cover; vertical-align:middle; margin-right:8px;" />
                    <?php endif; ?>
                    <?php echo e($topic->title); ?>

                </h2>
                <div class="topic-meta">
                    <span class="topic-author">
                        <i class="fas fa-user"></i> Créé par <?php echo e($topic->user->name); ?>

                    </span>
                    <span class="topic-date">
                        <i class="fas fa-calendar"></i> <?php echo e($topic->created_at->format('d/m/Y H:i')); ?>

                    </span>
                </div>
                
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
            
            <div class="topic-actions">
                <?php if(!$topic->is_locked || auth()->user()->hasModeratorRights() || auth()->user()->hasAdminRights()): ?>
                    <button wire:click="toggleReplyForm" class="btn btn-primary">
                        <i class="fas fa-reply"></i> Répondre
                    </button>
                <?php endif; ?>
                
                <?php if(auth()->guard()->check()): ?>
                    <?php if(auth()->user()->hasAdmin()): ?>
                        <div class="admin-actions">
                            <button wire:click="showCloseTopicModalOpen" class="btn btn-warning">
                                <?php if($topic->is_locked): ?>
                                    <i class="fas fa-unlock"></i> Ouvrir
                                <?php else: ?>
                                    <i class="fas fa-lock"></i> Fermer
                                <?php endif; ?>
                            </button>
                            <button wire:click="showDeleteTopicModalOpen" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        
        <?php if($showReplyForm): ?>
            <div class="reply-form">
                <div class="form-header">
                    <h4>
                        <i class="fas fa-reply"></i> Répondre au sujet
                    </h4>
                    <button wire:click="toggleReplyForm" class="btn-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form wire:submit.prevent="createPost">
                    <div class="form-group">
                        <label for="postContent">
                            <i class="fas fa-edit"></i> Votre réponse
                        </label>
                        <?php if (isset($component)) { $__componentOriginalac67a5bc9c14e131fd19e367e45ff063 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac67a5bc9c14e131fd19e367e45ff063 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.tinymce','data' => ['wire:model.live' => 'newPostContent','placeholder' => 'Votre réponse...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.tinymce'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'newPostContent','placeholder' => 'Votre réponse...']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalac67a5bc9c14e131fd19e367e45ff063)): ?>
<?php $attributes = $__attributesOriginalac67a5bc9c14e131fd19e367e45ff063; ?>
<?php unset($__attributesOriginalac67a5bc9c14e131fd19e367e45ff063); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalac67a5bc9c14e131fd19e367e45ff063)): ?>
<?php $component = $__componentOriginalac67a5bc9c14e131fd19e367e45ff063; ?>
<?php unset($__componentOriginalac67a5bc9c14e131fd19e367e45ff063); ?>
<?php endif; ?>
                        <?php $__errorArgs = ['newPostContent'];
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
                            <i class="fas fa-paper-plane"></i> Publier la Réponse
                        </button>
                        <button type="button" wire:click="toggleReplyForm" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        
        <div class="posts-list">
            <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="post-item <?php echo e($index === 0 ? 'first-post' : ''); ?>">
                    <div class="post-sidebar">
                        <div class="user-info">
                            <div class="user-avatar">
                                <?php ($avatar = $this->getUserAvatarUrl($post->user_id, 64)); ?>
                                <?php if(!empty($avatar)): ?>
                                    <img src="<?php echo e($avatar); ?>" alt="<?php echo e($post->user->name); ?>" />
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?php echo e(strtoupper(substr($post->user->name, 0, 1))); ?>

                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="user-name"><?php echo e($post->user->name); ?></div>
                            <div class="user-role">
                                <i class="<?php echo e($post->user->getRoleIcon()); ?>"></i>
                                <?php echo e($post->user->getRoleName()); ?>

                            </div>
                            <div class="user-stats">
                                <small>Messages: <?php echo e($post->user->posts_count() ?? 0); ?></small>
                                <br />
                                <small>Sujets: <?php echo e($post->user->posts_count() ?? 0); ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="post-content">
                        <div class="post-header">
                            <div class="post-number">#<?php echo e(($posts->currentPage() - 1) * $posts->perPage() + $index + 1); ?></div>
                            <div class="post-date"><?php echo e($post->created_at->format('d/m/Y H:i')); ?></div>
                            <?php if($post->updated_at != $post->created_at): ?>
                                <div class="post-edited">
                                    <i class="fas fa-edit"></i> Modifié le <?php echo e($post->updated_at->format('d/m/Y H:i')); ?>

                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if($editingPostId === $post->id): ?>
                            
                            <div class="post-edit-form">
                                <div class="form-group mb-3">
                                    <label for="editPostContent" class="form-label">Modifier le message</label>
                                    <?php if (isset($component)) { $__componentOriginalac67a5bc9c14e131fd19e367e45ff063 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac67a5bc9c14e131fd19e367e45ff063 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.tinymce','data' => ['wire:model' => 'editPostContent','placeholder' => 'Rédigez votre message...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.tinymce'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'editPostContent','placeholder' => 'Rédigez votre message...']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalac67a5bc9c14e131fd19e367e45ff063)): ?>
<?php $attributes = $__attributesOriginalac67a5bc9c14e131fd19e367e45ff063; ?>
<?php unset($__attributesOriginalac67a5bc9c14e131fd19e367e45ff063); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalac67a5bc9c14e131fd19e367e45ff063)): ?>
<?php $component = $__componentOriginalac67a5bc9c14e131fd19e367e45ff063; ?>
<?php unset($__componentOriginalac67a5bc9c14e131fd19e367e45ff063); ?>
<?php endif; ?>
                                    <?php $__errorArgs = ['editPostContent'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="form-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="edit-actions">
                                    <button wire:click="updatePost" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Sauvegarder
                                    </button>
                                    <button wire:click="cancelEdit" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Annuler
                                    </button>
                                </div>
                            </div>
                        <?php else: ?>
                            
                            <div class="post-body">
                                <?php echo $this->parseQuotes($post->content); ?>

                                <?php if($post->updated_at > $post->created_at): ?>
                                    <div class="post-edited-info">
                                        <small class="text-muted">
                                            <i class="fas fa-edit"></i> Modifié le <?php echo e($post->updated_at->format('d/m/Y H:i')); ?>

                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            
                            <div class="post-actions">
                                <?php if(auth()->id() === $post->user_id || auth()->user()->hasAdmin()): ?>
                                    <button wire:click="startEditPost(<?php echo e($post->id); ?>)" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                <?php endif; ?>
                                <?php if(auth()->check() && auth()->user()->hasAdmin()): ?>
                                    <button wire:click="showDeletePostModalOpen(<?php echo e($post->id); ?>)" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                <?php endif; ?>
                                <button wire:click="quotePost(<?php echo e($post->id); ?>)" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-quote-left"></i> Citer
                                </button>
                                <?php if(auth()->guard()->check()): ?>
                                    <button wire:click="startReportPost(<?php echo e($post->id); ?>)" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-flag"></i> Signaler
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3 class="empty-title">Aucun message</h3>
                    <p class="empty-description">Il n'y a pas encore de messages dans ce sujet.</p>
                    <?php if(!$topic->is_locked): ?>
                        <button wire:click="toggleReplyForm" class="btn btn-outline-primary">
                            <i class="fas fa-plus"></i> Soyez le premier à répondre
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        
        <?php if($posts->hasPages()): ?>
            <div class="pagination-wrapper">
                <?php echo e($posts->links()); ?>

            </div>
        <?php endif; ?>

        
        <?php if(!$topic->is_locked && !$showReplyForm): ?>
            <div class="quick-reply mt-4">
                <button wire:click="toggleReplyForm" class="btn btn-primary btn-block">
                    <i class="fas fa-reply"></i> Réponse Rapide
                </button>
            </div>
        <?php endif; ?>

        
        <?php if($showReportModal): ?>
            <div class="modal-overlay" wire:click="cancelReport">
                <div class="modal-content" wire:click.stop>
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-flag text-warning"></i> Signaler un message
                        </h5>
                        <button wire:click="cancelReport" class="btn-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="reportReason" class="form-label">Raison du signalement *</label>
                            <select wire:model="reportReason" id="reportReason" class="form-control">
                                <option value="">Sélectionnez une raison</option>
                                <option value="spam">Spam</option>
                                <option value="inappropriate">Contenu inapproprié</option>
                                <option value="harassment">Harcèlement</option>
                                <option value="off-topic">Hors sujet</option>
                                <option value="duplicate">Contenu dupliqué</option>
                                <option value="other">Autre</option>
                            </select>
                            <?php $__errorArgs = ['reportReason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                <div class="text-danger mt-1"><?php echo e($message); ?></div> 
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="reportDescription" class="form-label">Description (optionnel)</label>
                            <textarea 
                                wire:model="reportDescription" 
                                id="reportDescription" 
                                class="form-control" 
                                rows="4" 
                                placeholder="Décrivez le problème en détail..."
                                maxlength="500"
                            ></textarea>
                            <?php $__errorArgs = ['reportDescription'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                <div class="text-danger mt-1"><?php echo e($message); ?></div> 
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <small>Maximum 500 caractères</small>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button wire:click="submitReport" class="btn btn-warning">
                            <i class="fas fa-flag"></i> Signaler
                        </button>
                        <button wire:click="cancelReport" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if($showDeleteTopicModal): ?>
            <div class="modal-overlay" wire:click="cancelDeleteTopic">
                <div class="modal-content" wire:click.stop>
                    <div class="modal-header">
                        <h3 class="modal-title">Supprimer le topic</h3>
                        <button wire:click="cancelDeleteTopic" class="btn-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Attention !</strong> Cette action est irréversible.
                        </div>
                        <p>Êtes-vous sûr de vouloir supprimer ce topic et tous ses messages ?</p>
                        <p><strong>Topic :</strong> <?php echo e($topic->title); ?></p>
                    </div>
                    
                    <div class="modal-footer">
                        <button wire:click="cancelDeleteTopic" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button wire:click="deleteTopic" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Supprimer définitivement
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if($showCloseTopicModal): ?>
            <div class="modal-overlay" wire:click="cancelCloseTopic">
                <div class="modal-content" wire:click.stop>
                    <div class="modal-header">
                        <h3 class="modal-title">
                            <?php if($topic->is_locked): ?>
                                Ouvrir le topic
                            <?php else: ?>
                                Fermer le topic
                            <?php endif; ?>
                        </h3>
                        <button wire:click="cancelCloseTopic" class="btn-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="modal-body">
                        <p>
                            <?php if($topic->is_locked): ?>
                                Êtes-vous sûr de vouloir ouvrir ce topic ? Les utilisateurs pourront à nouveau y répondre.
                            <?php else: ?>
                                Êtes-vous sûr de vouloir fermer ce topic ? Les utilisateurs ne pourront plus y répondre.
                            <?php endif; ?>
                        </p>
                        <p><strong>Topic :</strong> <?php echo e($topic->title); ?></p>
                    </div>
                    
                    <div class="modal-footer">
                        <button wire:click="cancelCloseTopic" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button wire:click="toggleTopicLock" class="btn btn-warning">
                            <?php if($topic->is_locked): ?>
                                <i class="fas fa-unlock"></i> Ouvrir
                            <?php else: ?>
                                <i class="fas fa-lock"></i> Fermer
                            <?php endif; ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if($showDeletePostModal): ?>
            <div class="modal-overlay" wire:click="cancelDeletePost">
                <div class="modal-content" wire:click.stop>
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-trash text-danger"></i> Supprimer le message
                        </h5>
                        <button wire:click="cancelDeletePost" class="btn-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="modal-body">
                        <p class="text-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Attention !</strong> Cette action est irréversible.
                        </p>
                        <p>Êtes-vous sûr de vouloir supprimer ce message ?</p>
                        <p><strong>Note :</strong> Le premier message d'un topic ne peut pas être supprimé. Pour supprimer le premier message, vous devez supprimer le topic entier.</p>
                    </div>
                    
                    <div class="modal-footer">
                        <button wire:click="cancelDeletePost" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button wire:click="deletePost" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/forum-post.blade.php ENDPATH**/ ?>