<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Department;
use App\Models\Employee;
use App\Http\Requests\StoreAbsenceRequest;
use App\Http\Requests\UpdateAbsenceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Mail\AbsenceApproved;
use App\Mail\AbsenceRejected;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsencesExport;
use App\Exports\CountersExport;
use App\Exports\DroitsAbsenceExport;
use Barryvdh\DomPDF\Facade\Pdf;

class AbsenceController extends Controller
{
    // =========================================================================
    // index
    // =========================================================================
    public function index(Request $request)
    {
        $query = Absence::with([
            'employee:id,first_name,last_name,matricule,department',
            'replacement:id,first_name,last_name,matricule,department',
            'approvedByUser:id,name',
        ])->whereHas('employee');

        if (auth()->user()->isEmployee() && auth()->user()->employee_id) {
            $query->where('employee_id', auth()->user()->employee_id);
        } else {
            if ($request->employee_id) {
                $query->where('employee_id', $request->employee_id);
            }
        }

        $query->when($request->status, fn($q) => $q->where('status', $request->status))
              ->when($request->type,   fn($q) => $q->where('type',   $request->type))
              ->when($request->search, fn($q) => $q->whereHas('employee', function ($q) use ($request) {
                  $q->where('first_name', 'like', "%{$request->search}%")
                    ->orWhere('last_name',  'like', "%{$request->search}%");
              }));

        $absences = $query->latest()->paginate(20);

        $employeesQuery = Employee::active()
            ->when(auth()->user()->isEmployee(), fn($q) => $q->where('id', auth()->user()->employee_id))
            ->select(['id', 'first_name', 'last_name', 'matricule', 'department']);
        $this->applyEmployeeFilters($employeesQuery, $request);
        $employees = $employeesQuery->get();

        $departments = $this->getDepartments();

        if (auth()->user()->isEmployee() && auth()->user()->employee_id) {
            $pending_count = Absence::whereHas('employee')
                ->where('employee_id', auth()->user()->employee_id)
                ->where('status', 'pending')
                ->count();
        } else {
            $pending_count = Absence::whereHas('employee')
                ->where('status', 'pending')
                ->count();
        }

        return view('absences.index', compact('absences', 'employees', 'pending_count', 'departments'));
    }

    // =========================================================================
    // create
    // =========================================================================
    public function create()
    {
        if (auth()->user()->isEmployee() && auth()->user()->employee_id) {
            $employee  = Employee::find(auth()->user()->employee_id);
            $employees = Employee::active()
                ->where('id', '!=', $employee->id)
                ->select(['id', 'first_name', 'last_name', 'matricule', 'department'])
                ->get();
            $departments     = Department::names();
            $employeeOptions = $employees->map(fn($emp) => [
                'id'         => $emp->id,
                'label'      => $emp->full_name . ' — ' . $emp->department,
                'department' => $emp->department,
            ])->values();

            $allIds        = $employees->pluck('id')->push($employee->id)->toArray();
            $selfConflicts = $this->buildSelfConflictsData($allIds);

            return view('absences.create', compact(
                'employee', 'employees', 'departments', 'employeeOptions', 'selfConflicts'
            ));
        }

        $employees = Employee::active()
            ->when(auth()->user()->isEmployee(), fn($q) => $q->where('id', auth()->user()->employee_id))
            ->select(['id', 'first_name', 'last_name', 'matricule', 'department'])
            ->get();
        $departments     = Department::names();
        $employeeOptions = $employees->map(fn($emp) => [
            'id'         => $emp->id,
            'label'      => $emp->full_name . ' — ' . $emp->department,
            'department' => $emp->department,
        ])->values();

        $selfConflicts = $this->buildSelfConflictsData($employees->pluck('id')->toArray());

        return view('absences.create', compact('employees', 'departments', 'employeeOptions', 'selfConflicts'));
    }

