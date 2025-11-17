<x-layouts.game title="Alliance" >
    @php
        $user = auth()->user();
        $alliance = $user?->alliance;
        $userAllianceMember = $user?->allianceMember;
    @endphp
    <div page="alliance">
        <div class="alliance-container">
            <!-- Navigation Tabs (persistants via layout) -->
            <div class="alliance-tabs">
            @if($alliance)
                <a wire:navigate.hover 
                   href="{{ route('game.alliance.overview') }}"
                   class="alliance-tab {{ request()->routeIs('game.alliance.overview') ? 'active' : '' }}">
                    📊 Vue d'ensemble
                </a>
                <a wire:navigate.hover 
                   href="{{ route('game.alliance.members') }}"
                   class="alliance-tab {{ request()->routeIs('game.alliance.members') ? 'active' : '' }}">
                    👥 Membres
                </a>
                <a wire:navigate.hover 
                   href="{{ route('game.alliance.bank') }}"
                   class="alliance-tab {{ request()->routeIs('game.alliance.bank') ? 'active' : '' }}">
                    🏦 Banque
                </a>
                @if($userAllianceMember && $userAllianceMember->hasPermission('manage_ranks'))
                    <a wire:navigate.hover 
                       href="{{ route('game.alliance.ranks') }}"
                       class="alliance-tab {{ request()->routeIs('game.alliance.ranks') ? 'active' : '' }}">
                        🎖️ Rangs
                    </a>
                @endif
                @if($userAllianceMember && $userAllianceMember->hasPermission('manage_members'))
                    <a wire:navigate.hover 
                       href="{{ route('game.alliance.management') }}"
                       class="alliance-tab {{ request()->routeIs('game.alliance.management') ? 'active' : '' }}">
                        ⚙️ Gestion Membres
                    </a>
                @endif
                @if($userAllianceMember && $userAllianceMember->hasPermission('manage_applications'))
                    <a wire:navigate.hover 
                       href="{{ route('game.alliance.applications') }}"
                       class="alliance-tab {{ request()->routeIs('game.alliance.applications') ? 'active' : '' }}">
                        📝 Candidatures
                    </a>
                @endif
                <a wire:navigate.hover 
                   href="{{ route('game.alliance.wars') }}"
                   class="alliance-tab {{ request()->routeIs('game.alliance.wars') ? 'active' : '' }}">
                    ⚔️ Guerres
                </a>
                @if($userAllianceMember && $userAllianceMember->hasPermission('manage_alliance'))
                    <a wire:navigate.hover 
                       href="{{ route('game.alliance.technologies') }}"
                       class="alliance-tab {{ request()->routeIs('game.alliance.technologies') ? 'active' : '' }}">
                        🔬 Technologies
                    </a>
                @endif
            @else
                <a wire:navigate.hover 
                   href="{{ route('game.alliance.search') }}"
                   class="alliance-tab {{ request()->routeIs('game.alliance.search') ? 'active' : '' }}">
                    🔍 Rechercher
                </a>
                <a wire:navigate.hover 
                   href="{{ route('game.alliance.create') }}"
                   class="alliance-tab {{ request()->routeIs('game.alliance.create') ? 'active' : '' }}">
                    ➕ Créer
                </a>
            @endif
            </div>

            <div class="alliance-content">
                {{ $slot }}
            </div>
        </div>
    </div>
</x-layouts.game>