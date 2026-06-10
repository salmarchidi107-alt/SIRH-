<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Salary;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\SalariesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class SalaryController extends Controller
{
    public function __construct(private PayrollService $payrollService) {}

    // =========================================================================
    // INDEX
    // =========================================================================
    public function index(Request $request)
    {
        $month      = (int) $request->get('month', now()->month);
        $year       = (int) $request->get('year',  now()->year);
        $status     = $request->get('status');
        $search     = $request->get('search');
        $department = $request->get('department');

        $dateDebut = $request->get('date_debut');
        $dateFin   = $request->get('date_fin');

        if ($dateDebut && $dateFin) {
            try {
                $debutCarbon = Carbon::parse($dateDebut)->startOfDay();
                $finCarbon   = Carbon::parse($dateFin)->endOfDay();
                if ($debutCarbon->gt($finCarbon)) {
                    [$debutCarbon, $finCarbon] = [$finCarbon, $debutCarbon];
                    $dateDebut = $debutCarbon->format('Y-m-d');
                    $dateFin   = $finCarbon->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $dateDebut   = null;
                $dateFin     = null;
                $debutCarbon = Carbon::create($year, $month, 1)->startOfMonth();
                $finCarbon   = Carbon::create($year, $month, 1)->endOfMonth();
            }
        } else {
            $dateDebut   = null;
            $dateFin     = null;
            $debutCarbon = Carbon::create($year, $month, 1)->startOfMonth();
            $finCarbon   = Carbon::create($year, $month, 1)->endOfMonth();
        }

        $periodesMois = $this->getPeriodesMois($debutCarbon, $finCarbon);

        $query = Employee::with([
            'salaries' => function ($q) use ($periodesMois, $status) {
                $q->where(function ($sub) use ($periodesMois) {
                    foreach ($periodesMois as $pm) {
                        $sub->orWhere(function ($inner) use ($pm) {
                            $inner->where('month', $pm['month'])
                                  ->where('year',  $pm['year']);
                        });
                    }
                })
                ->with(['createdBy', 'validatedBy', 'paidBy'])
                ->orderBy('year')
                ->orderBy('month');
                if ($status) {
                    $q->where('status', $status);
                }
            },
        ]);

        if ($status) {
            $query->whereHas('salaries', function ($q) use ($periodesMois, $status) {
                $q->where('status', $status)
                  ->where(function ($sub) use ($periodesMois) {
                      foreach ($periodesMois as $pm) {
                          $sub->orWhere(function ($inner) use ($pm) {
                              $inner->where('month', $pm['month'])
                                    ->where('year',  $pm['year']);
                          });
                      }
                  });
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name',  'like', "%$search%")
                  ->orWhere('matricule',  'like', "%$search%");
            });
        }

        if ($department) {
            $query->where('department', $department);
        }

        $employees    = $query->orderByRaw("CONCAT(first_name, ' ', last_name) ASC")->paginate(50);
        $summary      = $this->getSummaryPeriode($periodesMois, $status);
        $departments  = Department::names();

        return view('salary.index', compact(
            'employees', 'month', 'year', 'summary',
            'status', 'search', 'department', 'departments',
            'dateDebut', 'dateFin', 'periodesMois'
        ));
    }

    // =========================================================================
    // EXPORT PDF
    // =========================================================================
    public function exportPdf(Request $request)
    {
        $month      = (int) $request->get('month', now()->month);
        $year       = (int) $request->get('year',  now()->year);
        $status     = $request->get('status');
        $department = $request->get('department');
        $dateDebut  = $request->get('date_debut');
        $dateFin    = $request->get('date_fin');

        if ($dateDebut && $dateFin) {
            try {
                $debutCarbon = Carbon::parse($dateDebut)->startOfDay();
                $finCarbon   = Carbon::parse($dateFin)->endOfDay();
                if ($debutCarbon->gt($finCarbon)) {
                    [$debutCarbon, $finCarbon] = [$finCarbon, $debutCarbon];
                    $dateDebut = $debutCarbon->format('Y-m-d');
                    $dateFin   = $finCarbon->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $dateDebut   = null;
                $dateFin     = null;
                $debutCarbon = Carbon::create($year, $month, 1)->startOfMonth();
                $finCarbon   = Carbon::create($year, $month, 1)->endOfMonth();
            }
        } else {
            $dateDebut   = null;
            $dateFin     = null;
            $debutCarbon = Carbon::create($year, $month, 1)->startOfMonth();
            $finCarbon   = Carbon::create($year, $month, 1)->endOfMonth();
        }

        $periodesMois = $this->getPeriodesMois($debutCarbon, $finCarbon);

        $query = Employee::with([
            'salaries' => function ($q) use ($periodesMois, $status) {
                $q->where(function ($sub) use ($periodesMois) {
                    foreach ($periodesMois as $pm) {
                        $sub->orWhere(function ($inner) use ($pm) {
                            $inner->where('month', $pm['month'])
                                  ->where('year',  $pm['year']);
                        });
                    }
                })
                ->with(['createdBy', 'validatedBy', 'paidBy'])
                ->orderBy('year')
                ->orderBy('month');
                if ($status) {
                    $q->where('status', $status);
                }
            },
        ]);

        if ($status) {
            $query->whereHas('salaries', function ($q) use ($periodesMois, $status) {
                $q->where('status', $status)
                  ->where(function ($sub) use ($periodesMois) {
                      foreach ($periodesMois as $pm) {
                          $sub->orWhere(function ($inner) use ($pm) {
                              $inner->where('month', $pm['month'])
                                    ->where('year',  $pm['year']);
                          });
                      }
                  });
            });
        }

        if ($department) {
            $query->where('department', $department);
        }

        $allEmployees = $query->orderByRaw("CONCAT(first_name, ' ', last_name) ASC")->get();
        $summary      = $this->getSummaryPeriode($periodesMois, $status);

        if ($dateDebut && $dateFin) {
            $periodLabel = Carbon::parse($dateDebut)->locale('fr')->isoFormat('D MMM YYYY')
                . ' → '
                . Carbon::parse($dateFin)->locale('fr')->isoFormat('D MMM YYYY');
        } else {
            $periodLabel = ucfirst(
                Carbon::create($year, $month)->locale('fr')->isoFormat('MMMM YYYY')
            );
        }

        $data = [
            'allEmployees' => $allEmployees,
            'summary'      => $summary,
            'periodLabel'  => $periodLabel,
            'month'        => $month,
            'year'         => $year,
            'department'   => $department,
            'status'       => $status,
            'dateDebut'    => $dateDebut,
            'dateFin'      => $dateFin,
            'tenant'       => auth()->user()?->tenant,
        ];

        $pdf = Pdf::loadView('salary.export_pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'DejaVu Sans',
                'dpi'                  => 150,
            ]);

        return $pdf->download('paie-' . Str::slug($periodLabel) . '.pdf');
    }

    // =========================================================================
    // Couples (mois, année) couverts entre deux dates
    // =========================================================================
    private function getPeriodesMois(Carbon $debut, Carbon $fin): array
    {
        $periodes    = [];
        $courant     = $debut->copy()->startOfMonth();
        $dernierMois = $fin->copy()->startOfMonth();

        while ($courant->lte($dernierMois)) {
            $periodes[] = ['month' => $courant->month, 'year' => $courant->year];
            $courant->addMonth();
        }

        return $periodes;
    }

    // =========================================================================
    // Summary agrégé sur plusieurs mois
    // =========================================================================
    private function getSummaryPeriode(array $periodesMois, ?string $status = null): array
    {
        if (count($periodesMois) === 1) {
            return $this->payrollService->getMonthlySummary(
                $periodesMois[0]['month'],
                $periodesMois[0]['year']
            );
        }

        $query = Salary::where(function ($q) use ($periodesMois) {
            foreach ($periodesMois as $pm) {
                $q->orWhere(function ($inner) use ($pm) {
                    $inner->where('month', $pm['month'])->where('year', $pm['year']);
                });
            }
        });

        if ($status) {
            $query->where('status', $status);
        }

        $salaries = $query->get();

        return [
            'total_gross'         => $salaries->sum('gross_salary'),
            'total_net'           => $salaries->sum('net_salary'),
            'total_cnss_sal'      => $salaries->sum('cnss_deduction'),
            'total_amo_sal'       => $salaries->sum('amo_deduction'),
            'total_ir'            => $salaries->sum('ir_deduction'),
            'total_employer_cost' => $salaries->sum('employer_total_cost'),
            'count'               => $salaries->count(),
            'count_draft'         => $salaries->where('status', 'draft')->count(),
            'count_validated'     => $salaries->where('status', 'validated')->count(),
            'count_paid'          => $salaries->where('status', 'paid')->count(),
        ];
    }

    // =========================================================================
    // SHOW
    // =========================================================================
    public function show(Employee $employee)
    {
        if (auth()->user()->isEmployee() && auth()->user()->employee_id !== $employee->id) {
            abort(403);
        }

        $salaries = $employee->salaries()
            ->with(['createdBy', 'validatedBy', 'paidBy'])
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        return view('salary.show', compact('employee', 'salaries'));
    }

    // =========================================================================
    // CREATE
    // =========================================================================
    public function create(Employee $employee, Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        $existing = $employee->salaries()
            ->where('month', $month)
            ->where('year',  $year)
            ->first();

        $variableElements = $employee->variableElements()
            ->where('month', $month)
            ->where('year',  $year)
            ->get();

        $workingData = $this->payrollService->getMonthlyWorkingHours(
            $employee->id, $month, $year
        );

        return view('salary.create', compact(
            'employee', 'month', 'year',
            'existing', 'variableElements', 'workingData'
        ));
    }

    // =========================================================================
    // STORE / UPDATE
    // =========================================================================
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

            // ── Les deux champs manquants — cause du bug ───────────
            'garde_indemnite'          => 'nullable|numeric|min:0',
            'garde_override'           => 'nullable|boolean',
        ]);

        $month = (int) $data['month'];
        $year  = (int) $data['year'];

        $salary = Salary::firstOrNew([
            'employee_id' => $employee->id,
            'month'       => $month,
            'year'        => $year,
        ]);

        if ($salary->exists && in_array($salary->status, ['validated', 'paid'])) {
            return redirect()
                ->route('salary.show', $employee)
                ->with('error', 'Ce bulletin est deja valide ou paye. Impossible de le modifier.');
        }

        if (! $salary->exists) {
            $salary->created_by = auth()->id();
        }

        $salary->fill([
            'employee_id'              => $employee->id,
            'month'                    => $month,
            'year'                     => $year,
            'currency'                 => $data['currency']        ?? 'MAD',
            'salary_type'              => $data['salary_type']     ?? 'monthly',
            'hourly_rate'              => $data['hourly_rate']     ?? 0,
            'working_hours'            => $data['working_hours']   ?? 0,
            'mode_cotisation'          => $data['mode_cotisation'] ?? 'auto',
            'base_salary'              => $data['base_salary'],
            'performance_bonus'        => $data['performance_bonus']        ?? 0,
            'transport_allowance'      => $data['transport_allowance']      ?? 0,
            'meal_allowance'           => $data['meal_allowance']           ?? 0,
            'housing_allowance'        => $data['housing_allowance']        ?? 0,
            'responsibility_allowance' => $data['responsibility_allowance'] ?? 0,
            'other_gains'              => $data['other_gains']              ?? 0,
            'advance_deduction'        => $data['advance_deduction']        ?? 0,
            'loan_deduction'           => $data['loan_deduction']           ?? 0,
            'garnishment_deduction'    => $data['garnishment_deduction']    ?? 0,
            'other_deductions'         => $data['other_deductions']         ?? 0,
            'cnss_deduction_manual'    => $data['cnss_deduction_manual']    ?? null,
            'amo_deduction_manual'     => $data['amo_deduction_manual']     ?? null,
            'fp_deduction_manual'      => $data['fp_deduction_manual']      ?? null,
            'gross_salary'             => $data['gross_salary']             ?? 0,
            'seniority_bonus'          => $data['seniority_bonus']          ?? 0,
            'overtime_day_amount'      => $data['overtime_day_amount']      ?? 0,
            'overtime_night_amount'    => $data['overtime_night_amount']    ?? 0,
            'overtime_weekend_amount'  => $data['overtime_weekend_amount']  ?? 0,
            'overtime_hours'           => $data['overtime_hours']           ?? 0,
            'overtime_hours_day'       => $data['overtime_hours_day']       ?? 0,
            'overtime_hours_night'     => $data['overtime_hours_night']     ?? 0,
            'overtime_hours_weekend'   => $data['overtime_hours_weekend']   ?? 0,
            'absence_deduction'        => $data['absence_deduction']        ?? 0,
            'absence_days'             => $data['absence_days']             ?? 0,
            'absence_hours'            => $data['absence_hours']            ?? 0,
            'delay_hours'              => $data['delay_hours']              ?? 0,
            'garde_hours'              => $data['garde_hours']              ?? 0,
            'cnss_base'                => $data['cnss_base']                ?? 0,
            'cnss_deduction'           => $data['cnss_deduction']           ?? 0,
            'amo_deduction'            => $data['amo_deduction']            ?? 0,
            'fp_deduction'             => $data['fp_deduction']             ?? 0,
            'taxable_income'           => $data['taxable_income']           ?? 0,
            'ir_annual'                => $data['ir_annual']                ?? 0,
            'ir_family_deduction'      => $data['ir_family_deduction']      ?? 0,
            'ir_deduction'             => $data['ir_deduction']             ?? 0,
            'net_salary'               => $data['net_salary']               ?? 0,
            'employer_cnss'            => $data['employer_cnss']            ?? 0,
            'employer_amo'             => $data['employer_amo']             ?? 0,
            'employer_tfp'             => $data['employer_tfp']             ?? 0,
            'employer_total_cost'      => $data['employer_total_cost']      ?? 0,
            'status'                   => $salary->status ?? 'draft',

            // ── Persister la garde manuelle ────────────────────────
            'garde_indemnite'          => $data['garde_indemnite'] ?? 0,
            'garde_override'           => (bool) ($data['garde_override'] ?? false),
        ]);

        $salary->save();

        $this->payrollService->clearSummaryCache($month, $year);

        return redirect()
            ->route('salary.show', $employee)
            ->with('success', 'Bulletin de paie enregistre avec succes.');
    }

    // =========================================================================
    // VALIDATE
    // =========================================================================
    public function validateSalary(Salary $salary)
    {
        abort_if(auth()->user()->isEmployee(), 403);
        abort_if($salary->status !== 'draft', 403, 'Ce bulletin ne peut pas etre valide.');

        $salary->update([
            'status'       => 'validated',
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        $this->payrollService->clearSummaryCache($salary->month, $salary->year);

        return back()->with('success', 'Bulletin valide.');
    }

    // =========================================================================
    // MARK PAID
    // =========================================================================
    public function markPaid(Salary $salary)
    {
        abort_if(auth()->user()->isEmployee(), 403);
        abort_if($salary->status !== 'validated', 403, "Valider d'abord le bulletin.");

        $salary->update([
            'status'  => 'paid',
            'paid_by' => auth()->id(),
            'paid_at' => now(),
        ]);

        $this->payrollService->clearSummaryCache($salary->month, $salary->year);

        return back()->with('success', 'Bulletin marque comme paye.');
    }

    // =========================================================================
    // DESTROY
    // =========================================================================
    public function destroy(Salary $salary)
    {
        abort_if($salary->status !== 'draft', 403, 'Seuls les bulletins brouillon peuvent etre supprimes.');

        $employee = $salary->employee;
        $month    = $salary->month;
        $year     = $salary->year;

        $salary->delete();

        $this->payrollService->clearSummaryCache($month, $year);

        return redirect()->route('salary.show', $employee)
            ->with('success', 'Bulletin supprime.');
    }

    // =========================================================================
    // PDF BULLETIN INDIVIDUEL
    // =========================================================================
    public function pdf(Salary $salary)
    {
        if (auth()->user()->isEmployee() && auth()->user()->employee_id !== $salary->employee_id) {
            abort(403);
        }

        $salary->load('employee');

        $pdf = Pdf::loadView('salary.bulletin_de_paie', compact('salary'))
            ->setPaper('a4', 'portrait');

        $filename = 'bulletin-'
            . str($salary->employee->full_name)->slug() . '-'
            . str_pad($salary->month, 2, '0', STR_PAD_LEFT) . '-'
            . $salary->year . '.pdf';

        return $pdf->download($filename);
    }

    // =========================================================================
    // GENERATE ALL
    // =========================================================================
    public function generateAll(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2000',
        ]);

        \App\Jobs\GeneratePayrollJob::dispatch($request->month, $request->year);

        $this->payrollService->clearSummaryCache($request->month, $request->year);

        return redirect()
            ->route('salary.index', ['month' => $request->month, 'year' => $request->year])
            ->with('success', 'Generation des paies lancee en arriere-plan.');
    }

    // =========================================================================
    // EXPORT EXCEL
    // =========================================================================
    public function export()
    {
        return Excel::download(new SalariesExport, 'salaires.xlsx');
    }
}
