<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Template\TemplatePlanet;
use App\Models\Planet\Planet;

class GalaxyDataService
{
    protected bool $enabled;
    protected array $ttl;

    public function __construct()
    {
        $this->enabled = (bool) config('game.cache_enabled', true);
        $this->ttl = (array) config('game.cache_ttl', []);
    }

    protected function remember(string $key, int $ttl, callable $callback)
    {
        if (!$this->enabled) {
            return $callback();
        }
        return Cache::remember($key, $ttl, $callback);
    }

    public function getSystemTemplates(int $galaxy, int $system, int $planetsPerSystem = 10)
    {
        $ttl = (int) ($this->ttl['galaxy_templates'] ?? 30);
        $key = "galaxy:templates:{$galaxy}:{$system}:{$planetsPerSystem}";
        return $this->remember($key, $ttl, function () use ($galaxy, $system, $planetsPerSystem) {
            return TemplatePlanet::where('galaxy', $galaxy)
                ->where('system', $system)
                ->whereIn('position', range(1, $planetsPerSystem))
                ->get()
                ->keyBy('position');
        });
    }

    public function getSystemPlanetsWithUsers(int $galaxy, int $system, int $planetsPerSystem = 10)
    {
        $ttl = (int) ($this->ttl['galaxy_system'] ?? 10);
        $key = "galaxy:system:{$galaxy}:{$system}:{$planetsPerSystem}:planets_with_users";
        return $this->remember($key, $ttl, function () use ($galaxy, $system, $planetsPerSystem) {
            $templates = $this->getSystemTemplates($galaxy, $system, $planetsPerSystem);
            $templateIds = $templates->pluck('id')->all();

            if (empty($templateIds)) {
                return collect();
            }

            return Planet::whereIn('template_planet_id', $templateIds)
                ->with(['user.alliance', 'templatePlanet'])
                ->get()
                ->keyBy('template_planet_id');
        });
    }
}