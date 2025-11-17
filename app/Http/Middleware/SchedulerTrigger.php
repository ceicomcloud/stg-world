<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SchedulerTrigger
{
    // Intervalle en secondes pour éviter une exécution trop fréquente
    const EXECUTION_INTERVAL = 15; // 15 secondes pour un ressenti plus temps réel

    // Tailles de lots adaptées au contexte web (post-réponse)
    private const PRODUCTION_BATCH_SIZE = 100;
    private const PRODUCTION_SLEEP_MS   = 10;
    private const QUEUES_BATCH_SIZE     = 500;
    private const QUEUES_SLEEP_MS       = 10;
    private const BADGES_BATCH_SIZE     = 200;
    private const BADGES_SLEEP_MS       = 10;

    public function handle(Request $request, Closure $next): Response
    {
        // Clé de cache pour suivre la dernière exécution
        $cacheKey = 'scheduler:last-run';

        // Tenter un verrou atomique sur une fenêtre d'une minute
        $lockKey = 'scheduler:running';
        $canRun = Cache::add($lockKey, 1, self::EXECUTION_INTERVAL);

        if ($canRun) {
            // Exécuter après envoi de la réponse pour ne pas impacter la latence
            app()->terminating(function () use ($cacheKey, $lockKey) {
                try {
                    // Exécutions avec lots modestes pour limiter la charge en contexte web
                    Artisan::call('production:tick', [
                        '--batch-size' => self::PRODUCTION_BATCH_SIZE,
                        '--sleep-ms'   => self::PRODUCTION_SLEEP_MS,
                    ]);

                    Artisan::call('queues:tick', [
                        '--batch-size' => self::QUEUES_BATCH_SIZE,
                        '--sleep-ms'   => self::QUEUES_SLEEP_MS,
                    ]);

                    Artisan::call('ranking:tick');
                    Artisan::call('missions:tick');

                    Artisan::call('badges:tick', [
                        '--batch-size' => self::BADGES_BATCH_SIZE,
                        '--sleep-ms'   => self::BADGES_SLEEP_MS,
                    ]);

                    Artisan::call('bot:tick');
                    Artisan::call('server:apply-schedules');
                } catch (\Throwable $e) {
                    report($e);
                } finally {
                    Cache::put($cacheKey, time());
                    Cache::forget($lockKey);
                }
            });
        }

        return $next($request);
    }
}