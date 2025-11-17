<?php

namespace App\Jobs;

use App\Models\Other\Queue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessQueueJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3; // Retry 3 times on failure

    protected ?int $planetId;
    protected ?int $userId;
    protected string $type;

    /**
     * Create a new job instance.
     */
    public function __construct(?int $planetId = null, string $type = 'building', ?int $userId = null)
    {
        $this->planetId = $planetId;
        $this->userId = $userId;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if ($this->planetId) {
                $this->processPlanetQueue();
            } elseif ($this->userId) {
                $this->processUserQueue();
            } else {
                Log::warning('ProcessQueueJob: Aucun identifiant spécifié');
                return;
            }
        } catch (\Exception $e) {
            Log::error('Erreur dans ProcessQueueJob', [
                'planet_id' => $this->planetId,
                'user_id' => $this->userId,
                'type' => $this->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Traiter la file d'une planète
     */
    private function processPlanetQueue(): void
    {
        $completedItems = Queue::getCompletedItems($this->planetId, $this->type);
        
        if ($completedItems->isEmpty()) {
            Log::info('Aucun élément terminé dans la file', [
                'planet_id' => $this->planetId,
                'type' => $this->type
            ]);
            return;
        }

        $processedCount = 0;
        
        foreach ($completedItems as $item) {
            try {
                $item->complete();
                $processedCount++;
                
                Log::info('Élément de file traité avec succès', [
                    'planet_id' => $this->planetId,
                    'type' => $this->type,
                    'item_id' => $item->item_id,
                    'level' => $item->level,
                    'quantity' => $item->quantity
                ]);
                
            } catch (\Exception $e) {
                Log::error('Erreur lors du traitement d\'un élément de file', [
                    'planet_id' => $this->planetId,
                    'type' => $this->type,
                    'item_id' => $item->item_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Programmer le prochain élément de la file s'il y en a un
        $this->scheduleNextQueueItem();
        
        Log::info('Traitement de file terminé', [
            'planet_id' => $this->planetId,
            'type' => $this->type,
            'processed_count' => $processedCount
        ]);
    }

    /**
     * Traiter la file d'un utilisateur (pour les technologies)
     */
    private function processUserQueue(): void
    {
        $completedItems = Queue::getUserQueue($this->userId, $this->type)
            ->where('is_completed', true)
            ->where('completed_at', '<=', now())
            ->get();
        
        if ($completedItems->isEmpty()) {
            Log::info('Aucun élément terminé dans la file utilisateur', [
                'user_id' => $this->userId,
                'type' => $this->type
            ]);
            return;
        }

        $processedCount = 0;
        
        foreach ($completedItems as $item) {
            try {
                $item->complete();
                $processedCount++;
                
                Log::info('Élément de file utilisateur traité avec succès', [
                    'user_id' => $this->userId,
                    'type' => $this->type,
                    'item_id' => $item->item_id,
                    'level' => $item->level
                ]);
                
            } catch (\Exception $e) {
                Log::error('Erreur lors du traitement d\'un élément de file utilisateur', [
                    'user_id' => $this->userId,
                    'type' => $this->type,
                    'item_id' => $item->item_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Programmer le prochain élément de la file s'il y en a un
        $this->scheduleNextUserQueueItem();
        
        Log::info('Traitement de file utilisateur terminé', [
            'user_id' => $this->userId,
            'type' => $this->type,
            'processed_count' => $processedCount
        ]);
    }

    /**
     * Programmer le prochain élément de la file de planète
     */
    private function scheduleNextQueueItem(): void
    {
        $nextItem = Queue::getPlanetQueue($this->planetId, $this->type)
            ->where('is_active', true)
            ->where('is_completed', false)
            ->orderBy('position')
            ->first();
            
        if ($nextItem) {
            $delay = $nextItem->end_time->diffInSeconds(now());
            
            if ($delay > 0) {
                ProcessQueueJob::dispatch($this->planetId, $this->type)
                    ->delay(now()->addSeconds($delay));
                    
                Log::info('Prochain élément de file programmé', [
                    'planet_id' => $this->planetId,
                    'type' => $this->type,
                    'item_id' => $nextItem->item_id,
                    'delay_seconds' => $delay
                ]);
            }
        }
    }

    /**
     * Programmer le prochain élément de la file utilisateur
     */
    private function scheduleNextUserQueueItem(): void
    {
        $nextItem = Queue::getUserQueue($this->userId, $this->type)
            ->where('is_active', true)
            ->where('is_completed', false)
            ->orderBy('position')
            ->first();
            
        if ($nextItem) {
            $delay = $nextItem->end_time->diffInSeconds(now());
            
            if ($delay > 0) {
                ProcessQueueJob::dispatch(null, $this->type, $this->userId)
                    ->delay(now()->addSeconds($delay));
                    
                Log::info('Prochain élément de file utilisateur programmé', [
                    'user_id' => $this->userId,
                    'type' => $this->type,
                    'item_id' => $nextItem->item_id,
                    'delay_seconds' => $delay
                ]);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessQueueJob a échoué définitivement', [
            'planet_id' => $this->planetId,
            'user_id' => $this->userId,
            'type' => $this->type,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}