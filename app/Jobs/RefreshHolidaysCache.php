<?php

namespace App\Jobs;

use App\Services\HolidayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshHolidaysCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $year = null)
    {
    }

    public function handle(HolidayService $holidayService): void
    {
        $holidayService->refreshHolidaysCache($this->year);
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('RefreshHolidaysCache job failed: ' . $exception->getMessage());
    }
}
