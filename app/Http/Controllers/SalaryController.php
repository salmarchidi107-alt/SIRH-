<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Salary;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\SalariesExport;
use Maatwebsite\Excel\Facades\Excel;

class SalaryController extends Controller
{
    public function __construct(private PayrollService $payrollService) {}



    public function index(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $status = $request->get('status');
        $search = $request->get('search');

        $query = Employee::with([
            'salaries' => fn($q) => $q->where('month', $month)->where('year', $year),
        ]);

        if ($status) {
            $query->whereHas('salaries', function ($q) use ($status, $month, $year) {
                $q->where('status', $status)
                  ->where('month', $month)
                  ->where('year', $year);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('matricule', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        $employees = $query->orderByRaw("CONCAT(first_name, ' ', last_name) ASC")
            ->paginate(15);

        $summary = $this->payrollService->getMonthlySummary($month, $year);

        return view('salary.index', compact('employees', 'month', 'year', 'summary', 'status', 'search'));
    }

    public function show(Employee $employee)
    {
        if (auth()->user()->isEmployee() && auth()->user()->employee_id !== $employee->id) {
            abort(403, 'Accès non autorisé.');
        }

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
            auth()->user()->isEmployee(),
            403,
            'Accès non autorisé.'
        );

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
            auth()->user()->isEmployee(),
            403,
            'Accès non autorisé.'
        );

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
        if (auth()->user()->isEmployee() && auth()->user()->employee_id !== $salary->employee_id) {
            abort(403, 'Accès non autorisé.');
        }

        $salary->load('employee');

        // Préparer données pour bulletin
        $paie = [
'salaire_base' => $salary->base_salary,
            'prime_anciennete' => $salary->seniority_bonus ?? 0,
            'salaire_brut' => $salary->gross_salary,
            'net_a_payer' => $salary->net_salary,
            'cotisation_cnss' => $salary->cnss_deduction,
            'cotisation_amo' => $salary->amo_deduction,
            'ir_deduction' => $salary->ir_deduction,
            // Ajoutez autres champs selon besoin
        ];

        $employe = [
            'matricule' => $salary->employee->employee_code ?? $salary->employee->id,
            'nom' => $salary->employee->full_name,
            'fonction' => $salary->employee->position ?? 'Employé',
            'depart' => $salary->employee->department ?? '',
            'date_embauche' => $salary->employee->hire_date ? $salary->employee->hire_date->format('d/m/Y') : '',
            // Ajoutez autres champs
        ];

        $periode = [
            'debut' => \Carbon\Carbon::create($salary->year, $salary->month, 1)->format('d/m/Y'),
            'fin' => \Carbon\Carbon::create($salary->year, $salary->month)->endOfMonth()->format('d/m/Y'),
        ];

        $pdf = Pdf::loadView('salary.bulletin_de_paie', compact('salary', 'paie', 'employe', 'periode'))
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

        \App\Jobs\GeneratePayrollJob::dispatch($request->month, $request->year);

        return redirect()
            ->route('salary.index', [
                'month' => $request->month,
                'year' => $request->year
            ])
            ->with('success', 'Génération des paies lancée en arrière-plan (file d\'attente).');
    }

    public function export()
    {
        return Excel::download(new SalariesExport, 'salaires.xlsx');
    }
}

