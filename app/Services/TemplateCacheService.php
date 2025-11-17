<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Template\TemplateBuild;

class TemplateCacheService
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

    /**
     * Récupère et met en cache la liste des TemplateBuild pour un type avec relations.
     */
    public function getTemplateBuildsByType(string $type)
    {
        $ttl = (int) ($this->ttl['template_builds'] ?? 300);
        $key = "templates:builds:type:{$type}";
        return $this->remember($key, $ttl, function () use ($type) {
            return TemplateBuild::where('type', $type)
                ->with([
                    'costs.resource',
                    'requirements',
                    'advantages',
                    'disadvantages'
                ])
                ->orderBy('category')
                ->orderBy('id')
                ->get();
        });
    }
}