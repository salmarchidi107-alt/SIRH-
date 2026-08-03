<?php

namespace App\Services\Absence;

use App\Mail\AbsenceApproved;
use App\Mail\AbsenceRejected;
use App\Models\Absence;
use App\Models\DroitAbsence;
use App\Models\Employee;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;


class AbsenceService
{
    private const ABSENCE_TYPES_COUNTED = [
        'conge_annuel', 'conge_sans_solde', 'conge_maladie', 'absence_justifiee',
    ];

    public function __construct(
        private AbsenceConflictService $conflictService,
        private EmployeeFilterService $employeeFilterService,
    ) {}

    // =========================================================================
    // index
    // =========================================================================
    public function getIndexData(Request $request): array
    {
        $query = Absence::with([
            'employee:id,first_name,last_name,matricule,department',
            'replacement:id,first_name,last_name,matricule,department',
            'approvedByUser:id,name',
        ])->whereHas('employee');

        if (auth()->user()->isEmployee() && auth()->user()->employee_id) {
            $query->where('employee_id', auth()->user()->employee_id);
        } elseif ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        $query->when($request->status, fn ($q) => $q->where('status', $request->status))
              ->when($request->type, fn ($q) => $q->where('type', $request->type))
              ->when($request->search, fn ($q) => $q->whereHas('employee', function ($q) use ($request) {
                  $q->where('first_name', 'like', "%{$request->search}%")
                    ->orWhere('last_name', 'like', "%{$request->search}%");
              }));

        $absences = $query->latest()->paginate(20);

        $employeesQuery = Employee::active()
            ->when(auth()->user()->isEmployee(), fn ($q) => $q->where('id', auth()->user()->employee_id))
            ->select(['id', 'first_name', 'last_name', 'matricule', 'department']);
        $this->employeeFilterService->applyFilters($employeesQuery, $request);
        $employees = $employeesQuery->get();

        $departments   = $this->employeeFilterService->getDepartments();
        $pending_count = $this->getPendingCount();

        return compact('absences', 'employees', 'pending_count', 'departments');
    }

    private function getPendingCount(): int
    {
        $query = Absence::whereHas('employee')->where('status', 'pending');

        if (auth()->user()->isEmployee() && auth()->user()->employee_id) {
            $query->where('employee_id', auth()->user()->employee_id);
        }

        return $query->count();
    }

    // =========================================================================
    // create
    // =========================================================================
    public function getCreateData(): array
    {
        $user = auth()->user();

        if ($user->isEmployee() && $user->employee_id) {
            return $this->getCreateDataForEmployee($user->employee_id);
        }

        return $this->getCreateDataForAdmin();
    }

    private function getCreateDataForEmployee(int $employeeId): array
    {
        $employee  = Employee::find($employeeId);
        $employees = Employee::active()
            ->where('id', '!=', $employee->id)
            ->select(['id', 'first_name', 'last_name', 'matricule', 'department'])
            ->get();

        $departments     = $this->employeeFilterService->getDepartments();
        $employeeOptions = $this->employeeFilterService->buildEmployeeOptions($employees);

        $allIds        = $employees->pluck('id')->push($employee->id)->toArray();
        $selfConflicts = $this->conflictService->buildSelfConflictsData($allIds);

        return compact('employee', 'employees', 'departments', 'employeeOptions', 'selfConflicts');
    }

    private function getCreateDataForAdmin(): array
    {
        $employees = Employee::active()
            ->when(auth()->user()->isEmployee(), fn ($q) => $q->where('id', auth()->user()->employee_id))
            ->select(['id', 'first_name', 'last_name', 'matricule', 'department'])
            ->get();

        $departments     = $this->employeeFilterService->getDepartments();
        $employeeOptions = $this->employeeFilterService->buildEmployeeOptions($employees);
        $selfConflicts   = $this->conflictService->buildSelfConflictsData($employees->pluck('id')->toArray());

        return compact('employees', 'departments', 'employeeOptions', 'selfConflicts');
    }


