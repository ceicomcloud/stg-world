<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Jobs\AutoAwardBadgesJob;
use App\Jobs\CalculateUserPointsJob;
use App\Jobs\ProcessPlanetMissionsJob;
use App\Jobs\ProcessQueueJob;
use App\Jobs\UpdateResourcesJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Traits\LogsUserActions;
use Carbon\Carbon;
use App\Models\Server\ServerConfig;
use App\Models\BotTickRun;

#[Layout('components.layouts.admin')]
class Jobs extends Component
{
    use WithPagination, LogsUserActions;
    
    // Propriétés pour la pagination et le filtrage
    public $perPage = 10;
    public $search = '';
    public $activeTab = 'available'; // Onglets: available, running, failed, batches
    
    // Propriétés pour le lancement des jobs
    public $selectedJob = null;
    public $jobParams = [];

    // États d'exécution des ticks
    public bool $runningQueuesTick = false;
    public bool $runningMissionsTick = false;
    public bool $runningProductionTick = false;
    public bool $runningBotTick = false;
    public bool $runningBadgesTick = false;
    public bool $runningRankingTick = false;
    
    
    /**
     * Règles de validation pour les propriétés
     */
    protected function rules()
    {
        return [
            'search' => 'nullable|string|max:100',
            'jobParams.*' => 'nullable',
        ];
    }
    
