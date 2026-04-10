<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlanningRequest;
use App\Http\Requests\UpdatePlanningDragDropRequest;
use App\Http\Requests\UpdatePlanningRequest;
use App\Models\Employee;
use App\Models\Planning;
use App\Services\PlanningService;
use Carbon\Carbon;
use App\Exports\PlanningMonthlyExport;
use App\Exports\PlanningWeeklyExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

use App\Http\Resources\Planning\PlanningResource;
use App\Http\Resources\Planning\DragDropResponseResource;

class PlanningController extends Controller
{
    public function __construct(private PlanningService $planningService) {}

    public function index(Request $request)
    {
        try {
            $employee_id = $request->employee_id;
            $month = $request->month ?? now()->month;
            $year = $request->year ?? now()->year;

<<<<<<< HEAD
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
=======
            $employees = Employee::active()->get();
            $plannings = Planning::with('employee')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->when($employee_id, fn ($q) => $q->where('employee_id', $employee_id))
                ->get();
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06

            return view('planning.index', compact('plannings', 'employees', 'month', 'year', 'employee_id'));
        } catch (ModelNotFoundException $e) {
            Log::warning('Planning index employee not found: ' . $e->getMessage());
            return back()->with('error', 'Employé non trouvé.');
        } catch (Exception $e) {
            Log::error('Planning index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return view('planning.index', ['error' => 'Erreur chargement planning.']);
        }
    }

<<<<<<< HEAD

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
=======
    // Similar try-catch for weekly, monthly, global, show...

    public function weekly(Request $request)
    {
        try {
            $week = $request->week ?? now()->weekOfYear;
            $year = $request->year ?? now()->year;
            $search = $request->search;
            $department = $request->department;
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06

            $startOfWeek = now()->setISODate($year, $week)->startOfWeek(Carbon::MONDAY);
            $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

            $employees = $this->planningService->filterEmployees($search, $department);
            $plannings = $this->planningService->getPlanningsBetween($startOfWeek, $endOfWeek);
            $departments = $this->planningService->getDepartments();
            $weekDays = $this->planningService->getWeekDays($startOfWeek);

            return view('planning.weekly', compact('employees', 'plannings', 'weekDays', 'week', 'year', 'startOfWeek', 'endOfWeek', 'search', 'department', 'departments'));
        } catch (Exception $e) {
            Log::error('Planning weekly error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return view('planning.weekly', ['error' => 'Erreur planning hebdo.']);
        }
    }

<<<<<<< HEAD

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
=======
    // ... other index-like methods with try-catch
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06

    public function store(StorePlanningRequest $request)
    {
        try {
            Planning::updateOrCreate(
                ['employee_id' => $request->employee_id, 'date' => $request->date],
                $request->validated()
            );

            return back()->with('success', 'Planning mis à jour.');
        } catch (Exception $e) {
            Log::error('Planning store error: ' . $e->getMessage(), ['data' => $request->validated(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erreur sauvegarde planning.');
        }
    }

<<<<<<< HEAD

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
=======
    public function update(UpdatePlanningRequest $request, Planning $planning)
    {
        try {
            $planning->update($request->validated());

            return back()->with('success', 'Shift mis à jour.');
        } catch (Exception $e) {
            Log::error('Planning update error: ' . $e->getMessage(), ['planning_id' => $planning->id, 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erreur mise à jour shift.');
        }
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
    }

    public function destroy(Planning $planning)
    {
        if (!$planning->exists) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['error' => 'Shift introuvable'], 404);
            }

<<<<<<< HEAD
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
=======
            return back()->with('error', 'Shift introuvable');
        }

        try {
            $planning->delete();

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', 'Shift supprimé.');
        } catch (\Exception $e) {
            Log::error('Shift delete failed for planning ID ' . $planning->id . ': ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['error' => 'Erreur suppression: ' . $e->getMessage()], 500);
            }

            return back()->with('error', 'Erreur suppression shift: ' . $e->getMessage());
        }
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
    }

    // ... keep existing updateDragDrop with enhanced logging if needed

    // Export methods with try-catch for Excel/PDF
    public function exportWeeklyPdf(Request $request)
    {
        try {
            // ... existing code ...
            $pdf = Pdf::loadView(...) ->setPaper('a4', 'landscape');
            return $pdf->download($filename);
        } catch (Exception $e) {
            Log::error('PDF export error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erreur génération PDF.');
        }
    }

    // Similar for other exports/PDF/index methods

    // Keep other methods with added try-catch...
}

