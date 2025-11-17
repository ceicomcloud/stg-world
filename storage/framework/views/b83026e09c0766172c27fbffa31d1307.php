<div page="forum">
    <div class="forum-container">
        
        <?php if($currentView === 'categories'): ?>
            <div class="forum-header">
                <h1 class="forum-title">
                    <i class="fas fa-comments"></i>
                    Forum Communautaire <?php echo e(config('app.name')); ?>

                </h1>
                <p class="forum-subtitle">Échangez, partagez et discutez avec la communauté</p>
            </div>
        <?php endif; ?>

        
        <nav class="breadcrumb-nav mb-4">
            <button wire:click="showCategories" class="breadcrumb-item <?php echo e($currentView === 'categories' ? 'active' : ''); ?>">
                <i class="fas fa-home"></i> Forum Principal
            </button>
            
            <?php if($selectedCategory): ?>
                <span class="breadcrumb-separator">></span>
                <span class="breadcrumb-current"><?php echo e($selectedCategory->name); ?></span>
            <?php endif; ?>
        </nav>

        
        <?php if($currentView === 'categories'): ?>
            <div class="forum-categories">
                
                <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="category-card">
                        <div class="category-header" style="border-left: 4px solid <?php echo e($category->color ?? '#007bff'); ?>">
                            <div class="category-info">
                                <?php if($category->icon): ?>
                                    <i class="<?php echo e($category->icon); ?> category-icon"></i>
                                <?php endif; ?>
                                <div>
                                    <h3 class="category-name"><?php echo e($category->name); ?></h3>
                                    <?php if($category->description): ?>
                                        <p class="category-description"><?php echo e($category->description); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if($category->forums->count() > 0): ?>
                            <div class="forums-list">
                                <?php $__currentLoopData = $category->forums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $forum): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a href="<?php echo e(route('game.forum.topics', ['categoryId' => $category->slug, 'forumId' => $forum->slug])); ?>" class="forum-item">
                                        <div class="forum-info">
                                            <?php if($forum->icon): ?>
                                                <i class="<?php echo e($forum->icon); ?> forum-icon"></i>
                                            <?php endif; ?>
                                            <div>
                                                <h4 class="forum-name">
                                                    <?php echo e($forum->name); ?>

                                                    <?php if($forum->is_locked): ?>
                                                        <i class="fas fa-lock text-warning"></i>
                                                    <?php endif; ?>
                                                </h4>
                                                <?php if($forum->description): ?>
                                                    <p class="forum-description"><?php echo e($forum->description); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="forum-stats">
                                            <div class="stat">
                                                <span class="stat-number"><?php echo e($forum->topics_count()); ?></span>
                                                <span class="stat-label">Sujets</span>
                                            </div>
                                            <div class="stat">
                                                <span class="stat-number"><?php echo e($forum->posts_count()); ?></span>
                                                <span class="stat-label">Messages</span>
                                            </div>
                                        </div>
                                        <?php if($forum->lastPost): ?>
                                            <div class="forum-last-post">
                                                <div class="last-post-info">
                                                    <div class="last-post-date"><?php echo e($forum->last_post_at->diffForHumans()); ?></div>
                                                    <div class="last-post-user">par <?php echo e($forum->lastPost->user->name); ?></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="no-forums">
                                <p>Aucun forum dans cette catégorie pour le moment.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="no-categories">
                        <p>Aucune catégorie de forum disponible.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        
        <?php if($currentView === 'forums'): ?>
            <div class="forum-list">
                <h2 class="forum-title">
                    <i class="fas fa-folder"></i> <?php echo e($selectedCategory->name); ?>

                </h2>
                
                <?php $__empty_1 = true; $__currentLoopData = $forums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $forum): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="forum-group">
                        <a href="<?php echo e(route('game.forum.topics', ['categoryId' => $selectedCategory->slug, 'forumId' => $forum->slug])); ?>" class="forum-item">
                            <div class="forum-info">
                                <?php if($forum->icon): ?>
                                    <i class="<?php echo e($forum->icon); ?> forum-icon"></i>
                                <?php endif; ?>
                                <div>
                                    <h3 class="forum-name">
                                        <?php echo e($forum->name); ?>

                                        <?php if($forum->is_locked): ?>
                                            <i class="fas fa-lock text-warning"></i>
                                        <?php endif; ?>
                                        <?php if($forum->children->count() > 0): ?>
                                            <span class="subforum-count">(<?php echo e($forum->children->count()); ?> sous-forums)</span>
                                        <?php endif; ?>
                                    </h3>
                                    <?php if($forum->description): ?>
                                        <p class="forum-description"><?php echo e($forum->description); ?></p>
                                    <?php endif; ?>
                                    
                                    
                                    <?php if($forum->children->count() > 0): ?>
                                        <div class="subforums">
                                            <span class="subforums-label">Sous-forums :</span>
                                            <?php $__currentLoopData = $forum->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $subforum): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <a href="<?php echo e(route('game.forum.topics', ['categoryId' => $selectedCategory->id, 'forumId' => $subforum->id])); ?>" class="subforum-link">
                                                    <?php echo e($subforum->name); ?>

                                                    <?php if($subforum->is_locked): ?>
                                                        <i class="fas fa-lock text-warning"></i>
                                                    <?php endif; ?>
                                                </a><?php if(!$loop->last): ?>, <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="forum-stats">
                                <div class="stat">
                                    <span class="stat-number"><?php echo e($forum->topics_count()); ?></span>
                                    <span class="stat-label">Sujets</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-number"><?php echo e($forum->posts_count()); ?></span>
                                    <span class="stat-label">Messages</span>
                                </div>
                            </div>
                            
                            <?php if($forum->lastPost): ?>
                                <div class="forum-last-post">
                                    <div class="last-post-info">
                                        <div class="last-post-date"><?php echo e($forum->last_post_at->diffForHumans()); ?></div>
                                        <div class="last-post-user">par <?php echo e($forum->lastPost->user->name); ?></div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="forum-last-post">
                                    <div class="last-post-info">
                                        <div class="last-post-date">Aucun message</div>
                                        <div class="last-post-user">-</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="no-forums">
                        <p>Aucun forum dans cette catégorie.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/forum.blade.php ENDPATH**/ ?>