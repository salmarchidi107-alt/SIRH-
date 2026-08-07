<?php

namespace App\Services\Badge;

use App\Models\BadgeRecord;
use App\Models\Employee;
use App\Models\Pointage;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BadgePointageService
{
    /** Fuseau de repli si le tenant n'a pas de timezone configuré */
    private const DEFAULT_TZ = 'Africa/Casablanca';

    /** Cache local (par instance/requête) des fuseaux déjà résolus, clé = tenant_id */
    private array $tzCache = [];

    // ─── Résolution employé ──────────────────────────────────────────────

    public function resolveEmployee(): ?Employee
    {
        $user = auth('badge')->user();
        if (! $user) return null;

        if ($user->employee_id && $user->employee) {
            return $user->employee;
        }

        return Employee::where('user_id', $user->id)->with('user')->first();
    }

    // ─── Résolution tenant_id ────────────────────────────────────────────

    public function resolveTenantId(Employee $employee): string|int|null
    {
        $tenantId = config('app.current_tenant_id');
        if (! blank($tenantId)) return $tenantId;

        $badgeUser = auth('badge')->user();
        if (! blank($badgeUser?->tenant_id)) return $badgeUser->tenant_id;

        if (! blank($employee->user?->tenant_id)) return $employee->user->tenant_id;

        return User::whereNotNull('tenant_id')
            ->where('role', 'admin')
            ->value('tenant_id');
    }

    // ─── Résolution fuseau horaire du tenant ──────────────────────────────

    private function resolveTimezone(Employee $employee): string
    {
        $tenantId = $this->resolveTenantId($employee);

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

    // ─── Helpers Carbon (fuseau dynamique par tenant) ─────────────────────

    public function nowTenant(Employee $employee): Carbon
    {
        return Carbon::now($this->resolveTimezone($employee));
    }

    public function todayTenant(Employee $employee): Carbon
    {
        return Carbon::now($this->resolveTimezone($employee));
    }

    /**
     * Retourne l'heure (H:i) telle qu'enregistrée en base pour un BadgeRecord,
     * en lisant l'attribut BRUT (getRawOriginal) plutôt que la valeur castée
     * par Eloquent. Depuis que created_at/updated_at sont fillable sur
     * BadgeRecord, la chaîne brute représente déjà l'heure locale correcte
     * du tenant (écrite via nowTenant()) — AUCUNE conversion supplémentaire
     * ne doit lui être appliquée. Un ->setTimezone() ici reproduirait le même
     * bug de double-décalage que dans PointageService : Eloquent réinterprète
     * la chaîne brute selon config('app.timezone') à la lecture, donc
     * setTimezone($tz) déciderait un DEUXIÈME décalage sur une valeur déjà
     * correcte.
     */
    private function badgeTimeString(?BadgeRecord $record): ?string
    {
        if (! $record) return null;
        $raw = $record->getRawOriginal('created_at');
        return $raw ? substr($raw, 11, 5) : null; // H:i
    }

    // ─── Données pour les pages ────────────────────────────────────────────

    public function buildDashboardData(Employee $employee): array
    {
        $shift = $this->getTodayShift($employee);

        return [
            'employee'   => $employee,
            'todayShift' => $this->buildShiftSummary($shift, $employee),
            'canEntree'  => $shift->where('type', 'entree')->isEmpty()
                            || $shift->last()?->type === 'sortie',
            'canSortie'  => $shift->where('type', 'entree')->isNotEmpty()
                            && $shift->last()?->type !== 'sortie',
        ];
    }

    public function buildResultData(Employee $employee, string $type, array $geoData, string $shiftType): array
    {
        $shift = $this->getTodayShift($employee);

        $pauseRecords  = $shift->where('type', 'pause')->values();
        $retourRecords = $shift->where('type', 'retour_pause')->values();

        $todayShift = array_merge(
            $this->buildShiftSummary($shift, $employee),
            [
                'pause_start'       => $this->badgeTimeString($pauseRecords->first()),
                'pause_end'         => $this->badgeTimeString($retourRecords->first()),
                'total_pause_human' => $this->calcTotalPause($pauseRecords, $retourRecords),
                'shift_type'        => $shiftType,
            ]
        );

        return [
            'employee'   => $employee,
            'todayShift' => $todayShift,
            'type'       => $type,
            'geoData'    => $geoData,
            'shiftType'  => $shiftType,
        ];
    }

    // ─── Résolution des inputs de handleAction ────────────────────────────

    public function resolveShiftType(?string $input): string
    {
        return in_array($input, ['normal', 'garde'], true) ? $input : 'normal';
    }

    public function buildGeoDataFromRequest(Request $request): array
    {
        return [
            'latitude'  => $request->filled('geo_latitude')  ? (float) $request->input('geo_latitude')  : null,
            'longitude' => $request->filled('geo_longitude') ? (float) $request->input('geo_longitude') : null,
            'accuracy'  => $request->filled('geo_accuracy')  ? (float) $request->input('geo_accuracy')  : null,
            'address'   => $request->input('geo_address'),
            'denied'    => $request->boolean('geo_denied'),
        ];
    }

    public function resolveType(string $action): string
    {
        return match ($action) {
            'debut', 'entree'            => 'entree',
            'pause', 'sortie_pause'      => 'pause',
            'retour_pause'               => 'retour_pause',
            'fin', 'fin_shift', 'sortie' => 'sortie',
            default => throw new \InvalidArgumentException("Action invalide : {$action}"),
        };
    }

    // ─── Photo faciale : écriture directe sur le disque, jamais en base64 en DB ──

    public function storeFacePhoto(Employee $employee, string $type, ?string $dataUri): array
    {
        $empty = [
            'face_photo_path' => null,
            'face_photo_disk' => 'public',
            'face_photo_size' => 0,
            'face_photo_mime' => null,
        ];

        if (blank($dataUri) || ! str_starts_with($dataUri, 'data:image/')) {
            return $empty;
        }

        if (! preg_match('/^data:(image\/\w+);base64,(.+)$/', $dataUri, $matches)) {
            return $empty;
        }

        $mime   = $matches[1];
        $binary = base64_decode($matches[2], true);

        if ($binary === false || strlen($binary) === 0) {
            return $empty;
        }

        $extension = match ($mime) {
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };

        $tenantId = $this->resolveTenantId($employee);
        $now      = $this->nowTenant($employee);

        $path = sprintf(
            'badges/%s/%s/%s/%s_%s.%s',
            $tenantId ?: 'default',
            $employee->id,
            $now->format('Y-m-d'),
            $type,
            $now->format('His') . '_' . Str::random(6),
            $extension
        );

        Storage::disk('public')->put($path, $binary);

        return [
            'face_photo_path' => $path,
            'face_photo_disk' => 'public',
            'face_photo_size' => strlen($binary),
            'face_photo_mime' => $mime,
        ];
    }

    // ─── Enregistrement principal ─────────────────────────────────────────

    public function recordAction(
        string   $type,
        Employee $employee,
        array    $geoData   = [],
        array    $photoData = [],
        string   $shiftType = 'normal'
    ): void {
        $now     = $this->nowTenant($employee);
        $today   = $now->format('Y-m-d');
        $nowTime = $now->format('H:i:s');

        // IMPORTANT : ceci ne fonctionne que parce que 'created_at' et
        // 'updated_at' sont désormais dans $fillable de BadgeRecord — sans ça
        // Laravel les ignore silencieusement et retombe sur son timestamp
        // automatique (heure serveur, pas heure tenant).
        BadgeRecord::create(array_merge(
            [
                'employee_id'        => $employee->id,
                'type'               => $type,
                'shift_type'         => $shiftType,
                'created_at'         => $now,
                'updated_at'         => $now,
                'latitude'           => $geoData['latitude']  ?? null,
                'longitude'          => $geoData['longitude'] ?? null,
                'accuracy'           => $geoData['accuracy']  ?? null,
                'location_address'   => isset($geoData['address'])
                                            ? substr($geoData['address'], 0, 255)
                                            : null,
                'geolocation_denied' => $geoData['denied']    ?? false,
            ],
            $photoData ?: [
                'face_photo_path' => null,
                'face_photo_disk' => 'public',
                'face_photo_size' => 0,
                'face_photo_mime' => null,
            ]
        ));

        $pointage = Pointage::firstOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            [
                'statut'     => 'present',
                'valide'     => false,
                'source'     => 'badge',
                'shift_type' => $shiftType,
                'tenant_id'  => $this->resolveTenantId($employee),
            ]
        );

        if (! $pointage->wasRecentlyCreated && $pointage->shift_type !== $shiftType) {
            $pointage->shift_type = $shiftType;
        }

        match ($type) {
            'entree'       => $pointage->heure_entree === null && ($pointage->heure_entree = $nowTime),
            'pause'        => $pointage->pause_start  === null && ($pointage->pause_start  = $nowTime),
            'retour_pause' => $pointage->pause_end    === null && ($pointage->pause_end    = $nowTime),
            'sortie'       => ($pointage->heure_sortie = $nowTime),
            default        => null,
        };

        $pointage->save();

        if (method_exists($pointage, 'calculerTotalHeures')) {
            $pointage->calculerTotalHeures(false);
        }
    }

    // ─── Helpers privés ───────────────────────────────────────────────────

    private function getTodayShift(Employee $employee): Collection
    {
        return BadgeRecord::where('employee_id', $employee->id)
            ->whereDate('created_at', $this->todayTenant($employee))
            ->orderBy('created_at')
            ->get();
    }

    private function buildShiftSummary(Collection $shift, Employee $employee): array
    {
        $entrees = $shift->where('type', 'entree')->values();
        $sorties = $shift->where('type', 'sortie')->values();
        $pauses  = $shift->where('type', 'pause')->values();
        $retours = $shift->where('type', 'retour_pause')->values();

        return [
            'first_entree'  => $this->badgeTimeString($entrees->first()),
            'last_sortie'   => $this->badgeTimeString($sorties->last()),
            'pause_display' => $pauses->count() ? $pauses->count() . ' pause(s)' : null,
            'total_human'   => $this->calcTotalTime($entrees, $sorties, $pauses, $retours),
            'shift_type'    => $shift->last()?->shift_type ?? 'normal',
        ];
    }

    private function calcTotalTime($entrees, $sorties, $pauses = null, $retours = null): string
    {
        if ($entrees->isEmpty() || $sorties->isEmpty()) return '0h 0m';

        $total = 0;
        $count = min($entrees->count(), $sorties->count());

        for ($i = 0; $i < $count; $i++) {
            $diff = $sorties[$i]->created_at->timestamp - $entrees[$i]->created_at->timestamp;
            if ($diff > 0) $total += $diff;
        }

        if ($pauses && $retours && $pauses->isNotEmpty() && $retours->isNotEmpty()) {
            $pauseCount = min($pauses->count(), $retours->count());
            for ($i = 0; $i < $pauseCount; $i++) {
                $pauseDiff = $retours[$i]->created_at->timestamp - $pauses[$i]->created_at->timestamp;
                if ($pauseDiff > 0) $total -= $pauseDiff;
            }
        }

        if ($total < 0) $total = 0;

        return floor($total / 3600) . 'h ' . floor(($total % 3600) / 60) . 'm';
    }

    private function calcTotalPause($pauses, $retours): string
    {
        if ($pauses->isEmpty() || $retours->isEmpty()) return '0m';

        $total = 0;
        $count = min($pauses->count(), $retours->count());

        for ($i = 0; $i < $count; $i++) {
            $diff = $retours[$i]->created_at->timestamp - $pauses[$i]->created_at->timestamp;
            if ($diff > 0) $total += $diff;
        }

        $minutes = floor($total / 60);
        return $minutes > 0 ? $minutes . 'm' : '0m';
    }
}
