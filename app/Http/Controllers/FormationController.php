<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Http\Requests\FormationRequest;
use App\Services\FormationService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class FormationController extends Controller
{
    public function __construct(private FormationService $formationService) {}

    public function index(Request $request)
    {
        return view('lms.index', $this->formationService->getIndexData($request));
    }


    public function planning(Request $request)
    {
        return view('lms.planning', $this->formationService->getPlanningData($request));
    }


    public function employeesByDepartment(Request $request)
    {
        return response()->json(
            $this->formationService->getEmployeesByDepartment($request->departement_id)
        );
    }


    public function store(FormationRequest $request)
    {
        $data = $this->formationService->resolveLibre($request->validated(), $request);
        $this->formationService->createFormation($data);

        return redirect()->back()->with('success', 'Formation ajoutée avec succès.');
    }

    public function update(FormationRequest $request, Formation $formation)
    {
        $data = $this->formationService->resolveLibre($request->validated(), $request);
        $this->formationService->updateFormation($formation, $data);

        return redirect()->back()->with('success', 'Formation mise à jour.');
    }

    public function destroy(Formation $formation)
    {
        $this->formationService->deleteFormation($formation);

        return redirect()->back()->with('success', 'Formation supprimée.');
    }


    public function exportPdf(Request $request)
    {
        $data = $this->formationService->getExportPdfData();

        $pdf = Pdf::loadView('lms.pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('formations_' . now()->format('Y-m-d') . '.pdf');
    }
}
