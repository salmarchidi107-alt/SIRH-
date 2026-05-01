<?php

namespace App\Http\Controllers\Badge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\Pointage;
use App\Models\BadgeRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BadgePointageController extends Controller
{
    private const TZ = 'Africa/Casablanca';

    public function __construct()
    {
        Auth::shouldUse('badge');
    }

    // ─── Résolution employé ──────────────────────────────────────────────────

    /**
     * Trouve l'employé lié au User connecté.
     * Essaie d'abord user->employee (via employee_id),
     * puis Employee::where('user_id') en fallback legacy.
     */
    private function resolveEmployee(): ?Employee
    {
        /** @var User|null $user */
        $user = auth('badge')->user();

        if (! $user) {
            return null;
        }

        // Cas 1 : employee_id renseigné sur le User (relation directe)
        if ($user->employee_id && $user->employee) {
            return $user->employee;
        }

        // Cas 2 : legacy — employee.user_id pointe vers ce User
        return Employee::where('user_id', $user->id)->first();
    }

    private function getAuthEmployee(): Employee
    {
        $employee = $this->resolveEmployee();

        if (! $employee) {
            abort(403, 'Aucun employé associé à ce compte badge. Contactez l\'administrateur.');
        }

        return $employee;
    }

    // ─── Helpers Carbon ─────────────────────────────────────────────────────

    private function nowCasa(): Carbon
    {
        return Carbon::now(self::TZ);
    }

    private function todayCasa(): Carbon
    {
        return Carbon::today(self::TZ);
    }

    // ─── Controllers ────────────────────────────────────────────────────────

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

    public function handleAction(Request $request)
    {
        $request->validate(['action' => 'required|string']);

        $employee = $this->getAuthEmployee();
        $realType = $this->resolveType($request->action);
        $this->recordAction($realType, $employee);

        $request->session()->flash('last_type', $request->action);

        return response()->json([
            'success'  => true,
            'redirect' => route('badge.result'),
            'message'  => 'Pointage enregistré avec succès',
        ]);
    }

    public function action(Request $request)
    {
        $employee = $this->getAuthEmployee();
        $realType = $this->resolveType($request->action ?? 'entree');
        $this->recordAction($realType, $employee);

        return response()->json(['success' => true, 'redirect' => route('badge.pointage')]);
    }

    public function entree(Request $request)
    {
        
        $employee = $this->getAuthEmployee();

        $this->recordAction('entree', $employee);
        $request->session()->flash('last_type', 'entree');
        return redirect()->route('badge.result');
    }

    public function sortie(Request $request)
    {
        $employee = $this->getAuthEmployee();
        $this->recordAction('sortie', $employee);
        $request->session()->flash('last_type', 'sortie');
        return redirect()->route('badge.result');
    }

    public function result(Request $request)
    {
        $employee = $this->getAuthEmployee();
        $shift    = $this->getTodayShift($employee);
        $type     = $request->session()->get('last_type', 'action');

        $pauseRecords  = $shift->where('type', 'pause')->values();
        $retourRecords = $shift->where('type', 'entree')->slice(1)->values();

        $todayShift = array_merge($this->buildShiftSummary($shift), [
            'pause_start'       => $pauseRecords->first()?->created_at?->setTimezone(self::TZ)->format('H:i'),
            'pause_end'         => $retourRecords->first()?->created_at?->setTimezone(self::TZ)->format('H:i'),
            'total_pause_human' => $this->calcTotalPause($pauseRecords, $retourRecords),
        ]);

        return view('badge.result', compact('employee', 'todayShift', 'type'));
    }

    // ─── Enregistrement ─────────────────────────────────────────────────────

    public function recordAction(string $type, Employee $employee): void
    {
      
        $now     = $this->nowCasa();
       
        $today   = $now->format('Y-m-d');
        $nowTime = $now->format('H:i:s');

        // 1. BadgeRecord
        BadgeRecord::create([
            'employee_id' => $employee->id,
            'type'        => $type,
        ]);
   
        
        // 2. Synchronisation Pointage RH
        $pointage = Pointage::firstOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            ['statut' => 'present', 'valide' => false, 'source' => 'badge', 'tenant_id' => $employee->user->tenant_id ]
        );
   
//  TODO: add pause start and pause end logi
      if ($type === 'entree' && !$pointage->heure_entree) {
    $pointage->heure_entree = $nowTime;
}

elseif ($type === 'pause' && !$pointage->pause_start) {
    $pointage->pause_start = $nowTime;
}

elseif ($type === 'retour_pause' && !$pointage->pause_end) {
    $pointage->pause_end = $nowTime;
}

elseif ($type === 'sortie') {
    $pointage->heure_sortie = $nowTime;
}
        //  dd($pointage,$nowTime);
        $pointage->save();
        // dd($pointage);
        if (method_exists($pointage, 'calculerTotalHeures')) {
            $pointage->calculerTotalHeures(false);
        }
    }

    // ─── Helpers privés ─────────────────────────────────────────────────────

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
            'debut', 'retour_pause', 'entree' => 'entree',
            'pause', 'sortie_pause'            => 'pause',
            'fin', 'fin_shift', 'sortie'       => 'sortie',
            default => throw new \InvalidArgumentException("Action invalide : {$action}"),
        };
    }

    /**
     * Résumé commun dashboard + result.
     * Les heures sont converties en timezone Casablanca.
     */
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

    /**
     * Temps de travail réel = somme des périodes entree[i] -> sortie[i].
     *
     * Séquence : entree(8h) pause(10h) entree(10h30) sortie(17h)
     * Couples  : (8h->10h) + (10h30->17h) = 2h + 6h30 = 8h30
     */
    private function calcTotalTime($entrees, $sorties): string
    {
        if ($entrees->isEmpty() || $sorties->isEmpty()) {
            return '0h 0m';
        }

        $total = 0;
        $count = min($entrees->count(), $sorties->count());

        for ($i = 0; $i < $count; $i++) {
            $diff = $sorties[$i]->created_at->timestamp - $entrees[$i]->created_at->timestamp;
            if ($diff > 0) {
                $total += $diff;
            }
        }

        return floor($total / 3600) . 'h ' . floor(($total % 3600) / 60) . 'm';
    }

    /**
     * Temps de pause = somme des périodes pause[i] -> entree[i+1].
     */
    private function calcTotalPause($pauses, $retours): string
    {
        if ($pauses->isEmpty() || $retours->isEmpty()) {
            return '0m';
        }

        $total = 0;
        $count = min($pauses->count(), $retours->count());

        for ($i = 0; $i < $count; $i++) {
            $diff = $retours[$i]->created_at->timestamp - $pauses[$i]->created_at->timestamp;
            if ($diff > 0) {
                $total += $diff;
            }
        }

        $minutes = floor($total / 60);
        return $minutes > 0 ? $minutes . 'm' : '0m';
    }
}