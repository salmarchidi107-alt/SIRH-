<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Pointage;
use App\Models\BadgeRecord;
use App\Scopes\TenantScope;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Exception;

class PointageController extends Controller
{
    private const TZ = 'Africa/Casablanca';

    // =========================================================================
    // Helper : récupère le tenant_id courant
    // =========================================================================
    private function getCurrentTenantId(): mixed
    {
        $tenantId = config('app.current_tenant_id');
        if (blank($tenantId) && auth()->check()) {
            $tenantId = auth()->user()->tenant_id;
        }
        return filled($tenantId) ? $tenantId : null;
    }

    // =========================================================================
    // Helper : résout le shift_type depuis une collection de BadgeRecords
    // =========================================================================
    private function resolveShiftType(Collection $shift, ?Pointage $pointage = null): string
    {
        $entree = $shift->where('type', 'entree')->first();
        if ($entree && (string) $entree->shift_type === 'garde') {
            return 'garde';
        }

        foreach ($shift as $record) {
            if ((string) $record->shift_type === 'garde') {
                return 'garde';
            }
        }

        if ($pointage && (string) $pointage->shift_type === 'garde') {
            return 'garde';
        }

        return 'normal';
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
        $shiftFilter = $request->get('shift');

        Carbon::setLocale('fr');
        $startOfWeek = $currentDate->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = $currentDate->copy()->endOfWeek(Carbon::SUNDAY);

        $weekDays = collect();
        for ($d = $startOfWeek->copy(); $d->lte($endOfWeek); $d->addDay()) {

            $validationInfo = DB::table('pointages')
                ->leftJoin('users', 'pointages.validated_by', '=', 'users.id')
                ->where('pointages.date', $d->toDateString())
                ->where('pointages.tenant_id', $tenantId)
                ->where('pointages.valide', true)
                ->whereNotNull('pointages.validated_by')
                ->select('users.name as validator_name', 'pointages.validated_at')
                ->orderByDesc('pointages.validated_at')
                ->first();

            $isValide = $validationInfo
                ? true
                : DB::table('pointages')
                    ->where('date', $d->toDateString())
                    ->where('tenant_id', $tenantId)
                    ->where('valide', true)
                    ->exists();

            $weekDays->push([
                'date'         => $d->copy(),
                'label'        => ucfirst($d->translatedFormat('l')),
                'short'        => $d->translatedFormat('d M.'),
                'isToday'      => $d->isToday(),
                'isSelected'   => $d->toDateString() === $currentDate->toDateString(),
                'valide'       => $isValide,
                'validated_by' => $validationInfo?->validator_name ?? null,
                'validated_at' => $validationInfo?->validated_at
                    ? Carbon::parse($validationInfo->validated_at)->setTimezone(self::TZ)->format('H\hi')
                    : null,
            ]);
        }

        $departments = \App\Models\Department::names();
        $vue         = $request->get('vue', 'tous');

        $allBadgeRecords = BadgeRecord::whereDate('created_at', $currentDate->toDateString())
            ->orderBy('created_at')
            ->get()
            ->groupBy('employee_id');

        Log::debug('BadgeRecords shift_type debug', [
            'date'    => $currentDate->toDateString(),
            'records' => BadgeRecord::whereDate('created_at', $currentDate->toDateString())
                ->get(['id', 'employee_id', 'type', 'shift_type'])
                ->toArray(),
        ]);

        $employeesQuery = Employee::active()
            ->with(['pointages' => function ($q) use ($currentDate, $tenantId) {
                $q->withoutGlobalScope(TenantScope::class)
                  ->where('date', $currentDate->toDateString())
                  ->where('tenant_id', $tenantId);
            }])
            ->when($request->filled('search'),     fn($q) => $q->search($request->search))
            ->when($request->filled('department'), fn($q) => $q->department($request->department))
            ->defaultOrder();

        $employees = $employeesQuery->get()
            ->map(function ($emp) use ($currentDate, $tenantId, $allBadgeRecords) {

                $pointage = $emp->pointages->first();
                $shift    = $allBadgeRecords->get($emp->id, collect());

                if ($pointage && in_array($pointage->statut, ['absent', 'absence_injustifiee'])) {
                    return [
                        'id'         => $emp->id,
                        'nom'        => $emp->first_name . ' ' . $emp->last_name,
                        'avatar'     => strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)),
                        'pointage'   => $pointage,
                        'geo'        => null,
                        'shift_type' => $this->resolveShiftType($shift, $pointage),
                    ];
                }

                if (! $pointage || ! $pointage->ignore_badge) {
                    if ($shift->isNotEmpty()) {
                        $pointage = $this->syncPointageFromBadgeRecords(
                            $emp->id, $currentDate, $shift, $tenantId
                        );
                    }
                }

                return [
                    'id'         => $emp->id,
                    'nom'        => $emp->first_name . ' ' . $emp->last_name,
                    'avatar'     => strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)),
                    'pointage'   => $pointage,
                    'geo'        => $this->extractGeoFromBadgeRecords($shift),
                    'shift_type' => $this->resolveShiftType($shift, $pointage),
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

