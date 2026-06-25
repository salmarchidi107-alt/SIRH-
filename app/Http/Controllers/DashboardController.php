<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Absence;
use App\Models\Planning;
use App\Models\News;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use App\Services\Dashboard\HolidayService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Scopes\TenantScope;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index()
    {
        $user        = Auth::user();
        $tenantId    = $user?->tenant_id;
        $isAdminOrRH = $user && ($user->isAdmin() || $user->isRh());

        $absentTodayIds = Absence::where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date',   '>=', today())
            ->pluck('employee_id')
            ->unique()
            ->toArray();

        $stats = [
            'total_employees'  => Employee::where('tenant_id', $tenantId)->count(),
            'active_employees' => Employee::where('tenant_id', $tenantId)->where('status', 'active')->count(),
            'today_present'    => 0,
        ];

        $recent_absences = collect();
        $contract_types  = collect();

        if ($isAdminOrRH) {
            $stats['pending_absences'] = Absence::where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->count();

            $recent_absences = Absence::with(['employee' => fn($q) => $q->withoutGlobalScopes()])
                ->where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get();

            $contract_types = Employee::where('tenant_id', $tenantId)
                ->groupBy('contract_type')
                ->selectRaw('contract_type, count(*) as total')
                ->pluck('total', 'contract_type');
        }

        $departments = Employee::where('tenant_id', $tenantId)
            ->groupBy('department')
            ->selectRaw('department, count(*) as total')
            ->pluck('total', 'department');

        $today_planning = Planning::with('employee')
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)
                  ->orWhereNull('tenant_id');
            })
            ->whereDate('date', today())
            ->whereNotIn('employee_id', $absentTodayIds)
            ->orderBy('shift_start')
            ->get()
            ->filter(fn($p) => $p->employee !== null);

        $stats['today_present'] = $today_planning->count();

        $currentYear  = now()->year;
        $currentMonth = now()->month;
        $nextMonth    = $currentMonth + 1;

        $hasNextMonth = $nextMonth <= 12 && Absence::where('tenant_id', $tenantId)
            ->whereYear('start_date', $currentYear)
            ->whereMonth('start_date', $nextMonth)
            ->exists();

        $upToMonth = $hasNextMonth ? $nextMonth : $currentMonth;

        $monthly_absences_raw = Absence::where('tenant_id', $tenantId)
            ->selectRaw('MONTH(start_date) as month_num, COUNT(*) as count')
            ->whereYear('start_date', $currentYear)
            ->whereMonth('start_date', '<=', $upToMonth)
            ->groupBy('month_num')
            ->orderBy('month_num', 'asc')
            ->get()
            ->keyBy('month_num');

        $monthly_absences = collect(range(1, $upToMonth))->map(function ($month) use ($monthly_absences_raw, $currentYear) {
            return [
                'month' => Carbon::create($currentYear, $month, 1)->locale('fr')->isoFormat('MMM'),
                'count' => (int) ($monthly_absences_raw->get($month)?->count ?? 0),
            ];
        })->toArray();

        // ── Anniversaires : UNIQUEMENT aujourd'hui ────────────────────────────
        $birthdays = Employee::with('user')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', now()->month)
            ->whereDay('birth_date', now()->day)       // ← CHANGEMENT : jour exact uniquement
            ->where('status', 'active')
            ->get()
            ->map(function ($employee) use ($currentYear) {
                $employee->birthday_this_year = Carbon::createFromDate(
                    $currentYear,
                    $employee->birth_date->month,
                    $employee->birth_date->day
                );
                return $employee;
            })
            ->sortBy('birthday_this_year');

        // ── Actualités : à venir (date >= aujourd'hui) ────────────────────────
        $upcomingNews = News::active()
            ->whereDate('event_date', '>=', today())   // ← CHANGEMENT : exclut les passées
            ->orderBy('event_date', 'asc')             // ← CHANGEMENT : plus proche en premier
            ->take(5)
            ->get();

        // ── Actualités récentes : uniquement aujourd'hui ──────────────────────
        $recentNews = News::active()
            ->whereDate('event_date', today())         // ← CHANGEMENT : uniquement aujourd'hui
            ->orderBy('event_date', 'desc')
            ->take(3)
            ->get();

        $approvedAbsences = Absence::with(['employee' => fn($q) => $q->withoutGlobalScopes()])
            ->where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->orderBy('employee_id')
            ->orderBy('start_date')
            ->get();

        $conflicts         = [];
        $currentEmployeeId = null;
        $employeeAbsences  = collect();

        $processConflicts = function ($employeeAbsences, $currentEmployeeId) use (&$conflicts) {
            $sorted = $employeeAbsences->sortBy('start_date');
            for ($i = 0; $i < $sorted->count() - 1; $i++) {
                $a         = $sorted[$i];
                $nextIndex = $i + 1;
                while ($nextIndex < $sorted->count()) {
                    $b = $sorted[$nextIndex];
                    if ($a->end_date < $b->start_date) break;
                    $overlapStart = max($a->start_date, $b->start_date);
                    $overlapEnd   = min($a->end_date, $b->end_date);
                    if ($overlapStart <= $overlapEnd) {
                        $conflicts[] = [
                            'employee_id' => $currentEmployeeId,
                            'a_id'        => $a->id,
                            'b_id'        => $b->id,
                            'employee'    => $a->employee->full_name,
                            'absence1'    => \App\Models\Absence::TYPES[$a->type] ?? $a->type,
                            'absence2'    => \App\Models\Absence::TYPES[$b->type] ?? $b->type,
                            'start'       => $overlapStart->format('d/m'),
                            'end'         => $overlapEnd->format('d/m/Y'),
                        ];
                    }
                    $nextIndex++;
                }
            }
        };

        foreach ($approvedAbsences as $absence) {
            if (!$absence->employee) continue;

            if ($absence->employee_id !== $currentEmployeeId) {
                if ($currentEmployeeId) {
                    $processConflicts($employeeAbsences, $currentEmployeeId);
                }
                $currentEmployeeId = $absence->employee_id;
                $employeeAbsences  = collect([$absence]);
            } else {
                $employeeAbsences->push($absence);
            }
        }

        if ($currentEmployeeId) {
            $processConflicts($employeeAbsences, $currentEmployeeId);
        }

        $employee = null;

        if ($user) {
            $employee = Employee::with('user')
                ->where('tenant_id', $tenantId)
                ->where('user_id', $user->id)
                ->first();

            if (!$employee) {
                $employee = Employee::with('user')
                    ->where('tenant_id', $tenantId)
                    ->where('email', $user->email)
                    ->first();
            }

            if (!$employee && $user->employee_id) {
                $employee = Employee::with('user')
                    ->where('tenant_id', $tenantId)
                    ->find($user->employee_id);
            }
        }

        $tempsWidget  = null;
        $droitsWidget = null;

        if ($employee && $employee->id) {
            $tempsWidget  = \App\Models\CompteurTemps::getOuCreeParMois($employee->id, $currentYear, $currentMonth);
            $droitsWidget = \App\Models\DroitAbsence::getOuCreeParAnnee($employee->id, $currentYear);
        }

        $holidays = [];

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
        try {
            $tenantId = Auth::user()?->tenant_id;

            return response()->json([
                'total_employees' => Employee::where('tenant_id', $tenantId)->count(),
                'active'          => Employee::where('tenant_id', $tenantId)->where('status', 'active')->count(),
                'on_leave'        => Absence::where('tenant_id', $tenantId)
                    ->where('status', 'approved')
                    ->whereDate('start_date', '<=', today())
                    ->whereDate('end_date',   '>=', today())
                    ->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard stats error: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur stats'], 500);
        }
    }

    public function data()
    {
        try {
            return response()->json($this->dashboardService->getDashboardData(Auth::user()));
        } catch (ModelNotFoundException | NotFoundHttpException $e) {
            Log::warning('Dashboard data not found: ' . $e->getMessage());
            return response()->json(['error' => 'Données non trouvées'], 404);
        } catch (\Exception $e) {
            Log::error('Dashboard data error: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur chargement données'], 500);
        }
    }
}
