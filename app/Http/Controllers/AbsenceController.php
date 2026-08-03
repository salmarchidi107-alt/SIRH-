<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAbsenceRequest;
use App\Http\Requests\UpdateAbsenceRequest;
use App\Models\Absence;
use App\Services\Absence\AbsenceConflictService;
use App\Services\Absence\AbsenceCounterService;
use App\Services\Absence\AbsenceService;
use App\Services\Absence\EmployeeFilterService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsencesExport;
use App\Exports\CountersExport;
use App\Exports\DroitsAbsenceExport;

class AbsenceController extends Controller
{
    public function __construct(
        private AbsenceService $absenceService,
        private AbsenceCounterService $counterService,
        private AbsenceConflictService $conflictService,
        private EmployeeFilterService $employeeFilterService,
    ) {}

    public function index(Request $request)
    {
        return view('absences.index', $this->absenceService->getIndexData($request));
    }

    public function create()
    {
        return view('absences.create', $this->absenceService->getCreateData());
    }

    public function store(StoreAbsenceRequest $request)
    {
        $result = $this->absenceService->store($request->validated(), $request);

        return match ($result['result']) {
            'type_autre_missing' => back()
                ->withInput()
                ->withErrors(['type_autre' => "Veuillez préciser le type d'absence."]),

            'conflict' => back()
                ->withInput()
                ->with('conflict_warning', "Cette période est déjà occupée par <strong>{$result['employee_name']}</strong> (du {$result['from']} au {$result['to']}). Voulez-vous soumettre quand même ?"),

            'self_conflict' => back()
                ->withInput()
                ->withErrors(['start_date' => 'Cet employé a déjà une absence approuvée qui chevauche cette période.']),

            default => redirect()->route('absences.index')
                ->with('success', "Demande d'absence soumise avec succès."),
        };
    }

    public function show(Absence $absence)
    {
        $absence = $this->absenceService->getShowData($absence);

        return view('absences.show', compact('absence'));
    }

    public function edit(Absence $absence)
    {
        $employees = $this->absenceService->getEditEmployees();

        return view('absences.edit', compact('absence', 'employees'));
    }

    public function update(UpdateAbsenceRequest $request, Absence $absence)
    {
        $this->absenceService->update($absence, $request->validated());

        return redirect()->route('absences.index')
            ->with('success', 'Absence mise à jour.');
    }

    public function destroy(Absence $absence)
    {
        $this->absenceService->delete($absence);

        return redirect()->route('absences.index')
            ->with('success', 'Demande supprimée.');
    }

    public function approve(Absence $absence)
    {
        if (! auth()->user()->can('approve_absences')) {
            abort(403, 'Accès non autorisé.');
        }

        $this->absenceService->approve($absence, auth()->id());

        return back()->with('success', "Demande approuvée. Un email a été envoyé à l'employé.");
    }

    public function reject(Absence $absence)
    {
        if (! auth()->user()->can('approve_absences')) {
            abort(403, 'Accès non autorisé.');
        }

        $this->absenceService->reject($absence, auth()->id());

        return back()->with('success', "Demande rejetée. Un email a été envoyé à l'employé.");
    }

    public function export()
    {
        return Excel::download(
            new AbsencesExport(config('app.current_tenant_id')),
            'demandes_absences.xlsx'
        );
    }

    public function counters(Request $request)
    {
        $departments  = $this->employeeFilterService->getDepartments();
        $countersData = $this->counterService->buildCountersData($request);
        $cycle        = $request->filled('cycle') ? (int) $request->get('cycle') : null;
        $maxCycle     = $this->counterService->getMaxCycleNumber($request);

        $search     = $request->get('search');
        $department = $request->get('department');

        return view('absences.counters', compact(
            'countersData', 'cycle', 'maxCycle', 'departments', 'search', 'department'
        ));
    }

    public function countersExport(Request $request)
    {
        $cycle        = $request->filled('cycle') ? (int) $request->get('cycle') : 'actuel';
        $countersData = $this->counterService->buildCountersData($request);

        return Excel::download(
            new CountersExport($countersData, $cycle),
            "compteurs_absences_cycle_{$cycle}.xlsx"
        );
    }

    public function droitsExport(Request $request)
    {
        $cycle        = $request->filled('cycle') ? (int) $request->get('cycle') : 'actuel';
        $countersData = $this->counterService->buildCountersData($request);

        return Excel::download(
            new DroitsAbsenceExport($countersData),
            "droits_absences_cycle_{$cycle}.xlsx"
        );
    }

    public function calendar(Request $request)
    {
        return view('absences.calendar', $this->absenceService->getCalendarData($request));
    }

    public function getConflicts(Request $request)
    {
        $conflicts = $this->conflictService->getConflictsForMonth($request);

        return response()->json($conflicts->values());
    }

    public function downloadPdf(Absence $absence)
    {
        $data = $this->absenceService->preparePdfData($absence);

        $pdf = Pdf::loadView('absences.pdf', [
                'absence' => $data['absence'],
                'tenant'  => $data['tenant'],
            ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isRemoteEnabled'      => false,
                'isHtml5ParserEnabled' => true,
            ]);

        return $pdf->download($data['filename']);
    }
}
