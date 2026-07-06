<?php

namespace App\Console;

use App\Console\Commands\ExpireVerificationCodes;
use App\Jobs\RefreshHolidaysCache;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new RefreshHolidaysCache())->dailyAt('02:00')->withoutOverlapping();

        $schedule->command(ExpireVerificationCodes::class)
            ->dailyAt('00:05')
            ->withoutOverlapping();
    }

    protected function commands(): void
    {
        require base_path('routes/console.php');
    }
}
