<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Discord: nudge just before each schedule item starts.
        $schedule->command('discord:schedule-reminders')->everyMinute()->withoutOverlapping();

        // Discord: post the day programme each morning of the event.
        $schedule->command('discord:daily-digest')->dailyAt('08:00');

        // Discord: keep the guild scheduled events in sync with the speelschema.
        $schedule->command('discord:sync-events')->hourly()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
