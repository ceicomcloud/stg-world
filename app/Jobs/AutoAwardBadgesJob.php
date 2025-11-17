<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoAwardBadgesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $checkAllUsers;

    /**
     * Create a new job instance.
     */
    public function __construct($userId = null, bool $checkAllUsers = false)
    {
        $this->userId = $userId;
        $this->checkAllUsers = $checkAllUsers;
    }

    /**
     * Execute the job.
     */
    public function handle(BadgeService $badgeService): void
    {
        try {
            if ($this->checkAllUsers) {
                // Vérifier tous les utilisateurs
                $results = $badgeService->autoAwardBadgesToAllUsers();
                
                Log::info('Auto-award badges completed for all users', [
                    'total_users_awarded' => count($results),
                    'results' => $results
                ]);
            } elseif ($this->userId) {
                // Vérifier un utilisateur spécifique
                $user = User::find($this->userId);
                
                if ($user) {
                    $newBadges = $badgeService->autoAwardBadges($user);
                    
                    Log::info('Auto-award badges completed for user', [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'new_badges' => $newBadges ? $newBadges->pluck('name')->toArray() : [],
                        'badges_count' => count($newBadges)
                    ]);
                } else {
                    Log::warning('User not found for auto-award badges', [
                        'user_id' => $this->userId
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in AutoAwardBadgesJob', [
                'user_id' => $this->userId,
                'check_all_users' => $this->checkAllUsers,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AutoAwardBadgesJob failed', [
            'user_id' => $this->userId,
            'check_all_users' => $this->checkAllUsers,
            'error' => $exception->getMessage()
        ]);
    }
}