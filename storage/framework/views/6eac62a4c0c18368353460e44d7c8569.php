<div page="auth">
    <div class="auth-card">
        <h2 class="auth-title">Inscription</h2>
        <p class="auth-subtitle">Créez votre compte <?php echo e(config('app.name')); ?></p>

        <form wire:submit="register">
            <!-- Honeypot anti-bot -->
            <div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">
                <label for="hp">Ne pas remplir</label>
                <input type="text" id="hp" name="hp" wire:model="hp" tabindex="-1" autocomplete="off" />
            </div>
            <div class="form-group">
                <label for="name" class="form-label">Pseudo</label>
                <input 
                    type="text" 
                    class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                    id="name" 
                    wire:model="name" 
                    placeholder="Votre pseudo" 
                    required 
                />
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="error-message">
                        <?php echo e($message); ?>

                    </div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Adresse email</label>
                <input 
                    type="email" 
                    class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                    id="email" 
                    wire:model="email" 
                    placeholder="votre.email@wos.fr" 
                    required 
                />
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="error-message">
                        <?php echo e($message); ?>

                    </div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Mot de passe</label>
                <input 
                    type="password" 
                    class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                    id="password" 
                    wire:model.live="password" 
                    placeholder="••••••••" 
                    required 
                />
                
                <!-- Indicateur de force du mot de passe -->
                <?php if(strlen($password) > 0): ?>
                    <div class="password-strength">
                        <div class="password-strength-bar">
                            <div 
                                class="password-strength-progress <?php echo e($this->getPasswordStrengthClass()); ?>" 
                                style="width: <?php echo e($this->getPasswordStrength()); ?>%"
                            ></div>
                        </div>
                        <div class="password-strength-text">
                            <?php echo e($this->getPasswordStrengthText()); ?>

                        </div>
                    </div>
                    <div class="password-tips">
                        <ul>
                            <li class="<?php echo e(strlen($password) >= 8 ? 'valid' : 'invalid'); ?>">
                                <i class="fas fa-<?php echo e(strlen($password) >= 8 ? 'check' : 'times'); ?>"></i> 
                                Au moins 8 caractères
                            </li>
                            <li class="<?php echo e(preg_match('/[A-Z]/', $password) ? 'valid' : 'invalid'); ?>">
                                <i class="fas fa-<?php echo e(preg_match('/[A-Z]/', $password) ? 'check' : 'times'); ?>"></i> 
                                Au moins une majuscule
                            </li>
                            <li class="<?php echo e(preg_match('/[0-9]/', $password) ? 'valid' : 'invalid'); ?>">
                                <i class="fas fa-<?php echo e(preg_match('/[0-9]/', $password) ? 'check' : 'times'); ?>"></i> 
                                Au moins un chiffre
                            </li>
                            <li class="<?php echo e(preg_match('/[^A-Za-z0-9]/', $password) ? 'valid' : 'invalid'); ?>">
                                <i class="fas fa-<?php echo e(preg_match('/[^A-Za-z0-9]/', $password) ? 'check' : 'times'); ?>"></i> 
                                Au moins un caractère spécial
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="error-message">
                        <?php echo e($message); ?>

                    </div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                <input 
                    type="password" 
                    class="form-control <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                    id="password_confirmation" 
                    wire:model="password_confirmation" 
                    placeholder="••••••••" 
                    required 
                />
                <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="error-message">
                        <?php echo e($message); ?>

                    </div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            
            <!-- Sélection de faction -->
            <div class="form-group">
                <label for="faction_id" class="form-label">Choisir une faction</label>
                <div class="faction-selection">
                    <?php $__currentLoopData = $factions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="faction-option <?php echo e($faction_id == $faction->id ? 'selected' : ''); ?>" wire:click="$set('faction_id', <?php echo e($faction->id); ?>)">
                            <div class="faction-icon" style="--faction-color: <?php echo e($faction->color_code); ?>">
                                <i class="fas fa-<?php echo e($faction->icon); ?>"></i>
                            </div>
                            <div class="faction-info">
                                <div class="faction-name"><?php echo e($faction->name); ?></div>
                                <div class="faction-description"><?php echo e(Str::limit($faction->description, 60)); ?></div>
                            </div>
                            <?php if($faction_id == $faction->id): ?>
                                <div class="faction-selected">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php $__errorArgs = ['faction_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="error-message">
                        <?php echo e($message); ?>

                    </div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Captcha -->
            <div class="form-group">
                <label class="form-label">Captcha</label>
                <div class="captcha-row" style="display:flex;align-items:center;gap:.75rem;">
                    <span class="captcha-question">Combien font <?php echo e($captchaA); ?> + <?php echo e($captchaB); ?> ?</span>
                    <button type="button" class="btn btn-link" wire:click="generateCaptcha" aria-label="Rafraîchir le captcha">
                        <i class="fas fa-rotate"></i>
                    </button>
                </div>
                <input 
                    type="text" 
                    inputmode="numeric"
                    class="form-control <?php $__errorArgs = ['captchaAnswer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                    wire:model="captchaAnswer" 
                    placeholder="Votre réponse" 
                    required 
                />
                <?php $__errorArgs = ['captchaAnswer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="error-message">
                        <?php echo e($message); ?>

                    </div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                <span>Créer mon compte</span>
            </button>
        </form>

        <div class="social-login">
            <p class="social-login-text">Rejoignez la communauté</p>
            <a href="https://discord.gg/UpBp2x6VPV" class="btn btn-discord" target="_blank" rel="noopener">
                <i class="fab fa-discord"></i>
                Rejoindre notre Discord
            </a>
        </div>

        <div class="auth-links">
            <a href="<?php echo e(route('login')); ?>" class="auth-link" wire:navigate.hover>
                Déjà un compte ? Se connecter
            </a>
        </div>
    </div>
</div>
<?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/auth/register.blade.php ENDPATH**/ ?>