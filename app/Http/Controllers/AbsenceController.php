<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\AbsenceApproved;
use App\Mail\AbsenceRejected;
use Carbon\Carbon;

class AbsenceController extends Controller
{
    public function index(Request $request)
    {
        $query = Absence::with(['employee', 'replacement']);

        // Employees can only see their own absences
        if (Auth::user()->role === 'employee' && Auth::user()->employee_id) {
            $query->where('employee_id', Auth::user()->employee_id);
        } else {
            // Admin/RH can filter by employee
            if ($request->employee_id) {
                $query->where('employee_id', $request->employee_id);
            }
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->search) {
            $search = $request->search;
            $query->whereHas('employee', function($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%");
            });
        }

        $absences = $query->latest()->paginate(20);
        $employees = Employee::where('status', 'active')->get();
        
        // Only admin/RH can see pending count for all
        if (Auth::user()->role === 'employee' && Auth::user()->employee_id) {
            $pending_count = Absence::where('employee_id', Auth::user()->employee_id)->where('status', 'pending')->count();
        } else {
            $pending_count = Absence::where('status', 'pending')->count();
        }

        return view('absences.index', compact('absences', 'employees', 'pending_count'));
    }

    public function create()
    {
        // If employee, only allow creating for themselves
        if (Auth::user()->role === 'employee' && Auth::user()->employee_id) {
            $employee = Employee::find(Auth::user()->employee_id);
            return view('absences.create', compact('employee'));
        }
        
        $employees = Employee::where('status', 'active')->get();
        return view('absences.create', compact('employees'));
    }

    public function store(Request $request)
    {
        // Employees can only create absence for themselves
        if (Auth::user()->role === 'employee' && Auth::user()->employee_id) {
            $validated = $request->validate([
                'type' => 'required|in:' . implode(',', array_keys(Absence::TYPES)),
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'nullable|string',
                'replacement_id' => 'nullable|exists:employees,id',
                'notes' => 'nullable|string',
            ]);
            $validated['employee_id'] = Auth::user()->employee_id;
        } else {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'type' => 'required|in:' . implode(',', array_keys(Absence::TYPES)),
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'nullable|string',
                'replacement_id' => 'nullable|exists:employees,id',
                'notes' => 'nullable|string',
            ]);
        }

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
        $employees = Employee::where('status', 'active')->get();
        return view('absences.edit', compact('absence', 'employees'));
    }

    public function update(Request $request, Absence $absence)
    {
        $validated = $request->validate([
            'type' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
            'replacement_id' => 'nullable|exists:employees,id',
        ]);

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
        $absence->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Envoi mail automatique à l'employé
        if ($absence->employee && $absence->employee->email) {
            try {
                Mail::to($absence->employee->email)->send(new AbsenceApproved($absence));
            } catch (\Exception $e) {
                // Log l'erreur sans bloquer le flux
                \Log::error('Mail approve error: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Demande approuvée. Un email a été envoyé à l\'employé.');
    }

    public function reject(Absence $absence)
    {
        $absence->update([
            'status' => 'rejected',
            'approved_at' => now(),
        ]);

        // Envoi mail automatique à l'employé
        if ($absence->employee && $absence->employee->email) {
            try {
                Mail::to($absence->employee->email)->send(new AbsenceRejected($absence));
            } catch (\Exception $e) {
                \Log::error('Mail reject error: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Demande rejetée. Un email a été envoyé à l\'employé.');
    }

    // =========================================================
    // NOUVELLE PAGE : État visuel calendrier (planning mensuel)
    // =========================================================
    public function calendar(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);
        $viewMode = $request->get('view', 'calendar');

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth   = Carbon::create($year, $month, 1)->endOfMonth();

        // Get all employees for filtering
        $employees = Employee::where('status', 'active')
            ->orderBy('department')
            ->orderBy('last_name')
            ->get();

        // Build query for absences
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

        // Apply filters
        if ($request->department) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }

        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $absences = $query->get();

        // Get employees who have absences (for list view)
        $employeeIdsWithAbsences = $absences->pluck('employee_id')->unique();
        $employeesWithAbsences = $employees->filter(function($emp) use ($employeeIdsWithAbsences) {
            return $employeeIdsWithAbsences->contains($emp->id);
        });

        // Conflits : 2 absences approuvées qui se chevauchent pour le même employé
        $conflicts = [];
        $approvedAbsences = $absences->where('status', 'approved');
        foreach ($approvedAbsences as $a) {
            foreach ($approvedAbsences as $b) {
                if ($a->id >= $b->id) continue;
                if ($a->employee_id === $b->employee_id) {
                    $overlapStart = max($a->start_date, $b->start_date);
                    $overlapEnd   = min($a->end_date,   $b->end_date);
                    if ($overlapStart <= $overlapEnd) {
                        $conflicts[] = ['a' => $a, 'b' => $b];
                    }
                }
            }
        }

        // Remplacements du mois
        $replacements = $absences->whereNotNull('replacement_id');

        $daysInMonth = $startOfMonth->daysInMonth;

        // For list view, we need all employees with their absences
        return view('absences.calendar', compact(
            'absences', 'conflicts', 'replacements',
            'employees', 'employeesWithAbsences', 'month', 'year',
            'startOfMonth', 'endOfMonth', 'daysInMonth', 'viewMode'
        ));
    }

    // =========================================================
    // NOUVELLE PAGE : Compteurs et droits d'absence
    // =========================================================
    public function counters(Request $request)
    {
        $year = $request->get('year', now()->year);

        $employees = Employee::where('status', 'active')
            ->orderBy('department')
            ->orderBy('last_name')
            ->get();

        $countersData = [];

        foreach ($employees as $emp) {
            // Date d'embauche
            $hireDate = $emp->hire_date ? Carbon::parse($emp->hire_date) : Carbon::create($year, 1, 1);
            $startOfYear = Carbon::create($year, 1, 1);
            $endOfYear   = Carbon::create($year, 12, 31);

            // Mois travaillés dans l'année (depuis embauche)
            $workStart = $hireDate->gt($startOfYear) ? $hireDate : $startOfYear;
            $workEnd   = now()->lt($endOfYear) ? now() : $endOfYear;
            $monthsWorked = max(0, $workStart->floatDiffInMonths($workEnd));

            // Droits acquis : 1.5 jour / mois → entier
            $acquis = floor($monthsWorked * 1.5);

            // Congés pris (approuvés dans l'année)
            $taken = Absence::where('employee_id', $emp->id)
                ->where('status', 'approved')
                ->whereYear('start_date', $year)
                ->whereIn('type', ['conge_annuel', 'conge_sans_solde', 'conge_maladie'])
                ->sum('days');

            // Solde disponible → entier
            $solde = $acquis - $taken;

            // Congés en attente
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

        return view('absences.counters', compact('countersData', 'year'));
    }
}
