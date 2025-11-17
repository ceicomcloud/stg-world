<div>
    <!-- Indicateur de type -->
    <div class="modal-type-indicator modal-type-report">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Signalement de problème</span>
    </div>
    
    <form wire:submit.prevent="submitReport">
        <div class="form-group mb-4">
            <label class="form-label">
                <i class="fas fa-tag"></i> Catégorie
            </label>
            <div class="category-selector">
                <div class="category-dropdown" x-data="{ open: false }" x-on:click.outside="open = false">
                    <button type="button" class="category-button" x-on:click="open = !open" :class="{ 'open': open }">
                        <i class="fas" :class="{
                            'fa-bug': '<?php echo e($category); ?>' === 'bug',
                            'fa-gamepad': '<?php echo e($category); ?>' === 'gameplay',
                            'fa-lightbulb': '<?php echo e($category); ?>' === 'suggestion',
                            'fa-question-circle': '<?php echo e($category); ?>' === 'other'
                        }"></i>
                        <span>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span x-show="'<?php echo e($category); ?>' === '<?php echo e($value); ?>'"><?php echo e($label); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </span>
                        <i class="fas fa-chevron-down dropdown-icon" :class="{ 'rotated': open }"></i>
                    </button>

                    <div class="category-options" x-show="open" x-transition style="display: none;">
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="category-option <?php echo e($category === $value ? 'active' : ''); ?>" 
                                    wire:click="$set('category', '<?php echo e($value); ?>')" 
                                    x-on:click="open = false">
                                <div class="category-option-info">
                                    <i class="fas <?php echo e($value === 'bug' ? 'fa-bug' : 
                                        ($value === 'gameplay' ? 'fa-gamepad' : 
                                        ($value === 'suggestion' ? 'fa-lightbulb' : 'fa-question-circle'))); ?>"></i>
                                    <div class="category-details">
                                        <span class="category-name"><?php echo e($label); ?></span>
                                    </div>
                                </div>
                                <?php if($category === $value): ?>
                                <div class="category-badge">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
            <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        
        <div class="form-group mb-4">
            <label for="problem" class="form-label">
                <i class="fas fa-comment-alt"></i> Description du problème
            </label>
            <textarea wire:model="problem" id="problem" class="form-control" rows="5" placeholder="Décrivez le problème rencontré en détail..."></textarea>
            <?php $__errorArgs = ['problem'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" wire:click="$dispatch('closeModal')">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="submitReport">
                <i class="fas fa-paper-plane" wire:loading.class.remove="fa-paper-plane" wire:loading.class.add="fa-spinner fa-spin" wire:target="submitReport"></i>
                <span wire:loading.remove wire:target="submitReport">Envoyer</span>
                <span wire:loading wire:target="submitReport">Envoi en cours...</span>
            </button>
        </div>
    </form>
</div>
<?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/modal/report-problem.blade.php ENDPATH**/ ?>