    public function store(array $validated, Request $request): array
    {
        if ($validated['type'] === 'autre') {
            $typeAutre = trim($request->input('type_autre', ''));
            if (empty($typeAutre)) {
                return ['result' => 'type_autre_missing'];
            }
            $validated['type'] = $typeAutre;
        }

        $start = Carbon::parse($validated['start_date']);
        $end   = Carbon::parse($validated['end_date']);
        $validated['days']      = $start->diffInWeekdays($end) + 1;
        $validated['status']    = 'pending';
        $validated['tenant_id'] = config('app.current_tenant_id');

        $conflictingAbsence = $this->conflictService->findOtherEmployeeApprovedOverlap(
            $validated['employee_id'], $validated['start_date'], $validated['end_date']
        );

        $confirmed = $request->input('conflict_confirmed') === '1';

        if ($conflictingAbsence && ! $confirmed) {
            return [
                'result'        => 'conflict',
                'employee_name' => $conflictingAbsence->employee->full_name,
                'from'          => Carbon::parse($conflictingAbsence->start_date)->format('d/m/Y'),
                'to'            => Carbon::parse($conflictingAbsence->end_date)->format('d/m/Y'),
            ];
        }

        $selfConflict = $this->conflictService->hasSameEmployeeApprovedOverlap(
            $validated['employee_id'], $validated['start_date'], $validated['end_date']
        );

        if ($selfConflict) {
            return ['result' => 'self_conflict'];
        }

        $absence = Absence::create($validated);

        return ['result' => 'success', 'absence' => $absence];
    }

    // =========================================================================
    // show / edit / update / destroy
    // =========================================================================
    public function getShowData(Absence $absence): Absence
    {
        $absence->load(['employee', 'replacement', 'approver', 'approvedByUser']);

        return $absence;
    }

    public function getEditEmployees()
    {
        return Employee::active()
            ->when(auth()->user()->isEmployee(), fn ($q) => $q->where('id', auth()->user()->employee_id))
            ->select(['id', 'first_name', 'last_name', 'matricule', 'department'])
            ->get();
    }

    public function update(Absence $absence, array $validated): Absence
    {
        $start = Carbon::parse($validated['start_date']);
        $end   = Carbon::parse($validated['end_date']);
        $validated['days'] = $start->diffInWeekdays($end) + 1;

        $absence->update($validated);

        return $absence;
    }

    public function delete(Absence $absence): void
    {
        $absence->delete();
    }

    // =========================================================================
    // approve / reject
    // =========================================================================
    public function approve(Absence $absence, int $userId): void
    {
        $absence->update([
            'tenant_id'   => config('app.current_tenant_id'),
            'status'      => 'approved',
            'approved_at' => now(),
            'approved_by' => $userId,
        ]);

        $this->updateDroitAbsenceAfterApproval($absence);
        $this->sendApprovalEmail($absence);
    }

    private function updateDroitAbsenceAfterApproval(Absence $absence): void
    {
        if (! in_array($absence->type, self::ABSENCE_TYPES_COUNTED)) {
            return;
        }

        $year  = $absence->start_date->year;
        $droit = DroitAbsence::getOuCreeParAnnee($absence->employee_id, $year);
        $droit->jours_pris  += $absence->days;
        $droit->jours_solde  = $droit->jours_acquis - $droit->jours_pris - $droit->jours_en_attente;
        $droit->save();
    }

    private function sendApprovalEmail(Absence $absence): void
    {
        if (! $absence->employee || ! $absence->employee->email) {
            return;
        }

        try {
            Mail::to($absence->employee->email)->send(new AbsenceApproved($absence));
        } catch (\Exception $e) {
            Log::error('Mail approve error: ' . $e->getMessage());
        }
    }

    public function reject(Absence $absence, int $userId): void
    {
        $absence->update([
            'status'      => 'rejected',
            'approved_at' => now(),
            'approved_by' => $userId,
        ]);

        $this->updateDroitAbsenceAfterRejection($absence);
        $this->sendRejectionEmail($absence);
    }

    private function updateDroitAbsenceAfterRejection(Absence $absence): void
    {
        $year  = $absence->start_date->year;
        $droit = DroitAbsence::where('employee_id', $absence->employee_id)
            ->where('annee', $year)->first();

        if (! $droit) {
            return;
        }

        $droit->jours_en_attente -= $absence->days;
        $droit->jours_solde       = $droit->jours_acquis - $droit->jours_pris - $droit->jours_en_attente;
        $droit->save();
    }

