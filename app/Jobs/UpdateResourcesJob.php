<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ResourceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateResourcesJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3; // Retry 3 times on failure

    protected ?int $userId;
    protected bool $updateAllUsers;

    /**
     * Create a new job instance.
     */
    public function __construct(?int $userId = null, bool $updateAllUsers = false)
    {
        $this->userId = $userId;
        $this->updateAllUsers = $updateAllUsers;
    }

    /**
     * Execute the job.
     */
    public function handle(ResourceService $resourceService): void
    {
        try {
            if ($this->updateAllUsers) {
                $this->updateAllUsersResources($resourceService);
            } elseif ($this->userId) {
                $this->updateSingleUserResources($resourceService, $this->userId);
            } else {
                Log::warning('UpdateResourcesJob: Aucun utilisateur spécifié');
                return;
            }
        } catch (\Exception $e) {
            Log::error('Erreur dans UpdateResourcesJob', [
                'user_id' => $this->userId,
                'update_all_users' => $this->updateAllUsers,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Mettre à jour les ressources d'un utilisateur spécifique
     */
    private function updateSingleUserResources(ResourceService $resourceService, int $userId): void
    {
        $user = User::find($userId);
        
        if (!$user) {
            Log::warning('UpdateResourcesJob: Utilisateur non trouvé', ['user_id' => $userId]);
            return;
        }

        $startTime = microtime(true);
        $updatedResources = $resourceService->updateAllUserResources($userId);
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        // Afficher les détails des changements
        $this->logResourceChanges($user, $updatedResources, $executionTime);
    }

    /**
     * Mettre à jour les ressources de tous les utilisateurs
     */
    private function updateAllUsersResources(ResourceService $resourceService): void
    {
        $startTime = microtime(true);
        $totalUsersUpdated = 0;
        $totalPlanetsUpdated = 0;
        $totalResourcesChanged = 0;

        // Traiter les utilisateurs par batch pour éviter les problèmes de mémoire
        User::chunk(50, function ($users) use ($resourceService, &$totalUsersUpdated, &$totalPlanetsUpdated, &$totalResourcesChanged) {
            foreach ($users as $user) {
                try {
                    $updatedResources = $resourceService->updateAllUserResources($user->id);
                    $totalUsersUpdated++;
                    $totalPlanetsUpdated += count($updatedResources);
                    
                    // Compter les ressources qui ont réellement changé
                    foreach ($updatedResources as $planetData) {
                        if (isset($planetData['resources'])) {
                            $totalResourcesChanged += count($planetData['resources']);
                        }
                    }
                    
                    // Afficher les détails pour chaque utilisateur
                    $this->logResourceChanges($user, $updatedResources);
                    
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la mise à jour des ressources pour un utilisateur', [
                        'user_id' => $user->id,
                        'username' => $user->name,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        Log::info('Mise à jour globale des ressources terminée', [
            'users_updated' => $totalUsersUpdated,
            'planets_updated' => $totalPlanetsUpdated,
            'resources_changed' => $totalResourcesChanged,
            'execution_time_ms' => $executionTime
        ]);
    }

    /**
     * Logger les changements de ressources avec détails
     */
    private function logResourceChanges($user, array $updatedResources, ?float $executionTime = null): void
    {
        if (empty($updatedResources)) {
            Log::info('Aucun changement de ressources', [
                'user_id' => $user->id,
                'username' => $user->name,
                'execution_time_ms' => $executionTime
            ]);
            return;
        }

        foreach ($updatedResources as $planetId => $planetData) {
            if (isset($planetData['resources']) && !empty($planetData['resources'])) {
                Log::info('Ressources mises à jour sur planète', [
                    'user_id' => $user->id,
                    'username' => $user->name,
                    'planet_id' => $planetId,
                    'planet_name' => $planetData['planet_name'] ?? 'N/A',
                    'resources_updated' => count($planetData['resources']),
                    'resource_details' => $planetData['resources'],
                    'execution_time_ms' => $executionTime
                ]);
            } else {
                Log::info('Aucun changement sur planète', [
                    'user_id' => $user->id,
                    'username' => $user->name,
                    'planet_id' => $planetId,
                    'planet_name' => $planetData['planet_name'] ?? 'N/A',
                    'execution_time_ms' => $executionTime
                ]);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('UpdateResourcesJob a échoué définitivement', [
            'user_id' => $this->userId,
            'update_all_users' => $this->updateAllUsers,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
