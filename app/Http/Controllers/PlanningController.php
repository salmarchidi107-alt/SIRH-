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

class PlanningController extends Controller
{
    public function __construct(private PlanningService $planningService) {}

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request)
    {
        $employee_id = $request->employee_id;
        $room_id     = $request->room_id;
        $month       = $request->month ?? now()->month;
        $year        = $request->year  ?? now()->year;

        // Résoudre le nom de la salle depuis l'ID
        $roomName = null;
        if ($room_id) {
            $room     = Room::find($room_id);
            $roomName = $room?->name;
        }

        $plannings = Planning::with(['employee', 'room'])
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->when($employee_id, fn($q) => $q->where('employee_id', $employee_id))
            ->when($roomName,    fn($q) => $q->where('room', $roomName))
            ->get();

        $employees = Employee::active()->get();
        $rooms     = Room::all();

        return view('planning.index', compact(
            'plannings', 'employees', 'rooms',
            'month', 'year', 'employee_id', 'room_id'
        ));
    }

    // =========================================================================
    // WEEKLY
    // =========================================================================

    public function weekly(Request $request)
    {
        try {
            $rooms      = Room::all();
            $week       = $request->week       ?? now()->weekOfYear;
            $year       = $request->year       ?? now()->year;
            $search     = $request->search;
            $department = $request->department;
            $roomId     = $request->room_id;

            // ✅ showAllRooms = false si une salle est sélectionnée
            // Cela active le filtre employés dans filterEmployees()
            $showAllRooms = empty($roomId);

            $startOfWeek = now()->setISODate($year, $week)->startOfWeek(Carbon::MONDAY);
            $endOfWeek   = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

            // ✅ Résoudre le NOM de la salle depuis l'ID
            $roomName = null;
            if ($roomId) {
                $room     = Room::find($roomId);
                $roomName = $room?->name;
            }

            // Filtre employés : si salle sélectionnée, seuls les employés
            // ayant un planning dans cette salle sont retournés
            $employees = $this->planningService->filterEmployees(
                $search, $department, $roomId, $showAllRooms, $startOfWeek, $endOfWeek
            );

            // Filtre plannings : si salle sélectionnée, seuls les shifts
            // de cette salle sont retournés
            $plannings   = $this->planningService->getPlanningsBetween($startOfWeek, $endOfWeek, $roomName);
            $departments = $this->planningService->getDepartments();
            $weekDays    = $this->planningService->getWeekDays($startOfWeek);

            return view('planning.weekly', compact(
                'employees', 'plannings', 'weekDays', 'week', 'year',
                'startOfWeek', 'endOfWeek', 'search', 'department',
                'departments', 'rooms', 'showAllRooms'
            ));
        } catch (Exception $e) {
            Log::error('Planning weekly error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return view('planning.weekly', ['error' => 'Erreur planning hebdo.']);
        }
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

            $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
            $endOfMonth   = $startOfMonth->copy()->endOfMonth();

            $employees   = $this->planningService->filterEmployees($search, $department);
            $plannings   = $this->planningService->getPlanningsBetween($startOfMonth, $endOfMonth);
            $departments = $this->planningService->getDepartments();

            $daysOfMonth = collect();
            $currentDay  = $startOfMonth->copy();
            while ($currentDay <= $endOfMonth) {
                $daysOfMonth->push($currentDay->copy());
                $currentDay->addDay();
            }

            return view('planning.monthly', compact(
                'employees', 'plannings', 'daysOfMonth',
                'month', 'year', 'startOfMonth', 'endOfMonth',
                'search', 'department', 'departments'
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
            Planning::updateOrCreate(
                ['employee_id' => $request->employee_id, 'date' => $request->date],
                $request->validated()
            );

            return back()->with('success', 'Planning mis à jour.');
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
    // UPDATE ROOM — stocke le NOM de la salle, pas l'ID
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
            // Récupérer le NOM depuis l'ID
            $roomName = null;
            if (!empty($validated['room_id'])) {
                $room     = Room::find($validated['room_id']);
                $roomName = $room?->name;
            }

            // Stocker le NOM dans la colonne `room`
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
            $week = $request->week ?? now()->weekOfYear;
            $year = $request->year ?? now()->year;

            $startOfWeek = now()->setISODate($year, $week)->startOfWeek(Carbon::MONDAY);
            $endOfWeek   = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

            $employees = $this->planningService->filterEmployees(null, null);
            $plannings = $this->planningService->getPlanningsBetween($startOfWeek, $endOfWeek);
            $weekDays  = $this->planningService->getWeekDays($startOfWeek);

            $filename = "planning_week_{$week}_{$year}.pdf";

            $pdf = Pdf::loadView('planning.weekly_pdf', [
                'employees'   => $employees,
                'plannings'   => $plannings,
                'weekDays'    => $weekDays,
                'week'        => $week,
                'year'        => $year,
                'startOfWeek' => $startOfWeek,
                'endOfWeek'   => $endOfWeek,
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

            $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
            $endOfMonth   = $startOfMonth->copy()->endOfMonth();

            $employees   = $this->planningService->filterEmployees($search, $department);
            $plannings   = $this->planningService->getPlanningsBetween($startOfMonth, $endOfMonth);
            $departments = $this->planningService->getDepartments();

            $daysOfMonth = collect();
            $currentDay  = $startOfMonth->copy();
            while ($currentDay <= $endOfMonth) {
                $daysOfMonth->push($currentDay->copy());
                $currentDay->addDay();
            }

            $filename = "planning_mensuel_{$month}_{$year}.pdf";

            $pdf = Pdf::loadView('planning.monthly_pdf', compact(
                'employees', 'plannings', 'daysOfMonth',
                'month', 'year', 'startOfMonth', 'endOfMonth',
                'search', 'department', 'departments'
            ))->setPaper('a4', 'landscape');

            return $pdf->download($filename);
        } catch (Exception $e) {
            Log::error('Monthly PDF export error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erreur génération PDF mensuel.');
        }
    }
}