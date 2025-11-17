<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurer Carbon en français
        Carbon::setLocale('fr');
        
        // Définir les gates pour les autorisations
        Gate::define('manage-badges', function (User $user) {
            return $user->canManageBadges();
        });
    }
}
