<div>
    <h3>🎖️ Gestion des Rangs</h3>
                    
    <div class="ranks-section">
        <!-- Existing Ranks -->
        <div class="ranks-list">
            <h4>Rangs existants</h4>
            <?php $__currentLoopData = $ranks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="rank-item">
                    <div class="rank-info">
                        <span class="rank-name"><?php echo e($rank->name); ?></span>
                        <span class="rank-level">Niveau <?php echo e($rank->level); ?></span>
                    </div>
                    <div class="rank-permissions">
                        <?php $__currentLoopData = $rank->permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="permission-badge"><?php echo e($availablePermissions[$permission] ?? $permission); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="rank-actions">
                        <button class="btn btn-sm btn-secondary" wire:click="editRank(<?php echo e($rank->id); ?>)">
                            ✏️ Modifier
                        </button>
                        <?php if($rank->level > 1): ?>
                            <button class="btn btn-sm btn-danger" wire:click="deleteRank(<?php echo e($rank->id); ?>)"
                                    onclick="return confirm('Supprimer ce rang ?')">
                                🗑️ Supprimer
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        
        <!-- Create/Edit Rank Form -->
        <div class="rank-form">
            <h4><?php echo e($editingRank ? 'Modifier le rang' : 'Créer un nouveau rang'); ?></h4>
            
            <div class="form-group">
                <label>Nom du rang</label>
                <input type="text" class="form-input" wire:model="newRankName" 
                    placeholder="Nom du rang">
                <?php $__errorArgs = ['newRankName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #dc3545; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            
            <div class="form-group">
                <label>Niveau (1-10)</label>
                <input type="number" class="form-input" wire:model="newRankLevel" 
                    min="1" max="10">
                <?php $__errorArgs = ['newRankLevel'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #dc3545; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            
            <div class="form-group">
                <label>Permissions</label>
                <div class="permissions-grid">
                    <?php $__currentLoopData = $availablePermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="permission-checkbox">
                            <input type="checkbox" wire:model="newRankPermissions" value="<?php echo e($key); ?>">
                            <span><?php echo e($label); ?></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            
            <div class="form-actions">
                <button class="btn btn-primary" wire:click="<?php echo e($editingRank ? 'updateRank' : 'createRank'); ?>">
                    <?php echo e($editingRank ? '💾 Mettre à jour' : '➕ Créer le rang'); ?>

                </button>
                <?php if($editingRank): ?>
                    <button class="btn btn-secondary" wire:click="cancelEditRank">
                        ❌ Annuler
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/alliance/rank.blade.php ENDPATH**/ ?>