    /**
     * Définir l'onglet actif
     */
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }
    
    /**
     * Mise à jour de la recherche
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }
    
    /**
     * Sélectionner un job pour le lancer
     */
    public function selectJob($jobName)
    {
        $this->selectedJob = $jobName;
        $this->jobParams = [];
        
        // Initialiser les paramètres en fonction du job sélectionné
        switch ($jobName) {
            case 'AutoAwardBadgesJob':
                $this->jobParams['userId'] = null;
                $this->jobParams['checkAllUsers'] = false;
                break;
            case 'CalculateUserPointsJob':
                $this->jobParams['userId'] = null;
                break;
            case 'ProcessPlanetMissionsJob':
                // Pas de paramètres spécifiques
                break;
            case 'ProcessQueueJob':
                // Pas de paramètres spécifiques
                break;
            case 'UpdateResourcesJob':
                // Pas de paramètres spécifiques
                break;
        }
    }
    
    /**
     * Lancer le job sélectionné
     */
    public function dispatchJob()
    {
        $this->validate();
        
        try {
            switch ($this->selectedJob) {
                case 'AutoAwardBadgesJob':
                    AutoAwardBadgesJob::dispatch(
                        $this->jobParams['userId'],
                        $this->jobParams['checkAllUsers']
                    );
                    break;
                case 'CalculateUserPointsJob':
                    CalculateUserPointsJob::dispatch(
                        $this->jobParams['userId']
                    );
                    break;
                case 'ProcessPlanetMissionsJob':
                    ProcessPlanetMissionsJob::dispatch();
                    break;
                case 'ProcessQueueJob':
                    ProcessQueueJob::dispatch();
                    break;
                case 'UpdateResourcesJob':
                    UpdateResourcesJob::dispatch();
                    break;
            }
            
            $this->dispatch('admin-toast', [
                'type' => 'success',
                'message' => 'Le job a été lancé avec succès.'
            ]);
            
            $this->logAction(
                'admin_jobs_dispatch',
                'settings',
                'Lancement du job: {job}',
                [
                    'job' => $this->selectedJob,
                    'params' => $this->jobParams
                ]
            );
            
            $this->selectedJob = null;
            $this->jobParams = [];
            
        } catch (\Exception $e) {
            $this->dispatch('admin-toast', [
                'type' => 'error',
                'message' => 'Erreur lors du lancement du job: ' . $e->getMessage()
            ]);
            
            $this->logAction(
                'admin_jobs_dispatch_error',
                'settings',
                'Erreur lors du lancement du job: {job}',
                [
                    'job' => $this->selectedJob,
                    'error' => $e->getMessage()
                ],
                null,
                null,
                'error'
            );
        }
    }
    
    /**
     * Récupérer la liste des jobs disponibles
     */
    public function getAvailableJobsProperty()
    {
        $jobs = [
            [
                'name' => 'AutoAwardBadgesJob',
                'description' => 'Attribution automatique des badges aux utilisateurs',
                'params' => [
                    'userId' => 'ID utilisateur spécifique (optionnel)',
                    'checkAllUsers' => 'Vérifier tous les utilisateurs'
                ]
            ],
            [
                'name' => 'CalculateUserPointsJob',
                'description' => 'Calcul des points des utilisateurs',
                'params' => [
                    'userId' => 'ID utilisateur spécifique (optionnel)'
                ]
            ],
            [
                'name' => 'ProcessPlanetMissionsJob',
                'description' => 'Traitement des missions planétaires',
                'params' => []
            ],
            [
                'name' => 'ProcessQueueJob',
                'description' => 'Traitement de la file d\'attente',
                'params' => []
            ],
            [
                'name' => 'UpdateResourcesJob',
                'description' => 'Mise à jour des ressources',
                'params' => []
            ]
        ];
        
        if ($this->search) {
            $search = strtolower($this->search);
            $jobs = array_filter($jobs, function ($job) use ($search) {
                return str_contains(strtolower($job['name']), $search) || 
                       str_contains(strtolower($job['description']), $search);
            });
        }
        
        return $jobs;
    }
    
    /**
     * Récupérer les jobs en cours d'exécution
     */
    public function getRunningJobsProperty()
    {
        $jobs = DB::table('jobs')
            ->select('id', 'queue', 'payload', 'attempts', 'reserved_at', 'available_at', 'created_at')
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);
        
        // Décoder le payload pour afficher des informations plus lisibles
        $jobs->getCollection()->transform(function ($job) {
            $payload = json_decode($job->payload);
            $job->job_name = $payload->displayName ?? 'Unknown';
            $job->created_at = Carbon::createFromTimestamp($job->created_at)->format('d/m/Y H:i:s');
            $job->available_at = Carbon::createFromTimestamp($job->available_at)->format('d/m/Y H:i:s');
            $job->reserved_at = $job->reserved_at ? Carbon::createFromTimestamp($job->reserved_at)->format('d/m/Y H:i:s') : null;
            return $job;
        });
        
        return $jobs;
    }
    
    /**
     * Récupérer les jobs échoués
     */
    public function getFailedJobsProperty()
    {
        return DB::table('failed_jobs')
            ->select('id', 'uuid', 'connection', 'queue', 'payload', 'exception', 'failed_at')
            ->orderBy('failed_at', 'desc')
            ->paginate($this->perPage);
    }
    
    /**
     * Récupérer les lots de jobs
     */
    public function getJobBatchesProperty()
    {
        return DB::table('job_batches')
            ->select('id', 'name', 'total_jobs', 'pending_jobs', 'failed_jobs', 'created_at', 'finished_at', 'cancelled_at')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }
    
    /**
     * Relancer un job échoué
     */
    public function retryFailedJob($id)
    {
        try {
            Artisan::call('queue:retry', ['id' => [$id]]);
            
            $this->dispatch('admin-toast', [
                'type' => 'success',
                'message' => 'Le job a été remis en file d\'attente avec succès.'
            ]);
            
            $this->logAction(
                'admin_jobs_retry',
                'settings',
                'Relance du job échoué: {id}',
                ['id' => $id]
            );
        } catch (\Exception $e) {
            $this->dispatch('admin-toast', [
                'type' => 'error',
                'message' => 'Erreur lors de la remise en file d\'attente: ' . $e->getMessage()
            ]);
            
            $this->logAction(
                'admin_jobs_retry_error',
                'settings',
                'Erreur lors de la relance du job: {id}',
                [
                    'id' => $id,
                    'error' => $e->getMessage()
                ],
                null,
                null,
                'error'
            );
        }
    }
    
    /**
     * Supprimer un job échoué
     */
    public function deleteFailedJob($id)
    {
        try {
            Artisan::call('queue:forget', ['id' => $id]);
            
            $this->dispatch('admin-toast', [
                'type' => 'success',
                'message' => 'Le job échoué a été supprimé avec succès.'
            ]);
            
            $this->logAction(
                'admin_jobs_forget',
                'settings',
                'Suppression du job échoué: {id}',
                ['id' => $id]
            );
        } catch (\Exception $e) {
            $this->dispatch('admin-toast', [
                'type' => 'error',
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ]);
            
            $this->logAction(
                'admin_jobs_forget_error',
                'settings',
                'Erreur lors de la suppression du job: {id}',
                [
                    'id' => $id,
                    'error' => $e->getMessage()
                ],
                null,
                null,
                'error'
            );
        }
    }
    
    /**
     * Vider tous les jobs échoués
     */
    public function flushFailedJobs()
    {
        try {
            Artisan::call('queue:flush');
            
            $this->dispatch('admin-toast', [
                'type' => 'success',
                'message' => 'Tous les jobs échoués ont été supprimés avec succès.'
            ]);
            
            $this->logAction(
                'admin_jobs_flush',
                'settings',
                'Suppression de tous les jobs échoués',
                []
            );
        } catch (\Exception $e) {
            $this->dispatch('admin-toast', [
                'type' => 'error',
                'message' => 'Erreur lors de la suppression des jobs échoués: ' . $e->getMessage()
            ]);
            
            $this->logAction(
                'admin_jobs_flush_error',
                'settings',
                'Erreur lors de la suppression de tous les jobs échoués: {error}',
                [
                    'error' => $e->getMessage()
                ],
                null,
                null,
                'error'
            );
        }
    }
    
    /**
     * Rendu du composant
     */
    public function render()
    {
        $botRuns = BotTickRun::orderByDesc('id')->paginate($this->perPage);
        return view('livewire.admin.jobs', [
            'runningJobs' => $this->getRunningJobsProperty(),
            'failedJobs' => $this->getFailedJobsProperty(),
            'jobBatches' => $this->getJobBatchesProperty(),
            'tickMetrics' => $this->getTickMetricsProperty(),
            'botRuns' => $botRuns,
        ]);
    }

    /**
     * Métriques des ticks (dernière exécution, durée, éléments traités)
     */
    public function getTickMetricsProperty(): array
    {
        $formatTs = function ($ts) {
            if (!$ts) return null;
            try { return Carbon::parse($ts)->format('d/m/Y H:i:s'); } catch (\Throwable $e) { return $ts; }
        };

        return [
            'queues' => [
                'last_run_at' => $formatTs(ServerConfig::get('queues_tick_last_run_at')),
                'duration_ms' => (int) ServerConfig::get('queues_tick_duration_ms', 0),
                'processed_count' => (int) ServerConfig::get('queues_tick_processed_count', 0),
            ],
            'missions' => [
                'last_run_at' => $formatTs(ServerConfig::get('missions_tick_last_run_at')),
                'duration_ms' => (int) ServerConfig::get('missions_tick_duration_ms', 0),
                'processed_count' => (int) ServerConfig::get('missions_tick_processed_count', 0),
            ],
            'production' => [
                'last_run_at' => $formatTs(ServerConfig::get('production_tick_last_run_at')),
                'duration_ms' => (int) ServerConfig::get('production_tick_duration_ms', 0),
                'processed_count' => (int) ServerConfig::get('production_tick_processed_count', 0),
            ],
            'bot' => [
                'last_run_at' => $formatTs(ServerConfig::get('bot_tick_last_run_at')),
                'duration_ms' => (int) ServerConfig::get('bot_tick_duration_ms', 0),
                'processed_count' => (int) ServerConfig::get('bot_tick_processed_count', 0),
            ],
            'badges' => [
                'last_run_at' => $formatTs(ServerConfig::get('badges_tick_last_run_at')),
                'duration_ms' => (int) ServerConfig::get('badges_tick_duration_ms', 0),
                'processed_count' => (int) ServerConfig::get('badges_tick_processed_count', 0),
            ],
            'ranking' => [
                'last_run_at' => $formatTs(ServerConfig::get('ranking_tick_last_run_at')),
                'duration_ms' => (int) ServerConfig::get('ranking_tick_duration_ms', 0),
                'processed_count' => (int) ServerConfig::get('ranking_tick_processed_count', 0),
            ],
        ];
    }

    /**
     * Actions pour exécuter les ticks directement depuis l'interface Admin
     */
    public function runQueuesTick(): void
    {
        if ($this->runningQueuesTick) return;
        $this->runningQueuesTick = true;
        try {
            Artisan::call('queues:tick');
            $this->dispatch('admin-toast', ['type' => 'success', 'message' => 'queues:tick exécuté']);
        } catch (\Throwable $e) {
            $this->dispatch('admin-toast', ['type' => 'error', 'message' => 'Erreur queues:tick: '.$e->getMessage()]);
        } finally {
            $this->runningQueuesTick = false;
        }
    }

    public function runMissionsTick(): void
    {
        if ($this->runningMissionsTick) return;
        $this->runningMissionsTick = true;
        try {
            Artisan::call('missions:tick');
            $this->dispatch('admin-toast', ['type' => 'success', 'message' => 'missions:tick exécuté']);
        } catch (\Throwable $e) {
            $this->dispatch('admin-toast', ['type' => 'error', 'message' => 'Erreur missions:tick: '.$e->getMessage()]);
        } finally {
            $this->runningMissionsTick = false;
        }
    }

    public function runProductionTick(): void
    {
        if ($this->runningProductionTick) return;
        $this->runningProductionTick = true;
        try {
            Artisan::call('production:tick');
            $this->dispatch('admin-toast', ['type' => 'success', 'message' => 'production:tick exécuté']);
        } catch (\Throwable $e) {
            $this->dispatch('admin-toast', ['type' => 'error', 'message' => 'Erreur production:tick: '.$e->getMessage()]);
        } finally {
            $this->runningProductionTick = false;
        }
    }

    public function runBotTick(): void
    {
        if ($this->runningBotTick) return;
        $this->runningBotTick = true;
        try {
            Artisan::call('bot:tick');
            $this->dispatch('admin-toast', ['type' => 'success', 'message' => 'bot:tick exécuté']);
        } catch (\Throwable $e) {
            $this->dispatch('admin-toast', ['type' => 'error', 'message' => 'Erreur bot:tick: '.$e->getMessage()]);
        } finally {
            $this->runningBotTick = false;
        }
    }

    public function runBadgesTick(): void
    {
        if ($this->runningBadgesTick) return;
        $this->runningBadgesTick = true;
        try {
            Artisan::call('badges:tick');
            $this->dispatch('admin-toast', ['type' => 'success', 'message' => 'badges:tick exécuté']);
        } catch (\Throwable $e) {
            $this->dispatch('admin-toast', ['type' => 'error', 'message' => 'Erreur badges:tick: '.$e->getMessage()]);
        } finally {
            $this->runningBadgesTick = false;
        }
    }

    public function runRankingTick(): void
    {
        if ($this->runningRankingTick) return;
        $this->runningRankingTick = true;
        try {
            Artisan::call('ranking:tick');
            $this->dispatch('admin-toast', ['type' => 'success', 'message' => 'ranking:tick exécuté']);
        } catch (\Throwable $e) {
            $this->dispatch('admin-toast', ['type' => 'error', 'message' => 'Erreur ranking:tick: '.$e->getMessage()]);
        } finally {
            $this->runningRankingTick = false;
        }
    }
}