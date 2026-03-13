<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Absence;
use App\Models\Planning;
use App\Models\News;
use App\Models\CompteurTemps;
use App\Models\Pointage;
use App\Models\DroitAbsence;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        
        $holidays = [];
        try {
            $response = Http::get('https://calendar-api.ma/api/holidays?year=' . date('Y'));
            if ($response->successful()) {
                $holidaysData = $response->json();
                if (isset($holidaysData['holidays'])) {
                    $holidays = $holidaysData['holidays'];
                } elseif (is_array($holidaysData)) {
                    $holidays = $holidaysData;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Calendar API Error: ' . $e->getMessage());
        }

       
        if (empty($holidays)) {
            $currentYear = date('Y');
            $holidays = [
                ['name' => 'Nouvel An', 'date' => $currentYear . '-01-01'],
                ['name' => 'Manifeste de l\'Indépendance', 'date' => $currentYear . '-01-11'],
                ['name' => 'Fête du Travail', 'date' => $currentYear . '-05-01'],
                ['name' => 'Fête de la Throne', 'date' => $currentYear . '-07-30'],
                ['name' => 'Fête de la Révolution', 'date' => $currentYear . '-08-14'],
                ['name' => 'Fête de la Jeunesse', 'date' => $currentYear . '-08-21'],
                ['name' => 'Mort du Roi Hassan II', 'date' => $currentYear . '-07-30'],
                ['name' => 'Anniversaire du Roi', 'date' => $currentYear . '-08-21'],
                ['name' => 'Aïd al-Fitr', 'date' => ''], 
                ['name' => 'Aïd al-Adha', 'date' => ''], 
                ['name' => 'Nouvel An Hégirien', 'date' => ''], 
                ['name' => 'Fête de l’Indépendance', 'date' => $currentYear . '-11-18'],
            ];
        }

$user = Auth::user();
        $isAdminOrRH = $user && in_array($user->role, ['admin', 'rh']);

        $stats = [
            'total_employees' => Employee::count(),
            'active_employees' => Employee::where('status', 'active')->count(),
            'today_present' => Planning::whereDate('date', today())->count(),
        ];

        if ($isAdminOrRH) {
            $stats['pending_absences'] = Absence::where('status', 'pending')->count();
            $recent_absences = Absence::with('employee')
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get();
            $contract_types = Employee::groupBy('contract_type')
                ->selectRaw('contract_type, count(*) as total')
                ->pluck('total', 'contract_type');
        }

        $departments = Employee::groupBy('department')
            ->selectRaw('department, count(*) as total')
            ->pluck('total', 'department');


        $today_planning = Planning::with('employee')
            ->whereDate('date', today())
            ->get();

    
        $monthly_absences = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthly_absences[] = [
                'month' => $month->format('M Y'),
                'count' => Absence::whereYear('start_date', $month->year)
                    ->whereMonth('start_date', $month->month)
                    ->count()
            ];
        }

    
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        $birthdays = Employee::whereNotNull('birth_date')
            ->whereMonth('birth_date', $currentMonth)
            ->where('status', 'active')
            ->get()
            ->map(function ($employee) use ($currentYear) {
                $employee->birthday_this_year = Carbon::createFromDate($currentYear, $employee->birth_date->month, $employee->birth_date->day);
                return $employee;
            })
            ->sortBy('birthday_this_year');

        
        $upcomingNews = News::active()
            ->upcoming()
            ->take(5)
            ->get();

        
        $recentNews = News::active()
            ->where('event_date', '>=', now()->subDays(7))
            ->orderBy('event_date', 'desc')
            ->take(3)
            ->get();

        
        $conflicts = [];
        $allApprovedAbsences = Absence::with('employee')
            ->where('status', 'approved')
            ->get();

        foreach ($allApprovedAbsences as $a) {
            foreach ($allApprovedAbsences as $b) {
                if ($a->id >= $b->id) continue;
                if ($a->employee_id === $b->employee_id) {
                    $overlapStart = max($a->start_date, $b->start_date);
                    $overlapEnd = min($a->end_date, $b->end_date);
                    if ($overlapStart <= $overlapEnd) {
                        $exists = false;
                        foreach ($conflicts as $c) {
                            if ($c['employee_id'] == $a->employee_id && 
                                $c['a_id'] == $a->id && 
                                $c['b_id'] == $b->id) {
                                $exists = true;
                                break;
                            }
                        }
                        if (!$exists) {
                            $conflicts[] = [
                                'employee_id' => $a->employee_id,
                                'a_id' => $a->id,
                                'b_id' => $b->id,
                                'employee' => $a->employee->full_name,
                                'absence1' => \App\Models\Absence::TYPES[$a->type] ?? $a->type,
                                'absence2' => \App\Models\Absence::TYPES[$b->type] ?? $b->type,
                                'start' => $overlapStart->format('d/m'),
                                'end' => $overlapEnd->format('d/m/Y'),
                            ];
                        }
                    }
                }
            }
        }

       
        $user = Auth::user();
        $currentYear = now()->year;
        $currentMonth = now()->month;
        
        $employee = null;
        
        if ($user) {
           
            $employee = Employee::with('user')->where('user_id', $user->id)->first();
            if (!$employee) {
                $employee = Employee::with('user')->where('email', $user->email)->first();
            }
           
            if (!$employee && $user->employee_id) {
                $employee = Employee::with('user')->find($user->employee_id);
            }
        }
        
        $tempsWidget = null;
        $droitsWidget = null;
        
        if ($employee && $employee->id) {
            $tempsWidget = CompteurTemps::getOuCreeParMois($employee->id, $currentYear, $currentMonth);
            $droitsWidget = DroitAbsence::getOuCreeParAnnee($employee->id, $currentYear);
        }

        return view('dashboard.index', compact(
            'stats',
            'holidays',
            'departments',
            'today_planning',
            'monthly_absences',
            'birthdays',
            'upcomingNews',
            'recentNews',
            'conflicts',
            'employee',
            'tempsWidget',
            'droitsWidget',
            'isAdminOrRH',
            'recent_absences',
            'contract_types'
        ));

    }

    public function stats()
    {
        return response()->json([
            'total_employees' => Employee::count(),
            'active' => Employee::where('status', 'active')->count(),
            'on_leave' => Absence::where('status', 'approved')
                ->whereDate('start_date', '<=', today())
                ->whereDate('end_date', '>=', today())
                ->count(),
        ]);
    }
}
