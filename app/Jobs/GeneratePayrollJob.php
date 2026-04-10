<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Services\PayrollService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GeneratePayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour
    public $tries = 3;

    public function __construct(
        protected int $month,
        protected int $year
    ) {}

    public function handle(PayrollService $payrollService): void
    {
        $employees = Employee::cursor(); // Memory efficient

        $count = 0;
        foreach ($employees as $emp) {
            try {
                $payrollService->calculate($emp, [
                    'month' => $this->month,
                    'year' => $this->year,
                    'base_salary' => $emp->base_salary,
                ]);
                $count++;
            } catch (\Exception $e) {
                Log::error("Payroll calc failed for employee {$emp->id}: " . $e->getMessage());
            }
        }

        Log::info("Payroll generated for {$count} employees: month={$this->month}, year={$this->year}");
    }
}