        if (in_array($shiftFilter, ['normal', 'garde'])) {
            $employees = $employees->filter(fn($e) => $e['shift_type'] === $shiftFilter);
        }

        $stats = [
            'valides'      => $employees->filter(fn($e) => $e['pointage']?->valide)->count(),
            'presents'     => $employees->filter(fn($e) => $e['pointage']?->statut === 'present')->count(),
            'absents'      => $employees->filter(fn($e) => in_array($e['pointage']?->statut, ['absent', 'absence_injustifiee']))->count(),
            'en_attente'   => $employees->filter(fn($e) => ! $e['pointage'] || $e['pointage']?->statut === 'pas_de_badge')->count(),
            'total'        => $employees->count(),
            'geo_ok'       => $employees->filter(fn($e) => isset($e['geo']) && !($e['geo']['denied'] ?? true))->count(),
            'shift_normal' => $employees->filter(fn($e) => $e['shift_type'] === 'normal')->count(),
            'shift_garde'  => $employees->filter(fn($e) => $e['shift_type'] === 'garde')->count(),
        ];

        return view('pointage.index', compact(
            'employees', 'departments', 'weekDays', 'currentDate',
            'startOfWeek', 'endOfWeek', 'stats', 'vue', 'shiftFilter'
        ));
    }

    // =========================================================================
    // EXPORT EXCEL  ← MÉTHODE AJOUTÉE
    // =========================================================================
    public function export(Request $request)
    {
        $date        = $request->get('date', today()->toDateString());
        $currentDate = Carbon::parse($date);
        $tenantId    = $this->getCurrentTenantId();
        $dept        = $request->get('department');
        $search      = $request->get('search');
        $shiftFilter = $request->get('shift');

        $allBadgeRecords = BadgeRecord::whereDate('created_at', $currentDate->toDateString())
            ->orderBy('created_at')
            ->get()
            ->groupBy('employee_id');

        $data = Employee::active()
            ->with(['pointages' => function ($q) use ($currentDate, $tenantId) {
                $q->withoutGlobalScope(TenantScope::class)
                  ->where('date', $currentDate->toDateString())
                  ->where('tenant_id', $tenantId);
            }])
            ->when($search, fn($q) => $q->search($search))
            ->when($dept,   fn($q) => $q->department($dept))
            ->defaultOrder()
            ->get()
            ->map(function ($emp) use ($currentDate, $tenantId, $allBadgeRecords) {
                $pointage  = $emp->pointages->first();
                $shift     = $allBadgeRecords->get($emp->id, collect());
                $shiftType = $this->resolveShiftType($shift, $pointage);

                if (! $pointage || ! $pointage->ignore_badge) {
                    if ($shift->isNotEmpty()) {
                        $pointage = $this->syncPointageFromBadgeRecords(
                            $emp->id, $currentDate, $shift, $tenantId
                        );
                    }
                }

                return [
                    'nom'          => $emp->first_name . ' ' . $emp->last_name,
                    'department'   => $emp->department,
                    'shift_type'   => $shiftType,
                    'heure_entree' => $pointage?->heure_entree,
                    'heure_sortie' => $pointage?->heure_sortie,
                    'total_heures' => $pointage?->total_heures_formate ?? '—',
                    'statut'       => $pointage?->statut ?? 'pas_de_badge',
                    'valide'       => $pointage?->valide ?? false,
                ];
            });

        if (in_array($shiftFilter, ['normal', 'garde'])) {
            $data = $data->filter(fn($e) => $e['shift_type'] === $shiftFilter);
        }

        $filename = 'pointage_' . $currentDate->format('Y-m-d')
                  . ($dept        ? '_' . \Illuminate\Support\Str::slug($dept)   : '')
                  . ($shiftFilter ? '_' . $shiftFilter                            : '')
                  . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PointageExport(collect($data), $currentDate),
            $filename
        );
    }

    // =========================================================================
    // extractGeoFromBadgeRecords
    // =========================================================================
    private function extractGeoFromBadgeRecords(Collection $shift): ?array
    {
        if ($shift->isEmpty()) return null;

        $record = $shift
            ->where('type', 'entree')
            ->whereNotNull('latitude')
            ->where('geolocation_denied', false)
            ->first();

        if (! $record) {
            $record = $shift
                ->whereNotNull('latitude')
                ->where('geolocation_denied', false)
                ->first();
        }

        if (! $record) {
            $firstBadge = $shift->first();
            return [
                'denied'      => true,
                'latitude'    => null,
                'longitude'   => null,
                'accuracy'    => null,
                'address'     => null,
                'reason'      => $firstBadge->geolocation_denied ? 'denied_by_user' : 'no_coords',
                'recorded_at' => null,
            ];
        }

        return [
            'denied'      => false,
            'latitude'    => (float) $record->latitude,
            'longitude'   => (float) $record->longitude,
            'accuracy'    => $record->accuracy ? (int) $record->accuracy : null,
            'address'     => $record->location_address,
            'reason'      => '',
            'recorded_at' => $record->created_at
                ? Carbon::parse($record->created_at)->setTimezone(self::TZ)->format('H:i:s')
                : null,
        ];
    }

    // =========================================================================
    // validerJournee
    // =========================================================================
    public function validerJournee(Request $request): JsonResponse
    {
        $date     = $request->input('date', today()->toDateString());
        $tenantId = $this->getCurrentTenantId();
        $userId   = auth()->id();
        $userName = auth()->user()->name;

        $count = DB::table('pointages')
            ->where('date', $date)
            ->where('tenant_id', $tenantId)
            ->where('statut', 'present')
            ->update([
                'valide'       => 1,
                'validated_by' => $userId,
                'validated_at' => now(),
                'updated_at'   => now(),
            ]);

        return response()->json([
            'success'      => true,
            'count'        => $count,
            'message'      => $count . ' pointage(s) validé(s)',
            'validator'    => $userName,
            'validated_at' => now()->setTimezone(self::TZ)->format('H\hi'),
        ]);
    }

    // =========================================================================
    // toggleValider
    // =========================================================================
    public function toggleValider(Pointage $pointage): JsonResponse
    {
        $newValide = ! ((bool) $pointage->valide);

        DB::table('pointages')
            ->where('id', $pointage->id)
            ->update([
                'valide'       => $newValide ? 1 : 0,
                'validated_by' => $newValide ? auth()->id() : null,
                'validated_at' => $newValide ? now() : null,
                'updated_at'   => now(),
            ]);

        return response()->json([
            'success'      => true,
            'valide'       => $newValide,
            'validator'    => $newValide ? auth()->user()->name : null,
            'validated_at' => $newValide
                ? now()->setTimezone(self::TZ)->format('H\hi')
                : null,
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
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // syncPointageFromBadgeRecords
    // =========================================================================
    private function syncPointageFromBadgeRecords(
        int $employeeId,
        Carbon $date,
        Collection $shift,
        mixed $tenantId = null
    ): Pointage {

        $shiftType = $this->resolveShiftType($shift);

        Log::debug('syncPointage shift_type', [
            'employee_id'   => $employeeId,
            'shift_type'    => $shiftType,
            'badge_records' => $shift->map(fn($r) => [
                'type'       => $r->type,
                'shift_type' => $r->shift_type,
            ])->toArray(),
        ]);

        $pointage = Pointage::withoutGlobalScope(TenantScope::class)
            ->firstOrCreate(
                [
                    'employee_id' => $employeeId,
                    'date'        => $date->toDateString(),
                    'tenant_id'   => $tenantId,
                ],
                [
                    'statut'     => 'present',
                    'valide'     => false,
                    'source'     => 'badge',
                    'shift_type' => $shiftType,
                    'tenant_id'  => $tenantId,
                ]
            );

        if (in_array($pointage->statut, ['absent', 'absence_injustifiee'])) {
            return $pointage;
        }

        $firstEntree = $shift->where('type', 'entree')->first()?->created_at;
        $lastSortie  = $shift->where('type', 'sortie')->last()?->created_at;
        $firstPause  = $shift->where('type', 'pause')->first()?->created_at;
        $firstRetour = $shift->where('type', 'retour_pause')->first()?->created_at;

        $updateData = [
            'statut'     => 'present',
            'shift_type' => $shiftType,
            'updated_at' => now(),
        ];

        if ($firstEntree) $updateData['heure_entree'] = Carbon::parse($firstEntree)->setTimezone(self::TZ)->format('H:i:s');
        if ($lastSortie)  $updateData['heure_sortie'] = Carbon::parse($lastSortie)->setTimezone(self::TZ)->format('H:i:s');
        if ($firstPause)  $updateData['pause_start']  = Carbon::parse($firstPause)->setTimezone(self::TZ)->format('H:i:s');
        if ($firstRetour) $updateData['pause_end']    = Carbon::parse($firstRetour)->setTimezone(self::TZ)->format('H:i:s');
        if ($firstPause && $firstRetour) {
            $diff = Carbon::parse($firstPause)->diffInMinutes(Carbon::parse($firstRetour));
            $updateData['pause_minutes'] = $diff > 0 ? $diff : 0;
        }

        DB::table('pointages')->where('id', $pointage->id)->update($updateData);
        $pointage = $pointage->fresh();

        if (method_exists($pointage, 'calculerTotalHeures')) {
            $pointage->calculerTotalHeures(true);
        }

        return $pointage->fresh();
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
            $vue         = $request->get('vue', 'tous');
            $shiftFilter = $request->get('shift');

            $allBadgeRecords = BadgeRecord::whereDate('created_at', $currentDate->toDateString())
                ->orderBy('created_at')
                ->get()
                ->groupBy('employee_id');

            $employees = Employee::active()
                ->with(['pointages' => function ($q) use ($currentDate, $tenantId) {
                    $q->withoutGlobalScope(TenantScope::class)
                      ->where('date', $currentDate->toDateString())
                      ->where('tenant_id', $tenantId);
                }])
                ->when($request->filled('search'),     fn($q) => $q->search($request->search))
                ->when($request->filled('department'), fn($q) => $q->department($request->department))
                ->defaultOrder()
                ->get()
                ->map(function ($emp) use ($currentDate, $tenantId, $allBadgeRecords) {
                    $pointage = $emp->pointages->first();
                    $shift    = $allBadgeRecords->get($emp->id, collect());

                    if ($pointage && in_array($pointage->statut, ['absent', 'absence_injustifiee'])) {
                        return ['id' => $emp->id, 'nom' => $emp->first_name . ' ' . $emp->last_name, 'department' => $emp->department, 'pointage' => $pointage, 'shift_type' => $this->resolveShiftType($shift, $pointage)];
                    }
                    if (! $pointage || ! $pointage->ignore_badge) {
                        if ($shift->isNotEmpty()) $pointage = $this->syncPointageFromBadgeRecords($emp->id, $currentDate, $shift, $tenantId);
                    }
                    return ['id' => $emp->id, 'nom' => $emp->first_name . ' ' . $emp->last_name, 'department' => $emp->department, 'pointage' => $pointage, 'shift_type' => $this->resolveShiftType($shift, $pointage)];
                });

            if ($vue === 'pointe')         $employees = $employees->filter(fn($e) => $e['pointage']?->heure_entree && ! in_array($e['pointage']->statut ?? '', ['absent']));
            elseif ($vue === 'non_pointe') $employees = $employees->filter(fn($e) => ! $e['pointage']?->heure_entree || in_array($e['pointage']?->statut ?? '', ['absent', 'pas_de_badge']));

            if (in_array($shiftFilter, ['normal', 'garde'])) {
                $employees = $employees->filter(fn($e) => $e['shift_type'] === $shiftFilter);
            }

            if ($employees->isEmpty()) return back()->with('error', 'Aucun résultat avec ces filtres.');

            $stats       = ['valides' => $employees->filter(fn($e) => $e['pointage']?->valide)->count(), 'total' => $employees->count()];
            $dateStr     = $currentDate->format('d/m/Y');
            $dept        = $request->get('department', 'Tous');
            $filterInfo  = 'Département: ' . $dept . ' | Vue: ' . ucfirst($vue) . ($shiftFilter ? ' | Shift: ' . $shiftFilter : '');
            $generatedAt = now()->format('d/m/Y H:i');
            $filename    = 'pointage_' . $currentDate->format('Y-m-d') . '_' . \Illuminate\Support\Str::slug($dept) . '_' . $vue . '.pdf';

            return Pdf::loadView('pdf.pointage', compact('employees', 'stats', 'dateStr', 'filterInfo', 'generatedAt'))
                ->setPaper('a4', 'portrait')
                ->setOptions(['isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans'])
                ->download($filename);

        } catch (Exception $e) {
            Log::error('Pointage PDF error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur PDF: ' . $e->getMessage());
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

        $byDept       = $employees->groupBy('department');
        $allEmployees = Employee::active()->defaultOrder()->get(['id', 'first_name', 'last_name', 'matricule', 'plain_pin', 'department']);
        $allByDept    = $allEmployees->groupBy('department');
        $departments  = \App\Models\Department::names();

        return view('pointage.badges-pin', compact('byDept', 'allByDept', 'departments'));
    }

    // =========================================================================
    // regenererPin
    // =========================================================================
    public function regenererPin(Request $request): JsonResponse
    {
        $request->validate(['employee_id' => 'required|exists:employees,id']);
        $employee = Employee::findOrFail($request->employee_id);
        $newPin   = $this->generateUniquePin();
        $employee->update(['plain_pin' => $newPin]);
        return response()->json(['success' => true, 'employee_id' => $employee->id, 'new_pin' => $newPin]);
    }

    // =========================================================================
    // regenererTousPins
    // =========================================================================
    public function regenererTousPins(Request $request): JsonResponse
    {
        $query = Employee::active();
        if ($request->filled('department')) $query->where('department', $request->department);
        $employees = $query->get();
        $updated   = [];
        foreach ($employees as $emp) {
            $pin = $this->generateUniquePin($updated);
            $emp->update(['plain_pin' => $pin]);
            $updated[] = ['id' => $emp->id, 'pin' => $pin];
        }
        return response()->json(['success' => true, 'count' => count($updated), 'pins' => $updated]);
    }

    private function generateUniquePin(array $alreadyUsed = []): string
    {
        $usedPins = array_column($alreadyUsed, 'pin');
        do {
            $pin = str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT)
                 . chr(random_int(65, 90))
                 . chr(random_int(65, 90));
        } while (in_array($pin, $usedPins) || Employee::where('plain_pin', $pin)->exists());
        return $pin;
    }
}
