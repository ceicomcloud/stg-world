<div>
    <h3>🔍 Rechercher une Alliance</h3>
                    
    <div class="alliance-search">
        <input type="text" class="search-input" wire:model.live="searchQuery" 
            placeholder="Rechercher par nom ou tag...">
    </div>
    
    <?php if(count($searchResults) > 0): ?>
        <div class="search-results">
            <?php $__currentLoopData = $searchResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="alliance-result">
                    <div class="alliance-result-info">
                        <h4><?php echo e($result->name); ?> [<?php echo e($result->tag); ?>]</h4>
                        <p>Leader: <?php echo e($result->leader->name); ?></p>
                        <?php if($result->external_description): ?>
                            <p><?php echo Str::limit($result->external_description, 100); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="alliance-result-stats">
                        <div class="result-stat">
                            <span class="result-stat-value"><?php echo e($result->member_count); ?></span>
                            <span class="result-stat-label">Membres</span>
                        </div>
                        <div class="result-stat">
                            <span class="result-stat-value"><?php echo e($result->open_recruitment ? 'Ouvert' : 'Fermé'); ?></span>
                            <span class="result-stat-label">Recrutement</span>
                        </div>
                        
                        <?php if($result->open_recruitment && $result->canAcceptNewMembers()): ?>
                            <button class="btn btn-primary" 
                                    wire:click="applyToAlliance(<?php echo e($result->id); ?>)">
                                📝 Candidater
                            </button>
                        <?php else: ?>
                            <span style="color: var(--stargate-text-secondary); font-size: 12px;">
                                <?php echo e($result->canAcceptNewMembers() ? 'Recrutement fermé' : 'Alliance complète'); ?>

                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php elseif(!empty($searchQuery)): ?>
        <p style="text-align: center; color: var(--stargate-text-secondary); padding: 40px;">
            Aucune alliance trouvée
        </p>
    <?php endif; ?>
</div>
<?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/alliance/join.blade.php ENDPATH**/ ?>