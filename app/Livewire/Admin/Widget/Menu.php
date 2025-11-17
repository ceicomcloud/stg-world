<?php

namespace App\Livewire\Admin\Widget;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Menu extends Component
{
    /**
     * Indique si le menu est ouvert (pour mobile)
     */
    public bool $isOpen = false;
    
    /**
     * Sections du menu d'administration
     */
    protected array $menuSections = [
        [
            'title' => 'Tableau de bord',
            'items' => [
                [
                    'name' => 'Dashboard',
                    'route' => 'admin.dashboard',
                    'icon' => 'tachometer-alt',
                ],
            ],
        ],
        [
            'title' => 'Gestion du jeu',
            'items' => [
                [
                    'name' => 'Utilisateurs',
                    'route' => 'admin.users',
                    'icon' => 'users',
                ],
                [
                    'name' => 'Planètes',
                    'route' => 'admin.planets',
                    'icon' => 'globe',
                ],
                [
                    'name' => 'Alliances',
                    'route' => 'admin.alliances',
                    'icon' => 'sitemap',
                ],
                [
                    'name' => 'Factions',
                    'route' => 'admin.factions',
                    'icon' => 'flag',
                ],
                [
                    'name' => 'Événements serveur',
                    'route' => 'admin.server-events',
                    'icon' => 'calendar-check',
                ],
                [
                    'name' => 'Forum',
                    'route' => 'admin.forum',
                    'icon' => 'comments',
                ],
                [
                    'name' => 'Messagerie',
                    'route' => 'admin.messaging',
                    'icon' => 'envelope',
                ],
            ],
        ],
        [
            'title' => 'Templates',
            'items' => [
                [
                    'name' => 'Ressources',
                    'route' => 'admin.templates.resources',
                    'icon' => 'coins',
                ],
                [
                    'name' => 'Bâtiments',
                    'route' => 'admin.templates.buildings',
                    'icon' => 'building',
                ],
                [
                    'name' => 'Planètes',
                    'route' => 'admin.templates.planets',
                    'icon' => 'globe',
                ],
                [
                    'name' => 'Badges',
                    'route' => 'admin.templates.badges',
                    'icon' => 'medal',
                ],
            ],
        ],
        [
            'title' => 'Configuration',
            'items' => [
                [
                    'name' => 'Paramètres',
                    'route' => 'admin.settings',
                    'icon' => 'cog',
                ],
                [
                    'name' => 'Options',
                    'route' => 'admin.options',
                    'icon' => 'sliders-h',
                ],
                [
                    'name' => 'Actualités',
                    'route' => 'admin.news',
                    'icon' => 'newspaper',
                ],
                [
                    'name' => 'Discord',
                    'route' => 'admin.discord',
                    'icon' => 'bullhorn',
                ],
                [
                    'name' => 'Logs',
                    'route' => 'admin.logs',
                    'icon' => 'clipboard-list',
                ],
                [
                    'name' => 'Jobs',
                    'route' => 'admin.jobs',
                    'icon' => 'tasks',
                ],
                [
                    'name' => 'Paiements',
                    'route' => 'admin.payments',
                    'icon' => 'file-invoice-dollar',
                ],
            ],
        ],
    ];
    
    /**
     * Basculer l'état du menu (pour mobile)
     */
    public function toggleMenu(): void
    {
        $this->isOpen = !$this->isOpen;
    }
    
    /**
     * Vérifier si une route est active
     */
    public function isRouteActive(string $route): bool
    {
        return request()->routeIs($route);
    }
    
    /**
     * Obtenir les initiales de l'utilisateur pour l'avatar
     */
    public function getUserInitials(): string
    {
        $user = Auth::user();
        $name = $user->name;
        
        $words = explode(' ', $name);
        $initials = '';
        
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        
        return substr($initials, 0, 2);
    }
    
    /**
     * Rendre le composant
     */
    public function render()
    {
        return view('livewire.admin.widget.menu', [
            'menuSections' => $this->menuSections,
            'user' => Auth::user(),
        ]);
    }
}