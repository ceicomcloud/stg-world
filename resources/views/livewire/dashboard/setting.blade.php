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
                <div class="nav-item {{ $activeTab === 'avatar' ? 'active' : '' }}" wire:click="setActiveTab('avatar')">
                    <i class="fas fa-user-circle"></i>
                    <span>Avatar</span>
                </div>
                <div class="nav-item {{ $activeTab === 'personnel' ? 'active' : '' }}" wire:click="setActiveTab('personnel')">
                    <i class="fas fa-user-edit"></i>
                    <span>Personnel</span>
                </div>
                <div class="nav-item {{ $activeTab === 'apparence' ? 'active' : '' }}" wire:click="setActiveTab('apparence')">
                    <i class="fas fa-palette"></i>
                    <span>Apparence</span>
                </div>
            </div>

            @if($activeTab === 'avatar')
                <div class="tab-content">
                    <div class="content-section">
                        <h2 class="section-title">
                            <i class="fas fa-user-circle"></i>
                            Gestion de l'avatar
                        </h2>

                        <div class="avatar-section">
                            <div class="avatar-preview">
                                <img src="{{ $this->avatarUrl }}" alt="Avatar" class="avatar-image" />
                                <div class="avatar-overlay">
                                    <button class="btn-refresh" wire:click="refreshAvatar">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="avatar-info">
                                <h3 class="avatar-title">Avatar actuel</h3>
                                <p class="avatar-description">Vous pouvez utiliser un avatar personnalisé (stocké dans <code>public/avatars/{{ $user->id }}/</code>) ou Gravatar via votre email. Si aucun avatar personnalisé n'est présent, Gravatar est utilisé par défaut.</p>
                                <div class="email-display">
                                    <i class="fas fa-envelope"></i>
                                    <span>{{ $user->email }}</span>
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
                                        @error('avatarUpload')
                                            <div class="form-error">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
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
            @endif

            @if($activeTab === 'personnel')
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
                                <input type="text" wire:model="name" {{ !$this->canChangeUsername ? 'disabled' : '' }} class="form-input" placeholder="Votre pseudo" />
                                @error('name')
                                    <div class="form-error">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                                @if(!$this->canChangeUsername)
                                    <div class="username-restriction">
                                        <i class="fas fa-clock"></i>
                                        <span>Prochaine modification possible dans {{ $this->daysUntilUsernameChange }} jours</span>
                                    </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Adresse email
                                </label>
                                <input type="email" value="{{ $user->email }}" disabled class="form-input disabled" placeholder="Votre email" />
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
                                @error('password')
                                    <div class="form-error">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
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
            @endif

            @if($activeTab === 'apparence')
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
                                    @foreach($backgroundOptions as $bg)
                                        <div
                                            class="background-option {{ $selectedBackground === $bg['id'] ? 'selected' : '' }}"
                                            :style="'background-image: url({{ $bg['preview'] }})'"
                                            wire:click="selectBackground('{{ $bg['id'] }}')"
                                        >
                                            <div class="background-overlay">
                                                <div class="background-name">{{ $bg['name'] }}</div>
                                                @if($selectedBackground === $bg['id'])
                                                    <div class="background-check">
                                                        <i class="fas fa-check"></i>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
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
            @endif
        </div>
    </div>
</div>