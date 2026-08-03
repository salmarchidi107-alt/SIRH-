<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlanningRequest;
use App\Http\Requests\UpdatePlanningDragDropRequest;
use App\Http\Requests\UpdatePlanningRequest;
use App\Models\Planning;
use App\Services\PlanningService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Maatwebsite\Excel\Facades\Excel;

class PlanningController extends Controller
{
    public function __construct(private PlanningService $planningService) {}

    public function index(Request $request)
    {
        $data = $this->planningService->getIndexData($request);

        return view('planning.index', $data);
    }

    public function weekly(Request $request)
    {
        $data = $this->planningService->getWeeklyData($request, auth()->user());

        return view('planning.weekly', $data);
    }

    public function monthly(Request $request)
    {
        try {
            $data = $this->planningService->getMonthlyData($request, auth()->user());

            return view('planning.monthly', $data);
        } catch (Exception $e) {
            Log::error('Planning monthly error: ' . $e->getMessage());
            return view('planning.monthly', ['error' => 'Erreur planning mensuel.']);
        }
    }

    public function store(StorePlanningRequest $request)
    {
        try {
            $this->planningService->createPlanning($request->validated());
            return back()->with('success', 'Planning créé.');
        } catch (Exception $e) {
            Log::error('Planning store error: ' . $e->getMessage(), [
                'data'  => $request->validated(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Erreur sauvegarde planning.');
        }
    }

    public function update(UpdatePlanningRequest $request, Planning $planning)
    {
        try {
            $this->planningService->updatePlanning($planning, $request->validated());
            return back()->with('success', 'Shift mis à jour.');
        } catch (Exception $e) {
            Log::error('Planning update error: ' . $e->getMessage(), [
                'planning_id' => $planning->id,
                'trace'       => $e->getTraceAsString(),
            ]);
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
            $this->planningService->deletePlanning($planning);

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

    public function updateRoom(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'room_id'     => 'nullable|exists:rooms,id',
            'start'       => 'required|date',
            'end'         => 'required|date',
        ]);

        try {
            $roomName = $this->planningService->updateEmployeeRoom($validated);
            return response()->json(['success' => true, 'room_name' => $roomName]);
        } catch (Exception $e) {
            Log::error('Update room error: ' . $e->getMessage(), [
                'data'  => $validated,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'error' => 'Erreur mise à jour salle'], 500);
        }
    }

    public function exportWeeklyPdf(Request $request)
    {
        try {
            $export = $this->planningService->getWeeklyPdfData($request);

            $pdf = Pdf::loadView($export['view'], $export['data'])->setPaper('a4', 'landscape');

            return $pdf->download($export['filename']);
        } catch (Exception $e) {
            Log::error('PDF export error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erreur génération PDF.');
        }
    }

    public function exportMonthlyPdf(Request $request)
    {
        try {
            $export = $this->planningService->getMonthlyPdfData($request);

            $pdf = Pdf::loadView($export['view'], $export['data'])->setPaper('a4', 'landscape');

            return $pdf->download($export['filename']);
        } catch (Exception $e) {
            Log::error('Monthly PDF export error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erreur génération PDF mensuel.');
        }
    }

    public function exportMonthly(Request $request)
    {
        try {
            $tenantId = auth()->user()->tenant_id;
            $export   = $this->planningService->getMonthlyExcelExport($request, $tenantId);

            return Excel::download($export['export'], $export['filename']);
        } catch (Exception $e) {
            Log::error('Monthly Excel export error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erreur génération export mensuel.');
        }
    }

    public function exportWeekly(Request $request)
    {
        try {
            $tenantId = auth()->user()->tenant_id;
            $export   = $this->planningService->getWeeklyExcelExport($request, $tenantId);

            return Excel::download($export['export'], $export['filename']);
        } catch (Exception $e) {
            Log::error('Weekly Excel export error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erreur génération export hebdomadaire.');
        }
    }
}
