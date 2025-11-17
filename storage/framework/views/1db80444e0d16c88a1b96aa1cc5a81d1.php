<div>
    <h3>➕ Créer une Alliance</h3>
                    
    <div class="alliance-form">
        <div class="form-group">
            <label>Nom de l'alliance</label>
            <input type="text" class="form-input" wire:model="createAllianceName" 
                placeholder="Nom de votre alliance">
            <?php $__errorArgs = ['createAllianceName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #dc3545; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        
        <div class="form-group">
            <label>Tag (3-10 caractères)</label>
            <input type="text" class="form-input" wire:model="createAllianceTag" 
                placeholder="TAG" maxlength="10">
            <?php $__errorArgs = ['createAllianceTag'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #dc3545; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        
        <div class="form-group">
            <label>Description (optionnelle)</label>
            <textarea class="form-input form-textarea" wire:model="createAllianceDescription" 
                    placeholder="Décrivez votre alliance..."></textarea>
            <?php $__errorArgs = ['createAllianceDescription'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #dc3545; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        
        <button class="btn btn-primary btn-lg" wire:click="createAlliance">
            🛡️ Créer l'Alliance
        </button>
    </div>
</div>
<?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/alliance/create.blade.php ENDPATH**/ ?>