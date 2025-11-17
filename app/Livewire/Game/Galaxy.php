<?php

namespace App\Livewire\Game;

use App\Models\Planet\Planet;
use App\Models\Template\TemplatePlanet;
use Illuminate\Support\Facades\Auth;
use App\Services\DailyQuestService;
use App\Services\GalaxyDataService;
use App\Services\GalacticEventService;
use App\Models\Other\GalacticEvent;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Support\Device;

#[Layout('components.layouts.game')]
class Galaxy extends Component
{
    public $user;
    public $currentSystem = 1;
    public $currentGalaxy = 1;
    public $maxSystems = 1000;
    public $planetsPerSystem = 10;
    public $systems = [];
    public $systemPlanets = [];
    public $planetPositions3D = [];
    public $occupiedCount = 0;
    public $ownCount = 0;
    public $botCount = 0;
    public $freeCount = 0;
    public $targetSystem;
    public $viewMode = '3d';
    public $activeEvents = [];

    public function mount()
    {
        $this->user = auth()->user();
        
        // Récupérer la planète actuelle de l'utilisateur
        $actualPlanet = $this->user->getActualPlanet();
        
        if ($actualPlanet && $actualPlanet->templatePlanet) {
            $this->currentGalaxy = $actualPlanet->templatePlanet->galaxy;
            $this->currentSystem = $actualPlanet->templatePlanet->system;
        } else {
            // Valeurs par défaut si aucune planète
            $this->currentGalaxy = 1;
            $this->currentSystem = 1;
        }
        
        $this->targetSystem = $this->currentSystem;
        // Par défaut: 2D sur mobile, 3D sur desktop
        $this->viewMode = Device::isMobile() ? '2d' : '3d';
        $this->loadSystemData();
    }

    public function loadSystemData(GalaxyDataService $galaxyData = null, GalacticEventService $eventService = null)
    {
        $galaxyData = $galaxyData ?? app(GalaxyDataService::class);
        $eventService = $eventService ?? app(GalacticEventService::class);
        $this->systemPlanets = [];
        
        // Requêtes groupées et cache
        $templatesByPosition = $galaxyData->getSystemTemplates($this->currentGalaxy, $this->currentSystem, $this->planetsPerSystem);
        $planetsByTemplate = $galaxyData->getSystemPlanetsWithUsers($this->currentGalaxy, $this->currentSystem, $this->planetsPerSystem);

        for ($position = 1; $position <= $this->planetsPerSystem; $position++) {
            $templatePlanet = $templatesByPosition->get($position);
            $planet = $templatePlanet ? $planetsByTemplate->get($templatePlanet->id) : null;

            // Déterminer l'état allié
            $isAlly = $planet && $planet->user ? $this->isAllyWith($planet->user) : false;

            $this->systemPlanets[$position] = [
                'position' => $position,
                'planet' => $planet,
                'template' => $templatePlanet,
                'user' => $planet ? $planet->user : null,
                'is_own' => $planet && $planet->user_id === $this->user->id,
                'is_ally' => $isAlly,
                'is_main' => $planet && $planet->id === $this->user->main_planet_id,
                'coordinates' => "{$this->currentGalaxy}:{$this->currentSystem}:{$position}",
                'is_bot' => $templatePlanet && $templatePlanet->is_colonizable === false,
                'is_protected' => $planet && $planet->isShieldProtectionActive(),
                'is_vacation_mode' => $planet && $planet->user && $planet->user->isInVacationMode()
            ];
        }

        // Statistiques pour l'en-tête
        $this->occupiedCount = collect($this->systemPlanets)->filter(fn($p) => $p['planet'] !== null)->count();
        $this->ownCount = collect($this->systemPlanets)->filter(fn($p) => $p['is_own'] === true)->count();
        $this->botCount = collect($this->systemPlanets)->filter(fn($p) => $p['is_bot'] === true)->count();
        $this->freeCount = collect($this->systemPlanets)->filter(fn($p) => $p['planet'] === null && $p['template'] !== null && $p['is_bot'] === false)->count();

        // Calcul positions 3D (deterministes, sans @php dans Blade)
        $this->planetPositions3D = [];
        $usedPositions = [];
        $minDistance = 80; // px
        $galaxySize = 500; // pour conversion en %
        $orbitRadius = 150;
        for ($position = 1; $position <= $this->planetsPerSystem; $position++) {
            $seed = crc32($this->currentGalaxy.'-'.$this->currentSystem.'-'.$position);
            $baseAngle = ($position * (360 / max(1, $this->planetsPerSystem))) % 360;
            $variation = (($seed >> 8) % 101) - 50; // -50..+50
            $angle = ($baseAngle + $variation) % 360;
            $radiusVariation = (($seed >> 16) % 101) - 50; // -50..+50
            $actualRadius = $orbitRadius + $radiusVariation;

            $attempt = 0;
            $maxAttempts = 30;
            do {
                $x = 50 + (($actualRadius * cos(deg2rad($angle))) / $galaxySize * 100);
                $y = 50 + (($actualRadius * sin(deg2rad($angle))) / $galaxySize * 100);
                $valid = true;
                foreach ($usedPositions as $used) {
                    $distance = sqrt(pow(($x - $used['x']) * $galaxySize / 100, 2) + pow(($y - $used['y']) * $galaxySize / 100, 2));
                    if ($distance < $minDistance) { $valid = false; break; }
                }
                if (!$valid) { $angle = ($angle + 12) % 360; $attempt++; }
            } while (!$valid && $attempt < $maxAttempts);

            $usedPositions[] = ['x' => $x, 'y' => $y];
            $this->planetPositions3D[$position] = [
                'x' => $x,
                'y' => $y,
                'angle' => $angle,
            ];
        }

        // Incrémenter la quête quotidienne de navigation dans la galaxie (1 système parcouru)
        $user = Auth::user();
        if ($user) {
            app(DailyQuestService::class)->incrementProgressByPrefix($user, 'galaxy_browse_', 1);
        }

        // Évènements galactiques actifs pour ce système
        // Tentative d'engendrer un nouvel événement avec faible probabilité
        try {
            $eventService->maybeSpawnAmbientEvent($this->currentGalaxy, $this->currentSystem, $this->planetsPerSystem);
        } catch (\Throwable $e) {
            // ignorer silencieusement
        }

        $events = $eventService->getActiveEventsForSector($this->currentGalaxy, $this->currentSystem);
        $this->activeEvents = $events->map(function(GalacticEvent $ev) {
            return [
                'key' => $ev->key,
                'title' => $ev->title,
                'severity' => $ev->severity,
                'icon' => $ev->icon,
                'description' => $ev->description,
                'position' => $ev->position,
                'start_at' => $ev->start_at?->toDateTimeString(),
                'end_at' => $ev->end_at?->toDateTimeString(),
            ];
        })->values()->toArray();
    }

