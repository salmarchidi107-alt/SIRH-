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

class PlanningController extends Controller
{
    public function __construct(private PlanningService $planningService) {}

    public function index(Request $request)
    {
        try {
            $employee_id = $request->employee_id;
            $month = $request->month ?? now()->month;
            $year = $request->year ?? now()->year;

            $employees = Employee::active()->get();
            $plannings = Planning::with('employee')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->when($employee_id, fn ($q) => $q->where('employee_id', $employee_id))
                ->get();

            return view('planning.index', compact('plannings', 'employees', 'month', 'year', 'employee_id'));
        } catch (ModelNotFoundException $e) {
            Log::warning('Planning index employee not found: ' . $e->getMessage());
            return back()->with('error', 'Employé non trouvé.');
        } catch (Exception $e) {
            Log::error('Planning index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return view('planning.index', ['error' => 'Erreur chargement planning.']);
        }
    }

    // Similar try-catch for weekly, monthly, global, show...

    public function weekly(Request $request)
    {
        try {
            $week = $request->week ?? now()->weekOfYear;
            $year = $request->year ?? now()->year;
            $search = $request->search;
            $department = $request->department;

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

    // ... other index-like methods with try-catch

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

    public function update(UpdatePlanningRequest $request, Planning $planning)
    {
        try {
            $planning->update($request->validated());

            return back()->with('success', 'Shift mis à jour.');
        } catch (Exception $e) {
            Log::error('Planning update error: ' . $e->getMessage(), ['planning_id' => $planning->id, 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erreur mise à jour shift.');
        }
    }

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
        } catch (\Exception $e) {
            Log::error('Shift delete failed for planning ID ' . $planning->id . ': ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['error' => 'Erreur suppression: ' . $e->getMessage()], 500);
            }

            return back()->with('error', 'Erreur suppression shift: ' . $e->getMessage());
        }
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

