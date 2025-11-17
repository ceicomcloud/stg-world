<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Server\ServerSchedule;
use App\Models\Server\ServerConfig;
use App\Models\Server\ServerScheduleLog;
use Illuminate\Support\Carbon;
use App\Services\EventService;
use App\Services\PrivateMessageService;

class ApplyServerSchedules extends Command
{
    protected $signature = 'server:apply-schedules';
    protected $description = 'Appliquer les trêves et bonus planifiés selon la date actuelle';

    public function handle(): int
    {
        $now = Carbon::now();

        $active = ServerSchedule::query()
            ->where('enabled', true)
            ->where('starts_at', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->get();

        // Par défaut
        $truceEnabled = false;
        $blockEarth = false;
        $blockSpatial = false;
        $blockSpy = false;
        $truceMessage = null;

        $productionBonusPercent = 0.0;
        $storageBonusPercent = 0.0;
        $shopBonusPercent = 0.0;

        foreach ($active as $schedule) {
            if ($schedule->type === 'truce') {
                $truceEnabled = true;
                $payload = $schedule->payload ?? [];
                $blockEarth = $blockEarth || (bool)($payload['block_earth'] ?? false);
                $blockSpatial = $blockSpatial || (bool)($payload['block_spatial'] ?? false);
                $blockSpy = $blockSpy || (bool)($payload['block_spy'] ?? false);
                // Utiliser le premier message non vide
                if (!$truceMessage && !empty($schedule->message)) {
                    $truceMessage = $schedule->message;
                }
            } elseif ($schedule->type === 'bonus') {
                $payload = $schedule->payload ?? [];
                $productionBonusPercent += (float)($payload['production_bonus_percent'] ?? 0);
                $storageBonusPercent += (float)($payload['storage_bonus_percent'] ?? 0);
            } elseif ($schedule->type === 'shop_bonus') {
                $payload = $schedule->payload ?? [];
                $shopBonusPercent += (float)($payload['gold_bonus_percent'] ?? 0);
            } elseif ($schedule->type === 'server_event') {
                // La gestion de l'événement se fait après le calcul des trêves/bonus
            }
        }

        // Obtenir les valeurs actuelles
        $currentTruceEnabled = (bool) ServerConfig::get('truce_enabled', false);
        $currentEarth = (bool) ServerConfig::get('truce_block_earth_attack', false);
        $currentSpatial = (bool) ServerConfig::get('truce_block_spatial_attack', false);
        $currentSpy = (bool) ServerConfig::get('truce_block_spy', false);
        $currentMessage = (string) ServerConfig::get('truce_message', '');
        $currentProdRate = (float) ServerConfig::get('production_rate', 1.0);
        $currentStockRate = (float) ServerConfig::get('storage_rate', 1.0);
        $currentShopRate = (float) ServerConfig::get('shop_reward_rate', 1.0);

        // Calcul des nouveaux multiplicateurs
        $productionRate = max(0.0, 1.0 + ($productionBonusPercent / 100.0));
        $storageRate = max(0.0, 1.0 + ($storageBonusPercent / 100.0));
        $shopRewardRate = max(0.0, 1.0 + ($shopBonusPercent / 100.0));

        $changes = [
            'truce' => [
                'enabled' => [$currentTruceEnabled, $truceEnabled],
                'block_earth_attack' => [$currentEarth, $blockEarth],
                'block_spatial_attack' => [$currentSpatial, $blockSpatial],
                'block_spy' => [$currentSpy, $blockSpy],
                'message' => [$currentMessage, $truceMessage],
            ],
            'bonus' => [
                'production_rate' => [$currentProdRate, $productionRate],
                'storage_rate' => [$currentStockRate, $storageRate],
                'production_bonus_percent' => $productionBonusPercent,
                'storage_bonus_percent' => $storageBonusPercent,
            ],
            'shop' => [
                'shop_reward_rate' => [$currentShopRate, $shopRewardRate],
                'gold_bonus_percent' => $shopBonusPercent,
            ],
            'active_schedule_ids' => $active->pluck('id')->all(),
        ];

        $didChange = (
            $currentTruceEnabled !== $truceEnabled ||
            $currentEarth !== $blockEarth ||
            $currentSpatial !== $blockSpatial ||
            $currentSpy !== $blockSpy ||
            $currentMessage !== ($truceMessage ?? '') ||
            abs($currentProdRate - $productionRate) > 1e-9 ||
            abs($currentStockRate - $storageRate) > 1e-9 ||
            abs($currentShopRate - $shopRewardRate) > 1e-9
        );

        if ($didChange) {
            // Appliquer trêve
            ServerConfig::set('truce_enabled', $truceEnabled, ServerConfig::TYPE_BOOLEAN, ServerConfig::CATEGORY_COMBAT);
            ServerConfig::set('truce_block_earth_attack', $blockEarth, ServerConfig::TYPE_BOOLEAN, ServerConfig::CATEGORY_COMBAT);
            ServerConfig::set('truce_block_spatial_attack', $blockSpatial, ServerConfig::TYPE_BOOLEAN, ServerConfig::CATEGORY_COMBAT);
            ServerConfig::set('truce_block_spy', $blockSpy, ServerConfig::TYPE_BOOLEAN, ServerConfig::CATEGORY_COMBAT);
            if ($truceMessage) {
                ServerConfig::set('truce_message', $truceMessage, ServerConfig::TYPE_STRING, ServerConfig::CATEGORY_COMBAT);
            }

            // Appliquer bonus globaux via multiplicateurs
            ServerConfig::set('production_rate', $productionRate, ServerConfig::TYPE_FLOAT, ServerConfig::CATEGORY_PRODUCTION);
            ServerConfig::set('storage_rate', $storageRate, ServerConfig::TYPE_FLOAT, ServerConfig::CATEGORY_STORAGE);
            ServerConfig::set('shop_reward_rate', $shopRewardRate, ServerConfig::TYPE_FLOAT, ServerConfig::CATEGORY_SHOP);

            // Consigner le log d’application
            ServerScheduleLog::create([
                'schedule_id' => null,
                'type' => 'apply',
                'applied_at' => $now,
                'message' => 'Application des plannings actifs (trêve/bonus/shop)',
                'changes' => $changes,
            ]);
        }

        // ---- Gestion des événements serveur planifiés ----
        // On prend le premier planning de type 'server_event' actif (si plusieurs, le premier suffira)
        $eventSchedule = $active->first(function ($s) { return $s->type === 'server_event'; });
        $eventService = app(EventService::class);
        $pmService = app(PrivateMessageService::class);

        $currentEvent = $eventService->getActiveEvent();
        if ($eventSchedule && !$currentEvent) {
            // Démarrer l'événement selon le planning
            $payload = $eventSchedule->payload ?? [];
            $eventService->startEvent([
                'type' => $payload['type'] ?? 'attaque',
                'reward_type' => $payload['reward_type'] ?? 'resource',
                'base_reward' => (int)($payload['base_reward'] ?? 0),
                'points_multiplier' => (float)($payload['points_multiplier'] ?? 0.0),
                'end_at' => $eventSchedule->ends_at,
            ]);

            ServerScheduleLog::create([
                'schedule_id' => $eventSchedule->id,
                'type' => 'event_start',
                'applied_at' => $now,
                'message' => 'Démarrage de l\'événement serveur planifié',
                'changes' => [
                    'event' => [
                        'type' => $payload['type'] ?? 'attaque',
                        'reward_type' => $payload['reward_type'] ?? 'resource',
                        'base_reward' => (int)($payload['base_reward'] ?? 0),
                        'points_multiplier' => (float)($payload['points_multiplier'] ?? 0.0),
                        'end_at' => optional($eventSchedule->ends_at)->toIso8601String(),
                    ]
                ],
            ]);

            $this->info('Événement serveur démarré via planning: ' . ($payload['type'] ?? 'attaque'));
        } elseif ($currentEvent) {
            // Finaliser automatiquement si la date de fin de l'événement est passée
            $endAt = $currentEvent['end_at'] ? Carbon::parse($currentEvent['end_at']) : null;
            if ($endAt && $endAt->isPast()) {
                $eventService->finalizeEvent($pmService);

                ServerScheduleLog::create([
                    'schedule_id' => $eventSchedule->id ?? null,
                    'type' => 'event_finalize',
                    'applied_at' => $now,
                    'message' => 'Finalisation automatique de l\'événement serveur (date de fin atteinte)',
                    'changes' => [
                        'event' => [
                            'type' => $currentEvent['type'] ?? null,
                            'ended_at' => $now->toIso8601String(),
                        ]
                    ],
                ]);

                $this->info('Événement serveur finalisé (date de fin atteinte).');
            }
        }

        $this->info(sprintf(
            'Appliqué: trêve=%s, Earth=%s, Spatial=%s, Spy=%s, prod=+%0.2f%% (rate=%0.3f), stock=+%0.2f%% (rate=%0.3f), shop=+%0.2f%% (rate=%0.3f)',
            $truceEnabled ? 'on' : 'off',
            $blockEarth ? 'on' : 'off',
            $blockSpatial ? 'on' : 'off',
            $blockSpy ? 'on' : 'off',
            $productionBonusPercent,
            $productionRate,
            $storageBonusPercent,
            $storageRate,
            $shopBonusPercent,
            $shopRewardRate
        ));

        return Command::SUCCESS;
    }
}