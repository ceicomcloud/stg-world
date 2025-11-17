<div page="customization-page">
    <div class="customization-container">
        <div class="customization-header">
            <h1 class="customization-title"><i class="fas fa-paint-brush"></i> Personnalisation des unités et vaisseaux</h1>
            <p class="customization-subtitle">Vue d’ensemble de vos planètes, ressources et forces</p>
        </div>

        <?php if(empty($items)): ?>
            <p class="text-muted">Aucune unité ou vaisseau disponible sur votre planète actuelle.</p>
        <?php else: ?>
            <div class="items-grid">
                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="item-card">
                        <img src="<?php echo e($item['default_icon']); ?>" alt="Icône" class="item-icon" />
                        <div class="item-body">
                            <div class="item-title"><?php echo e($item['default_name']); ?></div>
                            <div class="item-type"><?php echo e($item['type'] === \App\Models\Template\TemplateBuild::TYPE_SHIP ? 'Vaisseau' : 'Unité'); ?></div>
                        </div>
                        <div class="item-actions">
                            <button class="btn btn-primary" wire:click="edit(<?php echo e($item['template_id']); ?>)" wire:loading.attr="disabled" wire:target="edit">Modifier</button>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <?php if($editing): ?>
            <div class="edit-modal">
                <div class="edit-card">
                    <h3>Modifier l'élément</h3>
                    <div class="form-group">
                        <label>Nouveau nom (optionnel)</label>
                        <input type="text" class="form-control" wire:model.defer="form.display_name" maxlength="50" />
                    </div>
                    <div class="form-group">
                        <label>Nouvelle icône (PNG/JPG, max 512KB)</label>
                        <input type="file" class="form-control" wire:model="form.icon" accept="image/*" />
                        <div wire:loading wire:target="form.icon" class="uploading-indicator">
                            Téléversement en cours...
                        </div>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn-success" wire:click="save" wire:loading.attr="disabled" wire:target="save">Enregistrer</button>
                        <button class="btn btn-secondary" wire:click="$set('editing', null)">Annuler</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/customization.blade.php ENDPATH**/ ?>