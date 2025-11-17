<div class="admin-sidebar {{ $isOpen ? 'open' : '' }}">
    <!-- En-tête du menu -->
    <div class="admin-sidebar-header">
        <div class="admin-logo">
            <i class="fas fa-circle-notch fa-spin"></i>
            <span>{{ config('app.name') }} Admin</span>
        </div>
        <button class="admin-sidebar-toggle" wire:click="toggleMenu">
            <i class="fas {{ $isOpen ? 'fa-times' : 'fa-bars' }}"></i>
        </button>
    </div>
    
    <!-- Navigation -->
    <nav class="admin-nav">
        @foreach($menuSections as $section)
            <div class="admin-nav-section">
                <div class="admin-nav-title">{{ $section['title'] }}</div>
                
                @foreach($section['items'] as $item)
                    <a href="{{ route($item['route']) }}" 
                       class="admin-nav-item {{ $this->isRouteActive($item['route']) ? 'active' : '' }}">
                        <i class="fas fa-{{ $item['icon'] }}"></i>
                        <span>{{ $item['name'] }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>
    
    <!-- Informations utilisateur -->
    <div class="admin-user-info">
        <div class="admin-user-avatar">
            {{ $this->getUserInitials() }}
        </div>
        <div class="admin-user-details">
            <div class="admin-user-name">{{ $user->name }}</div>
            <div class="admin-user-role">{{ $user->getRoleName() }}</div>
        </div>
    </div>
</div>