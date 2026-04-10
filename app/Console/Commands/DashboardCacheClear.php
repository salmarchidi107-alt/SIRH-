<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Dashboard\StatsService;

class DashboardCacheClear extends Command
{
    protected $signature = 'dashboard:cache-clear';
    protected $description = 'Clear dashboard cache';

    public function handle(StatsService $statsService)
    {
        $statsService->invalidateCache();
        $this->info('Dashboard cache cleared!');
    }
}

