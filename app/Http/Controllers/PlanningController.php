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
use App\Models\Room;
use App\Models\Tenant;

class PlanningController extends Controller
{
    public function __construct(private PlanningService $planningService) {}

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request)
    {
        $month      = $request->month ?? now()->month;
        $year       = $request->year ?? now()->year;
        $search     = $request->search;
        $department = $request->department;
        $shift_type = $request->shift_type;

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        $employees = Employee::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->when($department, function ($q) use ($department) {
                $q->where('department', $department);
            })
            ->orderBy('last_name')
            ->get();

        $plannings   = $this->planningService->getPlanningsBetween($startOfMonth, $endOfMonth);
        $departments = $this->planningService->getDepartments();

        return view('planning.index', compact(
            'employees', 'plannings', 'departments',
            'month', 'year', 'search', 'department', 'shift_type'
        ));
    }

    // =========================================================================
    // WEEKLY
    // =========================================================================

    public function weekly(Request $request)
    {
        $rooms      = Room::all();
        $week       = (int) ($request->week ?? now()->weekOfYear);
        $year       = (int) ($request->year ?? now()->year);
        $search     = $request->search;
        $department = $request->department;
        $shift_type = $request->shift_type;
        $roomId     = $request->room_id;

        $showAllRooms = empty($roomId);

        $startOfWeek = now()->setISODate($year, $week)->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

        $roomName = null;
        if ($roomId) {
            $room     = Room::find($roomId);
            $roomName = $room?->name;
        }

        // Si l'utilisateur est un employé : ne montrer que son planning
        $authUser   = auth()->user();
        $isEmployee = ($authUser->role === 'employee');

        if ($isEmployee && $authUser->employee) {
            $employees    = Employee::where('id', $authUser->employee->id)->get();
            $search       = null;
            $department   = null;
            $roomId       = null;
            $showAllRooms = true;
        } else {
            $employees = $this->planningService->filterEmployees(
                $search, $department, $roomId, $showAllRooms, $startOfWeek, $endOfWeek
            );
        }

        $plannings   = $this->planningService->getPlanningsBetween($startOfWeek, $endOfWeek, $roomName);
        $departments = $this->planningService->getDepartments();
        $weekDays    = $this->planningService->getWeekDays($startOfWeek);

        return view('planning.weekly', compact(
            'employees', 'plannings', 'weekDays', 'week', 'year',
            'startOfWeek', 'endOfWeek', 'search', 'department',
            'departments', 'rooms', 'showAllRooms', 'shift_type', 'isEmployee'
        ));
    }

    // =========================================================================
    // MONTHLY
    // =========================================================================

    public function monthly(Request $request)
    {
        try {
            $month      = $request->month      ?? now()->month;
            $year       = $request->year       ?? now()->year;
            $search     = $request->search;
            $department = $request->department;
            $shift_type = $request->shift_type;
            $roomId     = $request->room_id;

            $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
            $endOfMonth   = $startOfMonth->copy()->endOfMonth();

            $roomName = null;
            if ($roomId) {
                $room     = Room::find($roomId);
                $roomName = $room?->name;
            }

            $showAllRooms = empty($roomId);

            // Si l'utilisateur est un employé : ne montrer que son planning
            $authUser   = auth()->user();
            $isEmployee = ($authUser->role === 'employee');

            if ($isEmployee && $authUser->employee) {
                $employees    = Employee::where('id', $authUser->employee->id)->get();
                $search       = null;
                $department   = null;
                $roomId       = null;
                $showAllRooms = true;
            } else {
                $employees = $this->planningService->filterEmployees(
                    $search, $department, $roomId, $showAllRooms, $startOfMonth, $endOfMonth
                );
            }

            $plannings   = $this->planningService->getPlanningsBetween($startOfMonth, $endOfMonth, $roomName);
            $departments = $this->planningService->getDepartments();
            $rooms       = Room::all();

            $daysOfMonth = collect();
            $currentDay  = $startOfMonth->copy();
            while ($currentDay <= $endOfMonth) {
                $daysOfMonth->push($currentDay->copy());
                $currentDay->addDay();
            }

            return view('planning.monthly', compact(
                'employees', 'plannings', 'daysOfMonth',
                'month', 'year', 'startOfMonth', 'endOfMonth',
                'search', 'department', 'departments',
                'rooms', 'shift_type', 'roomId', 'isEmployee'
            ));
        } catch (Exception $e) {
            Log::error('Planning monthly error: ' . $e->getMessage());
            return view('planning.monthly', ['error' => 'Erreur planning mensuel.']);
        }
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function store(StorePlanningRequest $request)
    {
        try {
            Planning::create($request->validated());
            return back()->with('success', 'Planning créé.');
        } catch (Exception $e) {
            Log::error('Planning store error: ' . $e->getMessage(), [
                'data'  => $request->validated(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Erreur sauvegarde planning.');
        }
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(UpdatePlanningRequest $request, Planning $planning)
    {
        try {
            $planning->update($request->validated());
            return back()->with('success', 'Shift mis à jour.');
        } catch (Exception $e) {
            Log::error('Planning update error: ' . $e->getMessage(), [
                'planning_id' => $planning->id,
                'trace'       => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Erreur mise à jour shift.');
        }
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function destroy(Planning $planning)
    {
        if (!$planning->exists) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['error' => 'Shift introuvable'], 404);
            }
            return back()->with('error', 'Shift introuvable');
        }

        try {
            $planning->delete();

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => true]);
            }
            return back()->with('success', 'Shift supprimé.');
        } catch (Exception $e) {
            Log::error('Shift delete failed for planning ID ' . $planning->id . ': ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['error' => 'Erreur suppression: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Erreur suppression shift: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // DRAG & DROP
    // =========================================================================

    public function updateDragDrop(Request $request)
    {
        $validated = $request->validate([
            'planning_id'     => 'required|exists:plannings,id',
            'new_date'        => 'required|date',
            'new_employee_id' => 'sometimes|exists:employees,id',
            'duplicate'       => 'sometimes|boolean',
        ]);

        try {
            $this->planningService->updateDragDrop($validated);
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            Log::error('Drag-drop failed: ' . $e->getMessage(), ['data' => $validated]);
            return response()->json(['success' => false, 'error' => 'Erreur lors du déplacement'], 500);
        }
    }

    // =========================================================================
    // UPDATE ROOM
    // =========================================================================

    public function updateRoom(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'room_id'     => 'nullable|exists:rooms,id',
            'start'       => 'required|date',
            'end'         => 'required|date',
        ]);

        try {
            $roomName = null;
            if (!empty($validated['room_id'])) {
                $room     = Room::find($validated['room_id']);
                $roomName = $room?->name;
            }

            Planning::where('employee_id', $validated['employee_id'])
                ->whereDate('date', '>=', $validated['start'])
                ->whereDate('date', '<=', $validated['end'])
                ->update(['room' => $roomName]);

            return response()->json(['success' => true, 'room_name' => $roomName]);
        } catch (Exception $e) {
            Log::error('Update room error: ' . $e->getMessage(), [
                'data'  => $validated,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'error' => 'Erreur mise à jour salle'], 500);
        }
    }

    // =========================================================================
    // EXPORT PDF WEEKLY
    // =========================================================================

    public function exportWeeklyPdf(Request $request)
    {
        try {
            $week       = $request->week ?? now()->weekOfYear;
            $year       = $request->year ?? now()->year;
            $search     = $request->search;
            $department = $request->department;
            $shift_type = $request->shift_type;
            $roomId     = $request->room_id;

            $startOfWeek = now()->setISODate($year, $week)->startOfWeek(Carbon::MONDAY);
            $endOfWeek   = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

            $roomName = null;
            if ($roomId) {
                $room     = Room::find($roomId);
                $roomName = $room?->name;
            }
            $showAllRooms = empty($roomId);

            $employees = $this->planningService->filterEmployees(
                $search, $department, $roomId, $showAllRooms, $startOfWeek, $endOfWeek
            );
            $plannings = $this->planningService->getPlanningsBetween($startOfWeek, $endOfWeek, $roomName);

            // Filtrage shift_type — logique identique à celle de la vue weekly.blade.php
            if ($shift_type === 'absence') {
                $employees = $employees->filter(function ($emp) use ($startOfWeek, $endOfWeek) {
                    for ($d = $startOfWeek->copy(); $d->lte($endOfWeek); $d->addDay()) {
                        if ($emp->hasApprovedAbsenceOn($d)) return true;
                    }
                    return false;
                })->values();
            } elseif ($shift_type === 'sans_shift') {
                $employees = $employees->filter(function ($emp) use ($plannings, $startOfWeek, $endOfWeek) {
                    $empPlannings = $plannings->get($emp->id, collect());
                    for ($d = $startOfWeek->copy(); $d->lte($endOfWeek); $d->addDay()) {
                        $hasShift = $empPlannings->filter(fn($p) =>
                            \Carbon\Carbon::parse($p->date)->format('Y-m-d') === $d->format('Y-m-d')
                        )->isNotEmpty();
                        if ($hasShift) return false;
                    }
                    return true;
                })->values();
            } elseif ($shift_type) {
                $plannings = $plannings
                    ->map(fn($items) => $items->where('shift_type', $shift_type))
                    ->filter(fn($items) => $items->isNotEmpty());
            }

            $weekDays = $this->planningService->getWeekDays($startOfWeek);

            $tenant = auth()->user()?->tenant
                ?? Tenant::find(config('app.current_tenant_id'));

            $filename = "planning_week_{$week}_{$year}.pdf";

            $pdf = Pdf::loadView('planning.weekly_pdf', [
                'employees'   => $employees,
                'plannings'   => $plannings,
                'weekDays'    => $weekDays,
                'week'        => $week,
                'year'        => $year,
                'startOfWeek' => $startOfWeek,
                'endOfWeek'   => $endOfWeek,
                'search'      => $search,
                'department'  => $department,
                'shift_type'  => $shift_type,
                'roomName'    => $roomName,
                'tenant'      => $tenant,
            ])->setPaper('a4', 'landscape');

            return $pdf->download($filename);
        } catch (Exception $e) {
            Log::error('PDF export error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erreur génération PDF.');
        }
    }

    // =========================================================================
    // EXPORT PDF MONTHLY
    // =========================================================================

    public function exportMonthlyPdf(Request $request)
    {
        try {
            $month      = $request->month      ?? now()->month;
            $year       = $request->year       ?? now()->year;
            $search     = $request->search;
            $department = $request->department;
            $shift_type = $request->shift_type;
            $roomId     = $request->room_id;

            $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
            $endOfMonth   = $startOfMonth->copy()->endOfMonth();

            $roomName = null;
            if ($roomId) {
                $room     = Room::find($roomId);
                $roomName = $room?->name;
            }
            $showAllRooms = empty($roomId);

            $employees = $this->planningService->filterEmployees(
                $search, $department, $roomId, $showAllRooms, $startOfMonth, $endOfMonth
            );
            $plannings = $this->planningService->getPlanningsBetween($startOfMonth, $endOfMonth, $roomName);

            // La vue monthly ne gère pas absence/sans_shift, seulement un filtre shift_type simple
            if ($shift_type) {
                $plannings = $plannings
                    ->map(fn($items) => $items->where('shift_type', $shift_type))
                    ->filter(fn($items) => $items->isNotEmpty());
            }

            $departments = $this->planningService->getDepartments();

            $daysOfMonth = collect();
            $currentDay  = $startOfMonth->copy();
            while ($currentDay <= $endOfMonth) {
                $daysOfMonth->push($currentDay->copy());
                $currentDay->addDay();
            }

            $tenant = auth()->user()?->tenant
                ?? Tenant::find(config('app.current_tenant_id'));

            $filename = "planning_mensuel_{$month}_{$year}.pdf";

            $pdf = Pdf::loadView('planning.monthly_pdf', compact(
                'employees', 'plannings', 'daysOfMonth',
                'month', 'year', 'startOfMonth', 'endOfMonth',
                'search', 'department', 'departments', 'shift_type', 'tenant'
            ))->setPaper('a4', 'landscape');

            return $pdf->download($filename);
        } catch (Exception $e) {
            Log::error('Monthly PDF export error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erreur génération PDF mensuel.');
        }
    }

    // =========================================================================
    // EXPORT EXCEL MONTHLY
    // =========================================================================

    public function exportMonthly(Request $request)
    {
        try {
            $month    = (int) ($request->month ?? now()->month);
            $year     = (int) ($request->year  ?? now()->year);
            $tenantId = auth()->user()->tenant_id;
            $filename = "planning_mensuel_{$month}_{$year}.xlsx";

            $roomName = null;
            if ($request->room_id) {
                $room     = Room::find($request->room_id);
                $roomName = $room?->name;
            }

            $filters = [
                'department' => $request->department,
                'search'     => $request->search,
                'shift_type' => $request->shift_type,
                'room_name'  => $roomName,
            ];

            return Excel::download(
                new PlanningMonthlyExport($tenantId, $month, $year, $filters),
                $filename
            );
        } catch (Exception $e) {
            Log::error('Monthly Excel export error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erreur génération export mensuel.');
        }
    }

    // =========================================================================
    // EXPORT EXCEL WEEKLY
    // =========================================================================

    public function exportWeekly(Request $request)
    {
        try {
            $week     = (int) ($request->week ?? now()->weekOfYear);
            $year     = (int) ($request->year ?? now()->year);
            $tenantId = auth()->user()->tenant_id;
            $filename = "planning_semaine_{$week}_{$year}.xlsx";

            $roomName = null;
            if ($request->room_id) {
                $room     = Room::find($request->room_id);
                $roomName = $room?->name;
            }

            $filters = [
                'department' => $request->department,
                'search'     => $request->search,
                'shift_type' => $request->shift_type,
                'room_name'  => $roomName,
            ];

            return Excel::download(
                new PlanningWeeklyExport($tenantId, $week, $year, $filters),
                $filename
            );
        } catch (Exception $e) {
            Log::error('Weekly Excel export error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erreur génération export hebdomadaire.');
        }
    }
}
