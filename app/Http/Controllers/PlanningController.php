<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Resources\Planning\PlanningResource;
use App\Http\Resources\Planning\DragDropResponseResource;

class PlanningController extends Controller
{
    public function index(Request $request)
    {
        $employee_id = $request->employee_id;
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $user_employee_id = null;
        if (auth()->check() && auth()->user()->role === 'employer' && auth()->user()->employee_id) {
            $user_employee_id = auth()->user()->employee_id;
        }

$employees = Employee::with(['user', 'manager'])->where('status', 'active')->when($user_employee_id, fn($q) => $q->where('id', $user_employee_id))->get();
        $plannings = Planning::with('employee')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->when($employee_id, fn($q) => $q->where('employee_id', $employee_id))
            ->when($user_employee_id, fn($q) => $q->where('employee_id', $user_employee_id))
            ->get();

        return view('planning.index', compact('plannings', 'employees', 'month', 'year', 'employee_id'));
    }


    public function weekly(Request $request)
    {
        $week = $request->week ?? now()->weekOfYear;
        $year = $request->year ?? now()->year;


        $search = $request->search;
        $department = $request->department;

        $user_employee_id = null;
        if (auth()->check() && auth()->user()->role === 'employer' && auth()->user()->employee_id) {
            $user_employee_id = auth()->user()->employee_id;
        }


        $startOfWeek = Carbon::now()->setISODate($year, $week)->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);


        $employees = Employee::where('status', 'active')
            ->when($user_employee_id, fn($q) => $q->where('id', $user_employee_id))
            ->when($search, fn($q) => $q->where(function($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereRaw("first_name || ' ' || last_name LIKE ?", ["%{$search}%"]);
            }))
            ->when($department, fn($q) => $q->where('department', $department))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();


        $plannings = Planning::with('employee')
            ->whereDate('date', '>=', $startOfWeek)
            ->whereDate('date', '<=', $endOfWeek)
            ->when($user_employee_id, fn($q) => $q->where('employee_id', $user_employee_id))
            ->get()
            ->groupBy('employee_id');


