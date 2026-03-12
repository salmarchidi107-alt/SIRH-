<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Salary;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    public function index()
    {
        $employees = Employee::where('status', 'active')
            ->with(['salaries' => fn($q) => $q->latest()->take(1)])
            ->paginate(20);

        return view('salary.index', compact('employees'));
    }

    public function show(Employee $employee)
    {
        $salaries = Salary::where('employee_id', $employee->id)
            ->orderByDesc('year')->orderByDesc('month')
            ->paginate(12);

        return view('salary.show', compact('employee', 'salaries'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000',
            'base_salary' => 'required|numeric|min:0',
            'bonuses' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $base = $validated['base_salary'];
        $bonuses = $validated['bonuses'] ?? 0;
        $overtime_pay = ($validated['overtime_hours'] ?? 0) * ($base / 173.33) * 1.25;
        $gross = $base + $bonuses + $overtime_pay;

        // Moroccan payroll calculations
        $cnss = min($gross * 0.0448, 419.96);
        $amo = $gross * 0.0226;
        $taxable = $gross - $cnss - $amo;
        $ir = $this->calculateIR($taxable * 12) / 12;

        $net = $gross - $cnss - $amo - $ir - ($validated['deductions'] ?? 0);

        Salary::updateOrCreate(
            ['employee_id' => $employee->id, 'month' => $validated['month'], 'year' => $validated['year']],
            array_merge($validated, [
                'overtime_pay' => $overtime_pay,
                'cnss_deduction' => $cnss,
                'amo_deduction' => $amo,
                'ir_deduction' => $ir,
                'net_salary' => $net,
            ])
        );

        return back()->with('success', 'Fiche de paie générée avec succès.');
    }

    private function calculateIR(float $annual): float
    {
        if ($annual <= 30000) return 0;
        if ($annual <= 50000) return ($annual - 30000) * 0.10;
        if ($annual <= 60000) return 2000 + ($annual - 50000) * 0.20;
        if ($annual <= 80000) return 4000 + ($annual - 60000) * 0.30;
        if ($annual <= 180000) return 10000 + ($annual - 80000) * 0.34;
        return 44000 + ($annual - 180000) * 0.38;
    }
}
