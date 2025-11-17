<?php

namespace App\Console\Commands;

use App\Jobs\ProcessPlanetMissionsJob;
use App\Models\Planet\PlanetMission;
use App\Models\Server\ServerConfig;
use Illuminate\Console\Command;

class MissionTick extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'missions:tick';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending planet missions (colonization, transport, attack, spy)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \set_time_limit(0);
        $this->info('Processing planet missions...');
        $startTime = microtime(true);

        // Nombre de missions prêtes à être traitées (arrivée/retour) via requête COUNT()
        $pendingCount = PlanetMission::whereIn('status', ['traveling', 'returning'])
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('status', 'traveling')
                      ->where('arrival_time', '<=', now());
                })->orWhere(function ($q) {
                    $q->where('status', 'returning')
                      ->where('return_time', '<=', now());
                });
            })
            ->count();

        // Exécuter le job de manière synchrone pour traiter immédiatement
        ProcessPlanetMissionsJob::dispatchSync();

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);
        ServerConfig::set('missions_tick_last_run_at', now()->toIso8601String(), ServerConfig::TYPE_STRING, ServerConfig::CATEGORY_GENERAL, 'Dernière exécution du tick des missions');
        ServerConfig::set('missions_tick_duration_ms', $durationMs, ServerConfig::TYPE_INTEGER, ServerConfig::CATEGORY_GENERAL, 'Durée du tick des missions en ms');
        ServerConfig::set('missions_tick_processed_count', $pendingCount, ServerConfig::TYPE_INTEGER, ServerConfig::CATEGORY_GENERAL, 'Nombre de missions traitées');

        $this->info("Planet missions processed: {$pendingCount} in {$durationMs}ms.");
        return 0;
    }
}