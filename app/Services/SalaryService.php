<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Salary;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SalaryService
{
    public function __construct(private PayrollService $payrollService) {}

    public function getIndexData(Request $request): array
    {
        $month      = (int) $request->get('month', now()->month);
        $year       = (int) $request->get('year',  now()->year);
        $status     = $request->get('status');
        $search     = $request->get('search');
        $department = $request->get('department');

        $period       = $this->resolvePeriod($request->get('date_debut'), $request->get('date_fin'), $month, $year);
        $periodesMois = $period['periodesMois'];

        $employees = $this->buildEmployeeQuery($periodesMois, $status, $search, $department)
            ->orderByRaw("CONCAT(first_name, ' ', last_name) ASC")
            ->paginate(50);

        $summary     = $this->getSummaryPeriode($periodesMois, $status);
        $departments = Department::names();

        return [
            'employees'    => $employees,
            'month'        => $month,
            'year'         => $year,
            'summary'      => $summary,
            'status'       => $status,
            'search'       => $search,
            'department'   => $department,
            'departments'  => $departments,
            'dateDebut'    => $period['dateDebut'],
            'dateFin'      => $period['dateFin'],
            'periodesMois' => $periodesMois,
        ];
    }

    public function getExportPdfData(Request $request): array
    {
        $month      = (int) $request->get('month', now()->month);
        $year       = (int) $request->get('year',  now()->year);
        $status     = $request->get('status');
        $department = $request->get('department');

        $period       = $this->resolvePeriod($request->get('date_debut'), $request->get('date_fin'), $month, $year);
        $periodesMois = $period['periodesMois'];
        $dateDebut    = $period['dateDebut'];
        $dateFin      = $period['dateFin'];

        // Note : contrairement à index(), l'export PDF n'applique pas de filtre "search".
        $allEmployees = $this->buildEmployeeQuery($periodesMois, $status, null, $department)
            ->orderByRaw("CONCAT(first_name, ' ', last_name) ASC")
            ->get();

        $summary = $this->getSummaryPeriode($periodesMois, $status);

        if ($dateDebut && $dateFin) {
            $periodLabel = Carbon::parse($dateDebut)->locale('fr')->isoFormat('D MMM YYYY')
                . ' → '
                . Carbon::parse($dateFin)->locale('fr')->isoFormat('D MMM YYYY');
        } else {
            $periodLabel = ucfirst(
                Carbon::create($year, $month)->locale('fr')->isoFormat('MMMM YYYY')
            );
        }

        return [
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
    }


    public function getEmployeeSalaries(Employee $employee): Collection
    {
        return $employee->salaries()
            ->with(['createdBy', 'validatedBy', 'paidBy'])
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();
    }

    public function getCreateData(Employee $employee, int $month, int $year): array
    {
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

        return compact('existing', 'variableElements', 'workingData');
    }


    /**
     * Crée ou met à jour le bulletin de paie du mois/année indiqués.
     * Modifiable quel que soit son statut (draft, validated, paid) :
     * le statut du bulletin est conservé tel quel après la mise à jour,
     * il n'est jamais réinitialisé à 'draft' par cette méthode.
     */
    public function upsertSalary(Employee $employee, array $data): Salary
    {
        $month = (int) $data['month'];
        $year  = (int) $data['year'];

        $salary = Salary::firstOrNew([
            'employee_id' => $employee->id,
            'month'       => $month,
            'year'        => $year,
        ]);

        if (! $salary->exists) {
            $salary->created_by = auth()->id();
        }

        $salary->fill([
            'employee_id'              => $employee->id,
            'month'                    => $month,
            'year'                     => $year,
            // ── Devise : priorité au champ soumis dans le formulaire,
            //    sinon devise du tenant de l'employé, sinon MAD.
            //    Ne retombe plus systématiquement sur 'MAD' en dur.
            'currency'                 => $data['currency'] ?? $employee->tenant?->currency ?? 'MAD',
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
            'garde_indemnite'          => $data['garde_indemnite'] ?? 0,
            'garde_override'           => (bool) ($data['garde_override'] ?? false),
        ]);

        $salary->save();

        $this->payrollService->clearSummaryCache($month, $year);

        return $salary;
    }



    public function markValidated(Salary $salary): void
    {
        $salary->update([
            'status'       => 'validated',
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        $this->payrollService->clearSummaryCache($salary->month, $salary->year);
    }

    public function markAsPaid(Salary $salary): void
    {
        $salary->update([
            'status'  => 'paid',
            'paid_by' => auth()->id(),
            'paid_at' => now(),
        ]);

        $this->payrollService->clearSummaryCache($salary->month, $salary->year);
    }


    /**
     * Supprime le bulletin et retourne l'employé associé
     * (nécessaire au contrôleur pour la redirection vers salary.show).
     */
    public function deleteSalary(Salary $salary): Employee
    {
        $employee = $salary->employee;
        $month    = $salary->month;
        $year     = $salary->year;

        $salary->delete();

        $this->payrollService->clearSummaryCache($month, $year);

        return $employee;
    }


    public function getBulletinPdfData(Salary $salary): array
    {
        $salary->load('employee');

        $filename = 'bulletin-'
            . str($salary->employee->full_name)->slug() . '-'
            . str_pad($salary->month, 2, '0', STR_PAD_LEFT) . '-'
            . $salary->year . '.pdf';

        return compact('salary', 'filename');
    }



    public function dispatchPayrollGeneration(int $month, int $year): void
    {
        \App\Jobs\GeneratePayrollJob::dispatch($month, $year);

        $this->payrollService->clearSummaryCache($month, $year);
    }


    /**
     * Résout la plage de dates à partir de date_debut/date_fin (si fournis
     * et valides), sinon retombe sur le mois civil demandé.
     */
    private function resolvePeriod(?string $dateDebut, ?string $dateFin, int $month, int $year): array
    {
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

        return compact('debutCarbon', 'finCarbon', 'dateDebut', 'dateFin', 'periodesMois');
    }

    /**
     * Couples (mois, année) couverts entre deux dates.
     */
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

    /**
     * Requête employés + bulletins de la/des période(s), avec les mêmes
     * filtres statut / recherche / département utilisés par index() et
     * exportPdf() (exportPdf n'utilise pas le filtre recherche).
     */
    private function buildEmployeeQuery(array $periodesMois, ?string $status, ?string $search, ?string $department): Builder
    {
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

        return $query;
    }

    /**
     * Summary agrégé sur plusieurs mois (ou délégué à PayrollService pour un seul mois).
     */
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
}
