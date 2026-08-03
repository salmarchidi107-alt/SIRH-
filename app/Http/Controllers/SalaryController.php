<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Salary;
use App\Services\SalaryService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\SalariesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class SalaryController extends Controller
{
    public function __construct(private SalaryService $salaryService) {}


    public function index(Request $request)
    {
        return view('salary.index', $this->salaryService->getIndexData($request));
    }


    public function exportPdf(Request $request)
    {
        $data = $this->salaryService->getExportPdfData($request);

        $pdf = Pdf::loadView('salary.export_pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'DejaVu Sans',
                'dpi'                  => 150,
            ]);

        return $pdf->download('paie-' . Str::slug($data['periodLabel']) . '.pdf');
    }


    public function show(Employee $employee)
    {
        if (auth()->user()->isEmployee() && auth()->user()->employee_id !== $employee->id) {
            abort(403);
        }

        $salaries = $this->salaryService->getEmployeeSalaries($employee);

        return view('salary.show', compact('employee', 'salaries'));
    }


    public function create(Employee $employee, Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        $data = $this->salaryService->getCreateData($employee, $month, $year);

        return view('salary.create', array_merge(compact('employee', 'month', 'year'), $data));
    }


    public function store(Request $request, Employee $employee)
    {
        return $this->_upsert($request, $employee);
    }

    public function update(Request $request, Employee $employee)
    {
        return $this->_upsert($request, $employee);
    }

    private function _upsert(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'month'                    => 'required|integer|min:1|max:12',
            'year'                     => 'required|integer|min:2000',
            'currency'                 => 'nullable|string|max:10',
            'salary_type'              => 'nullable|in:monthly,hourly',
            'hourly_rate'              => 'nullable|numeric|min:0',
            'working_hours'            => 'nullable|numeric|min:0',
            'mode_cotisation'          => 'nullable|in:auto,manual',
            'base_salary'              => 'required|numeric|min:0',
            'performance_bonus'        => 'nullable|numeric|min:0',
            'transport_allowance'      => 'nullable|numeric|min:0',
            'meal_allowance'           => 'nullable|numeric|min:0',
            'housing_allowance'        => 'nullable|numeric|min:0',
            'responsibility_allowance' => 'nullable|numeric|min:0',
            'other_gains'              => 'nullable|numeric|min:0',
            'advance_deduction'        => 'nullable|numeric|min:0',
            'loan_deduction'           => 'nullable|numeric|min:0',
            'garnishment_deduction'    => 'nullable|numeric|min:0',
            'other_deductions'         => 'nullable|numeric|min:0',
            'cnss_deduction_manual'    => 'nullable|numeric|min:0',
            'amo_deduction_manual'     => 'nullable|numeric|min:0',
            'fp_deduction_manual'      => 'nullable|numeric|min:0',
            'gross_salary'             => 'nullable|numeric|min:0',
            'seniority_bonus'          => 'nullable|numeric|min:0',
            'overtime_day_amount'      => 'nullable|numeric|min:0',
            'overtime_night_amount'    => 'nullable|numeric|min:0',
            'overtime_weekend_amount'  => 'nullable|numeric|min:0',
            'overtime_hours'           => 'nullable|numeric|min:0',
            'overtime_hours_day'       => 'nullable|numeric|min:0',
            'overtime_hours_night'     => 'nullable|numeric|min:0',
            'overtime_hours_weekend'   => 'nullable|numeric|min:0',
            'absence_deduction'        => 'nullable|numeric|min:0',
            'absence_days'             => 'nullable|numeric|min:0',
            'absence_hours'            => 'nullable|numeric|min:0',
            'delay_hours'              => 'nullable|numeric|min:0',
            'garde_hours'              => 'nullable|numeric|min:0',
            'cnss_base'                => 'nullable|numeric|min:0',
            'cnss_deduction'           => 'nullable|numeric|min:0',
            'amo_deduction'            => 'nullable|numeric|min:0',
            'fp_deduction'             => 'nullable|numeric|min:0',
            'taxable_income'           => 'nullable|numeric|min:0',
            'ir_annual'                => 'nullable|numeric|min:0',
            'ir_family_deduction'      => 'nullable|numeric|min:0',
            'ir_deduction'             => 'nullable|numeric|min:0',
            'net_salary'               => 'nullable|numeric|min:0',
            'employer_cnss'            => 'nullable|numeric|min:0',
            'employer_amo'             => 'nullable|numeric|min:0',
            'employer_tfp'             => 'nullable|numeric|min:0',
            'employer_total_cost'      => 'nullable|numeric|min:0',
            'garde_indemnite'          => 'nullable|numeric|min:0',
            'garde_override'           => 'nullable|boolean',
        ]);

        // ── Un admin est autorisé à modifier un bulletin même déjà
        //    validé ou payé ; les autres rôles restent bloqués (géré
        //    dans SalaryService::upsertSalary via ce second paramètre).
        $isAdmin = auth()->user()->isAdmin();

        $salary = $this->salaryService->upsertSalary($employee, $data, $isAdmin);

        if (! $salary) {
            return redirect()
                ->route('salary.show', $employee)
                ->with('error', 'Ce bulletin est deja valide ou paye. Impossible de le modifier.');
        }

        return redirect()
            ->route('salary.show', $employee)
            ->with('success', 'Bulletin de paie enregistre avec succes.');
    }


    public function validateSalary(Salary $salary)
    {
        abort_if(auth()->user()->isEmployee(), 403);
        abort_if($salary->status !== 'draft', 403, 'Ce bulletin ne peut pas etre valide.');

        $this->salaryService->markValidated($salary);

        return back()->with('success', 'Bulletin valide.');
    }


    public function markPaid(Salary $salary)
    {
        abort_if(auth()->user()->isEmployee(), 403);
        abort_if($salary->status !== 'validated', 403, "Valider d'abord le bulletin.");

        $this->salaryService->markAsPaid($salary);

        return back()->with('success', 'Bulletin marque comme paye.');
    }


    public function destroy(Salary $salary)
    {
        abort_if($salary->status !== 'draft', 403, 'Seuls les bulletins brouillon peuvent etre supprimes.');

        $employee = $this->salaryService->deleteSalary($salary);

        return redirect()->route('salary.show', $employee)
            ->with('success', 'Bulletin supprime.');
    }


    public function pdf(Salary $salary)
    {
        if (auth()->user()->isEmployee() && auth()->user()->employee_id !== $salary->employee_id) {
            abort(403);
        }

        $data = $this->salaryService->getBulletinPdfData($salary);

        $pdf = Pdf::loadView('salary.bulletin_de_paie', ['salary' => $data['salary']])
            ->setPaper('a4', 'portrait');

        return $pdf->download($data['filename']);
    }


    public function generateAll(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2000',
        ]);

        $this->salaryService->dispatchPayrollGeneration($request->month, $request->year);

        return redirect()
            ->route('salary.index', ['month' => $request->month, 'year' => $request->year])
            ->with('success', 'Generation des paies lancee en arriere-plan.');
    }

    public function export()
    {
        return Excel::download(new SalariesExport, 'salaires.xlsx');
    }
}
