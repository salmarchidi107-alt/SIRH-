<?php

namespace App\Http\Controllers\Badge;

use App\Http\Controllers\Controller;
use App\Models\BadgeRecord;
use App\Models\Employee;
use App\Models\Pointage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BadgePointageController extends Controller
{
    private const TZ = 'Africa/Casablanca';

    public function __construct()
    {
        Auth::shouldUse('badge');
    }

    // ─── Résolution employé ──────────────────────────────────────────────

    private function resolveEmployee(): ?Employee
    {
        /** @var User|null $user */
        $user = auth('badge')->user();
        if (! $user) return null;

        if ($user->employee_id && $user->employee) {
            return $user->employee;
        }

        return Employee::where('user_id', $user->id)->with('user')->first();
    }

    private function getAuthEmployee(): Employee
    {
        $employee = $this->resolveEmployee();

        if (! $employee) {
            abort(403, 'Aucun employé associé à ce compte badge. Contactez l\'administrateur.');
        }

        return $employee;
    }

    // ─── Résolution tenant_id ────────────────────────────────────────────

    private function resolveTenantId(Employee $employee): string|int|null
    {
        $tenantId = config('app.current_tenant_id');
        if (! blank($tenantId)) return $tenantId;

        $badgeUser = auth('badge')->user();
        if (! blank($badgeUser?->tenant_id)) return $badgeUser->tenant_id;

        if (! blank($employee->user?->tenant_id)) return $employee->user->tenant_id;

        return \App\Models\User::whereNotNull('tenant_id')
            ->where('role', 'admin')
            ->value('tenant_id');
    }

    // ─── Helpers Carbon ──────────────────────────────────────────────────

    private function nowCasa(): Carbon  { return Carbon::now(self::TZ); }
    private function todayCasa(): Carbon { return Carbon::today(self::TZ); }

    // ─── Pages ───────────────────────────────────────────────────────────

    /** Page d'accueil badgeuse (choix Entrée / Sortie) */
    public function pointage()
    {
        return view('badge.pointage');
    }

    /** Dashboard employé connecté (avec résumé shift) */
    public function dashboard(Request $request)
    {
        $employee = $this->getAuthEmployee();
        $shift    = $this->getTodayShift($employee);

        return view('badge.dashboard', [
            'employee'   => $employee,
            'todayShift' => $this->buildShiftSummary($shift),
            'canEntree'  => $shift->where('type', 'entree')->isEmpty()
                            || $shift->last()?->type === 'sortie',
            'canSortie'  => $shift->where('type', 'entree')->isNotEmpty()
                            && $shift->last()?->type !== 'sortie',
        ]);
    }

    /** Page de résultat après pointage */
    public function result(Request $request)
    {
        $employee = $this->getAuthEmployee();
        $shift    = $this->getTodayShift($employee);
        $type     = $request->session()->get('last_type', 'entree');

        // Données géo depuis la session (stockées par BadgeAuthController)
        $geoData  = $request->session()->get('last_geo', []);

        $pauseRecords  = $shift->where('type', 'pause')->values();
        $retourRecords = $shift->where('type', 'retour_pause')->values();

        $todayShift = array_merge(
            $this->buildShiftSummary($shift),
            [
                'pause_start'       => $pauseRecords->first()?->created_at?->setTimezone(self::TZ)->format('H:i'),
                'pause_end'         => $retourRecords->first()?->created_at?->setTimezone(self::TZ)->format('H:i'),
                'total_pause_human' => $this->calcTotalPause($pauseRecords, $retourRecords),
            ]
        );

        return view('badge.result', compact('employee', 'todayShift', 'type', 'geoData'));
    }

    // ─── Actions AJAX (dashboard blade) ──────────────────────────────────

    public function handleAction(Request $request)
    {
        $request->validate(['action' => 'required|string']);

        $employee = $this->getAuthEmployee();
        $realType = $this->resolveType($request->action);

        $geoData = [
            'latitude'  => $request->filled('geo_latitude')  ? (float) $request->input('geo_latitude')  : null,
            'longitude' => $request->filled('geo_longitude') ? (float) $request->input('geo_longitude') : null,
            'accuracy'  => $request->filled('geo_accuracy')  ? (float) $request->input('geo_accuracy')  : null,
            'address'   => $request->input('geo_address'),
            'denied'    => $request->boolean('geo_denied'),
        ];

        $this->recordAction($realType, $employee, $geoData);

        $request->session()->put('last_type', $realType);
        $request->session()->put('last_geo',  $geoData);

        return response()->json([
            'success'  => true,
            'redirect' => route('badge.result'),
        ]);
    }

    // ─── Enregistrement principal ─────────────────────────────────────────

    /**
     * Enregistre un BadgeRecord + synchronise le Pointage RH.
     *
     * @param  string   $type     entree | pause | retour_pause | sortie
     * @param  Employee $employee
     * @param  array    $geoData  latitude, longitude, accuracy, address, denied
     */
    public function recordAction(string $type, Employee $employee, array $geoData = []): void
    {
        $now     = $this->nowCasa();
        $today   = $now->format('Y-m-d');
        $nowTime = $now->format('H:i:s');

        // ── 1. BadgeRecord avec géolocalisation ──────────────────────────
        BadgeRecord::create([
            'employee_id'        => $employee->id,
            'type'               => $type,
            'latitude'           => $geoData['latitude']  ?? null,
            'longitude'          => $geoData['longitude'] ?? null,
            'accuracy'           => $geoData['accuracy']  ?? null,
            'location_address'   => isset($geoData['address']) ? substr($geoData['address'], 0, 255) : null,
            'geolocation_denied' => $geoData['denied']    ?? false,
        ]);

        // ── 2. Synchronisation Pointage RH ───────────────────────────────
        $pointage = Pointage::firstOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            [
                'statut'    => 'present',
                'valide'    => false,
                'source'    => 'badge',
                'tenant_id' => $this->resolveTenantId($employee),
            ]
        );

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

    private function getTodayShift(Employee $employee)
    {
        return BadgeRecord::where('employee_id', $employee->id)
            ->whereDate('created_at', $this->todayCasa())
            ->orderBy('created_at')
            ->get();
    }

    private function resolveType(string $action): string
    {
        return match ($action) {
            'debut', 'entree'            => 'entree',
            'pause', 'sortie_pause'      => 'pause',
            'retour_pause'               => 'retour_pause',
            'fin', 'fin_shift', 'sortie' => 'sortie',
            default => throw new \InvalidArgumentException("Action invalide : {$action}"),
        };
    }

    private function buildShiftSummary($shift): array
    {
        $entrees = $shift->where('type', 'entree')->values();
        $sorties = $shift->where('type', 'sortie')->values();
        $pauses  = $shift->where('type', 'pause')->values();

        return [
            'first_entree'  => $entrees->first()?->created_at?->setTimezone(self::TZ)->format('H:i'),
            'last_sortie'   => $sorties->last()?->created_at?->setTimezone(self::TZ)->format('H:i'),
            'pause_display' => $pauses->count() ? $pauses->count() . ' pause(s)' : null,
            'total_human'   => $this->calcTotalTime($entrees, $sorties),
        ];
    }

    private function calcTotalTime($entrees, $sorties): string
    {
        if ($entrees->isEmpty() || $sorties->isEmpty()) return '0h 0m';

        $total = 0;
        $count = min($entrees->count(), $sorties->count());

        for ($i = 0; $i < $count; $i++) {
            $diff = $sorties[$i]->created_at->timestamp - $entrees[$i]->created_at->timestamp;
            if ($diff > 0) $total += $diff;
        }

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
