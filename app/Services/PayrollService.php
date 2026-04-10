<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Salary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollService
{
    /**
     * Calculate salary for employee and create/update bulletin.
     */
    public function calculate(Employee $employee, array $data): Salary
    {
        $month = $data['month'];
        $year = $data['year'];

        DB::beginTransaction();

        try {
            // Delete existing draft for same month/year
            $employee->salaries()
                ->where('month', $month)
                ->where('year', $year)
                ->where('status', 'draft')
                ->delete();

            // Calculate components
            $baseSalary = $data['base_salary'] ?? $employee->base_salary;
            $overtimeDay = ($data['overtime_day_hours'] ?? 0) * 12; // 12 MAD/hour assumed
            $overtimeNight = ($data['overtime_night_hours'] ?? 0) * 18;
            $overtimeWeekend = ($data['overtime_weekend_hours'] ?? 0) * 24;

            $performanceBonus = $data['performance_bonus'] ?? 0;
            $transport = $data['transport_allowance'] ?? 0;
            $meal = $data['meal_allowance'] ?? 0;
            $housing = $data['housing_allowance'] ?? 0;
            $responsibility = $data['responsibility_allowance'] ?? 0;

            $absenceDeduction = ($data['absence_days'] ?? 0) * ($baseSalary / 26 / 8 * 8); // Daily rate
            $advance = $data['advance_deduction'] ?? 0;
            $loan = $data['loan_deduction'] ?? 0;
            $garnishment = $data['garnishment_deduction'] ?? 0;

            // Gross salary
            $grossSalary = $baseSalary + $overtimeDay + $overtimeNight + $overtimeWeekend
                         + $performanceBonus + $transport + $meal + $housing + $responsibility;

            // CNSS Employee  (4.28% + 2.26% AMO)
            $cnssEmployee = $grossSalary * 0.0678; // Approx 6.78%
            $cnssDeduction = min($cnssEmployee, 600); // CNSS cap

            // IR (simplified progressive tax ~10-30%)
            $taxable = $grossSalary - $cnssDeduction - $absenceDeduction;
            $irDeduction = $taxable * 0.15; // Simplified 15%

            // Net salary
            $netSalary = $grossSalary - $cnssDeduction - $irDeduction - $advance - $loan - $garnishment + $absenceDeduction * -1;

            // Create bulletin
            $salary = $employee->salaries()->create([
                'month' => $month,
                'year' => $year,
                'base_salary' => $baseSalary,
                'overtime_day_hours' => $data['overtime_day_hours'] ?? 0,
                'overtime_night_hours' => $data['overtime_night_hours'] ?? 0,
                'overtime_weekend_hours' => $data['overtime_weekend_hours'] ?? 0,
                'performance_bonus' => $performanceBonus,
                'transport_allowance' => $transport,
                'meal_allowance' => $meal,
                'housing_allowance' => $housing,
                'responsibility_allowance' => $responsibility,
                'absence_days' => $data['absence_days'] ?? 0,
                'advance_deduction' => $advance,
                'loan_deduction' => $loan,
                'garnishment_deduction' => $garnishment,
                'gross_salary' => $grossSalary,
                'cnss_deduction' => $cnssDeduction,
                'ir_deduction' => $irDeduction,
                'net_salary' => $netSalary,
                'status' => 'draft',
            ]);

            DB::commit();

            Log::info('Payroll calculated', [
                'employee_id' => $employee->id,
                'month' => $month,
                'year' => $year,
                'net_salary' => $netSalary,
            ]);

            return $salary;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payroll calculation failed', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get monthly payroll summary for dashboard/reports.
     */
    public function getMonthlySummary(int $month, int $year): array
    {
        $summary = Salary::selectRaw('
            SUM(base_salary + bonuses + overtime_pay) as total_gross,
            SUM(cnss_deduction) as total_cnss,
            SUM(ir_deduction) as total_ir,
            SUM(net_salary) as total_net,
            COUNT(*) as count,
            SUM(CASE WHEN status = "validated" THEN 1 ELSE 0 END) as validated_count
        ')
        ->where('month', $month)
        ->where('year', $year)
        ->where('tenant_id', config('app.current_tenant_id'))
        ->first();

        return [
            'total_gross' => $summary->total_gross ?? 0,
            'total_cnss' => $summary->total_cnss ?? 0,
            'total_ir' => $summary->total_ir ?? 0,
            'total_net' => $summary->total_net ?? 0,
            'count' => $summary->count ?? 0,
            'validated_count' => $summary->validated_count ?? 0,
        ];
    }
}

