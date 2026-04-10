<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Absence;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Planning;
use Carbon\Carbon;

class EmployeeDashboardController extends Controller
{
<<<<<<< HEAD
public function index()
=======

    public function index()
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
    {
        $user     = Auth::user();
        $employee = Employee::where('user_id', $user->id)->firstOrFail();

        $aujourd_hui  = Carbon::today();
        $debutSemaine = $aujourd_hui->copy()->startOfWeek();   // lundi
        $finSemaine   = $aujourd_hui->copy()->endOfWeek();     // dimanche
        $debutMois    = $aujourd_hui->copy()->startOfMonth();

        $planningSemaine = Planning::where('employee_id', $employee->id)
            ->whereDate('date', '>=', $debutSemaine)
            ->whereDate('date', '<=', $finSemaine)
            ->get()
            ->groupBy(function ($planning) {
                return $planning->date->format('Y-m-d');
            })
            ->map(function ($plannings, $dateStr) use ($employee) {
                $date = Carbon::parse($dateStr);
                $planning = $plannings->first();
                $absence = Absence::where('employee_id', $employee->id)
                    ->where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date)
                    ->where('status', 'approved')
                    ->first();

                return (object)[
                    'date'        => $date,
                    'planning'    => $planning,
                    'absence'     => $absence,
                    'statut'      => $absence ? 'absence' : ($planning ? 'travail' : 'repos'),
                    'heure_debut' => $planning?->shift_start ?? '—',
                    'heure_fin'   => $planning?->shift_end ?? '—',
                    'periode'     => $planning ? \App\Models\Planning::SHIFT_TYPES[$planning->shift_type] ?? 'Custom' : 'Repos',
                ];
            })
            ->values()
            ->take(5);

        $absences = Absence::where('employee_id', $employee->id)
            ->latest('start_date')
            ->get();

        $demandesEnAttente = $absences->where('status', 'pending')->count();

        $heuresMois    = $employee->work_hours_counter ?? 0;
        $heuresPrevues = ($employee->work_hours ?? 40) * 4;

        $annee           = $aujourd_hui->year;
        $congesUtilises  = Absence::where('employee_id', $employee->id)
            ->whereIn('type', ['conge_paye', 'conge_annuel', 'CP'])
            ->where('status', 'approved')
            ->whereYear('start_date', $annee)
            ->sum('days');
        $congesTotal    = $employee->cp_days ?? 15;
        $congesRestants = max(0, $congesTotal - $congesUtilises);

        $maladieUtilises = Absence::where('employee_id', $employee->id)
            ->whereIn('type', ['maladie', 'medical'])
            ->where('status', 'approved')
            ->whereYear('start_date', $annee)
            ->sum('days');
        $maladieTotal = 10;

        $rttUtilises = Absence::where('employee_id', $employee->id)
            ->where('type', 'rtt')
            ->where('status', 'approved')
            ->whereYear('start_date', $annee)
            ->sum('days');
        $rttTotal = 5;

        $absencesData = [
            'conges_utilises'  => $congesUtilises,
            'conges_total'     => $congesTotal,
            'conges_pct'       => $congesTotal > 0 ? min(100, round(($congesUtilises / $congesTotal) * 100)) : 0,
            'maladie_utilises' => $maladieUtilises,
            'maladie_total'    => $maladieTotal,
            'maladie_pct'      => min(100, round(($maladieUtilises / $maladieTotal) * 100)),
            'rtt_utilises'     => $rttUtilises,
            'rtt_total'        => $rttTotal,
            'rtt_pct'          => $rttTotal > 0 ? min(100, round(($rttUtilises / $rttTotal) * 100)) : 0,
        ];

        $evenements = collect();

        $upcomingNews = News::active()
            ->upcoming()
            ->take(5)
            ->get();

        $recentNews = News::active()
            ->where('event_date', '>=', now()->subDays(7))
            ->orderBy('event_date', 'desc')
            ->take(3)
            ->get();

        return view('employe.dashboard', compact(
            'employee',
            'planningSemaine',
            'absences',
            'demandesEnAttente',
            'heuresMois',
            'heuresPrevues',
            'congesRestants',
            'absencesData',
            'evenements',
            'upcomingNews',
            'recentNews'
        ));
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