    private function sendRejectionEmail(Absence $absence): void
    {
        if (! $absence->employee || ! $absence->employee->email) {
            return;
        }

        try {
            Mail::to($absence->employee->email)->send(new AbsenceRejected($absence));
        } catch (\Exception $e) {
            Log::error('Mail reject error: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // calendar
    // =========================================================================
    public function getCalendarData(Request $request): array
    {
        $month    = $request->get('month', now()->month);
        $year     = $request->get('year', now()->year);
        $viewMode = $request->get('view', 'calendar');

        $firstDay     = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $today        = Carbon::today();
        $startOfMonth = $firstDay->copy();
        $endOfMonth   = $firstDay->copy()->endOfMonth();
        $daysInMonth  = $firstDay->daysInMonth;

        [$prevMonthUrl, $nextMonthUrl, $todayUrl, $resetUrl] =
            $this->buildCalendarNavigationUrls($request, $firstDay, $month, $year);

        $employees   = Employee::active()->orderBy('department')->orderBy('last_name')->get();
        $departments = $this->employeeFilterService->getDepartments();

        $employeesQuery = Employee::active()->orderBy('department')->orderBy('last_name');
        $this->employeeFilterService->applyFilters($employeesQuery, $request);
        $filteredEmployees = $employeesQuery->get();

        $absences = $this->getCalendarAbsences($request, $startOfMonth, $endOfMonth);

        $absenceMap = $this->conflictService->buildAbsenceMap($absences, (int) $month, (int) $year);

        $employeeIdsWithAbsences = $absences->pluck('employee_id')->unique();
        $employeesWithAbsences   = $employees->filter(fn ($emp) => $employeeIdsWithAbsences->contains($emp->id));

        $conflicts = $this->conflictService->buildConflicts($absences);

        $conflictEmpIds = $conflicts->flatMap(fn ($c) => [
            $c['employee_id_1'],
            $c['employee_id_2'],
        ])->unique()->values()->toArray();

        $replacements     = $absences->whereNotNull('replacement_id');
        $approvedAbsences = $absences->where('status', 'approved');
        $pendingAbsences  = $absences->where('status', 'pending');

        $stats = [
            'approved_count'     => $approvedAbsences->count(),
            'pending_count'      => $pendingAbsences->count(),
            'conflicts_count'    => $conflicts->count(),
            'replacements_count' => $replacements->count(),
            'total_days'         => $absences->sum('days'),
        ];

        return compact(
            'absences', 'conflicts', 'conflictEmpIds', 'replacements', 'employees',
            'employeesWithAbsences', 'month', 'year', 'firstDay', 'today', 'daysInMonth',
            'startOfMonth', 'endOfMonth', 'viewMode', 'filteredEmployees', 'absenceMap',
            'stats', 'prevMonthUrl', 'nextMonthUrl', 'todayUrl', 'resetUrl', 'departments'
        );
    }

    private function buildCalendarNavigationUrls(Request $request, Carbon $firstDay, $month, $year): array
    {
        $prevMonthData = array_merge($request->query(), [
            'month' => $firstDay->copy()->subMonth()->month,
            'year'  => $firstDay->copy()->subMonth()->year,
        ]);
        $nextMonthData = array_merge($request->query(), [
            'month' => $firstDay->copy()->addMonth()->month,
            'year'  => $firstDay->copy()->addMonth()->year,
        ]);
        $todayData = array_merge($request->query(), [
            'month' => now()->month,
            'year'  => now()->year,
        ]);

        return [
            route('absences.calendar', $prevMonthData),
            route('absences.calendar', $nextMonthData),
            route('absences.calendar', $todayData),
            route('absences.calendar', ['month' => $month, 'year' => $year]),
        ];
    }

    private function getCalendarAbsences(Request $request, Carbon $startOfMonth, Carbon $endOfMonth)
    {
        $query = Absence::with(['employee', 'replacement', 'approvedByUser:id,name'])
            ->whereHas('employee')
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                  ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                  ->orWhere(function ($q2) use ($startOfMonth, $endOfMonth) {
                      $q2->where('start_date', '<=', $startOfMonth)
                         ->where('end_date', '>=', $endOfMonth);
                  });
            })
            ->whereIn('status', ['approved', 'pending']);

        $query->when($request->department, fn ($q) => $q->whereHas('employee', function ($q2) use ($request) {
                    $this->employeeFilterService->applyFilters($q2, $request);
                }))
              ->when($request->employee_id, fn ($q) => $q->where('employee_id', $request->employee_id))
              ->when($request->status, fn ($q) => $q->where('status', $request->status));

        return $query->get();
    }

    // =========================================================================
    // downloadPdf
    // =========================================================================
    public function preparePdfData(Absence $absence): array
    {
        $absence->load(['employee', 'replacement', 'approver', 'approvedByUser']);
        Carbon::setLocale('fr');

        $tenant = $absence->employee->user?->tenant
            ?? Tenant::find(config('app.current_tenant_id'));

        $filename = 'demande_absence_'
            . str_replace(' ', '_', strtolower($absence->employee->full_name))
            . '_' . $absence->start_date->format('Y-m-d') . '.pdf';

        return compact('absence', 'tenant', 'filename');
    }
}
