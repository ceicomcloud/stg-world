<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UserPointsService;
use Illuminate\Support\Facades\Log;

class RankingTick extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ranking:tick';

    /**
     * The console command description.
     */
    protected $description = 'Calcule les points pour tous les utilisateurs et met à jour les classements quotidiens en une seule exécution.';

    /**
     * Execute the console command.
     */
    public function handle(UserPointsService $userPointsService): int
    {
        \set_time_limit(0);
        $this->info('🏁 Démarrage du RankingTick: calcul des points et mise à jour des classements');
        $startTime = microtime(true);

        try {
            // Calcul des points pour tous les utilisateurs (exécution synchrone)
            $userPointsService->calculateAllUsersPoints(false);

            // La mise à jour quotidienne des classements est appelée par le job lorsque l'userId est null.
            // Nous n'appelons pas updateDailyRankings() une seconde fois pour éviter un passage inutile.

            $durationMs = (int) round((microtime(true) - $startTime) * 1000);
            $this->info('✅ RankingTick terminé');
            $this->line('⏱️ Durée: ' . $durationMs . ' ms');
            Log::info('[ranking:tick] terminé', ['duration_ms' => $durationMs]);

            // Enregistrer les métriques
            $processedUsers = \App\Models\User::where('role', '!=', 'bot')->count();
            \App\Models\Server\ServerConfig::set('ranking_tick_last_run_at', now()->toIso8601String(), \App\Models\Server\ServerConfig::TYPE_STRING, \App\Models\Server\ServerConfig::CATEGORY_GENERAL, 'Dernière exécution du tick ranking');
            \App\Models\Server\ServerConfig::set('ranking_tick_duration_ms', $durationMs, \App\Models\Server\ServerConfig::TYPE_INTEGER, \App\Models\Server\ServerConfig::CATEGORY_GENERAL, 'Durée du tick ranking en ms');
            \App\Models\Server\ServerConfig::set('ranking_tick_processed_count', $processedUsers, \App\Models\Server\ServerConfig::TYPE_INTEGER, \App\Models\Server\ServerConfig::CATEGORY_GENERAL, 'Nombre d\'utilisateurs traités pour le ranking');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ Erreur RankingTick: ' . $e->getMessage());
            Log::error('[ranking:tick] erreur', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            report($e);
            return self::FAILURE;
        }
    }
}