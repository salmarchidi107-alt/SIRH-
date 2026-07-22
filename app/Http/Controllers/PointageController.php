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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
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
        $configTenantId = config('app.current_tenant_id');
        $authTenantId   = auth()->check() ? auth()->user()->tenant_id : null;

        if (filled($configTenantId) && filled($authTenantId) && (string) $configTenantId !== (string) $authTenantId) {
            Log::warning('PointageController: incohérence tenant_id détectée', [
                'config_tenant_id' => $configTenantId,
                'auth_tenant_id'   => $authTenantId,
                'user_id'          => auth()->id(),
                'url'              => request()->fullUrl(),
            ]);
        }

        if (filled($authTenantId)) {
            return $authTenantId;
        }

        return filled($configTenantId) ? $configTenantId : null;
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
    // haversineDistance — distance en mètres entre deux points GPS
    // =========================================================================
    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    // =========================================================================
    // getTenantSiteLocations — charge toutes les localisations du tenant
    // depuis la table site_locations (multi-département), avec cache 10 min
    // =========================================================================
    private function getTenantSiteLocations(mixed $tenantId): Collection
    {
        if (! $tenantId) return collect();

        return Cache::remember(
            "tenant_site_locations_{$tenantId}",
            now()->addMinutes(10),
            function () use ($tenantId) {
                return DB::table('site_locations')
                    ->where('tenant_id', $tenantId)
                    ->get();
            }
        );
    }

    // =========================================================================
    // checkGeoAlert — trouve la localisation de référence selon le département
    // de l'employé et compare avec sa position GPS.
    // =========================================================================
    private function checkGeoAlert(?array $geo, mixed $tenantId, ?string $department = null): array
    {
        $locations = $this->getTenantSiteLocations($tenantId);

        if ($locations->isEmpty()) {
            return ['alert' => false, 'distance' => null, 'site_name' => null];
        }

        $site = null;
        if ($department) {
            $site = $locations->first(fn($l) => (string) $l->department === (string) $department);
        }

        if (! $site) {
            $site = $locations->first(fn($l) => is_null($l->department) || $l->department === '');
        }

        if (! $site) {
            return ['alert' => false, 'distance' => null, 'site_name' => null];
        }

        if (! $geo || ($geo['denied'] ?? true) || ! isset($geo['latitude'], $geo['longitude'])) {
            return ['alert' => false, 'distance' => null, 'site_name' => $site->site_name];
        }

        $distance = $this->haversineDistance(
            (float) $site->latitude, (float) $site->longitude,
            (float) $geo['latitude'], (float) $geo['longitude']
        );

        return [
            'alert'     => $distance > (int) $site->radius_meters,
            'distance'  => (int) round($distance),
            'site_name' => $site->site_name,
        ];
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

        if (app()->environment('local')) {
            Log::debug('BadgeRecords shift_type debug', [
                'date'    => $currentDate->toDateString(),
                'records' => BadgeRecord::whereDate('created_at', $currentDate->toDateString())
                    ->get(['id', 'employee_id', 'type', 'shift_type'])
                    ->toArray(),
            ]);
        }

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

                // ─────────────────────────────────────────────────────────
                // Garde entamée la veille (ex: 19h -> 07h) et toujours ouverte
                // (pas de sortie badgée) : on continue à l'afficher aujourd'hui
                // au lieu de la faire disparaître, tant qu'aucun badge n'a été
                // scanné aujourd'hui pour cet employé.
                // ─────────────────────────────────────────────────────────
                if (! $pointage && $shift->isEmpty()) {
                    $openPointage = $this->findOpenOvernightPointage($emp->id, $currentDate, $tenantId);
                    if ($openPointage) {
                        $pointage = $openPointage;
                    }
                }

                if ($pointage && in_array($pointage->statut, ['absent', 'absence_injustifiee'])) {
                    return [
                        'id'           => $emp->id,
                        'nom'          => $emp->first_name . ' ' . $emp->last_name,
                        'avatar'       => strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)),
                        'department'   => $emp->department,
                        'pointage'     => $pointage,
                        'geo'          => null,
                        'shift_type'   => $this->resolveShiftType($shift, $pointage),
                        'geo_alert'    => false,
                        'geo_distance' => null,
                        'site_name'    => null,
                    ];
                }

                if (! $pointage || ! $pointage->ignore_badge) {
                    if ($shift->isNotEmpty()) {
                        $pointage = $this->syncPointageFromBadgeRecords(
                            $emp->id, $currentDate, $shift, $tenantId
                        );
                    }
                }

                $geo      = $this->extractGeoFromBadgeRecords($shift);
                $geoCheck = $this->checkGeoAlert($geo, $tenantId, $emp->department);

                return [
                    'id'           => $emp->id,
                    'nom'          => $emp->first_name . ' ' . $emp->last_name,
                    'avatar'       => strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)),
                    'department'   => $emp->department,
                    'pointage'     => $pointage,
                    'geo'          => $geo,
                    'shift_type'   => $this->resolveShiftType($shift, $pointage),
                    'geo_alert'    => $geoCheck['alert'],
                    'geo_distance' => $geoCheck['distance'],
                    'site_name'    => $geoCheck['site_name'],
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
            'geo_alerts'   => $employees->filter(fn($e) => $e['geo_alert'] ?? false)->count(),
            'shift_normal' => $employees->filter(fn($e) => $e['shift_type'] === 'normal')->count(),
            'shift_garde'  => $employees->filter(fn($e) => $e['shift_type'] === 'garde')->count(),
        ];

        $geoAlerts = $employees->filter(fn($e) => $e['geo_alert'] ?? false)->values();

        return view('pointage.index', compact(
            'employees', 'departments', 'weekDays', 'currentDate',
            'startOfWeek', 'endOfWeek', 'stats', 'vue', 'shiftFilter', 'geoAlerts'
        ));
    }

    // =========================================================================
    // EXPORT EXCEL
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

                // Garde de la veille toujours ouverte : même règle que dans index()
                if (! $pointage && $shift->isEmpty()) {
                    $openPointage = $this->findOpenOvernightPointage($emp->id, $currentDate, $tenantId);
                    if ($openPointage) {
                        $pointage = $openPointage;
                    }
                }

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
    // lastPhoto — photo prise à la badgeuse pour un employé, strictement
    // bornée au jour calendaire sélectionné ($date passé en query string).
    // Chaque jour garde sa propre photo : pour une garde à cheval sur minuit,
    // l'entrée (jeudi 19h) et la sortie (vendredi 07h) ont des created_at sur
    // des jours différents, donc chaque ligne du tableau (jeudi / vendredi)
    // récupère naturellement sa propre photo, sans chevauchement.
    // =========================================================================
    public function lastPhoto(Employee $employee, Request $request): JsonResponse
    {
        $tenantId = $this->getCurrentTenantId();

        // Sécurité multi-tenant : l'employé doit appartenir au tenant courant
        if ($tenantId && (string) $employee->tenant_id !== (string) $tenantId) {
            return response()->json(['success' => false, 'message' => "Accès non autorisé."], 403);
        }

        // Date sélectionnée dans le tableau (query param ?date=YYYY-MM-DD)
        try {
            $date = Carbon::parse($request->query('date', today()->toDateString()));
        } catch (\Exception $e) {
            $date = today();
        }

        $record = BadgeRecord::where('employee_id', $employee->id)
            ->whereDate('created_at', $date->toDateString())
            ->where(function ($q) {
                $q->whereNotNull('face_photo_path')
                  ->orWhereNotNull('face_photo_base64');
            })
            ->orderByDesc('created_at')
            ->first();

        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune photo trouvée pour ce jour.',
            ]);
        }

        $photoUrl = null;

        if ($record->face_photo_path) {
            try {
                $disk     = $record->face_photo_disk ?: 'public';
                $photoUrl = Storage::disk($disk)->url($record->face_photo_path);
            } catch (\Throwable $e) {
                Log::warning('lastPhoto: impossible de générer l\'URL disque', [
                    'badge_record_id' => $record->id,
                    'error'           => $e->getMessage(),
                ]);
            }
        }

        if (! $photoUrl && $record->face_photo_base64) {
            $mime     = $record->face_photo_mime ?: 'image/jpeg';
            $photoUrl = 'data:' . $mime . ';base64,' . $record->face_photo_base64;
        }

        if (! $photoUrl) {
            return response()->json([
                'success' => false,
                'message' => 'Photo introuvable (fichier manquant sur le serveur).',
            ]);
        }

        return response()->json([
            'success'     => true,
            'photo_url'   => $photoUrl,
            'employee'    => $employee->first_name . ' ' . $employee->last_name,
            'type'        => $record->type,
            'recorded_at' => $record->created_at
                ? Carbon::parse($record->created_at)->setTimezone(self::TZ)->format('d/m/Y à H:i:s')
                : null,
        ]);
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

        $hasEntreeToday = $shift->where('type', 'entree')->isNotEmpty();
        $hasSortieToday = $shift->where('type', 'sortie')->isNotEmpty();

        // ─────────────────────────────────────────────────────────────────
        // Cas garde à cheval sur minuit (ex: 19h -> 07h le lendemain) :
        // une "sortie" badgée aujourd'hui SANS "entree" aujourd'hui doit être
        // rattachée au pointage encore ouvert de la veille, plutôt que de créer
        // une nouvelle ligne "pas de badge" pour aujourd'hui.
        // ─────────────────────────────────────────────────────────────────
        if (! $hasEntreeToday && $hasSortieToday) {
            $openPointage = $this->findOpenOvernightPointage($employeeId, $date, $tenantId);

            if ($openPointage) {
                $lastSortie  = $shift->where('type', 'sortie')->last()?->created_at;
                $firstPause  = $shift->where('type', 'pause')->first()?->created_at;
                $firstRetour = $shift->where('type', 'retour_pause')->first()?->created_at;

                $updateData = [
                    'statut'     => 'present',
                    'updated_at' => now(),
                ];

                if ($lastSortie) {
                    $updateData['heure_sortie'] = Carbon::parse($lastSortie)->setTimezone(self::TZ)->format('H:i:s');
                }
                // Pause éventuelle prise côté "lendemain" (avant la sortie), seulement
                // si aucune pause n'a déjà été enregistrée la veille.
                if ($firstPause && ! $openPointage->pause_start) {
                    $updateData['pause_start'] = Carbon::parse($firstPause)->setTimezone(self::TZ)->format('H:i:s');
                }
                if ($firstRetour) {
                    $updateData['pause_end'] = Carbon::parse($firstRetour)->setTimezone(self::TZ)->format('H:i:s');
                }
                if ($firstPause && $firstRetour && ! $openPointage->pause_minutes) {
                    $diff = Carbon::parse($firstPause)->diffInMinutes(Carbon::parse($firstRetour));
                    $updateData['pause_minutes'] = $diff > 0 ? $diff : 0;
                }

                Log::debug('syncPointage : rattachement sortie à la garde de la veille', [
                    'employee_id'        => $employeeId,
                    'pointage_veille_id' => $openPointage->id,
                    'date_veille'        => $openPointage->date,
                    'heure_sortie'       => $updateData['heure_sortie'] ?? null,
                ]);

                DB::table('pointages')->where('id', $openPointage->id)->update($updateData);
                $openPointage = $openPointage->fresh();

                if (method_exists($openPointage, 'calculerTotalHeures')) {
                    $openPointage->calculerTotalHeures(true);
                }

                return $openPointage->fresh();
            }
        }

        Log::debug('syncPointage shift_type', [
            'employee_id'   => $employeeId,
            'shift_type'    => $shiftType,
            'badge_records' => $shift->map(fn($r) => [
                'type'       => $r->type,
                'shift_type' => $r->shift_type,
            ])->toArray(),
        ]);

       $pointage = Pointage::withoutGlobalScope(TenantScope::class)
            ->updateOrCreate(
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
    // findOpenOvernightPointage — pointage de la veille encore ouvert
    // (entrée badgée, pas encore de sortie), quel que soit le shift_type
    // (garde OU normal — un shift normal peut lui aussi chevaucher minuit).
    // Utilisé pour :
    //   1) rattacher la sortie badgée le lendemain matin,
    //   2) continuer à l'afficher "en cours" tant qu'elle n'est pas clôturée.
    // =========================================================================
    private function findOpenOvernightPointage(int $employeeId, Carbon $date, mixed $tenantId): ?Pointage
    {
        return Pointage::withoutGlobalScope(TenantScope::class)
            ->where('employee_id', $employeeId)
            ->where('tenant_id', $tenantId)
            ->where('date', $date->copy()->subDay()->toDateString())
            ->whereNotNull('heure_entree')
            ->whereNull('heure_sortie')
            ->whereNotIn('statut', ['absent', 'absence_injustifiee'])
            ->first();
    }

    // =========================================================================
    // exportPdf — supporte jour / semaine / mois
    // =========================================================================
    public function exportPdf(Request $request)
    {
        try {
            $date        = $request->get('date', today()->toDateString());
            $currentDate = Carbon::parse($date);
            $tenantId    = $this->getCurrentTenantId();
            $vue         = $request->get('vue', 'tous');
            $shiftFilter = $request->get('shift');
            $periode     = $request->get('periode', 'jour');

            Carbon::setLocale('fr');

            switch ($periode) {
                case 'semaine':
                    $startDate    = $currentDate->copy()->startOfWeek(Carbon::MONDAY);
                    $endDate      = $currentDate->copy()->endOfWeek(Carbon::SUNDAY);
                    $periodeLabel = 'Semaine du ' . $startDate->format('d/m/Y') . ' au ' . $endDate->format('d/m/Y');
                    break;
                case 'mois':
                    $startDate    = $currentDate->copy()->startOfMonth();
                    $endDate      = $currentDate->copy()->endOfMonth();
                    $periodeLabel = ucfirst($currentDate->translatedFormat('F Y'));
                    break;
                default:
                    $periode      = 'jour';
                    $startDate    = $currentDate->copy();
                    $endDate      = $currentDate->copy();
                    $periodeLabel = $currentDate->translatedFormat('d F Y');
            }

            $rows = $this->gatherPeriodRows($startDate, $endDate, $tenantId, $request, $vue, $shiftFilter);

            if ($rows->isEmpty()) {
                return back()->with('error', 'Aucun résultat avec ces filtres.');
            }

            $stats = [
                'total'        => $rows->count(),
                'valides'      => $rows->where('valide', true)->count(),
                'absents'      => $rows->whereIn('statut', ['absent', 'absence_injustifiee'])->count(),
                'total_heures' => $rows->sum('total_heures_decimal'),
            ];

            $summary = null;
            if ($periode !== 'jour') {
                $summary = $rows->groupBy('nom')->map(function ($group) {
                    return [
                        'nom'          => $group->first()['nom'],
                        'department'   => $group->first()['department'],
                        'jours'        => $group->whereNotIn('statut', ['absent', 'absence_injustifiee'])->count(),
                        'absences'     => $group->whereIn('statut', ['absent', 'absence_injustifiee'])->count(),
                        'total_heures' => $group->sum('total_heures_decimal'),
                    ];
                })->values()->sortBy('nom')->values();
            }

            $dept        = $request->get('department', 'Tous');
            $filterInfo  = 'Département: ' . $dept . ' | Vue: ' . ucfirst($vue) . ($shiftFilter ? ' | Shift: ' . $shiftFilter : '');
            $generatedAt = now()->format('d/m/Y H:i');
            $filename    = 'pointage_' . $periode . '_' . $startDate->format('Y-m-d') . '_' . \Illuminate\Support\Str::slug($dept) . '.pdf';

            return Pdf::loadView('pointage.pdf', compact(
                    'rows', 'summary', 'stats', 'periode', 'periodeLabel', 'filterInfo', 'generatedAt'
                ))
                ->setPaper('a4', $periode === 'jour' ? 'portrait' : 'landscape')
                ->setOptions(['isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans'])
                ->download($filename);

        } catch (Exception $e) {
            Log::error('Pointage PDF error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur PDF: ' . $e->getMessage());
        }
    }

    private function gatherPeriodRows(
        Carbon $startDate,
        Carbon $endDate,
        mixed $tenantId,
        Request $request,
        string $vue,
        ?string $shiftFilter
    ): Collection {

        $rows = collect();

        $employeesQuery = Employee::active()
            ->when($request->filled('search'),     fn($q) => $q->search($request->search))
            ->when($request->filled('department'), fn($q) => $q->department($request->department))
            ->defaultOrder();

        $employees = $employeesQuery->get();

        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {

            $dateStr = $d->toDateString();

            $pointages = Pointage::withoutGlobalScope(TenantScope::class)
                ->where('date', $dateStr)
                ->where('tenant_id', $tenantId)
                ->get()
                ->keyBy('employee_id');

            $badgeRecords = BadgeRecord::whereDate('created_at', $dateStr)
                ->orderBy('created_at')
                ->get()
                ->groupBy('employee_id');

            foreach ($employees as $emp) {
                $pointage = $pointages->get($emp->id);
                $shift    = $badgeRecords->get($emp->id, collect());

                if (! $pointage && $shift->isNotEmpty()) {
                    $pointage = $this->syncPointageFromBadgeRecords($emp->id, $d, $shift, $tenantId);
                }

                // NB: on ne "reporte" pas ici la garde ouverte de la veille comme
                // dans index()/export() — le but de ce rapport période est de
                // sommer les heures une seule fois par garde (déjà comptées sous
                // la date où elle a démarré). La rattacher aussi au jour suivant
                // doublerait le total_heures dans les sommes hebdo/mensuelles.

                $shiftType = $this->resolveShiftType($shift, $pointage);
                $statut    = $pointage?->statut ?? 'pas_de_badge';

                if ($vue === 'pointe' && (! $pointage?->heure_entree || $statut === 'absent')) continue;
                if ($vue === 'non_pointe' && $pointage?->heure_entree && ! in_array($statut, ['absent', 'pas_de_badge'])) continue;
                if (in_array($shiftFilter, ['normal', 'garde']) && $shiftType !== $shiftFilter) continue;

                $rows->push([
                    'date'                 => $d->copy(),
                    'date_label'           => ucfirst($d->translatedFormat('D d/m')),
                    'date_full'            => $d->translatedFormat('d/m/Y'),
                    'nom'                  => $emp->first_name . ' ' . $emp->last_name,
                    'department'           => $emp->department,
                    'shift_type'           => $shiftType,
                    'heure_entree'         => $pointage?->heure_entree,
                    'heure_sortie'         => $pointage?->heure_sortie,
                    'total_heures'         => $pointage?->total_heures_formate ?? '-',
                    'total_heures_decimal' => $pointage?->total_heures ?? 0,
                    'statut'               => $statut,
                    'valide'               => $pointage?->valide ?? false,
                ]);
            }
        }

        return $rows->sortBy([['date', 'asc'], ['nom', 'asc']])->values();
    }

    // =========================================================================
    // badgesPin
    // =========================================================================
    public function badgesPin(Request $request): View
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

        return response()->json(['success' => true, 'count' => count($updated), 'pins' => $updated]);
    }

    // =========================================================================
    // exportBadgesPinPdf
    // =========================================================================
    public function exportBadgesPinPdf(Request $request)
    {
        try {
            $employees = Employee::active()
                ->when($request->filled('search'),     fn($q) => $q->search($request->search))
                ->when($request->filled('department'), fn($q) => $q->department($request->department))
                ->defaultOrder()
                ->get(['id', 'first_name', 'last_name', 'matricule', 'plain_pin', 'department']);

            if ($employees->isEmpty()) {
                return back()->with('error', 'Aucun employé trouvé.');
            }

            $byDept      = $employees->groupBy('department');
            $generatedAt = now()->format('d/m/Y H:i');
            $deptFilter  = $request->get('department', 'Tous');

            $filename = 'badges-pin_' . now()->format('Y-m-d_H-i-s')
                      . ($deptFilter !== 'Tous' ? '_' . \Illuminate\Support\Str::slug($deptFilter) : '')
                      . '.pdf';

            return Pdf::loadView('pdf.badges-pin', compact('byDept', 'generatedAt', 'deptFilter'))
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isRemoteEnabled' => true,
                    'defaultFont'     => 'DejaVu Sans',
                    'margin_left'     => 10,
                    'margin_right'    => 10,
                    'margin_top'      => 10,
                    'margin_bottom'   => 10,
                ])
                ->download($filename);

        } catch (Exception $e) {
            Log::error('Badges PIN PDF error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur PDF: ' . $e->getMessage());
        }
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
