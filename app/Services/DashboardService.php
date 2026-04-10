<?php

namespace App\Services;

use App\Models\Absence;
use App\Models\Employee;
use App\Models\News;
use App\Models\Planning;
use App\DTOs\CompteurMoisDTO;
use App\Models\CompteurTemps;
use App\Models\DroitAbsence;
use App\Models\Department;
use App\Services\HolidayService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class DashboardService
{
    public function __construct(private HolidayService $holidayService) {}

    public function getDashboardData(?Authenticatable $user): array
    {
        try {
            $isAdminOrRH = $user ? $user->isAdminOrRh() : false;
            $currentYear = now()->year;
            $currentMonth = now()->month;

            $employee = $this->resolveEmployee($user);
            [$tempsWidget, $droitsWidget] = $this->resolveEmployeeWidgets($employee, $currentYear, $currentMonth);

            return [
                'holidays' => $this->holidayService->getCurrentYearHolidays(),
                'stats' => $this->getCachedStats($isAdminOrRH),
                'recent_absences' => $this->getRecentAbsences($isAdminOrRH),
                'contract_types' => $this->getContractTypes($isAdminOrRH),
                'departments' => $this->getDepartments(),
                'today_planning' => $this->getTodayPlanning(),
                'monthly_absences' => $this->getMonthlyAbsences(),
                'birthdays' => $this->getBirthdays($currentMonth),
                'upcomingNews' => $this->getUpcomingNews(),
                'recentNews' => $this->getRecentNews(),
                'conflicts' => $this->getConflicts(),
                'employee' => $employee,
                'tempsWidget' => $tempsWidget,
                'droitsWidget' => $droitsWidget,
                'isAdminOrRH' => $isAdminOrRH,
            ];
        } catch (Exception $e) {
            Log::error('DashboardService getDashboardData error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ['error' => 'Erreur chargement dashboard', 'stats' => [], 'conflicts' => collect()];
        }
    }

    private function resolveEmployee(?Authenticatable $user)
    {
        try {
            if (! $user) {
                return null;
            }

            $employee = Employee::with('user')->where('user_id', $user->id)->first();

            if (! $employee && property_exists($user, 'email')) {
                $employee = Employee::with('user')->where('email', $user->email)->first();
            }

            if (! $employee && property_exists($user, 'employee_id') && $user->employee_id) {
                $employee = Employee::with('user')->find($user->employee_id);
            }

            return $employee;
        } catch (Exception $e) {
            Log::warning('Dashboard resolveEmployee error: ' . $e->getMessage());
            return null;
        }
    }

private function resolveEmployeeWidgets($employee, int $annee, int $mois): array
    {
        try {
            if (! $employee || ! $employee->id) {
                return [CompteurMoisDTO::defaults(), null];
            }

            return [
CompteurTemps::getOuCreeParMois($employee->id, $annee, $mois),
                DroitAbsence::getOuCreeParAnnee($employee->id, $year),
            ];
        } catch (Exception $e) {
            Log::error('Dashboard resolveEmployeeWidgets error: ' . $e->getMessage());
            return [CompteurMoisDTO::defaults(), null];
        }
    }

    private function getCachedStats(bool $isAdminOrRH): array
    {
        try {
            $cacheKey = 'dashboard.stats.' . ($isAdminOrRH ? 'admin' : 'user');

            return Cache::remember($cacheKey, now()->addMinutes(config('constants.dashboard.cache_minutes', 10)), function () use ($isAdminOrRH) {
                $stats = [
                    'total_employees' => Employee::count(),
                    'active_employees' => Employee::active()->count(),
                    'today_present' => Planning::whereDate('date', today())->count(),
                ];

                if ($isAdminOrRH) {
                    $stats['pending_absences'] = Absence::where('status', \App\Enums\AbsenceStatus::Pending->value)->count();
                }

                return $stats;
            });
        } catch (Exception $e) {
            Log::error('Dashboard getCachedStats error: ' . $e->getMessage());
            return ['total_employees' => 0, 'active_employees' => 0];
        }
    }

    private function getRecentAbsences(bool $isAdminOrRH)
    {
        try {
            if (! $isAdminOrRH) {
                return collect();
            }

            return Absence::with('employee')
                ->where('status', \App\Enums\AbsenceStatus::Pending->value)
                ->latest()
                ->take(config('constants.dashboard.recent_limit', 5))
                ->get();
        } catch (Exception $e) {
            Log::error('Dashboard getRecentAbsences error: ' . $e->getMessage());
            return collect();
        }
    }

    private function getContractTypes(bool $isAdminOrRH)
    {
        try {
            if (! $isAdminOrRH) {
                return collect();
            }

            return Employee::groupBy('contract_type')
                ->selectRaw('contract_type, count(*) as total')
                ->pluck('total', 'contract_type');
        } catch (Exception $e) {
            Log::error('Dashboard getContractTypes error: ' . $e->getMessage());
            return collect();
        }
    }

    private function getDepartments()
    {
        try {
            return Department::counts();
        } catch (Exception $e) {
            Log::error('Dashboard getDepartments error: ' . $e->getMessage());
            return collect();
        }
    }

    private function getTodayPlanning()
    {
        try {
            return Planning::with('employee')
                ->whereDate('date', today())
                ->get();
        } catch (Exception $e) {
            Log::error('Dashboard getTodayPlanning error: ' . $e->getMessage());
            return collect();
        }
    }

    private function getMonthlyAbsences(): array
    {
        try {
            $startDate = now()->subMonths(config('constants.dashboard.months_back', 5))->startOfMonth();
            $endDate = now()->endOfMonth();

            $monthlyCounts = Absence::selectRaw('YEAR(start_date) as year, MONTH(start_date) as month, COUNT(*) as count')
                ->whereBetween('start_date', [$startDate, $endDate])
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->mapWithKeys(function ($row) {
                    return [sprintf('%04d-%02d', $row->year, $row->month) => $row->count];
                });

            $monthlyAbsences = [];

            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $key = $month->format('Y-m');

                $monthlyAbsences[] = [
                    'month' => $month->format('M Y'),
                    'count' => $monthlyCounts->get($key, 0),
                ];
            }

            return $monthlyAbsences;
        } catch (Exception $e) {
            Log::error('Dashboard getMonthlyAbsences error: ' . $e->getMessage());
            return [];
        }
    }

    private function getBirthdays(int $currentMonth)
    {
        try {
            return Employee::whereNotNull('birth_date')
                ->whereMonth('birth_date', $currentMonth)
                ->active()
                ->select(['id', 'first_name', 'last_name', 'birth_date'])
                ->orderByRaw('DAY(birth_date)')
                ->take(10)
                ->get();
        } catch (Exception $e) {
            Log::error('Dashboard getBirthdays error: ' . $e->getMessage());
            return collect();
        }
    }

    private function getUpcomingNews()
    {
        try {
            return News::active()
                ->upcoming()
                ->take(5)
                ->get();
        } catch (Exception $e) {
            Log::error('Dashboard getUpcomingNews error: ' . $e->getMessage());
            return collect();
        }
    }

    private function getRecentNews()
    {
        try {
            return News::active()
                ->where('event_date', '>=', now()->subDays(config('constants.dashboard.recent_news_days', 7)))
                ->orderBy('event_date', 'desc')
                ->take(3)
                ->get();
        } catch (Exception $e) {
            Log::error('Dashboard getRecentNews error: ' . $e->getMessage());
            return collect();
        }
    }

    private function getConflicts()
    {
        try {
            return DB::table('absences as a1')
                ->join('absences as a2', function ($join) {
                    $join->on('a1.employee_id', '=', 'a2.employee_id')
                         ->whereColumn('a1.id', '<', 'a2.id')
                         ->where('a1.status', 'approved')
                         ->where('a2.status', 'approved');
                })
                ->whereRaw('GREATEST(a1.start_date, a2.start_date) <= LEAST(a1.end_date, a2.end_date)')
                ->join('employees', 'a1.employee_id', '=', 'employees.id')
                ->selectRaw('DISTINCT a1.id as absence1_id, a2.id as absence2_id, a1.employee_id, CONCAT(employees.first_name, \" \", employees.last_name) as employee_name, a1.type as absence1_type, a2.type as absence2_type, GREATEST(a1.start_date, a2.start_date) as overlap_start, LEAST(a1.end_date, a2.end_date) as overlap_end')
                ->get()
                ->map(function ($conflict) {
                    return [
                        'employee_id' => $conflict->employee_id,
                        'a_id' => $conflict->absence1_id,
                        'b_id' => $conflict->absence2_id,
                        'employee' => $conflict->employee_name,
                        'absence1' => \App\Models\Absence::TYPES[$conflict->absence1_type] ?? $conflict->absence1_type,
                        'absence2' => \App\Models\Absence::TYPES[$conflict->absence2_type] ?? $conflict->absence2_type,
                        'start' => Carbon::parse($conflict->overlap_start)->format('d/m'),
                        'end' => Carbon::parse($conflict->overlap_end)->format('d/m/Y'),
                    ];
                })->values();
        } catch (Exception $e) {
            Log::error('Dashboard getConflicts error: ' . $e->getMessage());
            return collect();
        }
    }
}