        $departments = Employee::whereNotNull('department')
            ->distinct()
            ->pluck('department');


        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $weekDays[$day->format('Y-m-d')] = [
                'date' => $day,
                'day_name' => $day->locale('fr')->dayName,
                'day_number' => $day->day,
            ];
        }

        return view('planning.weekly', compact('employees', 'plannings', 'weekDays', 'week', 'year', 'startOfWeek', 'endOfWeek', 'search', 'department', 'departments'));
    }


    public function monthly(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;


        $search = $request->search;
        $department = $request->department;

        $user_employee_id = null;
        if (auth()->check() && auth()->user()->role === 'employer' && auth()->user()->employee_id) {
            $user_employee_id = auth()->user()->employee_id;
        }


        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();


        $employees = Employee::where('status', 'active')
            ->when($user_employee_id, fn($q) => $q->where('id', $user_employee_id))
            ->when($search, fn($q) => $q->where(function($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereRaw("first_name || ' ' || last_name LIKE ?", ["%{$search}%"]);
            }))
            ->when($department, fn($q) => $q->where('department', $department))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();


        $plannings = Planning::with('employee')
            ->whereDate('date', '>=', $startOfMonth)
            ->whereDate('date', '<=', $endOfMonth)
            ->get()
            ->groupBy('employee_id');


        $departments = Employee::whereNotNull('department')
            ->distinct()
            ->pluck('department');


        $calendarDays = [];
        $startDay = $startOfMonth->copy();
        $endDay = $endOfMonth->copy();

        for ($i = 0; $i <= $endDay->diffInDays($startDay); $i++) {
            $day = $startDay->copy()->addDays($i);
            $calendarDays[] = [
                'date' => $day,
                'date_string' => $day->format('Y-m-d'),
                'day' => $day->day,
                'day_name_short' => substr($day->locale('fr')->dayName, 0, 3),
                'is_weekend' => in_array($day->dayOfWeek, [Carbon::SUNDAY, Carbon::SATURDAY]),
            ];
        }

        return view('planning.monthly', compact('employees', 'plannings', 'calendarDays', 'month', 'year', 'startOfMonth', 'endOfMonth', 'search', 'department', 'departments'));
    }


    public function global(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;


        $search = $request->search;
        $department = $request->department;

        $user_employee_id = null;
        if (auth()->check() && auth()->user()->role === 'employer' && auth()->user()->employee_id) {
            $user_employee_id = auth()->user()->employee_id;
        }


        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();


        $employees = Employee::where('status', 'active')
            ->when($user_employee_id, fn($q) => $q->where('id', $user_employee_id))
            ->when($search, fn($q) => $q->where(function($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereRaw("first_name || ' ' || last_name LIKE ?", ["%{$search}%"]);
            }))
            ->when($department, fn($q) => $q->where('department', $department))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();


        $plannings = Planning::with('employee')
            ->whereDate('date', '>=', $startOfMonth)
            ->whereDate('date', '<=', $endOfMonth)
            ->when($user_employee_id, fn($q) => $q->where('employee_id', $user_employee_id))
            ->get()
            ->groupBy('employee_id');


        $departments = Employee::whereNotNull('department')
            ->distinct()
            ->pluck('department');


        $calendarDays = [];
        $startDay = $startOfMonth->copy();
        $endDay = $endOfMonth->copy();

        for ($i = 0; $i <= $endDay->diffInDays($startDay); $i++) {
            $day = $startDay->copy()->addDays($i);
            $calendarDays[] = [
                'date' => $day,
                'date_string' => $day->format('Y-m-d'),
                'day' => $day->day,
                'day_name_short' => substr($day->locale('fr')->dayName, 0, 3),
                'is_weekend' => in_array($day->dayOfWeek, [Carbon::SUNDAY, Carbon::SATURDAY]),
            ];
        }

        return view('planning.global', compact('employees', 'plannings', 'calendarDays', 'month', 'year', 'startOfMonth', 'endOfMonth', 'search', 'department', 'departments'));
    }

    public function show(Request $request, Employee $employee = null)
    {

        if (!$employee) {
            $employee_id = $request->employee_id;
            if ($employee_id) {
                $employee = Employee::findOrFail($employee_id);
            }
        }

        if (!$employee) {
            return redirect()->route('planning.weekly');
        }


        $week = $request->week ?? now()->weekOfYear;
        $year = $request->year ?? now()->year;


        $startOfWeek = Carbon::now()->setISODate($year, $week)->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);


        $plannings = Planning::where('employee_id', $employee->id)
            ->whereDate('date', '>=', $startOfWeek)
            ->whereDate('date', '<=', $endOfWeek)
            ->get()
            ->keyBy('date');


        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $weekDays[$day->format('Y-m-d')] = [
                'date' => $day,
                'day_name' => $day->locale('fr')->dayName,
                'day_number' => $day->day,
            ];
        }

        return view('planning.show', compact('employee', 'plannings', 'weekDays', 'week', 'year', 'startOfWeek', 'endOfWeek'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'shift_start' => 'required',
            'shift_end' => 'required',
            'shift_type' => 'required|in:' . implode(',', array_keys(Planning::SHIFT_TYPES)),
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = config('app.current_tenant_id');

        Planning::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'date' => $validated['date']],
            $validated
        );

        return back()->with('success', 'Planning mis à jour.');
    }

    public function update(Request $request, Planning $planning)
    {
        $validated = $request->validate([
            'shift_start' => 'required',
            'shift_end' => 'required',
            'shift_type' => 'required|in:' . implode(',', array_keys(Planning::SHIFT_TYPES)),
            'notes' => 'nullable|string',
        ]);

        $planning->update($validated);

        return back()->with('success', 'Shift mis à jour.');
    }

    public function destroy(Planning $planning)
    {
        $planning->delete();
        return back()->with('success', 'Shift supprimé.');
    }

    // API pour le drag & drop
    public function updateDragDrop(Request $request)
    {
        $validated = $request->validate([
            'planning_id' => 'required|exists:plannings,id',
            'new_date' => 'required|date',
            'new_employee_id' => 'nullable|exists:employees,id',
        ]);

        $planning = Planning::findOrFail($validated['planning_id']);
        $planning->date = $validated['new_date'];

        if ($validated['new_employee_id']) {
            $planning->employee_id = $validated['new_employee_id'];
        }

        $planning->load('employee');
        $planning->save();

        return (new DragDropResponseResource(true, new PlanningResource($planning), 'Planning mis à jour avec succès'))->toResponse($request);
    }

    public function events(Request $request)
    {
        $plannings = Planning::with('employee')
            ->when($request->start, fn($q) => $q->whereDate('date', '>=', $request->start))
            ->when($request->end, fn($q) => $q->whereDate('date', '<=', $request->end))
            ->get()
            ->map(function($p) {
                return [
                    'id' => $p->id,
                    'title' => $p->employee->full_name . ' - ' . $p->shift_type,
                    'start' => $p->date->format('Y-m-d') . 'T' . $p->shift_start,
                    'end' => $p->date->format('Y-m-d') . 'T' . $p->shift_end,
                    'color' => $this->shiftColor($p->shift_type),
                ];
            });

        return response()->json($plannings);
    }

    private function shiftColor($type): string
    {
        return match($type) {
            'matin' => '#0ea5e9',
            'apres_midi' => '#f59e0b',
            'nuit' => '#6366f1',
            'garde' => '#ef4444',
            default => '#10b981',
        };
    }
}
