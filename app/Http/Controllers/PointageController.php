<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Pointage;
use App\Models\BadgeRecord;
use App\Models\Tablette;
use App\Scopes\TenantScope;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Collection;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Exception;

class PointageController extends Controller
{
    private const TZ = 'Africa/Casablanca';

    // =========================================================================
    // Helper : récupère le tenant_id courant
    // =========================================================================
    private function getCurrentTenantId(): ?int
    {
        $tenantId = config('app.current_tenant_id');

        if (blank($tenantId) && auth()->check()) {
            $tenantId = auth()->user()->tenant_id;
        }

        return $tenantId ? (int) $tenantId : null;
    }

    // =========================================================================
    // marquerAbsent
    // =========================================================================
    public function marquerAbsent($employee_id)
    {
        $today    = today()->toDateString();
        $tenantId = $this->getCurrentTenantId();

        $pointage = Pointage::withoutGlobalScope(TenantScope::class)
            ->where('employee_id', $employee_id)
            ->where('date', $today)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($pointage) {
            $pointage->update([
                'statut'       => 'absent',
                'heure_entree' => null,
                'heure_sortie' => null,
                'valide'       => false,
            ]);
        } else {
            Pointage::create([
                'employee_id'  => $employee_id,
                'date'         => $today,
                'tenant_id'    => $tenantId,
                'statut'       => 'absent',
                'heure_entree' => null,
                'heure_sortie' => null,
                'valide'       => false,
            ]);
        }

        return back()->with('success', 'Employé marqué absent');
    }

