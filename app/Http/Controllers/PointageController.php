<?php

namespace App\Http\Controllers;

use App\Models\BadgeRecord;
use App\Models\Employee;
use App\Models\Pointage;
use App\Services\Pointage\PointageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;

class PointageController extends Controller
{
    public function __construct(private PointageService $pointageService) {}

    public function index(Request $request): View
    {
        $data = $this->pointageService->getIndexData($request);

        return view('pointage.index', $data);
    }


    public function export(Request $request)
    {
        $export = $this->pointageService->buildExportData($request);

        $filename = 'pointage_' . $export['currentDate']->format('Y-m-d')
                  . ($export['department']  ? '_' . \Illuminate\Support\Str::slug($export['department']) : '')
                  . ($export['shiftFilter'] ? '_' . $export['shiftFilter']                                : '')
                  . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PointageExport($export['rows'], $export['currentDate']),
            $filename
        );
    }


    public function exportPdf(Request $request)
    {
        try {
            $report = $this->pointageService->buildReport($request);

            if (! $report) {
                return back()->with('error', 'Aucun résultat avec ces filtres.');
            }

            return Pdf::loadView('pointage.pdf', [
                    'rows'         => $report['rows'],
                    'summary'      => $report['summary'],
                    'stats'        => $report['stats'],
                    'periode'      => $report['periode'],
                    'periodeLabel' => $report['periodeLabel'],
                    'filterInfo'   => $report['filterInfo'],
                    'generatedAt'  => $report['generatedAt'],
                ])
                ->setPaper('a4', $report['periode'] === 'jour' ? 'portrait' : 'landscape')
                ->setOptions(['isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans'])
                ->download($report['filename']);

        } catch (Exception $e) {
            Log::error('Pointage PDF error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur PDF: ' . $e->getMessage());
        }
    }


    public function lastPhoto(Employee $employee, Request $request): JsonResponse
    {
        $tenantId = $this->pointageService->getCurrentTenantId();
        $result   = $this->pointageService->getPhotoData($employee, $tenantId, $request->query('date'));

        return match ($result['status']) {
            'forbidden'     => response()->json(['success' => false, 'message' => "Accès non autorisé."], 403),
            'not_found'     => response()->json(['success' => false, 'message' => 'Aucune photo trouvée pour ce jour.']),
            'photo_missing' => response()->json(['success' => false, 'message' => 'Photo introuvable (fichier manquant sur le serveur).']),
            default         => response()->json([
                'success'     => true,
                'photo_url'   => $result['photo_url'],
                'employee'    => $result['employee'],
                'type'        => $result['type'],
                'recorded_at' => $result['recorded_at'],
            ]),
        };
    }


    public function marquerAbsent($employee_id)
    {
        $this->pointageService->marquerAbsent($employee_id);

        return back()->with('success', 'Employé marqué absent');
    }


    public function validerJournee(Request $request): JsonResponse
    {
        $result = $this->pointageService->validerJournee($request->input('date', today()->toDateString()));

        return response()->json($result);
    }


    public function toggleValider(Pointage $pointage): JsonResponse
    {
        $result = $this->pointageService->toggleValider($pointage);

        return response()->json($result);
    }


    public function toggleIgnore(Pointage $pointage): JsonResponse
    {
        $result = $this->pointageService->toggleIgnore($pointage);

        return response()->json($result);
    }


    public function update(Request $request, Pointage $pointage): JsonResponse
    {
        $data = $request->validate([
            'heure_entree'  => 'nullable|date_format:H:i',
            'heure_sortie'  => 'nullable|date_format:H:i',
            'pause_minutes' => 'nullable|integer|min:0|max:480',
            'statut'        => 'nullable|in:present,absent,absence_injustifiee,pas_de_badge',
        ]);

        $pointage = $this->pointageService->updatePointage($pointage, $data);

        return response()->json([
            'success'  => true,
            'pointage' => $pointage,
        ]);
    }


    public function toggleAbsence(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'nullable|date',
            'absent'      => 'required',
        ]);

        $result = $this->pointageService->toggleAbsence(
            (int) $request->employee_id,
            $request->date ?? today()->toDateString(),
            filter_var($request->absent, FILTER_VALIDATE_BOOLEAN)
        );

        if (! $result['success']) {
            // Verrouillé par un congé approuvé -> 403, pas une vraie erreur serveur.
            $status = ! empty($result['locked']) ? 403 : 500;

            return response()->json([
                'success' => false,
                'locked'  => $result['locked'] ?? false,
                'error'   => $result['error'],
            ], $status);
        }

        return response()->json([
            'success' => true,
            'statut'  => $result['statut'],
            'id'      => $result['id'],
        ]);
    }


    public function badgesPin(Request $request): View
    {
        $data = $this->pointageService->getBadgesPinData($request);

        return view('pointage.badges-pin', $data);
    }


    public function regenererPin(Request $request): JsonResponse
    {
        $request->validate(['employee_id' => 'required|exists:employees,id']);

        $result = $this->pointageService->regenerateOne((int) $request->employee_id);

        return response()->json($result);
    }


    public function regenererTousPins(Request $request): JsonResponse
    {
        $department = $request->filled('department') ? $request->department : null;

        $result = $this->pointageService->regenerateAll($department);

        return response()->json($result);
    }


    public function exportBadgesPinPdf(Request $request)
    {
        try {
            $data = $this->pointageService->getExportPinData($request);

            if (! $data) {
                return back()->with('error', 'Aucun employé trouvé.');
            }

            return Pdf::loadView('pdf.badges-pin', [
                    'byDept'      => $data['byDept'],
                    'generatedAt' => $data['generatedAt'],
                    'deptFilter'  => $data['deptFilter'],
                ])
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isRemoteEnabled' => true,
                    'defaultFont'     => 'DejaVu Sans',
                    'margin_left'     => 10,
                    'margin_right'    => 10,
                    'margin_top'      => 10,
                    'margin_bottom'   => 10,
                ])
                ->download($data['filename']);

        } catch (Exception $e) {
            Log::error('Badges PIN PDF error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur PDF: ' . $e->getMessage());
        }
    }
}
