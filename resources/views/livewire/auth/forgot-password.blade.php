<div page="auth">
    <div class="auth-card">
        <h2 class="auth-title">Mot de passe oublié</h2>
        <p class="auth-subtitle">Saisissez votre email pour recevoir un lien de réinitialisation</p>

        @if($emailSent)
            <div class="alert alert-success">
                <strong>Email envoyé !</strong><br>
                Un lien de réinitialisation a été envoyé à votre adresse email.
                Vérifiez votre boîte de réception et vos spams.
            </div>
            
            <div class="auth-links">
                <a href="{{ route('login') }}" class="auth-link" wire:navigate>
                    Retour à la connexion
                </a>
            </div>
        @else
            <form wire:submit="sendResetLink">
                <div class="form-group">
                    <label for="email" class="form-label">Adresse email</label>
                    <input 
                        type="email" 
                        class="form-control @error('email') is-invalid @enderror" 
                        id="email" 
                        wire:model="email" 
                        placeholder="votre.email@wos.fr" 
                        required 
                    />
                    @error('email')
                        <div class="error-message">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <span>Envoyer le lien</span>
                </button>
            </form>

            <div class="auth-links">
                <a href="{{ route('login') }}" class="auth-link" wire:navigate.hover>
                    Retour à la connexion
                </a>
                <br />
                <br />
                <a href="{{ route('register') }}" class="auth-link" wire:navigate.hover>
                    Créer un compte
                </a>
            </div>
        @endif
    </div>
</div>
