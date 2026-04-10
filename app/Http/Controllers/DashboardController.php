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
use App\Services\Dashboard\HolidayService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {

// $holidays = app(HolidayService::class)->getCurrentYearHolidays(); // Missing service


$user = Auth::user();
$isAdminOrRH = $user && ($user->isAdmin() || $user->isRh());

        $stats = [
            'total_employees' => Employee::count(),
            'active_employees' => Employee::where('status', 'active')->count(),
            'today_present' => Planning::whereDate('date', today())->count(),
        ];


        $recent_absences = collect();
        $contract_types = collect();

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


$monthly_absences_raw = Absence::selectRaw('
            YEAR(start_date) year,
            MONTH(start_date) month_num,
            COUNT(*) count
        ')
        ->where('start_date', '>=', now()->subMonths(6))
        ->groupBy('year', 'month_num')
        ->orderBy('year', 'desc')
        ->orderBy('month_num', 'desc')
        ->get();

$monthly_absences = $monthly_absences_raw->map(function ($row) {
            $month = Carbon::create($row->year, $row->month_num, 1);
            return [
                'month' => $month->format('M Y'),
                'count' => (int) $row->count
            ];
        })->values()->toArray();


        $currentMonth = now()->month;
        $currentYear = now()->year;

$birthdays = Employee::with('user')->whereNotNull('birth_date')
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


$approvedAbsences = Absence::with('employee')
            ->where('status', 'approved')
            ->orderBy('employee_id')
            ->orderBy('start_date')
            ->get();

        $conflicts = [];
        $currentEmployeeId = null;
        $employeeAbsences = collect();

        foreach ($approvedAbsences as $absence) {
            if ($absence->employee_id !== $currentEmployeeId) {
                // Process previous employee
                $sorted = $employeeAbsences->sortBy('start_date');
                for ($i = 0; $i < $sorted->count() - 1; $i++) {
                    $a = $sorted[$i];
                    $nextIndex = $i + 1;
                    while ($nextIndex < $sorted->count()) {
                        $b = $sorted[$nextIndex];
                        if ($a->end_date < $b->start_date) break; // No more overlaps
                        $overlapStart = max($a->start_date, $b->start_date);
                        $overlapEnd = min($a->end_date, $b->end_date);
                        if ($overlapStart <= $overlapEnd) {
                            $conflicts[] = [
                                'employee_id' => $currentEmployeeId,
                                'a_id' => $a->id,
                                'b_id' => $b->id,
                                'employee' => $a->employee->full_name,
                                'absence1' => \App\Models\Absence::TYPES[$a->type] ?? $a->type,
                                'absence2' => \App\Models\Absence::TYPES[$b->type] ?? $b->type,
                                'start' => $overlapStart->format('d/m'),
                                'end' => $overlapEnd->format('d/m/Y'),
                            ];
                        }
                        $nextIndex++;
                    }
                }
                // Start new employee
                $currentEmployeeId = $absence->employee_id;
                $employeeAbsences = collect([$absence]);
            } else {
                $employeeAbsences->push($absence);
            }
        }

        // Process last employee
        if ($currentEmployeeId) {
            $sorted = $employeeAbsences->sortBy('start_date');
            for ($i = 0; $i < $sorted->count() - 1; $i++) {
                $a = $sorted[$i];
                $nextIndex = $i + 1;
                while ($nextIndex < $sorted->count()) {
                    $b = $sorted[$nextIndex];
                    if ($a->end_date < $b->start_date) break;
                    $overlapStart = max($a->start_date, $b->start_date);
                    $overlapEnd = min($a->end_date, $b->end_date);
                    if ($overlapStart <= $overlapEnd) {
                        $conflicts[] = [
                            'employee_id' => $currentEmployeeId,
                            'a_id' => $a->id,
                            'b_id' => $b->id,
                            'employee' => $a->employee->full_name,
                            'absence1' => \App\Models\Absence::TYPES[$a->type] ?? $a->type,
                            'absence2' => \App\Models\Absence::TYPES[$b->type] ?? $b->type,
                            'start' => $overlapStart->format('d/m'),
                            'end' => $overlapEnd->format('d/m/Y'),
                        ];
                    }
                    $nextIndex++;
                }
            }
        }



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

        $holidays = []; // Fallback for missing HolidayService

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
