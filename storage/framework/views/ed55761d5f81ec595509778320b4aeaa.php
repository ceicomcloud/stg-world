<div page="settings">
    <div class="settings-content">
        <div class="settings-container">
            <div class="settings-header">
                <h1 class="settings-title">
                    <i class="fas fa-cog"></i>
                    Paramètres
                </h1>
                <p class="settings-subtitle">Gérez vos préférences et informations personnelles</p>
            </div>

            <div class="settings-navbar">
                <div class="nav-item <?php echo e($activeTab === 'avatar' ? 'active' : ''); ?>" wire:click="setActiveTab('avatar')">
                    <i class="fas fa-user-circle"></i>
                    <span>Avatar</span>
                </div>
                <div class="nav-item <?php echo e($activeTab === 'personnel' ? 'active' : ''); ?>" wire:click="setActiveTab('personnel')">
                    <i class="fas fa-user-edit"></i>
                    <span>Personnel</span>
                </div>
                <div class="nav-item <?php echo e($activeTab === 'apparence' ? 'active' : ''); ?>" wire:click="setActiveTab('apparence')">
                    <i class="fas fa-palette"></i>
                    <span>Apparence</span>
                </div>
            </div>

            <?php if($activeTab === 'avatar'): ?>
                <div class="tab-content">
                    <div class="content-section">
                        <h2 class="section-title">
                            <i class="fas fa-user-circle"></i>
                            Gestion de l'avatar
                        </h2>

                        <div class="avatar-section">
                            <div class="avatar-preview">
                                <img src="<?php echo e($this->avatarUrl); ?>" alt="Avatar" class="avatar-image" />
                                <div class="avatar-overlay">
                                    <button class="btn-refresh" wire:click="refreshAvatar">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="avatar-info">
                                <h3 class="avatar-title">Avatar actuel</h3>
                                <p class="avatar-description">Vous pouvez utiliser un avatar personnalisé (stocké dans <code>public/avatars/<?php echo e($user->id); ?>/</code>) ou Gravatar via votre email. Si aucun avatar personnalisé n'est présent, Gravatar est utilisé par défaut.</p>
                                <div class="email-display">
                                    <i class="fas fa-envelope"></i>
                                    <span><?php echo e($user->email); ?></span>
                                </div>

                                <div class="avatar-actions">
                                    <button class="btn btn-primary" wire:click="refreshAvatar">
                                        <i class="fas fa-sync-alt"></i>
                                        Actualiser l'avatar
                                    </button>
                                    <a href="https://gravatar.com" target="_blank" class="btn btn-secondary">
                                        <i class="fas fa-external-link-alt"></i>
                                        Modifier sur Gravatar
                                    </a>
                                </div>

                                <hr class="settings-separator" />

                                <div class="custom-avatar-upload">
                                    <h3 class="avatar-title">Uploader un avatar personnalisé</h3>
                                    <p class="avatar-description">Formats acceptés: JPG, JPEG, PNG, WEBP. Taille max: 2MB.</p>
                                    <div class="upload-controls">
                                        <input type="file" wire:model="avatarUpload" accept="image/*" class="form-input" />
                                        <?php $__errorArgs = ['avatarUpload'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="form-error">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <?php echo e($message); ?>

                                            </div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        <button class="btn btn-primary" wire:click="uploadAvatar" wire:loading.attr="disabled">
                                            <i class="fas fa-upload"></i>
                                            Importer l'avatar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($activeTab === 'personnel'): ?>
                <div class="tab-content">
                    <div class="content-section">
                        <h2 class="section-title">
                            <i class="fas fa-user-edit"></i>
                            Informations personnelles
                        </h2>

                        <div class="form-container">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user"></i>
                                    Pseudo
                                </label>
                                <input type="text" wire:model="name" <?php echo e(!$this->canChangeUsername ? 'disabled' : ''); ?> class="form-input" placeholder="Votre pseudo" />
                                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="form-error">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <?php echo e($message); ?>

                                    </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <?php if(!$this->canChangeUsername): ?>
                                    <div class="username-restriction">
                                        <i class="fas fa-clock"></i>
                                        <span>Prochaine modification possible dans <?php echo e($this->daysUntilUsernameChange); ?> jours</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Adresse email
                                </label>
                                <input type="email" value="<?php echo e($user->email); ?>" disabled class="form-input disabled" placeholder="Votre email" />
                                <div class="form-help">
                                    <i class="fas fa-info-circle"></i>
                                    L'email ne peut pas être modifié
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-lock"></i>
                                    Nouveau mot de passe
                                </label>
                                <input type="password" wire:model="password" placeholder="Laissez vide pour ne pas modifier" class="form-input" />
                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="form-error">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <?php echo e($message); ?>

                                    </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-lock"></i>
                                    Confirmer le mot de passe
                                </label>
                                <input type="password" wire:model="password_confirmation" placeholder="Confirmez le nouveau mot de passe" class="form-input" />
                            </div>

                            <div class="form-group">
                                <button class="btn btn-primary" wire:click="savePersonalSettings" wire:loading.attr="disabled">
                                    <span>
                                        <i class="fas fa-save"></i>
                                        Sauvegarder les informations personnelles
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($activeTab === 'apparence'): ?>
                <div class="tab-content">
                    <div class="content-section">
                        <h2 class="section-title">
                            <i class="fas fa-palette"></i>
                            Personnalisation de l'apparence
                        </h2>

                        <div class="appearance-section">
                            <div class="appearance-group">
                                <h3 class="group-title">
                                    <i class="fas fa-image"></i>
                                    Fond d'écran
                                </h3>
                                <p class="group-description">
                                    Choisissez un fond d'écran pour personnaliser votre expérience
                                </p>

                                <div class="background-grid">
                                    <?php $__currentLoopData = $backgroundOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div
                                            class="background-option <?php echo e($selectedBackground === $bg['id'] ? 'selected' : ''); ?>"
                                            :style="'background-image: url(<?php echo e($bg['preview']); ?>)'"
                                            wire:click="selectBackground('<?php echo e($bg['id']); ?>')"
                                        >
                                            <div class="background-overlay">
                                                <div class="background-name"><?php echo e($bg['name']); ?></div>
                                                <?php if($selectedBackground === $bg['id']): ?>
                                                    <div class="background-check">
                                                        <i class="fas fa-check"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>

                            <div class="appearance-group">
                                <button class="btn btn-primary" wire:click="saveAppearanceSettings" wire:loading.attr="disabled">
                                    <span>
                                        <i class="fas fa-save"></i>
                                        Sauvegarder les préférences d'apparence
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/dashboard/setting.blade.php ENDPATH**/ ?>