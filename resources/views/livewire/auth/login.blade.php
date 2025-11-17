<div page="auth">
    <div class="auth-card">
        <h2 class="auth-title">Connexion</h2>
        <p class="auth-subtitle">Accédez à votre compte {{ config('app.name') }}</p>

        <form wire:submit="login">
            <div class="form-group">
                <label for="email" class="form-label">Identifiant</label>
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

            <div class="form-group">
                <label for="password" class="form-label">Mot de passe</label>
                <input 
                    type="password" 
                    class="form-control @error('password') is-invalid @enderror" 
                    id="password" 
                    wire:model="password" 
                    placeholder="••••••••" 
                    required 
                />
                @error('password')
                    <div class="error-message">
                        {{ $message }}
                    </div>
                @enderror
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
            <a href="{{ route('forgot-password') }}" class="auth-link" wire:navigate.hover>
                Mot de passe oublié ?
            </a>
            <br />
            <a href="{{ route('register') }}" class="auth-link" wire:navigate.hover>
                Créer un compte
            </a>
        </div>
    </div>
</div>
