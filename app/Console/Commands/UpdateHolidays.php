<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Dashboard\HolidayService;

class UpdateHolidays extends Command
{
    protected $signature = 'holidays:update {--year=}';
    protected $description = 'Refresh holidays cache from API';

    public function handle(HolidayService $holidayService)
    {
        $year = $this->option('year') ?: now()->year;
        $holidayService->refreshCache();

        $this->info("Holidays cache refreshed for {$year}");
        $holidays = $holidayService->getCurrentYearHolidays();
        $this->table(['Name', 'Date'], collect($holidays)->map(fn($h) => [$h['name'], $h['date']]));
    }
}

