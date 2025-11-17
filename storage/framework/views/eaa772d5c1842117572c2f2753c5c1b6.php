<div page="technology">
    <div class="technology-container">
        <!-- En-tête -->
        <div class="technology-header">
            <div class="research-points">
                <div class="points-display">
                    <i class="fas fa-atom"></i>
                    <span class="points-value"><?php echo e(number_format($researchPoints)); ?></span>
                    <span class="points-label">Points de Recherche</span>
                </div>
            </div>


        </div>

        <!-- Grille de technologies -->
        <div class="technologies-grid">
            <?php $__currentLoopData = $technologies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $technology): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="technology-card">
                    <!-- En-tête de la carte -->
                    <div class="technology-card-header" wire:click="openTechnologyModal(<?php echo e($technology['id']); ?>)">
                        <?php if($technology['icon']): ?>
                            <img src="<?php echo e(asset('images/technologies/' . $technology['icon'])); ?>" 
                                 alt="<?php echo e($technology['label']); ?>" 
                                 class="technology-image">
                        <?php else: ?>
                            <div class="technology-image" style="background: linear-gradient(135deg, #1e293b, #334155); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-flask" style="font-size: 3rem; color: var(--stargate-primary);"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="technology-level">
                            <i class="fas fa-layer-group"></i>
                            Niveau <?php echo e($technology['current_level']); ?>

                            <?php if($technology['max_level'] > 0): ?>
                                / <?php echo e($technology['max_level']); ?>

                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Contenu de la carte -->
                    <div class="technology-card-content">
                        <h3 class="technology-name">
                            <i class="fas fa-<?php echo e($technology['category'] === 'research' ? 'microscope' : ($technology['category'] === 'military' ? 'shield-alt' : 'cog')); ?>"></i>
                            <?php echo e($technology['label']); ?>

                        </h3>
                        
                        <?php if($technology['description']): ?>
                            <p class="technology-description"><?php echo e(Str::limit($technology['description'], 100)); ?></p>
                        <?php endif; ?>

                        <!-- Coût de recherche -->
                        <?php if($technology['current_level'] < $technology['max_level'] || $technology['max_level'] == 0): ?>
                            <div class="research-cost">
                                <div class="cost-item">
                                    <i class="fas fa-atom"></i>
                                    <span><?php echo e(number_format($technology['research_cost'])); ?> Points</span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Prérequis -->
                        <?php if(!$technology['requirements_met']): ?>
                            <div class="requirements-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Prérequis non satisfaits
                            </div>
                        <?php endif; ?>

                        <!-- Bouton de recherche -->
                        <div class="technology-actions">
                            <?php if($technology['current_level'] >= $technology['max_level'] && $technology['max_level'] > 0): ?>
                                <button class="btn btn-completed" disabled>
                                    <i class="fas fa-check"></i>
                                    Recherche terminée
                                </button>
                            <?php elseif($technology['can_research']): ?>
                                <button class="btn btn-research" wire:click="startResearch(<?php echo e($technology['id']); ?>)" 
                                        wire:loading.attr="disabled" wire:target="startResearch">
                                    <i class="fas fa-play"></i>
                                    Rechercher
                                </button>
                            <?php else: ?>
                                <button class="btn btn-disabled" disabled>
                                    <?php if(!$technology['requirements_met']): ?>
                                        <i class="fas fa-lock"></i>
                                        Prérequis manquants
                                    <?php elseif($researchPoints < $technology['research_cost']): ?>
                                        <i class="fas fa-coins"></i>
                                        Points insuffisants
                                    <?php else: ?>
                                        <i class="fas fa-ban"></i>
                                        Non disponible
                                    <?php endif; ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if(empty($technologies)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-flask"></i>
                </div>
                <h3>Aucune technologie disponible</h3>
                <p>Les technologies seront bientôt disponibles pour la recherche.</p>
            </div>
        <?php endif; ?>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/technology.blade.php ENDPATH**/ ?>