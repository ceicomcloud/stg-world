<div page="auth">
    <div class="auth-card">
        <h2 class="auth-title">Connexion</h2>
        <p class="auth-subtitle">Accédez à votre compte <?php echo e(config('app.name')); ?></p>

        <form wire:submit="login">
            <div class="form-group">
                <label for="email" class="form-label">Identifiant</label>
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
                    wire:model="password" 
                    placeholder="••••••••" 
                    required 
                />
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

            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                <span>Se connecter</span>
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
            <a href="<?php echo e(route('forgot-password')); ?>" class="auth-link" wire:navigate.hover>
                Mot de passe oublié ?
            </a>
            <br />
            <a href="<?php echo e(route('register')); ?>" class="auth-link" wire:navigate.hover>
                Créer un compte
            </a>
        </div>
    </div>
</div>
<?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/auth/login.blade.php ENDPATH**/ ?>