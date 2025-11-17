<nav class="game-navbar" layout="gamenavbar" x-data>
    @php
        $truceEnabled = \App\Models\Server\ServerConfig::get('truce_enabled');
        $truceMessage = \App\Models\Server\ServerConfig::get('truce_message');
    @endphp
    @if($truceEnabled)
        <div class="truce-banner" role="status" aria-live="polite">
            <div class="truce-banner__inner">
                <i class="fas fa-handshake"></i>
                <span>{{ $truceMessage ?? 'Trêve active: certaines actions sont temporairement désactivées.' }}</span>
            </div>
        </div>
    @endif

    <!-- Mobile Topbar - ALWAYS VISIBLE ON MOBILE -->
    <div class="mobile-topbar" x-data>
        <div class="mobile-topbar-left">
            <button x-on:click="$dispatch('toggle-drawer')" class="mobile-nav-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <div class="mobile-topbar-center">
            <span class="mobile-player-name">{{ Auth::user()->username }}</span>
        </div>
        
        <div class="mobile-topbar-right">
            <!-- Social buttons -->
            <a href="{{ route('game.chatbox') }}" class="mobile-nav-btn {{ request()->routeIs('game.chatbox') ? 'active' : '' }}">
                <i class="fas fa-comments"></i>
                @if(($unreadChatCount ?? 0) > 0)<span class="message-count">{{ ($unreadChatCount ?? 0) > 9 ? '9+' : ($unreadChatCount ?? 0) }}</span>@endif
            </a>
            <a href="{{ route('game.private') }}" class="mobile-nav-btn {{ request()->routeIs('game.private') ? 'active' : '' }}">
                <i class="fas fa-envelope"></i>
                @if($unreadMessagesCount > 0)<span class="message-count">{{ $unreadMessagesCount }}</span>@endif
            </a>
            <a href="{{ route('game.forum') }}" class="mobile-nav-btn {{ request()->routeIs('game.forum') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
            </a>
            
            <!-- Profile dropdown -->
            <div class="mobile-nav-item dropdown" x-data="{ open: false }" x-on:click.outside="open = false">
                <button class="mobile-nav-btn" x-on:click="open = !open">
                    <i class="fas fa-user"></i>
                    <i class="fas fa-chevron-down dropdown-icon" :class="{ 'rotated': open }"></i>
                </button>
                
                <div class="dropdown-menu mobile-dropdown-menu" x-show="open" x-transition x-cloak>
                    <div class="mobile-dropdown-header">
                        <i class="fas fa-user-circle"></i>
                        <span>{{ Auth::user()->username }}</span>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('game.manage-players') }}" wire:navigate.hover class="dropdown-item">
                        <i class="fas fa-user-cog"></i>
                        Gestion Joueurs
                    </a>
                    <a href="{{ route('game.inventory') }}" wire:navigate.hover class="dropdown-item">
                        <i class="fas fa-boxes"></i>
                        Inventaire
                    </a>
                    <a href="{{ route('dashboard.index') }}" wire:navigate.hover class="dropdown-item">
                        <i class="fas fa-home"></i>
                        Comptes
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('dashboard.profile') }}" wire:navigate.hover class="dropdown-item">
                        <i class="fas fa-user-edit"></i>
                        Mon Profil
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i>
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop Navbar - HIDDEN ON MOBILE -->
    <div class="desktop-navbar">
        <div class="navbar-container">
            <!-- Navigation Links -->
            <div class="navbar-nav">
                <a href="{{ route('game.index') }}" wire:navigate.hover class="nav-link {{ request()->routeIs('game.index') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Accueil</span>
                </a>

                <a href="{{ route('game.mission.index') }}" wire:navigate.hover class="nav-link {{ request()->routeIs('game.mission.index') ? 'active' : '' }}">
                    <i class="fas fa-bullseye"></i>
                    <span>Mission</span>
                </a>

                <a href="{{ route('game.construction.type', ['type' => 'building']) }}" wire:navigate.hover class="nav-link {{ request()->routeIs('game.construction.type') ? 'active' : '' }}">
                    <i class="fas fa-hammer"></i>
                    <span>Construction</span>
                </a>

                <a href="{{ route('game.technology') }}" wire:navigate.hover class="nav-link {{ request()->routeIs('game.technology') ? 'active' : '' }}">
                    <i class="fas fa-flask"></i>
                    <span>Technologies</span>
                </a>

                <a href="{{ route('game.galaxy') }}" wire:navigate.hover class="nav-link {{ request()->routeIs('game.galaxy') ? 'active' : '' }}">
                    <i class="fas fa-star"></i>
                    <span>Galaxie</span>
                </a>

                <a href="{{ route('game.empire') }}" wire:navigate.hover class="nav-link {{ request()->routeIs('game.empire') ? 'active' : '' }}">
                    <i class="fas fa-globe"></i>
                    <span>Empire</span>
                </a>

                <a href="{{ route('game.relations') }}" wire:navigate.hover class="nav-link {{ request()->routeIs('game.relations') ? 'active' : '' }}">
                    <i class="fas fa-handshake"></i>
                    <span>Relations</span>
                </a>

                <a href="{{ route('game.ranking') }}" wire:navigate.hover class="nav-link {{ request()->routeIs('game.ranking') ? 'active' : '' }}">
                    <i class="fas fa-trophy"></i>
                    <span>Classement</span>
                </a>

                <a href="{{ route('game.alliance.overview') }}" wire:navigate.hover class="nav-link {{ request()->routeIs('game.alliance.overview') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt"></i>
                    <span>Alliance</span>
                </a>

                <a href="{{ route('game.bunker') }}" wire:navigate.hover class="nav-link {{ request()->routeIs('game.bunker') ? 'active' : '' }}">
                    <i class="fas fa-warehouse"></i>
                    <span>Bunker</span>
                </a>

                <a href="{{ route('game.chatbox') }}" wire:navigate.hover class="nav-link {{ request()->routeIs('game.chatbox') ? 'active' : '' }}">
                    <i class="fas fa-comments"></i>
                    <span>Chat @if(($unreadChatCount ?? 0) > 0)<span class="message-count">{{ ($unreadChatCount ?? 0) > 9 ? '9+' : ($unreadChatCount ?? 0) }}</span>@endif</span>
                </a>

                <a href="{{ route('game.private') }}" wire:navigate.hover class="nav-link {{ request()->routeIs('game.private') ? 'active' : '' }}">
                    <i class="fas fa-envelope"></i>
                    <span>Messages @if($unreadMessagesCount > 0)<span class="message-count">{{ $unreadMessagesCount }}</span>@endif</span>
                </a>

                <a href="{{ route('game.forum') }}" wire:navigate.hover class="nav-link {{ request()->routeIs('game.forum') ? 'active' : '' }}">
                    <i class="fas fa-comments"></i>
                    <span>Forum</span>
                </a>

                <!-- Lien Discord (ouvre dans un nouvel onglet) -->
                <a href="https://discord.gg/UpBp2x6VPV" class="nav-link" target="_blank" rel="noopener">
                    <i class="fab fa-discord"></i>
                    <span>Discord</span>
                </a>

                <!-- Bouton Profil avec dropdown -->
                <div class="nav-item dropdown" x-data="{ open: false }" x-on:click.outside="open = false">
                    <button class="nav-link" x-on:click="open = !open">
                        <i class="fas fa-user"></i>
                        <span>{{ Auth::user()->username ?? 'Profil' }}</span>
                        <i class="fas fa-chevron-down dropdown-icon" :class="{ 'rotated': open }"></i>
                    </button>
                    
                    <div class="dropdown-menu" x-show="open" x-transition x-cloak>
                        <a href="{{ route('game.manage-players') }}" wire:navigate.hover class="dropdown-item">
                            <i class="fas fa-user-cog"></i>
                            Gestion Joueurs
                        </a>
                        <a href="{{ route('game.inventory') }}" wire:navigate.hover class="dropdown-item">
                            <i class="fas fa-boxes"></i>
                            Inventaire
                        </a>
                        <a href="{{ route('dashboard.index') }}" wire:navigate.hover class="dropdown-item">
                            <i class="fas fa-home"></i>
                            Comptes
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('dashboard.profile') }}" wire:navigate.hover class="dropdown-item">
                            <i class="fas fa-user-edit"></i>
                            Mon Profil
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>