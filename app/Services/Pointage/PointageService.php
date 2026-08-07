<?php

namespace App\Services\Pointage;

use App\Models\BadgeRecord;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Pointage;
use App\Models\Tenant;
use App\Scopes\TenantScope;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Toute la logique métier du module Pointage : tenant, géolocalisation,
 * synchronisation badgeuse <-> pointage, préparation des données pour
 * l'index/l'export Excel/le rapport PDF/les photos badgeuse, mutations
 * unitaires (absence, validation, ignore, update) et gestion des PIN.
 *
 * Le contrôleur ne fait que recevoir la requête, appeler ce service,
 * et retourner la réponse (view/redirect/json/download).
 */
class PointageService
{
    /** Fuseau de repli si le tenant n'a pas de timezone configuré */
    private const DEFAULT_TZ = 'Africa/Casablanca';

    /** Cache local (par instance/requête) des fuseaux déjà résolus, clé = tenant_id */
    private array $tzCache = [];

    // =========================================================================
    // FUSEAU HORAIRE DU TENANT
    // =========================================================================

    /**
     * Résout le fuseau horaire du tenant courant (colonne `timezone` sur la
     * table tenants). Remplace l'ancienne constante self::TZ = 'Africa/Casablanca'
     * qui était codée en dur et ignorait la configuration du tenant.
     */
    private function resolveTimezone(mixed $tenantId = null): string
    {
        $tenantId ??= $this->getCurrentTenantId();

        if (blank($tenantId)) {
            return self::DEFAULT_TZ;
        }

        if (array_key_exists($tenantId, $this->tzCache)) {
            return $this->tzCache[$tenantId];
        }

        $timezone = Tenant::where('id', $tenantId)->value('timezone');

        if (blank($timezone) || ! in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            $timezone = self::DEFAULT_TZ;
        }

        return $this->tzCache[$tenantId] = $timezone;
    }

    // =========================================================================
    // TENANT
    // =========================================================================

    public function getCurrentTenantId(): mixed
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
    // SHIFT TYPE
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
    // CONGÉS / ABSENCES APPROUVÉS
    // =========================================================================

    /**
     * Si l'employé a une absence APPROUVÉE couvrant cette date, force le
     * pointage du jour à statut='absent' et l'y maintient — le congé
     * approuvé prime toujours sur un éventuel badgeage (erreur, oubli de
     * badger la sortie de veille, etc.). Retourne null si aucune absence
     * approuvée ne couvre cette date (comportement normal, badge inchangé).
     *
     * IMPORTANT : cette méthode doit être appelée AVANT tout appel à
     * syncPointageFromBadgeRecords(), sinon un badge scanné le même jour
     * écraserait le statut 'absent' avec 'present'.
     */
    private function ensureAbsenceStatus(Employee $emp, Carbon $date, mixed $tenantId): ?Pointage
    {
        if (! $emp->hasApprovedAbsenceOn($date)) {
            return null;
        }

        $pointage = Pointage::withoutGlobalScope(TenantScope::class)
            ->where('employee_id', $emp->id)
            ->where('date', $date->toDateString())
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $pointage) {
            return Pointage::withoutGlobalScope(TenantScope::class)->create([
                'employee_id'  => $emp->id,
                'date'         => $date->toDateString(),
                'tenant_id'    => $tenantId,
                'statut'       => 'absent',
                'heure_entree' => null,
                'heure_sortie' => null,
                'valide'       => false,
            ]);
        }

        if ($pointage->statut !== 'absent') {
            DB::table('pointages')->where('id', $pointage->id)->update([
                'statut'       => 'absent',
                'heure_entree' => null,
                'heure_sortie' => null,
                'updated_at'   => now(),
            ]);
            $pointage = $pointage->fresh();
        }

