<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'Confirmation',
    'message' => '',
    'confirmText' => 'Confirmer',
    'cancelText' => 'Annuler',
    'icon' => 'fas fa-question-circle',
    // Actions Livewire sous forme de chaînes: e.g. 'performBreak' ou 'dismissModals'
    'onConfirm' => null,
    'onCancel' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'title' => 'Confirmation',
    'message' => '',
    'confirmText' => 'Confirmer',
    'cancelText' => 'Annuler',
    'icon' => 'fas fa-question-circle',
    // Actions Livewire sous forme de chaînes: e.g. 'performBreak' ou 'dismissModals'
    'onConfirm' => null,
    'onCancel' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div page="modal"
     x-data="{ open: <?php if ((object) ($attributes->wire('model')) instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e($attributes->wire('model')->value()); ?>')<?php echo e($attributes->wire('model')->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e($attributes->wire('model')); ?>')<?php endif; ?>.live }"
     x-show="open"
     x-cloak
>
    <div class="building-modal show">
        <div class="modal-content" x-on:click.stop>
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="<?php echo e($icon); ?>"></i>
                    <?php echo e($title); ?>

                </h5>
                <button class="modal-close" x-on:click="open = false" <?php if($onCancel): ?> wire:click="<?php echo e($onCancel); ?>" <?php endif; ?>>
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <?php if($message): ?>
                    <p><?php echo e($message); ?></p>
                <?php endif; ?>

                <?php echo e($slot); ?>


                <div class="planet-actions">
                    <button class="action-btn secondary" x-on:click="open = false" <?php if($onCancel): ?> wire:click="<?php echo e($onCancel); ?>" <?php endif; ?>>
                        <?php echo e($cancelText); ?>

                    </button>
                    <button class="action-btn danger" <?php if($onConfirm): ?> wire:click="<?php echo e($onConfirm); ?>" <?php endif; ?> wire:loading.attr="disabled">
                        <i class="fas fa-check"></i> <?php echo e($confirmText); ?>

                    </button>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/components/input/modal-confirmation.blade.php ENDPATH**/ ?>