    // =========================================================================
    // store
    // =========================================================================
    public function store(StoreAbsenceRequest $request)
    {
        $validated = $request->validated();

        if ($validated['type'] === 'autre') {
            $typeAutre = trim($request->input('type_autre', ''));
            if (empty($typeAutre)) {
                return back()
                    ->withInput()
                    ->withErrors(['type_autre' => "Veuillez préciser le type d'absence."]);
            }
            $validated['type'] = $typeAutre;
        }

        $start = Carbon::parse($validated['start_date']);
        $end   = Carbon::parse($validated['end_date']);
        $validated['days']      = $start->diffInWeekdays($end) + 1;
        $validated['status']    = 'pending';
        $validated['tenant_id'] = config('app.current_tenant_id');

        $conflictingAbsence = Absence::with('employee')
            ->whereHas('employee')
            ->where('employee_id', '!=', $validated['employee_id'])
            ->where('status', 'approved')
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                  ->orWhereBetween('end_date',   [$validated['start_date'], $validated['end_date']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('start_date', '<=', $validated['start_date'])
                         ->where('end_date',   '>=', $validated['end_date']);
                  });
            })->first();

        $selfConflict = Absence::whereHas('employee')
            ->where('employee_id', $validated['employee_id'])
            ->where('status', 'approved')
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                  ->orWhereBetween('end_date',   [$validated['start_date'], $validated['end_date']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('start_date', '<=', $validated['start_date'])
                         ->where('end_date',   '>=', $validated['end_date']);
                  });
            })->exists();

        $confirmed = $request->input('conflict_confirmed') === '1';

        if ($conflictingAbsence && ! $confirmed) {
            $empName = $conflictingAbsence->employee->full_name;
            $from    = Carbon::parse($conflictingAbsence->start_date)->format('d/m/Y');
            $to      = Carbon::parse($conflictingAbsence->end_date)->format('d/m/Y');

            return back()
                ->withInput()
                ->with('conflict_warning', "Cette période est déjà occupée par <strong>{$empName}</strong> (du {$from} au {$to}). Voulez-vous soumettre quand même ?");
        }

        if ($selfConflict) {
            return back()
                ->withInput()
                ->withErrors(['start_date' => 'Cet employé a déjà une absence approuvée qui chevauche cette période.']);
        }

        Absence::create($validated);

        return redirect()->route('absences.index')
            ->with('success', "Demande d'absence soumise avec succès.");
    }

    // =========================================================================
    // show
    // =========================================================================
    public function show(Absence $absence)
    {
        // ⚠️ FIX : withoutGlobalScopes() contourne le TenantScope sur Employee
        // qui bloquait le chargement quand le tenant_id de l'absence diffère
        // de celui de la session (cas multi-tenant / développement local).
        $absence->load([
            'employee'       => fn($q) => $q->withoutGlobalScopes(),
            'replacement'    => fn($q) => $q->withoutGlobalScopes(),
            'approver'       => fn($q) => $q->withoutGlobalScopes(),
            'approvedByUser',
        ]);

        return view('absences.show', compact('absence'));
    }

    // =========================================================================
    // edit
    // =========================================================================
    public function edit(Absence $absence)
    {
        $employees = Employee::active()
            ->when(auth()->user()->isEmployee(), fn($q) => $q->where('id', auth()->user()->employee_id))
            ->select(['id', 'first_name', 'last_name', 'matricule', 'department'])
            ->get();
        return view('absences.edit', compact('absence', 'employees'));
    }

    // =========================================================================
    // update
    // =========================================================================
    public function update(UpdateAbsenceRequest $request, Absence $absence)
    {
        $validated = $request->validated();

        $start = Carbon::parse($validated['start_date']);
        $end   = Carbon::parse($validated['end_date']);
        $validated['days'] = $start->diffInWeekdays($end) + 1;

        $absence->update($validated);

        return redirect()->route('absences.index')
            ->with('success', 'Absence mise à jour.');
    }

    // =========================================================================
    // destroy
    // =========================================================================
    public function destroy(Absence $absence)
    {
        $absence->delete();
        return redirect()->route('absences.index')
            ->with('success', 'Demande supprimée.');
    }

    // =========================================================================
    // approve
    // =========================================================================
    public function approve(Absence $absence)
    {
        if (! auth()->user()->can('approve_absences')) {
            abort(403, 'Accès non autorisé.');
        }

        // ⚠️ FIX : approved_by référence employees.id, pas users.id
        $absence->update([
            'tenant_id'   => config('app.current_tenant_id'),
            'status'      => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->user()->id,
        ]);

        if (in_array($absence->type, ['conge_annuel', 'conge_sans_solde', 'conge_maladie', 'absence_justifiee'])) {
            $year  = $absence->start_date->year;
            $droit = \App\Models\DroitAbsence::getOuCreeParAnnee($absence->employee_id, $year);
            $droit->jours_pris   += $absence->days;
            $droit->jours_solde   = $droit->jours_acquis - $droit->jours_pris - $droit->jours_en_attente;
            $droit->save();
        }

        if ($absence->employee && $absence->employee->email) {
            try {
                Mail::to($absence->employee->email)->send(new AbsenceApproved($absence));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Mail approve error: ' . $e->getMessage());
            }
        }

        return back()->with('success', "Demande approuvée. Un email a été envoyé à l'employé.");
    }

    // =========================================================================
    // reject
    // =========================================================================
    public function reject(Absence $absence)
    {
        if (! auth()->user()->can('approve_absences')) {
            abort(403, 'Accès non autorisé.');
        }

        // ⚠️ FIX : approved_by référence employees.id, pas users.id
        $absence->update([
            'status'      => 'rejected',
            'approved_at' => now(),
            'approved_by' => auth()->user()->id,

        ]);

        $year  = $absence->start_date->year;
        $droit = \App\Models\DroitAbsence::where('employee_id', $absence->employee_id)
            ->where('annee', $year)->first();
        if ($droit) {
            $droit->jours_en_attente -= $absence->days;
            $droit->jours_solde       = $droit->jours_acquis - $droit->jours_pris - $droit->jours_en_attente;
            $droit->save();
        }

        if ($absence->employee && $absence->employee->email) {
            try {
                Mail::to($absence->employee->email)->send(new AbsenceRejected($absence));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Mail reject error: ' . $e->getMessage());
            }
        }

        return back()->with('success', "Demande rejetée. Un email a été envoyé à l'employé.");
    }

    // =========================================================================
    // export
    // =========================================================================
    public function export()
    {
        return Excel::download(
            new AbsencesExport(config('app.current_tenant_id')),
            'demandes_absences.xlsx'
        );
    }

    // =========================================================================
    // counters — page web
    // =========================================================================
    public function counters(Request $request)
    {
        $year         = $request->get('year', now()->year);
        $departments  = $this->getDepartments();
        $countersData = $this->buildCountersData($request);

        $search     = $request->get('search');
        $department = $request->get('department');

        return view('absences.counters', compact(
            'countersData', 'year', 'departments', 'search', 'department'
        ));
    }

    // =========================================================================
    // countersExport — Excel compteurs
    // =========================================================================
    public function countersExport(Request $request)
    {
        $year         = $request->get('year', now()->year);
        $countersData = $this->buildCountersData($request);

        return Excel::download(
            new CountersExport($countersData, $year),
            "compteurs_absences_{$year}.xlsx"
        );
    }

    // =========================================================================
    // droitsExport — Excel droits d'absence
    // =========================================================================
    public function droitsExport(Request $request)
    {
        $year         = $request->get('year', now()->year);
        $countersData = $this->buildCountersData($request);

        return Excel::download(
            new DroitsAbsenceExport($countersData),
            "droits_absences_{$year}.xlsx"
        );
    }

    // =========================================================================
    // calendar
    // =========================================================================
    public function calendar(Request $request)
    {
        $month    = $request->get('month', now()->month);
        $year     = $request->get('year',  now()->year);
        $viewMode = $request->get('view',  'calendar');

        $firstDay     = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $today        = Carbon::today();
        $startOfMonth = $firstDay->copy();
        $endOfMonth   = $firstDay->copy()->endOfMonth();
        $daysInMonth  = $firstDay->daysInMonth;

        $prevMonthData = array_merge(request()->query(), ['month' => $firstDay->copy()->subMonth()->month, 'year' => $firstDay->copy()->subMonth()->year]);
        $nextMonthData = array_merge(request()->query(), ['month' => $firstDay->copy()->addMonth()->month, 'year' => $firstDay->copy()->addMonth()->year]);
        $todayData     = array_merge(request()->query(), ['month' => now()->month, 'year' => now()->year]);
        $prevMonthUrl  = route('absences.calendar', $prevMonthData);
        $nextMonthUrl  = route('absences.calendar', $nextMonthData);
        $todayUrl      = route('absences.calendar', $todayData);
        $resetUrl      = route('absences.calendar', ['month' => $month, 'year' => $year]);

        $employees   = Employee::active()->orderBy('department')->orderBy('last_name')->get();
        $departments = $this->getDepartments();

        $employeesQuery = Employee::active()->orderBy('department')->orderBy('last_name');
        $this->applyEmployeeFilters($employeesQuery, $request);
        $filteredEmployees = $employeesQuery->get();

        $query = Absence::with(['employee', 'replacement', 'approvedByUser:id,name'])
            ->whereHas('employee')
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                  ->orWhereBetween('end_date',   [$startOfMonth, $endOfMonth])
                  ->orWhere(function ($q2) use ($startOfMonth, $endOfMonth) {
                      $q2->where('start_date', '<=', $startOfMonth)
                         ->where('end_date',   '>=', $endOfMonth);
                  });
            })
            ->whereIn('status', ['approved', 'pending']);

        $query->when($request->department, fn($q) => $q->whereHas('employee', function ($q2) use ($request) {
                    $this->applyEmployeeFilters($q2, $request);
                }))
              ->when($request->employee_id, fn($q) => $q->where('employee_id', $request->employee_id))
              ->when($request->status,      fn($q) => $q->where('status',      $request->status));

        $absences = $query->get();

        $absenceMap = [];
        foreach ($absences as $absence) {
            $empId = $absence->employee_id;
            if (! isset($absenceMap[$empId])) {
                $absenceMap[$empId] = [];
            }
            $start = Carbon::parse($absence->start_date);
            $end   = Carbon::parse($absence->end_date);
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                if ($d->month == $month && $d->year == $year) {
                    $absenceMap[$empId][$d->day] = $absence;
                }
            }
        }

        $employeeIdsWithAbsences = $absences->pluck('employee_id')->unique();
        $employeesWithAbsences   = $employees->filter(fn($emp) => $employeeIdsWithAbsences->contains($emp->id));

        $conflicts = $this->buildConflicts($absences);

        $conflictEmpIds = $conflicts->flatMap(fn($c) => [
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

        return view('absences.calendar', compact(
            'absences', 'conflicts', 'conflictEmpIds', 'replacements', 'employees',
            'employeesWithAbsences', 'month', 'year', 'firstDay', 'today', 'daysInMonth',
            'startOfMonth', 'endOfMonth', 'viewMode', 'filteredEmployees', 'absenceMap',
            'stats', 'prevMonthUrl', 'nextMonthUrl', 'todayUrl', 'resetUrl', 'departments'
        ));
    }

    // =========================================================================
    // getConflicts (API JSON)
    // =========================================================================
    public function getConflicts(Request $request)
    {
        $month        = $request->get('month', now()->month);
        $year         = $request->get('year',  now()->year);
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        $absences = Absence::with(['employee'])
            ->whereHas('employee')
            ->where('status', 'approved')
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                  ->orWhereBetween('end_date',   [$startOfMonth, $endOfMonth])
                  ->orWhere(fn($q2) => $q2->where('start_date', '<=', $startOfMonth)
                                          ->where('end_date',   '>=', $endOfMonth));
            })
            ->when($request->employee_id, fn($q) => $q->where('employee_id', $request->employee_id))
            ->get();

        $conflicts = $this->buildConflicts($absences);

        return response()->json($conflicts->values());
    }

    // =========================================================================
    // downloadPdf
    // =========================================================================
    public function downloadPdf(Absence $absence)
    {
        // ⚠️ FIX : withoutGlobalScopes() pour charger l'employé sans filtre tenant
        $absence->load([
            'employee'       => fn($q) => $q->withoutGlobalScopes(),
            'replacement'    => fn($q) => $q->withoutGlobalScopes(),
            'approver'       => fn($q) => $q->withoutGlobalScopes(),
            'approvedByUser',
        ]);

        Carbon::setLocale('fr');

        $tenant = $absence->employee->user?->tenant
            ?? \App\Models\Tenant::find(config('app.current_tenant_id'));

        $pdf = Pdf::loadView('absences.pdf', compact('absence', 'tenant'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isRemoteEnabled'      => false,
                'isHtml5ParserEnabled' => true,
            ]);

        $filename = 'demande_absence_'
            . str_replace(' ', '_', strtolower($absence->employee->full_name ?? 'employe'))
            . '_' . $absence->start_date->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    // =========================================================================
    // HELPERS PRIVÉS
    // =========================================================================

    private function buildSelfConflictsData(array $employeeIds): array
    {
        return Absence::whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->select(['id', 'employee_id', 'type', 'start_date', 'end_date'])
            ->get()
            ->map(function ($a) {
                return [
                    'employee_id' => $a->employee_id,
                    'type_label'  => \App\Models\Absence::TYPES[$a->type] ?? $a->type,
                    'start_date'  => $a->start_date->format('Y-m-d'),
                    'end_date'    => $a->end_date->format('Y-m-d'),
                    'start_fmt'   => $a->start_date->format('d/m/Y'),
                    'end_fmt'     => $a->end_date->format('d/m/Y'),
                ];
            })
            ->toArray();
    }

    private function buildCountersData(Request $request): array
    {
        $query = Employee::active()->orderBy('department')->orderBy('last_name');
        $this->applyEmployeeFilters($query, $request);
        $employees = $query->get();

        $countersData = [];

        foreach ($employees as $emp) {
            $startDate    = Carbon::parse($emp->hire_date);
            $now          = Carbon::now();
            $monthsWorked = $startDate->diffInMonths($now);
            $acquis       = round($monthsWorked * 1.5, 1);

            $taken = Absence::where('employee_id', $emp->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '>=', $startDate)
                ->whereIn('type', ['conge_annuel', 'conge_sans_solde', 'conge_maladie', 'absence_justifiee'])
                ->sum('days');
            $taken += (float) ($emp->conges_anterieurs ?? 0);

            $pending = Absence::where('employee_id', $emp->id)
                ->where('status', 'pending')
                ->whereDate('start_date', '>=', $startDate)
                ->sum('days');

            $solde = $acquis - $taken;

            $countersData[] = [
                'employee'          => $emp,
                'months_worked'     => $monthsWorked,
                'acquis'            => $acquis,
                'taken'             => $taken,
                'conges_anterieurs' => (float) ($emp->conges_anterieurs ?? 0),
                'pending'           => $pending,
                'solde'             => $solde,
                'solde_if_pending'  => $solde - $pending,
            ];
        }

        return $countersData;
    }

    private function buildConflicts($absences)
    {
        $approved  = $absences->where('status', 'approved')->values();
        $conflicts = collect();
        $seen      = [];

        foreach ($approved as $i => $a1) {
            foreach ($approved as $j => $a2) {
                if ($i >= $j) continue;
                if ($a1->employee_id === $a2->employee_id) continue;

                $dept1 = $a1->employee->department ?? '';
                $dept2 = $a2->employee->department ?? '';
                if ($dept1 !== $dept2 || empty($dept1)) continue;

                $start1 = Carbon::parse($a1->start_date);
                $end1   = Carbon::parse($a1->end_date);
                $start2 = Carbon::parse($a2->start_date);
                $end2   = Carbon::parse($a2->end_date);

                if ($start1->gt($end2) || $start2->gt($end1)) continue;

                $key = min($a1->id, $a2->id) . '-' . max($a1->id, $a2->id);
                if (in_array($key, $seen)) continue;
                $seen[] = $key;

                $overlapStart = $start1->gt($start2) ? $start1 : $start2;
                $overlapEnd   = $end1->lt($end2)     ? $end1   : $end2;

                $conflicts->push([
                    'employee_id_1' => $a1->employee_id,
                    'employee_id_2' => $a2->employee_id,
                    'employee_id'   => $a1->employee_id,
                    'employee'      => ($a1->employee->full_name ?? '?') . ' ↔ ' . ($a2->employee->full_name ?? '?'),
                    'employee1'     => $a1->employee->full_name ?? '?',
                    'employee2'     => $a2->employee->full_name ?? '?',
                    'absence1'      => Absence::TYPES[$a1->type] ?? $a1->type,
                    'absence2'      => Absence::TYPES[$a2->type] ?? $a2->type,
                    'start'         => $overlapStart->format('d/m'),
                    'end'           => $overlapEnd->format('d/m/Y'),
                    'department'    => $dept1,
                    'a'             => $a1,
                    'b'             => $a2,
                ]);
            }
        }

        return $conflicts;
    }

    private function applyEmployeeFilters($query, Request $request)
    {
        $query->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name',  'like', "%{$request->search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$request->search}%"])
                  ->orWhere('matricule',  'like', "%{$request->search}%");
            }))
            ->when($request->department, fn($q, $dep) => $q->where('department', $dep));

        return $query;
    }

    private function getDepartments()
    {
        return Department::names();
    }
}
