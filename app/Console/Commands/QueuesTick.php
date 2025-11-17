<?php

namespace App\Console\Commands;

use App\Models\Other\Queue;
use App\Models\Server\ServerConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class QueuesTick extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'queues:tick {--batch-size=1000} {--sleep-ms=0}';

    /**
     * The console command description.
     */
    protected $description = 'Vérifie les files arrivées à terme et applique leurs effets aux planètes/utilisateurs.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Exécution longue autorisée
        \set_time_limit(0);

        $this->info('⏱️ Vérification des files terminées...');
        $startTime = microtime(true);

        try {
            $batchSize = (int) $this->option('batch-size');
            $sleepMs = (int) $this->option('sleep-ms');
            // Traite les éléments arrivés à échéance et non complétés en chunks
            $processed = Queue::processCompletedItems($batchSize, $sleepMs);
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors du traitement des files: ' . $e->getMessage());
            Log::error('queues:tick failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);
        // Enregistrer les métriques
        ServerConfig::set('queues_tick_last_run_at', now()->toIso8601String(), ServerConfig::TYPE_STRING, ServerConfig::CATEGORY_GENERAL, 'Dernière exécution du tick des files');
        ServerConfig::set('queues_tick_duration_ms', $durationMs, ServerConfig::TYPE_INTEGER, ServerConfig::CATEGORY_GENERAL, 'Durée du tick des files en ms');
        ServerConfig::set('queues_tick_processed_count', $processed ?? 0, ServerConfig::TYPE_INTEGER, ServerConfig::CATEGORY_GENERAL, 'Nombre d\'éléments de file traités');

        $this->info("✅ Traitement terminé: {$processed} éléments en {$durationMs}ms");
        return Command::SUCCESS;
    }
}