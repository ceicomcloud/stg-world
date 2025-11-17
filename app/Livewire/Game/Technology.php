<?php

namespace App\Livewire\Game;

use App\Models\User\UserTechnology;
use App\Models\Template\TemplateBuild;
use App\Models\Template\TemplateBuildCost;
use App\Models\Template\TemplateBuildRequired;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use App\Traits\LogsUserActions;
use App\Services\DailyQuestService;
use App\Services\TemplateCacheService;
use App\Support\Device;

#[Layout('components.layouts.game')]
class Technology extends Component
{
    use LogsUserActions;
    public $user;
    public $technologies = [];
    public $userTechnologies = [];
    public $researchPoints = 0;
    public $currentResearch = null;
    public $timeRemaining = 0;

    public function mount()
    {
        $this->user = auth()->user();
        
        if (!$this->user) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Utilisateur non trouvé.'
            ]);
            return;
        }

        $this->loadResearchPoints();
        $this->loadTechnologies();
        $this->loadUserTechnologies();
        $this->loadCurrentResearch();
    }

    public function loadTechnologies()
    {
        // Utiliser le cache des templates pour réduire les requêtes
        $templates = app(TemplateCacheService::class)
            ->getTemplateBuildsByType('technology')
            ->where('is_active', true);

        // Certaines vues s'appuient sur sort_order: conservons cet ordre si présent
        $technologies = $templates->sortBy('sort_order')->values();

        $this->technologies = $technologies->map(function ($tech) {
            $userTech = $this->user->technologies()->where('technology_id', $tech->id)->first();
            $currentLevel = $userTech ? $userTech->level : 0;
            $nextLevel = $currentLevel + 1;

            return [
                'id' => $tech->id,
                'name' => $tech->name,
                'label' => $tech->label ?? $tech->name,
                'description' => $tech->description,
                'icon' => $tech->icon,
                'category' => $tech->category,
                'current_level' => $currentLevel,
                'next_level' => $nextLevel,
                'max_level' => $tech->max_level,
                'can_research' => $this->canResearch($tech, $nextLevel),
                'research_cost' => $this->getResearchCost($tech, $nextLevel),
                'requirements_met' => $this->checkRequirements($tech),
                'sort_order' => $tech->sort_order
            ];
        })->toArray();
    }

    public function loadUserTechnologies()
    {
        $this->userTechnologies = $this->user->technologies()
            ->with('technology')
            ->get()
            ->keyBy('technology_id')
            ->toArray();
    }

    public function loadResearchPoints()
    {
        $this->researchPoints = $this->user->research_points;
    }

    public function loadCurrentResearch()
    {
        // Les technologies sont instantanées, pas de recherche en cours
        $this->currentResearch = null;
        $this->timeRemaining = 0;
    }

    public function canResearch($technology, $level)
    {
        // Vérifier si la technologie est déjà au niveau maximum
        if ($technology->max_level > 0 && $level > $technology->max_level) {
            return false;
        }

        // Vérifier les prérequis
        if (!$this->checkRequirements($technology)) {
            return false;
        }

        // Vérifier les points de recherche
        $cost = $this->getResearchCost($technology, $level);
        return $this->researchPoints >= $cost;
    }

    public function checkRequirements($technology)
    {
        // Utiliser les prérequis déjà chargés via le cache
        $requirements = ($technology->relationLoaded('requirements') ? $technology->requirements : 
            TemplateBuildRequired::where('build_id', $technology->id)
                ->where('is_active', true)
                ->get());

        foreach ($requirements as $requirement) {
            $userTech = $this->user->getActualPlanet()->buildings()
                ->where('building_id', $requirement->required_build_id)
                ->first();

            $currentLevel = $userTech ? $userTech->level : 0;
            
            if ($currentLevel < $requirement->required_level) {
                return false;
            }
        }

        return true;
    }

    public function getResearchCost($technology, $level)
    {
        // Chercher le coût de base depuis les coûts préchargés si disponibles
        $baseCostModel = null;
        if ($technology && $technology->relationLoaded('costs')) {
            $baseCostModel = $technology->costs->firstWhere('level', 1);
        }
        if (!$baseCostModel) {
            $baseCostModel = TemplateBuildCost::where('build_id', $technology->id)
                ->where('level', 1)
                ->where('is_active', true)
                ->first();
        }
        if (!$baseCostModel) {
            return 100; // Coût par défaut
        }

        // Nouveau calcul: coût de base multiplié par le niveau
        $cost = (int) ($baseCostModel->base_cost * $level);
        
        // Appliquer le bonus de faction pour le coût des technologies
        if ($this->user && $this->user->faction) {
            $technologyCostBonus = $this->user->faction->getBonusTechnologyCost();
            if ($technologyCostBonus < 0) { // Bonus négatif = réduction de coût
                $cost = (int) ($cost * (1 + $technologyCostBonus / 100));
            }
        }
        
        return $cost;
    }

    public function startResearch($technologyId)
    {
        $technology = TemplateBuild::findOrFail($technologyId);
        $userTech = $this->user->technologies()->where('technology_id', $technologyId)->first();
        $currentLevel = $userTech ? $userTech->level : 0;
        $nextLevel = $currentLevel + 1;

        // Vérifications
        if (!$this->canResearch($technology, $nextLevel)) {
            return $this->dispatch('toast:error', [
                'title' => 'Technologie!',
                'text' => "Impossible de rechercher cette technologie."
            ]);
        }

        $cost = $this->getResearchCost($technology, $nextLevel);

        // Déduire les points de recherche
        $this->user->decrement('research_points', $cost);

        // Créer ou mettre à jour la technologie utilisateur avec le nouveau niveau (instantané)
        $userTechnology = UserTechnology::updateOrCreate(
            [
                'user_id' => $this->user->id,
                'technology_id' => $technologyId
            ],
            [
                'level' => $nextLevel,
                'is_active' => true
            ]
        );

        // Log technology research
        $this->logTechnologyResearch(
            $technology->label,
            $technologyId,
            $nextLevel,
            ['research_points' => $cost]
        );

        $this->dispatch('toast:success', [
            'title' => 'Technologie recherchée!',
            'text' => "La technologie {$technology->label} a été améliorée au niveau {$nextLevel}."
        ]);

        // Incrémenter la quête quotidienne de démarrage de technologie
        app(DailyQuestService::class)->incrementProgress($this->user, 'start_technology');

        // Recharger les données
        $this->loadTechnologies();
        $this->loadUserTechnologies();
        $this->loadResearchPoints();
        $this->loadCurrentResearch();
    }

    public function openTechnologyModal($technologyId)
    {
        $this->dispatch('openModal', component: 'game.modal.technology-info', arguments: [
            'title' => 'Informations sur la technologie',
            'technologyId' => $technologyId,
            'type' => 'technology',
        ]);
    }



    public function formatTime($seconds)
    {
        if ($seconds <= 0) {
            return '00:00:00';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function render()
    {
        return view('livewire.game.technology');
    }
}