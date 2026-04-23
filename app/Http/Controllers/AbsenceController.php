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
    public function index(Request $request)
    {
        $query = Absence::with(['employee:id,first_name,last_name,matricule,department', 'replacement:id,first_name,last_name,matricule,department']);

        if (auth()->user()->isEmployee() && auth()->user()->employee_id) {
            $query->where('employee_id', auth()->user()->employee_id);
        } else {

            if ($request->employee_id) {
                $query->where('employee_id', $request->employee_id);
            }
        }

        $query->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->search, fn($q) => $q->whereHas('employee', function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%");
            }));

        $absences = $query->latest()->paginate(20);
        $employeesQuery = Employee::active()->when(auth()->user()->isEmployee(), fn($q) => $q->where('id', auth()->user()->employee_id))->select(['id', 'first_name', 'last_name', 'matricule', 'department']);
        $this->applyEmployeeFilters($employeesQuery, $request);
        $employees = $employeesQuery->get();
        $departments = $this->getDepartments();

        if (auth()->user()->isEmployee() && auth()->user()->employee_id) {
            $pending_count = Absence::where('employee_id', auth()->user()->employee_id)->where('status', 'pending')->count();
        } else {
            $pending_count = Absence::where('status', 'pending')->count();
        }

        return view('absences.index', compact('absences', 'employees', 'pending_count', 'departments'));

        return view('absences.index', compact('absences', 'employees', 'pending_count'));
    }

    public function create()
    {

        if (auth()->user()->isEmployee() && auth()->user()->employee_id) {
            $employee = Employee::find(auth()->user()->employee_id);
            $employees = Employee::active()
                ->where('id', '!=', $employee->id)
                ->select(['id', 'first_name', 'last_name', 'matricule', 'department'])
                ->get();
            $departments = Department::names();
            $employeeOptions = $employees->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'label' => $emp->full_name . ' — ' . $emp->department,
                    'department' => $emp->department,
                ];
            })->values();
            return view('absences.create', compact('employee', 'employees', 'departments', 'employeeOptions'));
        }

        $employees = Employee::active()->when(auth()->user()->isEmployee(), fn($q) => $q->where('id', auth()->user()->employee_id))->select(['id', 'first_name', 'last_name', 'matricule', 'department'])->get();
        $departments = Department::names();
        $employeeOptions = $employees->map(function ($emp) {
            return [
                'id' => $emp->id,
                'label' => $emp->full_name . ' — ' . $emp->department,
                'department' => $emp->department,
            ];
        })->values();
        return view('absences.create', compact('employees', 'departments', 'employeeOptions'));
    }

    public function store(StoreAbsenceRequest $request)
    {
        $validated = $request->validated();

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $validated['days'] = $start->diffInWeekdays($end) + 1;
        $validated['status'] = 'pending';

        $conflict = Absence::where('employee_id', $validated['employee_id'])
            ->where('status', 'approved')
            ->where(function($q) use ($validated) {
                $q->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                  ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']]);
            })->exists();

        Absence::create($validated);

        $message = $conflict
            ? 'Demande créée mais un conflit a été détecté avec une autre absence approuvée.'
            : 'Demande d\'absence soumise avec succès.';

        return redirect()->route('absences.index')
            ->with($conflict ? 'warning' : 'success', $message);
    }

    public function show(Absence $absence)
    {
        $absence->load(['employee', 'replacement', 'approver']);
        return view('absences.show', compact('absence'));
    }

    public function edit(Absence $absence)
    {
        $employees = Employee::active()->when(auth()->user()->isEmployee(), fn($q) => $q->where('id', auth()->user()->employee_id))->select(['id', 'first_name', 'last_name', 'matricule', 'department'])->get();
        return view('absences.edit', compact('absence', 'employees'));
    }

    public function update(UpdateAbsenceRequest $request, Absence $absence)
    {
        $validated = $request->validated();

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $validated['days'] = $start->diffInWeekdays($end) + 1;

        $absence->update($validated);

        return redirect()->route('absences.index')
            ->with('success', 'Absence mise à jour.');
    }

    public function destroy(Absence $absence)
    {
        $absence->delete();
        return redirect()->route('absences.index')
            ->with('success', 'Demande supprimée.');
    }

    public function approve(Absence $absence)
    {
        if (!auth()->user()->can('approve_absences')) {
            abort(403, 'Accès non autorisé.');
        }

        $absence->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Sync droit absence
        if (in_array($absence->type, ['conge_annuel', 'conge_sans_solde', 'conge_maladie', 'absence_justifiee'])) {
            $year = $absence->start_date->year;
            $droit = \App\Models\DroitAbsence::updateOrCreate(
                ['employee_id' => $absence->employee_id, 'annee' => $year],
                ['jours_pris' => 0, 'jours_en_attente' => 0, 'jours_solde' => 0]
            );
            $droit->jours_pris += $absence->days;
            $droit->jours_solde = $droit->jours_acquis - $droit->jours_pris - $droit->jours_en_attente;
            $droit->save();
        }

        if ($absence->employee && $absence->employee->email) {
            try {
                Mail::to($absence->employee->email)->send(new AbsenceApproved($absence));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Mail approve error: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Demande approuvée. Un email a été envoyé à l\'employé.');
    }

    public function reject(Absence $absence)
    {
        if (!auth()->user()->can('approve_absences')) {
            abort(403, 'Accès non autorisé.');
        }

        $absence->update([
            'status' => 'rejected',
            'approved_at' => now(),
        ]);

        // Remove from attente if was pending (already pending sum in counters)
        $year = $absence->start_date->year;
        $droit = \App\Models\DroitAbsence::where('employee_id', $absence->employee_id)
            ->where('annee', $year)->first();
        if ($droit) {
            $droit->jours_en_attente -= $absence->days;
            $droit->jours_solde = $droit->jours_acquis - $droit->jours_pris - $droit->jours_en_attente;
            $droit->save();
        }

        if ($absence->employee && $absence->employee->email) {
            try {
                Mail::to($absence->employee->email)->send(new AbsenceRejected($absence));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Mail reject error: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Demande rejetée. Un email a été envoyé à l\'employé.');
    }

    public function export()
    {
        return Excel::download(new AbsencesExport(), 'demandes_absences.xlsx');
    }

    public function countersExport(Request $request)
    {
        $year = $request->get('year', now()->year);
        $employees = Employee::active()
            ->withCount(['absences' => function ($q) use ($year) {
                $q->where('status', 'approved')->whereYear('start_date', $year);
            }])
            ->orderBy('department')
            ->orderBy('last_name')
            ->select(['id', 'first_name', 'last_name', 'department', 'matricule', 'hire_date'])
            ->get();

        $countersData = [];

        foreach ($employees as $emp) {
            $hireDate = $emp->hire_date ? Carbon::parse($emp->hire_date) : Carbon::create($year, 1, 1);
            $startOfYear = Carbon::create($year, 1, 1);
            $endOfYear   = Carbon::create($year, 12, 31);

            $workStart = $hireDate->gt($startOfYear) ? $hireDate : $startOfYear;
            $workEnd   = now()->lt($endOfYear) ? now() : $endOfYear;
            $monthsWorked = max(0, $workStart->floatDiffInMonths($workEnd));

            $acquis = floor($monthsWorked * 1.5);

            $taken = Absence::where('employee_id', $emp->id)
                ->where('status', 'approved')
                ->whereYear('start_date', $year)
                ->whereIn('type', ['conge_annuel', 'conge_sans_solde', 'conge_maladie', 'absence_justifiee'])
                ->sum('days');

            $solde = $acquis - $taken;

            $pending = Absence::where('employee_id', $emp->id)
                ->where('status', 'pending')
                ->whereYear('start_date', $year)
                ->sum('days');

            $countersData[] = [
                'employee'      => $emp,
                'months_worked' => floor($monthsWorked),
                'acquis'        => $acquis,
                'taken'         => $taken,
                'pending'       => $pending,
                'solde'         => round($solde, 2),
                'solde_if_pending' => round($solde - $pending, 2),
            ];
        }

        return Excel::download(new CountersExport($countersData, $year), "compteurs_absences_{$year}.xlsx");
    }

    public function droitsExport()
    {
        return Excel::download(new DroitsAbsenceExport(), 'droits_absences.xlsx');
    }

    public function calendar(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);
        $viewMode = $request->get('view', 'calendar');

        $firstDay = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $today = Carbon::today();
        $startOfMonth = $firstDay->copy();
        $endOfMonth   = $firstDay->copy()->endOfMonth();
        $daysInMonth = $firstDay->daysInMonth;

        // Navigation URLs
        $prevMonthData = array_merge(request()->query(), ['month' => $firstDay->copy()->subMonth()->month, 'year' => $firstDay->copy()->subMonth()->year]);
        $nextMonthData = array_merge(request()->query(), ['month' => $firstDay->copy()->addMonth()->month, 'year' => $firstDay->copy()->addMonth()->year]);
        $todayData = array_merge(request()->query(), ['month' => now()->month, 'year' => now()->year]);
        $prevMonthUrl = route('absences.calendar', $prevMonthData);
        $nextMonthUrl = route('absences.calendar', $nextMonthData);
        $todayUrl = route('absences.calendar', $todayData);
        $resetUrl = route('absences.calendar', ['month' => $month, 'year' => $year]);

        // Get all employees for filtering
        $employees = Employee::active()
            ->orderBy('department')
            ->orderBy('last_name')
            ->get();

$departments = $this->getDepartments();

        $employeesQuery = Employee::active()
            ->orderBy('department')
            ->orderBy('last_name');
        $this->applyEmployeeFilters($employeesQuery, $request);
        $filteredEmployees = $employeesQuery->get();

        // Build query for absences (already filtered by request params)
        $query = Absence::with(['employee', 'replacement'])
            ->where(function($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                  ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                  ->orWhere(function($q2) use ($startOfMonth, $endOfMonth) {
                      $q2->where('start_date', '<=', $startOfMonth)
                         ->where('end_date', '>=', $endOfMonth);
                  });
            })
            ->whereIn('status', ['approved', 'pending']);

        $query->when($request->department, fn($q) => $q->whereHas('employee', function ($q2) use ($request) {
                $this->applyEmployeeFilters($q2, $request);
            }))
            ->when($request->employee_id, fn($q) => $q->where('employee_id', $request->employee_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status));

        $absences = $query->get();

        // Build absenceMap: [emp_id][day] = absence (moved from blade)
        $absenceMap = [];
        foreach ($absences as $absence) {
            $empId = $absence->employee_id;
            if (!isset($absenceMap[$empId])) {
                $absenceMap[$empId] = [];
            }
            $start = Carbon::parse($absence->start_date);
            $end = Carbon::parse($absence->end_date);
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                if ($d->month == $month && $d->year == $year) {
                    $absenceMap[$empId][$d->day] = $absence;
                }
            }
        }

        $employeeIdsWithAbsences = $absences->pluck('employee_id')->unique();
        $employeesWithAbsences = $employees->filter(function($emp) use ($employeeIdsWithAbsences) {
            return $employeeIdsWithAbsences->contains($emp->id);
        });

        if ($request->status === 'pending') {
            $conflicts = collect();
        } else {
            $conflicts = DB::table('absences as a1')
                ->join('absences as a2', function ($join) {
                    $join->on('a1.employee_id', '=', 'a2.employee_id')
                         ->whereColumn('a1.id', '<', 'a2.id')
                         ->where('a1.status', 'approved')
                         ->where('a2.status', 'approved');
                })
                ->join('employees', 'employees.id', '=', 'a1.employee_id')
                ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('a1.start_date', [$startOfMonth, $endOfMonth])
                      ->orWhereBetween('a1.end_date', [$startOfMonth, $endOfMonth])
                      ->orWhere(function ($q2) use ($startOfMonth, $endOfMonth) {
                          $q2->where('a1.start_date', '<=', $startOfMonth)
                             ->where('a1.end_date', '>=', $endOfMonth);
                      });
                })
                ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('a2.start_date', [$startOfMonth, $endOfMonth])
                      ->orWhereBetween('a2.end_date', [$startOfMonth, $endOfMonth])
                      ->orWhere(function ($q2) use ($startOfMonth, $endOfMonth) {
                          $q2->where('a2.start_date', '<=', $startOfMonth)
                             ->where('a2.end_date', '>=', $endOfMonth);
                      });
                })
                ->when($request->department, fn($q) => $q->where('employees.department', $request->department))
                ->when($request->employee_id, fn($q) => $q->where('a1.employee_id', $request->employee_id))
                ->selectRaw('DISTINCT a1.id as absence1_id, a2.id as absence2_id, a1.employee_id, CONCAT(employees.first_name, " ", employees.last_name) as employee_name, a1.type as absence1_type, a2.type as absence2_type, GREATEST(a1.start_date, a2.start_date) as overlap_start, LEAST(a1.end_date, a2.end_date) as overlap_end')
                ->get()
                ->map(function ($conflict) {
                    $a = Absence::find($conflict->absence1_id);
                    $b = Absence::find($conflict->absence2_id);
                    return [
                        'employee_id' => $conflict->employee_id,
                        'a' => $a,
                        'b' => $b,
                        'employee' => $conflict->employee_name,
                        'absence1' => \App\Models\Absence::TYPES[$conflict->absence1_type] ?? $conflict->absence1_type,
                        'absence2' => \App\Models\Absence::TYPES[$conflict->absence2_type] ?? $conflict->absence2_type,
                        'start' => \Carbon\Carbon::parse($conflict->overlap_start)->format('d/m'),
                        'end' => \Carbon\Carbon::parse($conflict->overlap_end)->format('d/m/Y'),
                    ];
                });
        }

        $replacements = $absences->whereNotNull('replacement_id');

        // Stats (moved from blade quick-stats) - now after $conflicts defined
        $approvedAbsences = $absences->where('status', 'approved');
        $pendingAbsences = $absences->where('status', 'pending');
        $stats = [
            'approved_count' => $approvedAbsences->count(),
            'pending_count' => $pendingAbsences->count(),
            'conflicts_count' => $conflicts->count(),
            'replacements_count' => $replacements->count(),
            'total_days' => $absences->sum('days'),
        ];

        return view('absences.calendar', compact(
            'absences', 'conflicts', 'replacements', 'employees', 'employeesWithAbsences',
            'month', 'year', 'firstDay', 'today', 'daysInMonth', 'startOfMonth', 'endOfMonth',
            'viewMode', 'filteredEmployees', 'absenceMap', 'stats', 'prevMonthUrl',
            'nextMonthUrl', 'todayUrl', 'resetUrl', 'departments'
        ));
    }




    private function applyEmployeeFilters($query, Request $request)
    {
        $query->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$request->search}%"])
                  ->orWhere('matricule', 'like', "%{$request->search}%");
            }))
            ->when($request->department, fn($q, $dep) => $q->where('department', $dep));

        return $query;
    }

    private function getDepartments()
    {
        return Department::names();
    }

    public function getConflicts(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        $conflicts = DB::table('absences as a1')
            ->join('absences as a2', function ($join) {
                $join->on('a1.employee_id', '=', 'a2.employee_id')
                     ->whereColumn('a1.id', '<', 'a2.id')
                     ->where('a1.status', 'approved')
                     ->where('a2.status', 'approved');
            })
            ->join('employees', 'employees.id', '=', 'a1.employee_id')
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('a1.start_date', [$startOfMonth, $endOfMonth])
                  ->orWhereBetween('a1.end_date', [$startOfMonth, $endOfMonth])
                  ->orWhere(function ($q2) use ($startOfMonth, $endOfMonth) {
                      $q2->where('a1.start_date', '<=', $startOfMonth)
                         ->where('a1.end_date', '>=', $endOfMonth);
                  });
            })
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('a2.start_date', [$startOfMonth, $endOfMonth])
                  ->orWhereBetween('a2.end_date', [$startOfMonth, $endOfMonth])
                  ->orWhere(function ($q2) use ($startOfMonth, $endOfMonth) {
                      $q2->where('a2.start_date', '<=', $startOfMonth)
                         ->where('a2.end_date', '>=', $endOfMonth);
                  });
            })
            ->when($request->department, fn($q) => $q->whereHas('employee', fn($q2) => $this->applyEmployeeFilters($q2, $request)))
            ->when($request->employee_id, fn($q) => $q->where('a1.employee_id', $request->employee_id))
            ->selectRaw('DISTINCT a1.id as absence1_id, a2.id as absence2_id, a1.employee_id, CONCAT(employees.first_name, " ", employees.last_name) as employee_name, a1.type as absence1_type, a2.type as absence2_type, GREATEST(a1.start_date, a2.start_date) as overlap_start, LEAST(a1.end_date, a2.end_date) as overlap_end')
            ->get()
            ->map(function ($conflict) {
                $a = Absence::find($conflict->absence1_id);
                $b = Absence::find($conflict->absence2_id);
                return [
                    'employee_id' => $conflict->employee_id,
                    'a' => $a,
                    'b' => $b,
                    'employee' => $conflict->employee_name,
                    'absence1' => \App\Models\Absence::TYPES[$conflict->absence1_type] ?? $conflict->absence1_type,
                    'absence2' => \App\Models\Absence::TYPES[$conflict->absence2_type] ?? $conflict->absence2_type,
                    'start' => \Carbon\Carbon::parse($conflict->overlap_start)->format('d/m'),
                    'end' => \Carbon\Carbon::parse($conflict->overlap_end)->format('d/m/Y'),
                ];
            });

        return response()->json($conflicts);
    }
    // Dans app/Http/Controllers/AbsenceController.php
