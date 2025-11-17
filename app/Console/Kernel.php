<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\BotTick::class,
        \App\Console\Commands\GalacticTick::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Exécuter régulièrement pour que l'heure aléatoire quotidienne puisse se déclencher
        $schedule->command('bot:tick')->everyMinute()->withoutOverlapping();

        // Appliquer automatiquement les trêves et bonus planifiés
        $schedule->command('server:apply-schedules')->everyFiveMinutes()->withoutOverlapping();

        // Recompute event ranks for active events regularly
        $schedule->command('server-events:recompute')->everyTenMinutes()->withoutOverlapping();

        // Distribute rewards when events end
        $schedule->command('server-events:distribute')->hourly()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}