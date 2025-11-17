<?php

namespace App\Livewire\Auth;

use App\Models\Server\ServerNews;
use App\Models\User;
use App\Models\Planet\Planet;
use Livewire\Component;
use Carbon\Carbon;

class AuthExtra extends Component
{
    public $newsItems = [];
    public $currentIndex = 0;
    public $autoScroll = true;
    public $showGameHistory = false;
    public $serverStats = [];
    protected $listeners = [
        'autoNextNews' => 'nextNews',
    ];
    
    public function mount()
    {
        $this->loadServerNews();
        $this->loadServerStats();
    }
    
    /**
     * Charge les statistiques du serveur
     */
    public function loadServerStats()
    {
        // Nombre total de joueurs inscrits
        $totalPlayers = User::where('role', '!=', 'admin')->count();
        
        // Nombre de joueurs connectés dans les dernières 24h
        $onlinePlayers = User::where('last_login_at', '>=', Carbon::now()->subDay())
            ->where('role', '!=', 'admin')
            ->count();
        
        // Nombre total de planètes colonisées
        $totalPlanets = Planet::count();
        
        // Planètes par joueur en moyenne
        $avgPlanetsPerPlayer = $totalPlayers > 0 ? round($totalPlanets / $totalPlayers, 1) : 0;
        
        $this->serverStats = [
            'total_players' => $totalPlayers,
            'online_players' => $onlinePlayers,
            'total_planets' => $totalPlanets,
            'avg_planets_per_player' => $avgPlanetsPerPlayer
        ];
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

        // Si aucune actualité, ajouter un message par défaut
        if (count($this->newsItems) === 0) {
            $this->newsItems[] = [
                'type' => 'info',
                'icon' => 'info-circle',
                'text' => 'Bienvenue sur World Of Stargate ! Rejoignez l\'aventure spatiale.',
                'time' => 'maintenant',
                'priority' => 'normal'
            ];
        }
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
    
    public function toggleGameHistory()
    {
        $this->showGameHistory = !$this->showGameHistory;
    }

    public function render()
    {
        return view('livewire.auth.auth-extra');
    }
}