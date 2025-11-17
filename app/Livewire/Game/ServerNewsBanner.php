<?php

namespace App\Livewire\Game;

use App\Models\Server\ServerNews;
use App\Models\Planet\Planet;
use App\Models\User;
use Livewire\Component;
use Carbon\Carbon;

class ServerNewsBanner extends Component
{
    public $newsItems = [];
    public $currentIndex = 0;
    public $autoScroll = true;
    protected $listeners = [
        'autoNextNews' => 'nextNews',
    ];

    public function mount()
    {
        $this->loadServerNews();
    }

    public function loadServerNews()
    {
        $this->newsItems = [];

        // Récupérer les nouvelles du serveur publiées
        $serverNews = ServerNews::published()
            ->active()
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($serverNews as $news) {
            $this->newsItems[] = [
                'type' => 'news',
                'icon' => $news->getCategoryIcon(),
                'text' => $news->title,
                'time' => $news->published_at->diffForHumans(),
                'priority' => $news->priority
            ];
        }

        // Ajouter les nouvelles planètes colonisées (dernières 24h)
        $recentPlanets = Planet::where('created_at', '>=', Carbon::now()->subDay())
            ->where('is_main_planet', false)
            ->with(['user', 'templatePlanet'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($recentPlanets as $planet) {
            $this->newsItems[] = [
                'type' => 'colonization',
                'icon' => 'globe',
                'text' => "Nouvelle planète colonisée par {$planet->user->name} en [{$planet->templatePlanet->galaxy}:{$planet->templatePlanet->system}:{$planet->templatePlanet->position}]",
                'time' => $planet->created_at->diffForHumans(),
                'priority' => 'normal'
            ];
        }

        // Ajouter les nouveaux joueurs inscrits (dernières 24h)
        $newPlayers = User::where('created_at', '>=', Carbon::now()->subDay())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($newPlayers as $user) {
            $this->newsItems[] = [
                'type' => 'registration',
                'icon' => 'user-plus',
                'text' => "Nouveau joueur inscrit: {$user->name}",
                'time' => $user->created_at->diffForHumans(),
                'priority' => 'normal'
            ];
        }

        // Mélanger les éléments pour plus de variété
        $this->newsItems = collect($this->newsItems)
            ->sortByDesc(function ($item) {
                return $item['priority'] === 'urgent' ? 3 : ($item['priority'] === 'high' ? 2 : 1);
            })
            ->take(20)
            ->values()
            ->toArray();
    }

    public function nextNews()
    {
        if (count($this->newsItems) > 0) {
            $this->currentIndex = ($this->currentIndex + 1) % count($this->newsItems);
        }
    }

    public function previousNews()
    {
        if (count($this->newsItems) > 0) {
            $this->currentIndex = ($this->currentIndex - 1 + count($this->newsItems)) % count($this->newsItems);
        }
    }

    public function toggleAutoScroll()
    {
        $this->autoScroll = !$this->autoScroll;
    }

    public function render()
    {
        return view('livewire.game.server-news-banner');
    }
}