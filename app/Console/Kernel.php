<?php

namespace App\Console;

use App\Jobs\ParseSource;
use App\Models\Source;
use App\Services\ContentService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            foreach(Source::where('isActive', true)->get() as $source)
            {
                ParseSource::dispatch($source)->onQueue('scheduler');
            }
        })->weeklyOn(7, '6:00');

        $schedule->call(function () {
            ContentService::createContentForNextWeek();
        })->weeklyOn(6, '4:00');

        $schedule->call(function () {
            ContentService::generateVideoForWaitingContent();
        })->dailyAt("22:30");

        $schedule->call(function () {
            ContentService::clearErrorContent();
        })->dailyAt("22");
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
