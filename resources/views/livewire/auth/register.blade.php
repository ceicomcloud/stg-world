<div page="auth">
    <div class="auth-card">
        <h2 class="auth-title">Inscription</h2>
        <p class="auth-subtitle">Créez votre compte {{ config('app.name') }}</p>

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
                    class="form-control @error('name') is-invalid @enderror" 
                    id="name" 
                    wire:model="name" 
                    placeholder="Votre pseudo" 
                    required 
                />
                @error('name')
                    <div class="error-message">
                        {{ $message }}
                    </div>
                @enderror
            </div>

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

            <div class="form-group">
                <label for="password" class="form-label">Mot de passe</label>
                <input 
                    type="password" 
                    class="form-control @error('password') is-invalid @enderror" 
                    id="password" 
                    wire:model.live="password" 
                    placeholder="••••••••" 
                    required 
                />
                
                <!-- Indicateur de force du mot de passe -->
                @if(strlen($password) > 0)
                    <div class="password-strength">
                        <div class="password-strength-bar">
                            <div 
                                class="password-strength-progress {{ $this->getPasswordStrengthClass() }}" 
                                style="width: {{ $this->getPasswordStrength() }}%"
                            ></div>
                        </div>
                        <div class="password-strength-text">
                            {{ $this->getPasswordStrengthText() }}
                        </div>
                    </div>
                    <div class="password-tips">
                        <ul>
                            <li class="{{ strlen($password) >= 8 ? 'valid' : 'invalid' }}">
                                <i class="fas fa-{{ strlen($password) >= 8 ? 'check' : 'times' }}"></i> 
                                Au moins 8 caractères
                            </li>
                            <li class="{{ preg_match('/[A-Z]/', $password) ? 'valid' : 'invalid' }}">
                                <i class="fas fa-{{ preg_match('/[A-Z]/', $password) ? 'check' : 'times' }}"></i> 
                                Au moins une majuscule
                            </li>
                            <li class="{{ preg_match('/[0-9]/', $password) ? 'valid' : 'invalid' }}">
                                <i class="fas fa-{{ preg_match('/[0-9]/', $password) ? 'check' : 'times' }}"></i> 
                                Au moins un chiffre
                            </li>
                            <li class="{{ preg_match('/[^A-Za-z0-9]/', $password) ? 'valid' : 'invalid' }}">
                                <i class="fas fa-{{ preg_match('/[^A-Za-z0-9]/', $password) ? 'check' : 'times' }}"></i> 
                                Au moins un caractère spécial
                            </li>
                        </ul>
                    </div>
                @endif
                @error('password')
                    <div class="error-message">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                <input 
                    type="password" 
                    class="form-control @error('password_confirmation') is-invalid @enderror" 
                    id="password_confirmation" 
                    wire:model="password_confirmation" 
                    placeholder="••••••••" 
                    required 
                />
                @error('password_confirmation')
                    <div class="error-message">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            
            <!-- Sélection de faction -->
            <div class="form-group">
                <label for="faction_id" class="form-label">Choisir une faction</label>
                <div class="faction-selection">
                    @foreach($factions as $faction)
                        <div class="faction-option {{ $faction_id == $faction->id ? 'selected' : '' }}" wire:click="$set('faction_id', {{ $faction->id }})">
                            <div class="faction-icon" style="--faction-color: {{ $faction->color_code }}">
                                <i class="fas fa-{{ $faction->icon }}"></i>
                            </div>
                            <div class="faction-info">
                                <div class="faction-name">{{ $faction->name }}</div>
                                <div class="faction-description">{{ Str::limit($faction->description, 60) }}</div>
                            </div>
                            @if($faction_id == $faction->id)
                                <div class="faction-selected">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @error('faction_id')
                    <div class="error-message">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Captcha -->
            <div class="form-group">
                <label class="form-label">Captcha</label>
                <div class="captcha-row" style="display:flex;align-items:center;gap:.75rem;">
                    <span class="captcha-question">Combien font {{ $captchaA }} + {{ $captchaB }} ?</span>
                    <button type="button" class="btn btn-link" wire:click="generateCaptcha" aria-label="Rafraîchir le captcha">
                        <i class="fas fa-rotate"></i>
                    </button>
                </div>
                <input 
                    type="text" 
                    inputmode="numeric"
                    class="form-control @error('captchaAnswer') is-invalid @enderror" 
                    wire:model="captchaAnswer" 
                    placeholder="Votre réponse" 
                    required 
                />
                @error('captchaAnswer')
                    <div class="error-message">
                        {{ $message }}
                    </div>
                @enderror
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
            <a href="{{ route('login') }}" class="auth-link" wire:navigate.hover>
                Déjà un compte ? Se connecter
            </a>
        </div>
    </div>
</div>