    public function previousSystem()
    {
        if ($this->currentSystem > 1) {
            $this->currentSystem--;
            $this->loadSystemData();
        }
    }

    public function nextSystem()
    {
        if ($this->currentSystem < $this->maxSystems) {
            $this->currentSystem++;
            $this->loadSystemData();
        }
    }

    public function goToSystem($systemNumber = null)
    {
        $target = $systemNumber ?? $this->targetSystem;
        if ($target >= 1 && $target <= $this->maxSystems) {
            $this->currentSystem = $target;
            $this->targetSystem = $target;
            $this->loadSystemData();
        }
    }

    public function updatedTargetSystem()
    {
        // Cette méthode sera appelée quand targetSystem change
        if ($this->targetSystem >= 1 && $this->targetSystem <= $this->maxSystems) {
            $this->currentSystem = $this->targetSystem;
            $this->loadSystemData();
        }
    }

    public function openPlanetModal($planetId, $galaxy = null, $system = null, $position = null)
    {
        if ($planetId) {
            // Planète existante
            $planet = Planet::with(['user', 'templatePlanet'])->find($planetId);
            
            if ($planet) {
                $this->dispatch('openModal', component: 'game.modal.planet-info', arguments: [
                    'title' => 'Informations de la planète',
                    'planetId' => $planetId,
                ]);
            }
        } else if ($galaxy && $system && $position) {
            // Position libre - ouvrir modal de planète avec coordonnées
            $coordinates = "[{$galaxy}:{$system}:{$position}]";
            $this->dispatch('openModal', component: 'game.modal.planet-info', arguments: [
                'title' => 'Planète libre ['.$galaxy.':'.$system.':'.$position.']',
                'galaxy' => $galaxy,
                'system' => $system,
                'position' => $position,
                'planetId' => null,
            ]);
        }
    }

    /**
     * Déterminer si l'utilisateur cible est allié
     */
    protected function isAllyWith($otherUser): bool
    {
        if (!$otherUser) return false;

        // Même alliance
        if ($this->user->alliance_id && $otherUser->alliance_id && $this->user->alliance_id === $otherUser->alliance_id) {
            return true;
        }

        // Pacte accepté
        $relation = \App\Models\User\UserRelation::findBetween($this->user->id, $otherUser->id);
        if ($relation && $relation->status === \App\Models\User\UserRelation::STATUS_ACCEPTED) {
            return true;
        }

        return false;
    }

    public function render()
    {
        return view('livewire.game.galaxy');
    }

    /**
     * Basculer entre l’affichage 2D et 3D
     */
    public function setViewMode(string $mode): void
    {
        if (!in_array($mode, ['2d', '3d'], true)) {
            return;
        }
        $this->viewMode = $mode;
    }
}
             