    // =========================================================================
    // index
    // =========================================================================
    public function index(Request $request): View
    {
        $date        = $request->get('date', today()->toDateString());
        $currentDate = Carbon::parse($date);
        $tenantId    = $this->getCurrentTenantId();

        Carbon::setLocale('fr');
        $startOfWeek = $currentDate->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = $currentDate->copy()->endOfWeek(Carbon::SUNDAY);

        $weekDays = collect();
        for ($d = $startOfWeek->copy(); $d->lte($endOfWeek); $d->addDay()) {
            $weekDays->push([
                'date'       => $d->copy(),
                'label'      => ucfirst($d->translatedFormat('l')),
                'short'      => $d->translatedFormat('d M.'),
                'isToday'    => $d->isToday(),
                'isSelected' => $d->toDateString() === $currentDate->toDateString(),
                'valide'     => Pointage::forDate($d->toDateString())
                    ->where('tenant_id', $tenantId)
                    ->where('valide', true)
                    ->exists(),
            ]);
        }

        $departments = \App\Models\Department::names();
        $vue         = $request->get('vue', 'tous');

        $employeesQuery = Employee::active()
            ->with(['pointages' => function ($q) use ($currentDate, $tenantId) {
                $q->forDate($currentDate->toDateString())
                  ->where('tenant_id', $tenantId);
            }])
            ->when($request->filled('search'),     fn($q) => $q->search($request->search))
            ->when($request->filled('department'), fn($q) => $q->department($request->department))
            ->defaultOrder();

        $employees = $employeesQuery->get()
            ->map(function ($emp) use ($currentDate, $tenantId) {

                $pointage = $emp->pointages->first();

                // Ne jamais écraser une absence
                if ($pointage && in_array($pointage->statut, ['absent', 'absence_injustifiee'])) {
                    return [
                        'id'       => $emp->id,
                        'nom'      => $emp->first_name . ' ' . $emp->last_name,
                        'avatar'   => strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)),
                        'pointage' => $pointage,
                    ];
                }

                if (! $pointage || ! $pointage->ignore_badge) {
                    // Récupère les BadgeRecords (pas les Pointages)
                    $shift = BadgeRecord::where('employee_id', $emp->id)
                        ->whereDate('created_at', $currentDate->toDateString())
                        ->orderBy('created_at')
                        ->get();

                    if ($shift->isNotEmpty()) {
                        $pointage = $this->syncPointageFromBadgeRecords(
                            $emp->id, $currentDate, $shift, $tenantId
                        );
                    }
                }

                return [
                    'id'       => $emp->id,
                    'nom'      => $emp->first_name . ' ' . $emp->last_name,
                    'avatar'   => strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)),
                    'pointage' => $pointage,
                ];
            });

        if ($vue === 'pointe') {
            $employees = $employees->filter(function ($e) {
                return $e['pointage']?->heure_entree && ! in_array($e['pointage']->statut ?? '', ['absent']);
            });
        } elseif ($vue === 'non_pointe') {
            $employees = $employees->filter(function ($e) {
                return ! $e['pointage']?->heure_entree || in_array($e['pointage']?->statut ?? '', ['absent', 'pas_de_badge']);
            });
        }

        $stats = [
            'valides'    => $employees->filter(fn($e) => $e['pointage']?->valide)->count(),
            'presents'   => $employees->filter(fn($e) => $e['pointage']?->statut === 'present')->count(),
            'absents'    => $employees->filter(fn($e) => in_array($e['pointage']?->statut, ['absent', 'absence_injustifiee']))->count(),
            'en_attente' => $employees->filter(fn($e) => ! $e['pointage'] || $e['pointage']?->statut === 'pas_de_badge')->count(),
            'total'      => $employees->count(),
        ];

        $dernierSync = null;
        try {
            $dernierSync = Tablette::where('active', true)
                ->latest('derniere_connexion')
                ->first();
        } catch (\Exception $e) {}

        return view('pointage.index', compact(
            'employees', 'departments', 'weekDays', 'currentDate',
            'startOfWeek', 'endOfWeek', 'stats', 'dernierSync', 'vue'
        ));
    }

    // =========================================================================
    // validerJournee
    // =========================================================================
    public function validerJournee(Request $request): JsonResponse
    {
        $date     = $request->input('date', today()->toDateString());
        $tenantId = $this->getCurrentTenantId();

        $count = Pointage::forDate($date)
            ->where('tenant_id', $tenantId)
            ->where('statut', 'present')
            ->update(['valide' => true]);

        return response()->json([
            'success' => true,
            'count'   => $count,
            'message' => $count . ' pointage(s) validé(s)',
        ]);
    }

    // =========================================================================
    // toggleValider
    // =========================================================================
    public function toggleValider(Pointage $pointage): JsonResponse
    {
        $pointage->update(['valide' => ! $pointage->valide]);

        return response()->json([
            'success' => true,
            'valide'  => $pointage->fresh()->valide,
        ]);
    }

    // =========================================================================
    // toggleIgnore
    // =========================================================================
    public function toggleIgnore(Pointage $pointage): JsonResponse
    {
        $pointage->update(['ignore_badge' => ! $pointage->ignore_badge]);

        return response()->json([
            'success'      => true,
            'ignore_badge' => $pointage->fresh()->ignore_badge,
        ]);
    }

    // =========================================================================
    // update
    // =========================================================================
    public function update(Request $request, Pointage $pointage): JsonResponse
    {
        $data = $request->validate([
            'heure_entree'  => 'nullable|date_format:H:i',
            'heure_sortie'  => 'nullable|date_format:H:i',
            'pause_minutes' => 'nullable|integer|min:0|max:480',
            'statut'        => 'nullable|in:present,absent,absence_injustifiee,pas_de_badge',
        ]);

        $pointage->update($data);
        $pointage->calculerTotalHeures();

        return response()->json([
            'success'  => true,
            'pointage' => $pointage->fresh(),
        ]);
    }

    // =========================================================================
    // toggleAbsence
    // =========================================================================
    public function toggleAbsence(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'nullable|date',
            'absent'      => 'required',
        ]);

        $isAbsent = filter_var($request->absent, FILTER_VALIDATE_BOOLEAN);
        $date     = $request->date ?? today()->toDateString();
        $empId    = (int) $request->employee_id;
        $tenantId = $this->getCurrentTenantId();

        try {
            $pointage = Pointage::withoutGlobalScope(TenantScope::class)
                ->where('employee_id', $empId)
                ->where('date', $date)
                ->where('tenant_id', $tenantId)
                ->first();

            if ($pointage) {
                $pointage->update([
                    'statut'       => $isAbsent ? 'absent' : 'present',
                    'heure_entree' => null,
                    'heure_sortie' => null,
                    'valide'       => false,
                ]);
            } else {
                $pointage = Pointage::create([
                    'employee_id'  => $empId,
                    'date'         => $date,
                    'tenant_id'    => $tenantId,
                    'statut'       => $isAbsent ? 'absent' : 'present',
                    'heure_entree' => null,
                    'heure_sortie' => null,
                    'valide'       => false,
                ]);
            }

            Log::info('toggleAbsence OK', [
                'employee_id' => $empId,
                'date'        => $date,
                'absent'      => $isAbsent,
                'pointage_id' => $pointage->id,
                'statut'      => $pointage->statut,
                'tenant_id'   => $tenantId,
            ]);

            return response()->json([
                'success' => true,
                'statut'  => $pointage->fresh()->statut,
                'id'      => $pointage->id,
            ]);

        } catch (\Exception $e) {
            Log::error('toggleAbsence ERREUR', [
                'message'     => $e->getMessage(),
                'employee_id' => $empId,
                'date'        => $date,
                'tenant_id'   => $tenantId,
            ]);

            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // syncPointageFromBadgeRecords — corrigé : shift = BadgeRecord collection
    // =========================================================================
    private function syncPointageFromBadgeRecords(
        int $employeeId,
        Carbon $date,
        Collection $shift,  // Collection de BadgeRecord
        ?int $tenantId = null
    ): Pointage {
        $pointage = Pointage::withoutGlobalScope(TenantScope::class)
            ->firstOrCreate(
                [
                    'employee_id' => $employeeId,
                    'date'        => $date->toDateString(),
                    'tenant_id'   => $tenantId,
                ],
                [
                    'statut'    => 'present',
                    'valide'    => false,
                    'source'    => 'badge',
                    'tenant_id' => $tenantId,
                ]
            );

        // Ne jamais écraser une absence
        if (in_array($pointage->statut, ['absent', 'absence_injustifiee'])) {
            return $pointage;
        }

        // BadgeRecord.type : 'entree', 'pause', 'retour_pause', 'sortie'
        $firstEntree = $shift->where('type', 'entree')->first()?->created_at;
        $lastSortie  = $shift->where('type', 'sortie')->last()?->created_at;
        $firstPause  = $shift->where('type', 'pause')->first()?->created_at;
        $firstRetour = $shift->where('type', 'retour_pause')->first()?->created_at;

        if ($firstEntree) {
            $pointage->heure_entree = Carbon::parse($firstEntree)
                ->setTimezone(self::TZ)->format('H:i:s');
        }

        if ($lastSortie) {
            $pointage->heure_sortie = Carbon::parse($lastSortie)
                ->setTimezone(self::TZ)->format('H:i:s');
        }

        if ($firstPause) {
            $pointage->pause_start = Carbon::parse($firstPause)
                ->setTimezone(self::TZ)->format('H:i:s');
        }

        if ($firstRetour) {
            $pointage->pause_end = Carbon::parse($firstRetour)
                ->setTimezone(self::TZ)->format('H:i:s');
        }

        // Calcul pause_minutes
        if ($firstPause && $firstRetour) {
            $diff = Carbon::parse($firstRetour)->diffInMinutes(Carbon::parse($firstPause));
            $pointage->pause_minutes = $diff > 0 ? $diff : 0;
        }

        $pointage->statut = 'present';
        $pointage->save();

        if (method_exists($pointage, 'calculerTotalHeures')) {
            $pointage->calculerTotalHeures(false);
        }

        return $pointage->fresh();
    }

    // =========================================================================
    // Helpers calcul pauses
    // =========================================================================
    private function calcNetWorkedMinutes(Collection $shift): float
    {
        $entree = $shift->where('type', 'entree')->first();
        $sortie = $shift->where('type', 'sortie')->last();

        if (! $entree || ! $sortie) return 0;

        $total = strtotime($sortie->created_at) - strtotime($entree->created_at);

        $pausesStart = $shift->where('type', 'pause')->values();
        $pausesEnd   = $shift->where('type', 'retour_pause')->values();

        $pauseTotal = 0;
        $count      = min($pausesStart->count(), $pausesEnd->count());

        for ($i = 0; $i < $count; $i++) {
            $pauseTotal += strtotime($pausesEnd[$i]->created_at) - strtotime($pausesStart[$i]->created_at);
        }

        return ($total - $pauseTotal) / 60;
    }

    private function calcPauseMinutes(Collection $shift): int
    {
        $pausesStart = $shift->where('type', 'pause')
            ->sortBy('created_at')
            ->pluck('created_at')
            ->values();

        $pausesEnd = $shift->where('type', 'retour_pause')
            ->sortBy('created_at')
            ->pluck('created_at')
            ->values();

        if ($pausesStart->isEmpty() || $pausesEnd->isEmpty()) return 0;

        $total = 0;
        $count = min($pausesStart->count(), $pausesEnd->count());

        for ($i = 0; $i < $count; $i++) {
            $start = strtotime($pausesStart[$i]);
            $end   = strtotime($pausesEnd[$i]);
            if ($end > $start) {
                $total += ($end - $start);
            }
        }

        return (int) floor($total / 60);
    }

    // =========================================================================
    // exportPdf
    // =========================================================================
    public function exportPdf(Request $request)
    {
        try {
            $date        = $request->get('date', today()->toDateString());
            $currentDate = Carbon::parse($date);
            $tenantId    = $this->getCurrentTenantId();
            $departments = \App\Models\Department::names();
            $vue         = $request->get('vue', 'tous');

            $employeesQuery = Employee::active()
                ->with(['pointages' => function ($q) use ($currentDate, $tenantId) {
                    $q->forDate($currentDate->toDateString())
                      ->where('tenant_id', $tenantId);
                }])
                ->when($request->filled('search'),     fn($q) => $q->search($request->search))
                ->when($request->filled('department'), fn($q) => $q->department($request->department))
                ->defaultOrder();

            $employees = $employeesQuery->get()
                ->map(function ($emp) use ($currentDate, $tenantId) {
                    $pointage = $emp->pointages->first();

                    if ($pointage && in_array($pointage->statut, ['absent', 'absence_injustifiee'])) {
                        return [
                            'id'         => $emp->id,
                            'nom'        => $emp->first_name . ' ' . $emp->last_name,
                            'department' => $emp->department,
                            'pointage'   => $pointage,
                        ];
                    }

                    if (! $pointage || ! $pointage->ignore_badge) {
                        $shift = BadgeRecord::where('employee_id', $emp->id)
                            ->whereDate('created_at', $currentDate->toDateString())
                            ->orderBy('created_at')
                            ->get();

                        if ($shift->isNotEmpty()) {
                            $pointage = $this->syncPointageFromBadgeRecords(
                                $emp->id, $currentDate, $shift, $tenantId
                            );
                        }
                    }

                    return [
                        'id'         => $emp->id,
                        'nom'        => $emp->first_name . ' ' . $emp->last_name,
                        'department' => $emp->department,
                        'pointage'   => $pointage,
                    ];
                });

            if ($vue === 'pointe') {
                $employees = $employees->filter(fn($e) =>
                    $e['pointage']?->heure_entree && ! in_array($e['pointage']->statut ?? '', ['absent'])
                );
            } elseif ($vue === 'non_pointe') {
                $employees = $employees->filter(fn($e) =>
                    ! $e['pointage']?->heure_entree || in_array($e['pointage']?->statut ?? '', ['absent', 'pas_de_badge'])
                );
            }

            $stats = [
                'valides' => $employees->filter(fn($e) => $e['pointage']?->valide)->count(),
                'total'   => $employees->count(),
            ];

            if ($employees->isEmpty()) {
                return back()->with('error', 'Aucun résultat avec ces filtres.');
            }

            $dept        = $request->get('department', 'Tous');
            $dateStr     = $currentDate->format('d/m/Y');
            $filterInfo  = 'Département: ' . $dept . ' | Vue: ' . ucfirst($vue);
            $generatedAt = now()->format('d/m/Y H:i');
            $filename    = 'pointage_' . $currentDate->format('Y-m-d') . '_' . \Illuminate\Support\Str::slug($dept) . '_' . $vue . '.pdf';

            $pdf = Pdf::loadView('pdf.pointage', compact('employees', 'stats', 'dateStr', 'filterInfo', 'generatedAt'))
                ->setPaper('a4', 'portrait')
                ->setOptions(['isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);

            return $pdf->download($filename);

        } catch (Exception $e) {
            Log::error('Pointage PDF export error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur génération PDF: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // exportPdfByDept
    // =========================================================================
    public function exportPdfByDept(Request $request, string $department)
    {
        try {
            $employees = Employee::where('department', $department)->get();
            $total     = $employees->count();

            if ($total === 0) {
                return back()->with('error', 'Aucun employé dans ce département.');
            }

            $generatedAt = now()->format('d/m/Y à H:i');
            $filename    = 'employes-' . \Str::slug($department) . '_' . now()->format('Y-m-d') . '.pdf';

            $pdf = Pdf::loadView('pdf.employees', compact('employees', 'total', 'generatedAt'));
            return $pdf->download($filename);

        } catch (Exception $e) {
            Log::error('PDF dept export error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur génération PDF.');
        }
    }

    // =========================================================================
    // badgesPin
    // =========================================================================
    public function badgesPin(Request $request): \Illuminate\View\View
    {
        $employees = Employee::active()
            ->when($request->filled('search'),     fn($q) => $q->search($request->search))
            ->when($request->filled('department'), fn($q) => $q->department($request->department))
            ->defaultOrder()
            ->get(['id', 'first_name', 'last_name', 'matricule', 'plain_pin', 'department']);

        $byDept = $employees->groupBy('department');

        $allEmployees = Employee::active()
            ->defaultOrder()
            ->get(['id', 'first_name', 'last_name', 'matricule', 'plain_pin', 'department']);

        $allByDept   = $allEmployees->groupBy('department');
        $departments = \App\Models\Department::names();

        return view('pointage.badges-pin', compact('byDept', 'allByDept', 'departments'));
    }

    // =========================================================================
    // regenererPin
    // =========================================================================
    public function regenererPin(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['employee_id' => 'required|exists:employees,id']);

        $employee = Employee::findOrFail($request->employee_id);
        $newPin   = $this->generateUniquePin();
        $employee->update(['plain_pin' => $newPin]);

        return response()->json([
            'success'     => true,
            'employee_id' => $employee->id,
            'new_pin'     => $newPin,
        ]);
    }

    // =========================================================================
    // regenererTousPins
    // =========================================================================
    public function regenererTousPins(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Employee::active();

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        $employees = $query->get();
        $updated   = [];

        foreach ($employees as $emp) {
            $pin = $this->generateUniquePin($updated);
            $emp->update(['plain_pin' => $pin]);
            $updated[] = ['id' => $emp->id, 'pin' => $pin];
        }

        return response()->json([
            'success' => true,
            'count'   => count($updated),
            'pins'    => $updated,
        ]);
    }

    // =========================================================================
    // generateUniquePin
    // =========================================================================
    private function generateUniquePin(array $alreadyUsed = []): string
    {
        $usedPins = array_column($alreadyUsed, 'pin');

        do {
            $digits  = str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            $letter1 = chr(random_int(65, 90));
            $letter2 = chr(random_int(65, 90));
            $pin     = $digits . $letter1 . $letter2;
        } while (
            in_array($pin, $usedPins) ||
            Employee::where('plain_pin', $pin)->exists()
        );

        return $pin;
    }
}