<div class="admin-discord-webhooks">
    <div class="admin-page-header">
        <h1>Discord Webhooks</h1>
        <div class="admin-page-actions"></div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Envoyer un message Discord</h2>
            <p class="admin-card-subtitle">Embeds, liens cliquables et mention @here.</p>
        </div>
        <div class="admin-card-body">
            <div class="admin-form-grid admin-form-grid-2">
                <div class="admin-form-group">
                    <label for="channel">Canal</label>
                    <select id="channel" class="admin-select" wire:model="channel">
                        <option value="annonces">Annonces</option>
                        <option value="informations">Informations</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label for="color">Couleur (hex)</label>
                    <input id="color" type="text" class="admin-input" wire:model="color" placeholder="#5865F2" />
                    <?php $__errorArgs = ['color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <div class="admin-form-group">
                <label for="title">Titre de l’embed</label>
                <input id="title" type="text" class="admin-input" wire:model="title" placeholder="Titre" />
                <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="admin-form-group">
                <label for="content">Contenu (markdown autorisé)</label>
                <textarea id="content" rows="6" class="admin-textarea" wire:model="content" placeholder="Texte de l’embed. Vous pouvez utiliser [lien](https://exemple.com) et la mise en forme."></textarea>
                <?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="admin-form-group">
                <label for="url">Lien de l’embed (optionnel)</label>
                <input id="url" type="url" class="admin-input" wire:model="url" placeholder="https://..." />
                <?php $__errorArgs = ['url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="admin-form-group admin-form-checkbox-group">
                <label for="mentionHere">
                    <input id="mentionHere" type="checkbox" wire:model="mentionHere" />
                    Mentionner <code>@here</code>
                </label>
            </div>

            <div class="admin-card-actions">
                <button type="button" class="admin-btn admin-btn-primary" wire:click="send">
                    <i class="fas fa-paper-plane"></i> Envoyer
                </button>
            </div>

            <?php if($statusMessage): ?>
                <div class="admin-alert <?php echo e($statusType === 'success' ? 'admin-alert-success' : 'admin-alert-danger'); ?>">
                    <i class="fas <?php echo e($statusType === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'); ?>"></i>
                    <?php echo e($statusMessage); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Astuce</h2>
        </div>
        <div class="admin-card-body">
            <p>Utilisez <code>[texte](https://lien)</code> pour insérer des liens cliquables dans l’embed. Les mentions <code>@here</code> sont autorisées via <code>allowed_mentions</code>.</p>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/admin/discord-webhooks.blade.php ENDPATH**/ ?>