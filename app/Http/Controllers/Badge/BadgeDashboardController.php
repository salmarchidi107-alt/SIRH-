<?php
// ============================================================
//  app/Http/Controllers/Badge/BadgeDashboardController.php
// ============================================================

namespace App\Http\Controllers\Badge;

use App\Http\Controllers\Controller;
use App\Models\Pointage;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BadgeDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('badge')->user();

        // Main relation via employees.user_id, fallback via users.employee_id (legacy)
        $employee = $user->employee;
        if (!$employee && method_exists($user, 'employeeByLegacyKey')) {
            $employee = $user->employeeByLegacyKey;
        }

        // Dernier pointage du jour pour cet employé
        $lastPointage = null;
        $todayShift   = null;
        $canEntree    = true;
        $canSortie    = false;

        if ($employee) {
            $todayPointages = Pointage::where('employee_id', $employee->id)
                ->whereDate('date', today())
                ->get();

            $lastPointage = $todayPointages->last();

            // Résumé du shift du jour
            $todayShift = $this->getTodayShift($employee->id);

            // Déterminer les boutons disponibles - simple logic
            $hasEntree = $todayPointages->whereNotNull('heure_entree')->count() > 0;
            $hasSortie = $todayPointages->whereNotNull('heure_sortie')->count() > 0;
            $canEntree = !$hasEntree;
            $canSortie = $hasEntree && !$hasSortie;
        }

        return view('badge.dashboard', compact(
            'user', 'employee', 'lastPointage', 'todayShift', 'canEntree', 'canSortie'
        ));
    }

    // Récupère le résumé entrée/sortie du jour - ENHANCED avec pause et meilleur calcul
    public static function getTodayShift(int $employeeId): array
    {
        $todayPointages = Pointage::where('employee_id', $employeeId)
            ->whereDate('date', today())
            ->latest()
            ->first();

        if (!$todayPointages) return [
            'first_entree' => null,
            'last_sortie' => null,
            'total_human' => '—',
            'total_pause_minutes' => 0,
            'is_late' => false,
            'count' => 0
        ];

        $firstEntree = $todayPointages->heure_entree;
        $lastSortie = $todayPointages->heure_sortie;
        $totalPauseMinutes = $todayPointages->pause_minutes ?? 0;

        $totalMinutes = 0;
        if ($firstEntree && $lastSortie) {
            $entreeTime = Carbon::parse(today()->format('Y-m-d') . ' ' . $firstEntree);
            $sortieTime = Carbon::parse(today()->format('Y-m-d') . ' ' . $lastSortie);
            if ($sortieTime->lt($entreeTime)) $sortieTime->addDay();
            $totalMinutes = $entreeTime->diffInMinutes($sortieTime, false) - $totalPauseMinutes;
        }

        $totalHoursFormatted = $totalMinutes > 0 ? sprintf('%dh%02d', floor($totalMinutes / 60), $totalMinutes % 60) : '—';

        $isLate = str_contains($todayPointages->commentaire ?? '', 'Retard');
        $lateMinutes = 0;
        if ($isLate) preg_match('/(\\d+)min/', $todayPointages->commentaire, $matches);
        $lateMinutes = $matches[1] ?? 0;

        return [
            'first_entree' => $firstEntree,
            'last_sortie' => $lastSortie,
            'total_minutes' => $totalMinutes,
            'total_human' => $totalHoursFormatted,
            'total_pause_minutes' => $totalPauseMinutes,
            'pause_display' => $totalPauseMinutes > 0 ? $totalPauseMinutes . 'min' : null,
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes,
            'count' => 1,
        ];
    }
}

