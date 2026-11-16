<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $event = $schedule->command('inventory:check-reorder')
            ->dailyAt('06:00')
            ->withoutOverlapping()                              // never run two checks at once
            ->appendOutputTo(storage_path('logs/reorder-check.log'))
            ->onFailure(function () {
                Log::error('Scheduled command inventory:check-reorder failed. See storage/logs/reorder-check.log.');
            });

        // Optional: e-mail the command output to the administrator mailbox from .env.
        if (config('procurement.admin_email')) {
            $event->emailOutputTo(config('procurement.admin_email'));
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
