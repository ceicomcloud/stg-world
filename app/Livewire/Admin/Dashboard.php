<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Planet\Planet;
use App\Models\User\UserLog;
use App\Models\Template\TemplateBuild;
use App\Models\Template\TemplatePlanet;
use App\Models\Template\TemplateResource;
use App\Models\Alliance\Alliance;
use App\Models\Faction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Layout('components.layouts.admin')]
class Dashboard extends Component
{
    /**
     * Nombre d'entrées de logs à afficher
     */
    public int $logLimit = 10;
    
    /**
     * Obtenir les statistiques du jeu
     */
    public function getGameStats(): array
    {
        return [
            'users' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'new' => User::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
                'in_vacation' => User::where('vacation_mode', true)->count(),
            ],
            'planets' => [
                'total' => Planet::count(),
                'colonized' => Planet::whereNotNull('user_id')->count(),
                'free' => TemplatePlanet::where('is_colonizable', true)->whereNotIn('id', Planet::pluck('template_planet_id'))->count(),
            ],
            'alliances' => [
                'total' => Alliance::count(),
                'members' => DB::table('alliance_members')->count(),
            ],
            'templates' => [
                'buildings' => TemplateBuild::where('type', 'building')->count(),
                'units' => TemplateBuild::where('type', 'unit')->count(),
                'defenses' => TemplateBuild::where('type', 'defense')->count(),
                'ships' => TemplateBuild::where('type', 'ship')->count(),
                'technologies' => TemplateBuild::where('type', 'technology')->count(),
                'resources' => TemplateResource::count(),
                'planets' => TemplatePlanet::count(),
            ],
            'factions' => [
                'total' => Faction::count(),
                'distribution' => $this->getFactionDistribution(),
            ],
        ];
    }
    
    /**
     * Obtenir la distribution des factions
     */
    protected function getFactionDistribution(): array
    {
        $factions = Faction::all();
        $distribution = [];
        
        foreach ($factions as $faction) {
            $distribution[$faction->name] = User::where('faction_id', $faction->id)->count();
        }
        
        return $distribution;
    }
    
    /**
     * Obtenir les derniers logs utilisateur
     */
    public function getRecentUserLogs(): array
    {
        return UserLog::with(['user', 'planet', 'targetUser'])
            ->orderBy('created_at', 'desc')
            ->limit($this->logLimit)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user' => $log->user ? $log->user->name : 'Système',
                    'action_type' => $log->action_type,
                    'action_category' => $log->action_category,
                    'description' => $log->description,
                    'severity' => $log->severity,
                    'created_at' => $log->created_at->diffForHumans(),
                    'planet' => $log->planet ? $log->planet->name : null,
                    'target_user' => $log->targetUser ? $log->targetUser->name : null,
                ];
            })
            ->toArray();
    }
    
    /**
     * Obtenir la couleur CSS pour la sévérité du log
     */
    public function getLogSeverityClass(string $severity): string
    {
        return match($severity) {
            'info' => 'info',
            'warning' => 'warning',
            'error' => 'danger',
            'critical' => 'danger',
            default => 'info'
        };
    }
    
    /**
     * Obtenir l'icône pour la catégorie de log
     */
    public function getLogCategoryIcon(string $category): string
    {
        return match($category) {
            'auth' => 'user-shield',
            'resource' => 'coins',
            'building' => 'building',
            'unit' => 'users',
            'defense' => 'shield-alt',
            'ship' => 'rocket',
            'technology' => 'atom',
            'planet' => 'globe',
            'mission' => 'space-shuttle',
            'alliance' => 'handshake',
            'forum' => 'comments',
            'message' => 'envelope',
            'trade' => 'exchange-alt',
            'admin' => 'user-cog',
            'system' => 'cogs',
            default => 'info-circle'
        };
    }
    
    /**
     * Rendre le composant
     */
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'gameStats' => $this->getGameStats(),
            'recentLogs' => $this->getRecentUserLogs(),
        ])->title('Tableau de bord');
    }
}