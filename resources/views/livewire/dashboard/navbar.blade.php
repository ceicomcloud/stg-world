<div layout="dashboardNavbar">
    <nav class="navbar">
        <div class="navbar-container">
            <!-- Navigation Links -->
            <div class="navbar-nav">
                <a href="{{ route('dashboard.index') }}" wire:navigate class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    Tableau de bord
                </a>
                <a href="{{ route('dashboard.profile') }}" wire:navigate class="nav-link {{ request()->routeIs('dashboard.profile') ? 'active' : '' }}">
                    <i class="fas fa-user"></i>
                    Profil
                </a>
                <a href="{{ route('dashboard.settings') }}" wire:navigate class="nav-link {{ request()->routeIs('dashboard.settings') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i>
                    Paramètres
                </a>
            </div>
        </div>
    </nav>
</div>