<div page="auth">
    <div class="auth-card">
        <h2 class="auth-title">Mot de passe oublié</h2>
        <p class="auth-subtitle">Saisissez votre email pour recevoir un lien de réinitialisation</p>

        <?php if($emailSent): ?>
            <div class="alert alert-success">
                <strong>Email envoyé !</strong><br>
                Un lien de réinitialisation a été envoyé à votre adresse email.
                Vérifiez votre boîte de réception et vos spams.
            </div>
            
            <div class="auth-links">
                <a href="<?php echo e(route('login')); ?>" class="auth-link" wire:navigate>
                    Retour à la connexion
                </a>
            </div>
        <?php else: ?>
            <form wire:submit="sendResetLink">
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

                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <span>Envoyer le lien</span>
                </button>
            </form>

            <div class="auth-links">
                <a href="<?php echo e(route('login')); ?>" class="auth-link" wire:navigate.hover>
                    Retour à la connexion
                </a>
                <br />
                <br />
                <a href="<?php echo e(route('register')); ?>" class="auth-link" wire:navigate.hover>
                    Créer un compte
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/auth/forgot-password.blade.php ENDPATH**/ ?>