        return $pointage;
    }

    // =========================================================================
    // GÉOLOCALISATION
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

    /**
     * Charge toutes les localisations du tenant depuis la table site_locations
     * (multi-département), avec cache 10 min.
     */
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

    /**
     * Trouve la localisation de référence selon le département de l'employé
     * et compare avec sa position GPS.
     */
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

    /**
     * Retourne l'heure (HH:MM:SS) telle qu'enregistrée en base pour un BadgeRecord,
     * en lisant l'attribut BRUT (getRawOriginal) plutôt que la valeur castée par
     * Eloquent. C'est indispensable : le cast 'datetime' d'Eloquent réinterprète
     * la chaîne stockée selon config('app.timezone') à la lecture, ce qui fausse
     * l'heure dès que le fuseau du tenant diffère de app.timezone (Carbon::parse()
     * + ->setTimezone() partirait alors d'une base déjà incorrecte).
     * Ici on ne fait AUCUNE conversion : la chaîne brute représente déjà l'heure
     * locale correcte du tenant (elle a été écrite ainsi par BadgePointageService).
     */
    private function badgeTimeString(?BadgeRecord $record): ?string
    {
        if (! $record) return null;
        $raw = $record->getRawOriginal('created_at');
        return $raw ? substr($raw, 11, 8) : null;
    }

    private function extractGeoFromBadgeRecords(Collection $shift, mixed $tenantId = null): ?array
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
            'recorded_at' => $this->badgeTimeString($record),
        ];
    }

    // =========================================================================
    // SYNCHRONISATION BADGEUSE <-> POINTAGE
    // =========================================================================

    /**
     * Pointage de la veille encore ouvert (entrée badgée, pas encore de
     * sortie), quel que soit le shift_type (garde OU normal — un shift
     * normal peut lui aussi chevaucher minuit). Utilisé pour :
     *   1) rattacher la sortie badgée le lendemain matin,
     *   2) continuer à l'afficher "en cours" tant qu'elle n'est pas clôturée.
     */
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

    private function syncPointageFromBadgeRecords(
        int $employeeId,
        Carbon $date,
        Collection $shift,
        mixed $tenantId = null
    ): Pointage {

        $tz        = $this->resolveTimezone($tenantId);
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
                $lastSortieTime  = $this->badgeTimeString($shift->where('type', 'sortie')->last());
                $firstPauseTime  = $this->badgeTimeString($shift->where('type', 'pause')->first());
                $firstRetourTime = $this->badgeTimeString($shift->where('type', 'retour_pause')->first());

                $updateData = [
                    'statut'     => 'present',
                    'updated_at' => now(),
                ];

                if ($lastSortieTime) {
                    $updateData['heure_sortie'] = $lastSortieTime;
                }
                // Pause éventuelle prise côté "lendemain" (avant la sortie), seulement
                // si aucune pause n'a déjà été enregistrée la veille.
                if ($firstPauseTime && ! $openPointage->pause_start) {
                    $updateData['pause_start'] = $firstPauseTime;
                }
                if ($firstRetourTime) {
                    $updateData['pause_end'] = $firstRetourTime;
                }
                if ($firstPauseTime && $firstRetourTime && ! $openPointage->pause_minutes) {
                    $diff = Carbon::createFromFormat('H:i:s', $firstPauseTime)
                        ->diffInMinutes(Carbon::createFromFormat('H:i:s', $firstRetourTime));
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

        // ─────────────────────────────────────────────────────────────────
        // IMPORTANT : on ne touche JAMAIS au champ 'valide' d'un pointage
        // déjà existant ici. Cette méthode est appelée à CHAQUE chargement
        // de la page (index, export, PDF) dès qu'il y a des badge records
        // pour l'employé du jour. Si on remettait 'valide' à false sur un
        // updateOrCreate, on écraserait systématiquement une validation
        // faite via "Valider la journée" au refresh suivant.
        // ─────────────────────────────────────────────────────────────────
        $pointage = Pointage::withoutGlobalScope(TenantScope::class)
            ->where('employee_id', $employeeId)
            ->where('date', $date->toDateString())
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $pointage) {
            $pointage = Pointage::withoutGlobalScope(TenantScope::class)->create([
                'employee_id' => $employeeId,
                'date'        => $date->toDateString(),
                'tenant_id'   => $tenantId,
                'statut'      => 'present',
                'valide'      => false,
                'source'      => 'badge',
                'shift_type'  => $shiftType,
            ]);
        } else {
            DB::table('pointages')->where('id', $pointage->id)->update([
                'statut'     => 'present',
                'source'     => 'badge',
                'shift_type' => $shiftType,
                'updated_at' => now(),
            ]);
            $pointage = $pointage->fresh();
        }

        if (in_array($pointage->statut, ['absent', 'absence_injustifiee'])) {
            return $pointage;
        }

        $firstEntreeTime = $this->badgeTimeString($shift->where('type', 'entree')->first());
        $lastSortieTime  = $this->badgeTimeString($shift->where('type', 'sortie')->last());
        $firstPauseTime  = $this->badgeTimeString($shift->where('type', 'pause')->first());
        $firstRetourTime = $this->badgeTimeString($shift->where('type', 'retour_pause')->first());

        $updateData = [
            'statut'     => 'present',
            'shift_type' => $shiftType,
            'updated_at' => now(),
        ];

        if ($firstEntreeTime) $updateData['heure_entree'] = $firstEntreeTime;
        if ($lastSortieTime)  $updateData['heure_sortie'] = $lastSortieTime;
        if ($firstPauseTime)  $updateData['pause_start']  = $firstPauseTime;
        if ($firstRetourTime) $updateData['pause_end']    = $firstRetourTime;
        if ($firstPauseTime && $firstRetourTime) {
            $diff = Carbon::createFromFormat('H:i:s', $firstPauseTime)
                ->diffInMinutes(Carbon::createFromFormat('H:i:s', $firstRetourTime));
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
    // INDEX (tableau du jour)
    // =========================================================================

    public function getIndexData(Request $request): array
    {
        $date        = $request->get('date', today()->toDateString());
        $currentDate = Carbon::parse($date);
        $tenantId    = $this->getCurrentTenantId();
        $shiftFilter = $request->get('shift');

        Carbon::setLocale('fr');
        $startOfWeek = $currentDate->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = $currentDate->copy()->endOfWeek(Carbon::SUNDAY);

        $weekDays = $this->buildWeekDays($startOfWeek, $endOfWeek, $currentDate, $tenantId);

        $departments = Department::names();
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

        $employees = $this->buildEmployeesCollection($request, $currentDate, $tenantId, $allBadgeRecords);

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

        return compact(
            'employees', 'departments', 'weekDays', 'currentDate',
            'startOfWeek', 'endOfWeek', 'stats', 'vue', 'shiftFilter', 'geoAlerts'
        );
    }

    private function buildWeekDays(Carbon $startOfWeek, Carbon $endOfWeek, Carbon $currentDate, mixed $tenantId): Collection
    {
        $tz       = $this->resolveTimezone($tenantId);
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
                    ? Carbon::parse($validationInfo->validated_at)->setTimezone($tz)->format('H\hi')
                    : null,
            ]);
        }

        return $weekDays;
    }

    private function buildEmployeesCollection(Request $request, Carbon $currentDate, mixed $tenantId, $allBadgeRecords): Collection
    {
        $employeesQuery = Employee::active()
            ->with(['pointages' => function ($q) use ($currentDate, $tenantId) {
                $q->withoutGlobalScope(TenantScope::class)
                  ->where('date', $currentDate->toDateString())
                  ->where('tenant_id', $tenantId);
            }])
            ->when($request->filled('search'),     fn($q) => $q->search($request->search))
            ->when($request->filled('department'), fn($q) => $q->department($request->department))
            ->defaultOrder();

        return $employeesQuery->get()
            ->map(function ($emp) use ($currentDate, $tenantId, $allBadgeRecords) {

                $pointage = $emp->pointages->first();
                $shift    = $allBadgeRecords->get($emp->id, collect());

                // ─────────────────────────────────────────────────────────
                // Congé approuvé : prioritaire sur tout badgeage éventuel.
                // Doit être vérifié AVANT le rattachement de garde nocturne
                // et avant syncPointageFromBadgeRecords, pour qu'un employé
                // en congé ne se retrouve jamais affiché "En cours"/"Présent"
                // à cause d'un badge scanné par erreur.
                // ─────────────────────────────────────────────────────────
                $absencePointage = $this->ensureAbsenceStatus($emp, $currentDate, $tenantId);
                if ($absencePointage) {
                    return [
                        'id'           => $emp->id,
                        'nom'          => $emp->first_name . ' ' . $emp->last_name,
                        'avatar'       => strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)),
                        'department'   => $emp->department,
                        'pointage'     => $absencePointage,
                        'geo'          => null,
                        'shift_type'   => $this->resolveShiftType($shift, $absencePointage),
                        'geo_alert'    => false,
                        'geo_distance' => null,
                        'site_name'    => null,
                    ];
                }

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

                $geo      = $this->extractGeoFromBadgeRecords($shift, $tenantId);
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
    }

    // =========================================================================
    // EXPORT EXCEL
    // =========================================================================

    public function buildExportData(Request $request): array
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

        $rows = Employee::active()
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
                $pointage = $emp->pointages->first();
                $shift    = $allBadgeRecords->get($emp->id, collect());

                // Congé approuvé : même règle prioritaire que dans index()
                $absencePointage = $this->ensureAbsenceStatus($emp, $currentDate, $tenantId);
                if ($absencePointage) {
                    return [
                        'nom'          => $emp->first_name . ' ' . $emp->last_name,
                        'department'   => $emp->department,
                        'shift_type'   => $this->resolveShiftType($shift, $absencePointage),
                        'heure_entree' => null,
                        'heure_sortie' => null,
                        'total_heures' => '—',
                        'statut'       => 'absent',
                        'valide'       => $absencePointage->valide ?? false,
                    ];
                }

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
            $rows = $rows->filter(fn($e) => $e['shift_type'] === $shiftFilter);
        }

        return [
            'rows'        => $rows,
            'currentDate' => $currentDate,
            'department'  => $dept,
            'shiftFilter' => $shiftFilter,
        ];
    }

    // =========================================================================
    // RAPPORT PDF — jour / semaine / mois
    // =========================================================================

    /**
     * Retourne null si aucune ligne ne correspond aux filtres (le contrôleur
     * doit alors répondre par un back()->with('error', ...)).
     */
    public function buildReport(Request $request): ?array
    {
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
            return null;
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
        $generatedAt = now()->setTimezone($this->resolveTimezone($tenantId))->format('d/m/Y H:i');
        $filename    = 'pointage_' . $periode . '_' . $startDate->format('Y-m-d') . '_' . Str::slug($dept) . '.pdf';

        return compact('rows', 'summary', 'stats', 'periode', 'periodeLabel', 'filterInfo', 'generatedAt', 'filename');
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

                // Congé approuvé : même règle prioritaire que dans index()/export().
                $absencePointage = $this->ensureAbsenceStatus($emp, $d, $tenantId);
                if ($absencePointage) {
                    $pointage = $absencePointage;
                } elseif (! $pointage && $shift->isNotEmpty()) {
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
    // PHOTO BADGEUSE
    // =========================================================================

    /**
     * Photo prise à la badgeuse pour un employé, strictement bornée au jour
     * calendaire sélectionné. Chaque jour garde sa propre photo : pour une
     * garde à cheval sur minuit, l'entrée (jeudi 19h) et la sortie
     * (vendredi 07h) ont des created_at sur des jours différents, donc
     * chaque ligne du tableau (jeudi / vendredi) récupère naturellement sa
     * propre photo, sans chevauchement.
     *
     * La photo n'existe jamais qu'en fichier sur le disque de stockage
     * ('face_photo_path' + 'face_photo_disk') : il n'y a plus aucun
     * fallback base64 en base de données.
     *
     * @return array{status: string, photo_url?: string, employee?: string, type?: string, recorded_at?: string}
     */
    public function getPhotoData(Employee $employee, mixed $tenantId, ?string $rawDate): array
    {
        // Sécurité multi-tenant : l'employé doit appartenir au tenant courant
        if ($tenantId && (string) $employee->tenant_id !== (string) $tenantId) {
            return ['status' => 'forbidden'];
        }

        try {
            $date = Carbon::parse($rawDate ?? today()->toDateString());
        } catch (\Exception $e) {
            $date = today();
        }

        $record = BadgeRecord::where('employee_id', $employee->id)
            ->whereDate('created_at', $date->toDateString())
            ->whereNotNull('face_photo_path')
            ->orderByDesc('created_at')
            ->first();

        if (! $record) {
            return ['status' => 'not_found'];
        }

        if (! $record->face_photo_path) {
            return ['status' => 'photo_missing'];
        }

        try {
            $disk     = $record->face_photo_disk ?: 'public';
            $photoUrl = Storage::disk($disk)->url($record->face_photo_path);
        } catch (\Throwable $e) {
            Log::warning('lastPhoto: impossible de générer l\'URL disque', [
                'badge_record_id' => $record->id,
                'error'           => $e->getMessage(),
            ]);
            return ['status' => 'photo_missing'];
        }

        return [
            'status'      => 'ok',
            'photo_url'   => $photoUrl,
            'employee'    => $employee->first_name . ' ' . $employee->last_name,
            'type'        => $record->type,
            'recorded_at' => ($raw = $record->getRawOriginal('created_at'))
                ? Carbon::createFromFormat('Y-m-d H:i:s', $raw)->format('d/m/Y à H:i:s')
                : null,
        ];
    }

    // =========================================================================
    // MUTATIONS UNITAIRES
    // =========================================================================

    public function marquerAbsent($employeeId): void
    {
        $today    = today()->toDateString();
        $tenantId = $this->getCurrentTenantId();

        $pointage = Pointage::withoutGlobalScope(TenantScope::class)
            ->where('employee_id', $employeeId)
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
                'employee_id'  => $employeeId,
                'date'         => $today,
                'tenant_id'    => $tenantId,
                'statut'       => 'absent',
                'heure_entree' => null,
                'heure_sortie' => null,
                'valide'       => false,
            ]);
        }
    }

  public function validerJournee(string $date): array
    {
        $tenantId = $this->getCurrentTenantId();
        $userId   = auth()->id();
        $userName = auth()->user()->name;

        // On parcourt TOUS les employés actifs (pas seulement ceux qui ont déjà
        // un pointage avec statut='present'), pour que "Valider la journée"
        // fonctionne même quand un employé n'a aucune ligne pointage ce jour-là
        // (pas de badge, absence non enregistrée, etc.).
        $employees = Employee::active()->get(['id']);

        $count = 0;

        foreach ($employees as $emp) {
            $pointage = Pointage::withoutGlobalScope(TenantScope::class)
                ->where('employee_id', $emp->id)
                ->where('date', $date)
                ->where('tenant_id', $tenantId)
                ->first();

            if (! $pointage) {
                // Aucun pointage ce jour-là : on en crée un minimal pour
                // pouvoir quand même le marquer comme validé.
                $pointage = Pointage::withoutGlobalScope(TenantScope::class)->create([
                    'employee_id' => $emp->id,
                    'date'        => $date,
                    'tenant_id'   => $tenantId,
                    'statut'      => 'pas_de_badge',
                    'valide'      => false,
                ]);
            }

            DB::table('pointages')->where('id', $pointage->id)->update([
                'valide'       => 1,
                'validated_by' => $userId,
                'validated_at' => now(),
                'updated_at'   => now(),
            ]);

            $count++;
        }

        return [
            'success'      => true,
            'count'        => $count,
            'message'      => $count . ' pointage(s) validé(s)',
            'validator'    => $userName,
            'validated_at' => now()->setTimezone($this->resolveTimezone($tenantId))->format('H\hi'),
        ];
    }

    public function toggleValider(Pointage $pointage): array
    {
        $newValide = ! ((bool) $pointage->valide);
        $tz        = $this->resolveTimezone($pointage->tenant_id);

        DB::table('pointages')
            ->where('id', $pointage->id)
            ->update([
                'valide'       => $newValide ? 1 : 0,
                'validated_by' => $newValide ? auth()->id() : null,
                'validated_at' => $newValide ? now() : null,
                'updated_at'   => now(),
            ]);

        return [
            'success'      => true,
            'valide'       => $newValide,
            'validator'    => $newValide ? auth()->user()->name : null,
            'validated_at' => $newValide
                ? now()->setTimezone($tz)->format('H\hi')
                : null,
        ];
    }

    public function toggleIgnore(Pointage $pointage): array
    {
        $pointage->update(['ignore_badge' => ! $pointage->ignore_badge]);

        return [
            'success'      => true,
            'ignore_badge' => $pointage->fresh()->ignore_badge,
        ];
    }

    public function updatePointage(Pointage $pointage, array $data): Pointage
    {
        $pointage->update($data);
        $pointage->calculerTotalHeures();

        return $pointage->fresh();
    }

    /**
     * @return array{success: bool, statut?: string, id?: int, error?: string}
     */
    public function toggleAbsence(int $employeeId, string $date, bool $isAbsent): array
    {
        $tenantId = $this->getCurrentTenantId();

        try {
            $pointage = Pointage::withoutGlobalScope(TenantScope::class)
                ->where('employee_id', $employeeId)
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
                    'employee_id'  => $employeeId,
                    'date'         => $date,
                    'tenant_id'    => $tenantId,
                    'statut'       => $isAbsent ? 'absent' : 'present',
                    'heure_entree' => null,
                    'heure_sortie' => null,
                    'valide'       => false,
                ]);
            }

            return [
                'success' => true,
                'statut'  => $pointage->fresh()->statut,
                'id'      => $pointage->id,
            ];

        } catch (\Exception $e) {
            Log::error('toggleAbsence ERREUR', [
                'message'     => $e->getMessage(),
                'employee_id' => $employeeId,
                'date'        => $date,
                'tenant_id'   => $tenantId,
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // =========================================================================
    // BADGES / PIN
    // =========================================================================

    public function getBadgesPinData(Request $request): array
    {
        $employees = Employee::active()
            ->when($request->filled('search'),     fn($q) => $q->search($request->search))
            ->when($request->filled('department'), fn($q) => $q->department($request->department))
            ->defaultOrder()
            ->get(['id', 'first_name', 'last_name', 'matricule', 'plain_pin', 'department']);

        $byDept       = $employees->groupBy('department');
        $allEmployees = Employee::active()->defaultOrder()->get(['id', 'first_name', 'last_name', 'matricule', 'plain_pin', 'department']);
        $allByDept    = $allEmployees->groupBy('department');
        $departments  = Department::names();

        return compact('byDept', 'allByDept', 'departments');
    }

    public function regenerateOne(int $employeeId): array
    {
        $employee = Employee::findOrFail($employeeId);
        $newPin   = $this->generateUniquePin();
        $employee->update(['plain_pin' => $newPin]);

        return ['success' => true, 'employee_id' => $employee->id, 'new_pin' => $newPin];
    }

    public function regenerateAll(?string $department): array
    {
        $query = Employee::active();
        if ($department) {
            $query->where('department', $department);
        }

        $employees = $query->get();
        $updated   = [];

        foreach ($employees as $emp) {
            $pin = $this->generateUniquePin($updated);
            $emp->update(['plain_pin' => $pin]);
            $updated[] = ['id' => $emp->id, 'pin' => $pin];
        }

        return ['success' => true, 'count' => count($updated), 'pins' => $updated];
    }

    /**
     * Retourne null si aucun employé ne correspond aux filtres (le contrôleur
     * doit alors répondre par un back()->with('error', ...)).
     */
    public function getExportPinData(Request $request): ?array
    {
        $employees = Employee::active()
            ->when($request->filled('search'),     fn($q) => $q->search($request->search))
            ->when($request->filled('department'), fn($q) => $q->department($request->department))
            ->defaultOrder()
            ->get(['id', 'first_name', 'last_name', 'matricule', 'plain_pin', 'department']);

        if ($employees->isEmpty()) {
            return null;
        }

        $byDept      = $employees->groupBy('department');
        $generatedAt = now()->setTimezone($this->resolveTimezone($this->getCurrentTenantId()))->format('d/m/Y H:i');
        $deptFilter  = $request->get('department', 'Tous');

        $filename = 'badges-pin_' . now()->format('Y-m-d_H-i-s')
                  . ($deptFilter !== 'Tous' ? '_' . Str::slug($deptFilter) : '')
                  . '.pdf';

        return compact('byDept', 'generatedAt', 'deptFilter', 'filename');
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
