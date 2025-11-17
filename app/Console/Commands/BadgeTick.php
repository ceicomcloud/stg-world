<?php

namespace App\Console\Commands;

use App\Jobs\AutoAwardBadgesJob;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BadgeTick extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'badges:tick {--batch-size=500} {--sleep-ms=0}';

    /**
     * The console command description.
     */
    protected $description = 'Auto-attribue les badges pour tous les utilisateurs, sans options';

    protected $badgeService;

    /**
     * Create a new command instance.
     */
    public function __construct(BadgeService $badgeService)
    {
        parent::__construct();
        $this->badgeService = $badgeService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        \set_time_limit(0);
        $this->info('🔄 Attribution automatique des badges pour tous les utilisateurs...');
        $startTime = microtime(true);
        try {
            $batchSize = (int) $this->option('batch-size');
            $sleepMs = (int) $this->option('sleep-ms');
            $results = $this->badgeService->autoAwardBadgesToAllUsers($batchSize, $sleepMs);

            if (empty($results)) {
                $this->info('✅ Aucun nouveau badge à attribuer');
            } else {
                $this->info('🎉 Attribution terminée!');
                $this->newLine();

                foreach ($results as $userId => $data) {
                    $this->line("👤 {$data['user']}: " . count($data['badges']) . ' nouveaux badges');
                    foreach ($data['badges'] as $badgeName) {
                        $this->line("  🏆 {$badgeName}");
                    }
                }

                $totalBadges = array_sum(array_map(fn($data) => count($data['badges']), $results));
                $this->newLine();
                $this->info("📊 Total: {$totalBadges} badges attribués à " . count($results) . ' utilisateurs');
            }

            // Enregistrer les métriques
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);
            \App\Models\Server\ServerConfig::set('badges_tick_last_run_at', now()->toIso8601String(), \App\Models\Server\ServerConfig::TYPE_STRING, \App\Models\Server\ServerConfig::CATEGORY_GENERAL, 'Dernière exécution du tick des badges');
            \App\Models\Server\ServerConfig::set('badges_tick_duration_ms', $durationMs, \App\Models\Server\ServerConfig::TYPE_INTEGER, \App\Models\Server\ServerConfig::CATEGORY_GENERAL, 'Durée du tick des badges en ms');
            \App\Models\Server\ServerConfig::set('badges_tick_processed_count', $totalBadges ?? 0, \App\Models\Server\ServerConfig::TYPE_INTEGER, \App\Models\Server\ServerConfig::CATEGORY_GENERAL, 'Nombre de badges attribués');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Erreur lors de l\'auto-attribution des badges: ' . $e->getMessage());
            Log::error('AutoAwardBadgesCommand failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }
}