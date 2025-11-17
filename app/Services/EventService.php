<?php

namespace App\Services;

use App\Models\User;
use App\Models\User\UserStatEvent;
use App\Models\Server\ServerConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class EventService
{
    /**
     * Get active event config
     */
    public function getActiveEvent(): ?array
    {
        $active = (bool) ServerConfig::get('event_active', false);
        if (!$active) return null;

        return [
            'type' => ServerConfig::get('event_type', null),
            'start_at' => ServerConfig::get('event_start_at', null),
            'end_at' => ServerConfig::get('event_end_at', null),
            'reward_type' => ServerConfig::get('event_reward_type', 'resource'),
            'base_reward' => (int) ServerConfig::get('event_base_reward', 0),
            'points_multiplier' => (float) ServerConfig::get('event_points_multiplier', 0.0),
        ];
    }

    /**
     * Start event now with provided config
     */
    public function startEvent(array $config): void
    {
        ServerConfig::set('event_type', $config['type'] ?? 'attaque');
        ServerConfig::set('event_start_at', Carbon::now());
        ServerConfig::set('event_end_at', $config['end_at'] ?? null);
        ServerConfig::set('event_reward_type', $config['reward_type'] ?? 'resource');
        ServerConfig::set('event_base_reward', (int) ($config['base_reward'] ?? 0));
        ServerConfig::set('event_points_multiplier', (float) ($config['points_multiplier'] ?? 0.0));
        ServerConfig::set('event_active', true);

        // Reset event stats for all users
        DB::table('user_stat_events')->truncate();

        // Ensure entries exist lazily when needed; optional eager init can be added here
    }

    /**
     * Finalize event: compute rewards, notify, and reset data
     */
    public function finalizeEvent(PrivateMessageService $pm): void
    {
        $event = $this->getActiveEvent();
        if (!$event) return;

        $column = $this->mapTypeToColumn($event['type']);
        if (!$column) $column = 'attaque_points';

        // Leaderboard: participants with > 0
        $participants = UserStatEvent::with('user')
            ->where($column, '>', 0)
            ->orderBy($column, 'desc')
            ->get();

        $rank = 0;
        foreach ($participants as $entry) {
            $rank++;
            $score = (int) $entry->{$column};
            $reward = (int) (($event['base_reward'] ?? 0) + $score * ($event['points_multiplier'] ?? 0));
            if ($reward <= 0) continue;

            $this->grantReward($entry->user, $reward, $event['reward_type'] ?? 'resource');

            // Notify user
            $pm->createSystemNotification(
                $entry->user,
                'Événement terminé',
                "L'événement est terminé. Type: {$event['type']}.\n" .
                "Votre score: {$score}. Position: #{$rank}.\n" .
                "Récompense: {$reward} " . ($event['reward_type'] === 'gold' ? 'Or' : 'Ressources')
            );
        }

        // Reset event data
        DB::table('user_stat_events')->truncate();
        ServerConfig::set('event_active', false);
        ServerConfig::forget('event_type');
        ServerConfig::forget('event_start_at');
        ServerConfig::forget('event_end_at');
        ServerConfig::forget('event_reward_type');
        ServerConfig::forget('event_base_reward');
        ServerConfig::forget('event_points_multiplier');
    }

    /**
     * Map event type to UserStatEvent column
     */
    public function mapTypeToColumn(?string $type): ?string
    {
        return match($type) {
            'attaque', 'attack' => 'attaque_points',
            'exploration' => 'exploration_count',
            'extraction' => 'extraction_count',
            'pillage' => 'pillage_total',
            'construction' => 'construction_spent',
            default => null,
        };
    }

    /**
     * Get top users for active event
     */
    public function getTopUsersByActiveEvent(int $limit, int $offset = 0)
    {
        $event = $this->getActiveEvent();
        if (!$event) return collect([]);

        $column = $this->mapTypeToColumn($event['type']);
        if (!$column) return collect([]);

        return UserStatEvent::with(['user' => function($q) {
                $q->with(['userStat', 'alliance']);
            }])
            ->orderBy($column, 'desc')
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function($e) {
                // attach convenience for Ranking component
                $e->user->userStatEvent = $e; // for direct access
                return $e->user;
            });
    }

    /**
     * Grant reward to user (gold or resource)
     */
    protected function grantReward(User $user, int $amount, string $type): void
    {
        // Minimal implementation: use InventoryService or user gold/resource fields.
        // Here we assume "gold" stored on user, and resources via InventoryService.
        if ($type === 'gold') {
            // If a gold field exists; otherwise adjust to your economy system
            if (isset($user->gold_balance)) {
                $user->gold_balance += $amount;
                $user->save();
            }
        } else {
            // Distribute as generic resource to main planet
            try {
                app(InventoryService::class)->addGenericResourcesToUser($user, $amount);
            } catch (\Throwable $e) {
                // Fallback: no-op if inventory service not available
            }
        }
    }

    /**
     * Increment a specific event stat column for a user
     */
    public function incrementStat(int $userId, string $column, int $value = 1): void
    {
        if ($value <= 0) return;
        $stat = UserStatEvent::firstOrCreate(['user_id' => $userId]);
        $stat->increment($column, $value);
    }

    /**
     * Record attack points earned by the user
     */
    public function recordAttackPoints(int $userId, int $points): void
    {
        $this->incrementStat($userId, 'attaque_points', max(0, (int) $points));
    }

    /**
     * Record pillaged resource total for the user
     */
    public function recordPillage(int $userId, int $amount): void
    {
        $this->incrementStat($userId, 'pillage_total', max(0, (int) $amount));
    }

    /**
     * Record one exploration completion for the user
     */
    public function recordExploration(int $userId): void
    {
        $this->incrementStat($userId, 'exploration_count', 1);
    }

    /**
     * Record one extraction completion for the user
     */
    public function recordExtraction(int $userId): void
    {
        $this->incrementStat($userId, 'extraction_count', 1);
    }

    /**
     * Record construction resources spent by the user
     */
    public function recordConstructionSpent(int $userId, int $amount): void
    {
        $this->incrementStat($userId, 'construction_spent', max(0, (int) $amount));
    }
}