<div>
    <?php if($isOpen): ?>
        <div page="modal">
            <div class="building-modal show" wire:click="close">
                <div class="modal-content" wire:click.stop>
                    <!-- En-tête du modal -->
                    <div class="modal-header">
                        <h2 class="modal-title">
                            <?php echo e($modalTitle); ?>

                        </h2>
                        <button class="modal-close" wire:click="close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Corps du modal -->
                    <div class="modal-body">
                        <?php if($modalComponent): ?>
                            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split($modalComponent, $modalData);

$__html = app('livewire')->mount($__name, $__params, $modalComponent . '-' . now(), $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/modal/base-modal.blade.php ENDPATH**/ ?>