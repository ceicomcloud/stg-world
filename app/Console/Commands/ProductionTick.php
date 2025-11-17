<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ResourceService;
use App\Models\User;
use App\Models\Server\ServerConfig;
use Carbon\Carbon;

class ProductionTick extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'production:tick {--batch-size=500} {--sleep-ms=0}';

    /**
     * The console command description.
     */
    protected $description = 'Applique la production de ressources pour tous les utilisateurs, en une seule exécution.';

    /**
     * Execute the console command.
     */
    public function handle(ResourceService $resourceService): int
    {
        \set_time_limit(0);
        $this->info('🚀 Démarrage de la production de ressources (exécution unique)');
        $startTime = microtime(true);
        
        try {
            $processedUsers = 0;
            $batchSize = (int) $this->option('batch-size');
            $sleepMs = (int) $this->option('sleep-ms');

            User::query()
                ->select('id')
                ->where('role', '!=', 'bot')
                ->orderBy('id')
                ->chunkById($batchSize, function ($users) use ($resourceService, &$processedUsers, $sleepMs) {
                    foreach ($users as $user) {
                        $resourceService->updateAllUserResources($user->id);
                        $processedUsers++;
                    }
                    if ($sleepMs > 0) {
                        usleep($sleepMs * 1000);
                    }
                });

            $durationMs = (int) round((microtime(true) - $startTime) * 1000);
            ServerConfig::set('production_tick_last_run_at', now()->toIso8601String(), ServerConfig::TYPE_STRING, ServerConfig::CATEGORY_GENERAL, 'Dernière exécution du tick de production');
            ServerConfig::set('production_tick_duration_ms', $durationMs, ServerConfig::TYPE_INTEGER, ServerConfig::CATEGORY_GENERAL, 'Durée du tick de production en ms');
            ServerConfig::set('production_tick_processed_count', $processedUsers, ServerConfig::TYPE_INTEGER, ServerConfig::CATEGORY_GENERAL, 'Nombre d\'utilisateurs traités pour la production');

            $this->info("✅ Production appliquée: {$processedUsers} utilisateurs en {$durationMs}ms");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ Erreur lors de la production: ' . $e->getMessage());
            report($e);
            return Command::FAILURE;
        }
    }
}