<div page="dashboard">
    <div class="dashboard-content">
        <div class="central-menu">
            <div class="menu-card">
                <div class="menu-header">
                    <h1 class="game-title">
                        <i class="fas fa-circle-notch stargate-spin"></i>
                        {{ config('app.name') }}
                    </h1>
                </div>

                <div class="user-info">
                    <div class="user-avatar">
                        @if(!empty($avatarUrl))
                            <img src="{{ $avatarUrl }}" alt="Avatar de {{ $user->name }}" style="width:64px; height:64px; border-radius:50%; object-fit:cover;" />
                        @else
                            <i class="fas fa-user-astronaut"></i>
                        @endif
                    </div>
                    <div class="user-details">
                        <h2 class="username">{{ $user->name }}</h2>
                        <p class="user-email">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="main-action">
                    <button class="btn-play" wire:click="handleMainAction" wire:loading.attr="disabled">
                        @if($user->main_planet_id)
                            <i class="fas fa-play"></i>
                        @else
                            <i class="fas fa-user-plus"></i>
                        @endif
                        <span>{{ $this->getMainButtonText() }}</span>
                    </button>
                </div>

                <div class="secondary-menu">
                    <button class="btn-secondary" wire:click="goToProfile">
                        <i class="fas fa-user"></i>
                        Profil
                    </button>
                    <button class="btn-secondary" wire:click="goToSettings">
                        <i class="fas fa-cog"></i>
                        Paramètres
                    </button>
                    <button class="btn-secondary" wire:click="logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Déconnexion
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>