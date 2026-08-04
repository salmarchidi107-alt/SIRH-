<?php

namespace App\Services\Badge;

use App\Models\BadgeRecord;
use App\Models\Employee;
use App\Models\Pointage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BadgePointageService
{
    private const TZ = 'Africa/Casablanca';

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

    // ─── Helpers Carbon ──────────────────────────────────────────────────

    public function nowCasa(): Carbon   { return Carbon::now(self::TZ); }
    public function todayCasa(): Carbon { return Carbon::today(self::TZ); }

    // ─── Données pour les pages ────────────────────────────────────────────

    public function buildDashboardData(Employee $employee): array
    {
        $shift = $this->getTodayShift($employee);

        return [
            'employee'   => $employee,
            'todayShift' => $this->buildShiftSummary($shift),
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
            $this->buildShiftSummary($shift),
            [
                'pause_start'       => $pauseRecords->first()?->created_at?->setTimezone(self::TZ)->format('H:i'),
                'pause_end'         => $retourRecords->first()?->created_at?->setTimezone(self::TZ)->format('H:i'),
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

    /**
     * Décode une image data URI envoyée par la caméra ("data:image/jpeg;base64,...")
     * et l'écrit sur le disque 'public'. Ne renvoie jamais de contenu binaire ni de
     * base64 — uniquement le chemin relatif (et ses métadonnées) à stocker en base.
     *
     * @return array{face_photo_path: ?string, face_photo_disk: string, face_photo_size: int, face_photo_mime: ?string}
     */
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
        $path = sprintf(
            'badges/%s/%s/%s/%s_%s.%s',
            $tenantId ?: 'default',
            $employee->id,
            $this->todayCasa()->format('Y-m-d'),
            $type,
            now()->format('His') . '_' . Str::random(6),
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

    /**
     * Enregistre un BadgeRecord + synchronise le Pointage RH.
     *
     * @param  string   $type       entree | pause | retour_pause | sortie
     * @param  Employee $employee
     * @param  array    $geoData    latitude, longitude, accuracy, address, denied
     * @param  array    $photoData  face_photo_path, face_photo_disk, face_photo_size, face_photo_mime
     * @param  string   $shiftType  normal | garde
     */
    public function recordAction(
        string   $type,
        Employee $employee,
        array    $geoData   = [],
        array    $photoData = [],
        string   $shiftType = 'normal'
    ): void {
        $now     = $this->nowCasa();
        $today   = $now->format('Y-m-d');
        $nowTime = $now->format('H:i:s');

        // ── 1. BadgeRecord avec géolocalisation + photo + shift_type ────
        BadgeRecord::create(array_merge(
            [
                'employee_id'        => $employee->id,
                'type'               => $type,
                'shift_type'         => $shiftType,
                // Géolocalisation
                'latitude'           => $geoData['latitude']  ?? null,
                'longitude'          => $geoData['longitude'] ?? null,
                'accuracy'           => $geoData['accuracy']  ?? null,
                'location_address'   => isset($geoData['address'])
                                            ? substr($geoData['address'], 0, 255)
                                            : null,
                'geolocation_denied' => $geoData['denied']    ?? false,
            ],
            // Photo faciale — fichier sur disque uniquement
            $photoData ?: [
                'face_photo_path' => null,
                'face_photo_disk' => 'public',
                'face_photo_size' => 0,
                'face_photo_mime' => null,
            ]
        ));

        // ── 2. Synchronisation Pointage RH ───────────────────────────────
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

        // Mettre à jour shift_type si le pointage existait déjà
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
            ->whereDate('created_at', $this->todayCasa())
            ->orderBy('created_at')
            ->get();
    }

    private function buildShiftSummary(Collection $shift): array
    {
        $entrees = $shift->where('type', 'entree')->values();
        $sorties = $shift->where('type', 'sortie')->values();
        $pauses  = $shift->where('type', 'pause')->values();
        $retours = $shift->where('type', 'retour_pause')->values();

        return [
            'first_entree'  => $entrees->first()?->created_at?->setTimezone(self::TZ)->format('H:i'),
            'last_sortie'   => $sorties->last()?->created_at?->setTimezone(self::TZ)->format('H:i'),
            'pause_display' => $pauses->count() ? $pauses->count() . ' pause(s)' : null,
            'total_human'   => $this->calcTotalTime($entrees, $sorties, $pauses, $retours),
            'shift_type'    => $shift->last()?->shift_type ?? 'normal',
        ];
    }

    /**
     * Calcule le temps de travail net = (Sortie - Entrée) - durée_pause
     */
    private function calcTotalTime($entrees, $sorties, $pauses = null, $retours = null): string
    {
        if ($entrees->isEmpty() || $sorties->isEmpty()) return '0h 0m';

        // ── Temps brut (Sortie - Entrée) ─────────────────────────────────
        $total = 0;
        $count = min($entrees->count(), $sorties->count());

        for ($i = 0; $i < $count; $i++) {
            $diff = $sorties[$i]->created_at->timestamp - $entrees[$i]->created_at->timestamp;
            if ($diff > 0) $total += $diff;
        }

        // ── Déduction des pauses ──────────────────────────────────────────
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