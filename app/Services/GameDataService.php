<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Planet\PlanetMission;
use App\Models\Other\Queue;
use App\Models\User;

class GameDataService
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

    public function getUserPlanetsWithResources(int $userId)
    {
        $ttl = (int) ($this->ttl['planets'] ?? 30);
        $key = "game:user:{$userId}:planets_with_resources";
        return $this->remember($key, $ttl, function () use ($userId) {
            /** @var User|null $user */
            $user = User::with(['planets.templatePlanet', 'planets.resources'])
                ->find($userId);
            return $user ? $user->planets : collect();
        });
    }

    public function getPlanetQueuesGrouped(int $planetId)
    {
        $ttl = (int) ($this->ttl['queues'] ?? 5);
        $key = "game:planet:{$planetId}:queues";
        return $this->remember($key, $ttl, function () use ($planetId) {
            $query = Queue::active()
                ->notCompleted()
                ->forPlanet($planetId)
                ->orderedByPosition()
                ->with('item');

            $queues = $query->get()->groupBy('type');
            return [
                'building' => $queues->get(Queue::TYPE_BUILDING, collect()),
                'unit' => $queues->get(Queue::TYPE_UNIT, collect()),
                'defense' => $queues->get(Queue::TYPE_DEFENSE, collect()),
                'ship' => $queues->get(Queue::TYPE_SHIP, collect()),
            ];
        });
    }

    public function forgetPlanetQueues(int $planetId): void
    {
        Cache::forget("game:planet:{$planetId}:queues");
    }

    public function getUserActiveMissions(int $userId)
    {
        $ttl = (int) ($this->ttl['missions'] ?? 10);
        $key = "game:user:{$userId}:missions_active";
        return $this->remember($key, $ttl, function () use ($userId) {
            return PlanetMission::where('user_id', $userId)
                ->whereIn('status', ['traveling', 'returning', 'collecting', 'exploring'])
                ->with(['fromPlanet.templatePlanet', 'toPlanet.templatePlanet'])
                ->orderBy('departure_time', 'desc')
                ->get();
        });
    }

    public function forgetUserMissions(int $userId): void
    {
        Cache::forget("game:user:{$userId}:missions_active");
    }

    public function getRecentBadges(int $userId, int $limit = 3)
    {
        $ttl = (int) ($this->ttl['badges_recent'] ?? 60);
        $key = "game:user:{$userId}:badges_recent:{$limit}";
        return $this->remember($key, $ttl, function () use ($userId, $limit) {
            /** @var User|null $user */
            $user = User::find($userId);
            if (!$user) return collect();
            return $user->badges()
                ->orderBy('user_badges.earned_at', 'desc')
                ->take($limit)
                ->get();
        });
    }

    public function forgetUserBadges(int $userId, int $limit = 3): void
    {
        Cache::forget("game:user:{$userId}:badges_recent:{$limit}");
    }
}