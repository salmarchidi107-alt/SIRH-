<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Salary;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SalaryController extends Controller
{
    public function __construct(private PayrollService $payrollService) {}

    public function getMonthlySummary(int $month, int $year): array
    {
        $salaries = Salary::where('month', $month)
            ->where('year', $year)
            ->get();

        return [
            'total_gross' => $salaries->sum('gross_salary'),
            'total_cnss_sal' => $salaries->sum('cnss_deduction'),
            'total_amo_sal' => $salaries->sum('amo_deduction'),
            'total_ir' => $salaries->sum('ir_deduction'),
            'total_net' => $salaries->sum('net_salary'),
            'count' => $salaries->count(),
            'count_validated' => $salaries->where('status', 'validated')->count(),
            'count_paid' => $salaries->where('status', 'paid')->count(),
            'total_employer_cost' => 0,
            'total_employer_cnss' => 0,
            'total_employer_amo' => 0,
            'total_employer_tfp' => 0,
            'count_draft' => $salaries->where('status', 'draft')->count(),
        ];
    }

    public function index(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $employees = Employee::with([
            'salaries' => fn($q) => $q->where('month', $month)->where('year', $year),
        ])
        ->orderByRaw("CONCAT(first_name, ' ', last_name) ASC")
        ->get();

        $summary = $this->payrollService->getMonthlySummary($month, $year);

        return view('salary.index', compact('employees', 'month', 'year', 'summary'));
    }

    public function show(Employee $employee)
    {
        $salaries = $employee->salaries()
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        return view('salary.show', compact('employee', 'salaries'));
    }

    public function create(Employee $employee, Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $existing = $employee->salaries()
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        $variableElements = $employee->variableElements()
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        return view('salary.create', compact(
            'employee',
            'month',
            'year',
            'existing',
            'variableElements'
        ));
    }

    public function store(Request $request, Employee $employee)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
            'base_salary' => 'required|numeric|min:0',
            'overtime_day_hours' => 'nullable|numeric|min:0',
            'overtime_night_hours' => 'nullable|numeric|min:0',
            'overtime_weekend_hours' => 'nullable|numeric|min:0',
            'performance_bonus' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'meal_allowance' => 'nullable|numeric|min:0',
            'housing_allowance' => 'nullable|numeric|min:0',
            'responsibility_allowance' => 'nullable|numeric|min:0',
            'absence_days' => 'nullable|numeric|min:0|max:31',
            'advance_deduction' => 'nullable|numeric|min:0',
            'loan_deduction' => 'nullable|numeric|min:0',
            'garnishment_deduction' => 'nullable|numeric|min:0',
        ]);

        $this->payrollService->calculate($employee, $request->all());

        return redirect()
            ->route('salary.show', $employee)
            ->with('success', 'Bulletin de paie calculé avec succès.');
    }

    public function validateSalary(Salary $salary)
    {
        abort_if(
            $salary->status !== 'draft',
            403,
            'Ce bulletin ne peut pas être validé.'
        );

        $salary->update(['status' => 'validated']);

        return back()->with('success', 'Bulletin validé.');
    }

    public function markPaid(Salary $salary)
    {
        abort_if(
            $salary->status !== 'validated',
            403,
            "Valider d'abord le bulletin."
        );

        $salary->update(['status' => 'paid']);

        return back()->with('success', 'Bulletin marqué comme payé.');
    }

    public function destroy(Salary $salary)
    {
        abort_if(
            $salary->status !== 'draft',
            403,
            'Seuls les bulletins brouillon peuvent être supprimés.'
        );

        $employee = $salary->employee;

        $salary->delete();

        return redirect()
            ->route('salary.show', $employee)
            ->with('success', 'Bulletin supprimé.');
    }

    public function pdf(Salary $salary)
    {
        $salary->load('employee');

        $pdf = Pdf::loadView('salary.pdf', compact('salary'))
            ->setPaper('a4', 'portrait');

        $filename = 'bulletin-' .
            str($salary->employee->full_name)->slug() . '-' .
            str_pad($salary->month, 2, '0', STR_PAD_LEFT) . '-' .
            $salary->year . '.pdf';

        return $pdf->download($filename);
    }

    public function generateAll(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
        ]);

        $count = 0;

        foreach (Employee::all() as $emp) {
            $this->payrollService->calculate($emp, [
                'month' => $request->month,
                'year' => $request->year,
                'base_salary' => $emp->base_salary,
            ]);
            $count++;
        }

        return redirect()
            ->route('salary.index', [
                'month' => $request->month,
                'year' => $request->year
            ])
            ->with('success', "Paie générée pour $count employés.");
    }
}