public function downloadPdf(Absence $absence)
{
    $absence->load(['employee', 'replacement', 'approver']);
    \Carbon\Carbon::setLocale('fr');

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('absences.pdf', compact('absence'))
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'defaultFont'          => 'DejaVu Sans',
            'isRemoteEnabled'      => false,
            'isHtml5ParserEnabled' => true,
        ]);

    $filename = 'demande_absence_'
        . str_replace(' ', '_', strtolower($absence->employee->full_name))
        . '_' . $absence->start_date->format('Y-m-d') . '.pdf';

    return $pdf->download($filename);
}

    public function counters(Request $request)
    {
        $year = $request->get('year', now()->year);
        $search = $request->get('search');
        $department = $request->get('department');

        $query = Employee::active()
            ->orderBy('department')
            ->orderBy('last_name');

        $this->applyEmployeeFilters($query, $request);
        $employees = $query->get();
        $departments = $this->getDepartments();

        $countersData = [];



foreach ($employees as $emp) {

    $startDate = Carbon::parse($emp->hire_date);
    $now = Carbon::now();


    $monthsWorked = $startDate->diffInMonths($now);


    $acquis = round($monthsWorked * 1.5, 1);


    $taken = Absence::where('employee_id', $emp->id)
        ->where('status', 'approved')
        ->whereDate('start_date', '>=', $startDate)
        ->whereIn('type', [
            'conge_annuel',
            'conge_sans_solde',
            'conge_maladie',
            'absence_justifiee'
        ])
        ->sum('days');


    $pending = Absence::where('employee_id', $emp->id)
        ->where('status', 'pending')
        ->whereDate('start_date', '>=', $startDate)
        ->sum('days');


    $solde = $acquis - $taken;

    $countersData[] = [
        'employee' => $emp,
        'months_worked' => $monthsWorked,
        'acquis' => $acquis,
        'taken' => $taken,
        'pending' => $pending,
        'solde' => $solde,
        'solde_if_pending' => $solde - $pending,
    ];
}
        return view('absences.counters', compact('countersData', 'year', 'search', 'department', 'departments'));
    }
}
