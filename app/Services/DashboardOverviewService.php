<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Absence;
use App\Models\Planning;
use App\Models\News;
use App\Models\CompteurTemps;
use App\Models\DroitAbsence;
use Carbon\Carbon;

/**
 * Contient toute la logique métier liée au tableau de bord (index, stats).
 * Le Controller ne fait qu'appeler ces méthodes et renvoyer la réponse.
 */
class DashboardOverviewService
{
    /**
     * Construit toutes les données nécessaires à la vue dashboard.index.
     * Remplace exactement la logique précédemment présente dans
     * DashboardController::index().
     */
    public function getIndexData($user): array
{
    $tenantId    = $user?->tenant_id;
    $isAdminOrRH = $user && ($user->isAdmin() || $user->isRh());

    $absentTodayIds = $this->getAbsentTodayIds($tenantId);

    $stats = $this->getBaseStats($tenantId);

    $recentAbsences = collect();
    $contractTypes  = collect();

    if ($isAdminOrRH) {
        $stats['pending_absences'] = $this->getPendingAbsencesCount($tenantId);
        $recentAbsences            = $this->getRecentPendingAbsences($tenantId);
        $contractTypes             = $this->getContractTypeDistribution($tenantId);
    }

    $departments = $this->getDepartmentDistribution($tenantId);

    $todayPlanning           = $this->getTodayPlanning($tenantId, $absentTodayIds);
    $stats['today_present'] = $todayPlanning->count();

    $monthlyAbsences = $this->getMonthlyAbsences($tenantId);

    $birthdays = $this->getTodayBirthdays($tenantId);

    $upcomingNews = $this->getUpcomingNews();
    $recentNews   = $this->getRecentNews();

    $conflicts = $this->getAbsenceConflicts($tenantId);

    $employee = $this->resolveEmployee($user, $tenantId);

    [$tempsWidget, $droitsWidget] = $this->getEmployeeWidgets($employee);

    // Le RH partage ce tableau de bord avec l'Admin mais peut, comme un employé,
    // avoir des tâches qui lui sont assignées (module Suivi d'activité) : à afficher
    // avec le même bloc visuel que employee.dashboard.
    $myTasks     = collect();
    $myTasksLate = 0;

    if ($user && $user->isRh()) {
        $myTasks = \App\Models\Task::query()
            ->tenant($tenantId)
            ->assignedTo($user->id)
            ->whereNotIn('status', ['terminee', 'annulee'])
            ->with('project')
            ->orderByRaw("FIELD(status, 'en_cours','en_pause','a_faire')")
            ->orderByDesc('updated_at')
            ->get();

        $myTasksLate = $myTasks->filter(fn (\App\Models\Task $t) => $t->isLate())->count();
    }

    return [
        'stats'            => $stats,
        'holidays'         => [],
        'departments'      => $departments,
        'today_planning'   => $todayPlanning,
        'monthly_absences' => $monthlyAbsences,
        'birthdays'        => $birthdays,
        'upcomingNews'     => $upcomingNews,
        'recentNews'       => $recentNews,
        'conflicts'        => $conflicts,
        'employee'         => $employee,
        'tempsWidget'      => $tempsWidget,
        'droitsWidget'     => $droitsWidget,
        'isAdminOrRH'      => $isAdminOrRH,
        'recent_absences'  => $recentAbsences,
        'contract_types'   => $contractTypes,
        'myTasks'          => $myTasks,
        'myTasksLate'      => $myTasksLate,
    ];
}

    /**
     * Statistiques légères utilisées par DashboardController::stats().
     */
    public function getStats($user): array
    {
        $tenantId = $user?->tenant_id;

        return [
            'total_employees' => Employee::where('tenant_id', $tenantId)->count(),
            'active'          => Employee::where('tenant_id', $tenantId)->where('status', 'active')->count(),
            'on_leave'        => Absence::where('tenant_id', $tenantId)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', today())
                ->whereDate('end_date', '>=', today())
                ->count(),
        ];
    }

    private function getAbsentTodayIds($tenantId): array
    {
        return Absence::where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->pluck('employee_id')
            ->unique()
            ->toArray();
    }

    private function getBaseStats($tenantId): array
    {
        return [
            'total_employees'  => Employee::where('tenant_id', $tenantId)->count(),
            'active_employees' => Employee::where('tenant_id', $tenantId)->where('status', 'active')->count(),
            'today_present'    => 0,
        ];
    }

    private function getPendingAbsencesCount($tenantId): int
    {
        return Absence::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();
    }

    private function getRecentPendingAbsences($tenantId)
    {
        return Absence::with(['employee' => fn($q) => $q->withoutGlobalScopes()])
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();
    }

    private function getContractTypeDistribution($tenantId)
    {
        return Employee::where('tenant_id', $tenantId)
            ->groupBy('contract_type')
            ->selectRaw('contract_type, count(*) as total')
            ->pluck('total', 'contract_type');
    }

    private function getDepartmentDistribution($tenantId)
    {
        return Employee::where('tenant_id', $tenantId)
            ->groupBy('department')
            ->selectRaw('department, count(*) as total')
            ->pluck('total', 'department');
    }

    private function getTodayPlanning($tenantId, array $absentTodayIds)
    {
        return Planning::with('employee')
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)
                  ->orWhereNull('tenant_id');
            })
            ->whereDate('date', today())
            ->whereNotIn('employee_id', $absentTodayIds)
            ->orderBy('shift_start')
            ->get()
            ->filter(fn($p) => $p->employee !== null);
    }

    private function getMonthlyAbsences($tenantId): array
    {
        $currentYear  = now()->year;
        $currentMonth = now()->month;
        $nextMonth    = $currentMonth + 1;

        $hasNextMonth = $nextMonth <= 12 && Absence::where('tenant_id', $tenantId)
            ->whereYear('start_date', $currentYear)
            ->whereMonth('start_date', $nextMonth)
            ->exists();

        $upToMonth = $hasNextMonth ? $nextMonth : $currentMonth;

        $monthlyAbsencesRaw = Absence::where('tenant_id', $tenantId)
            ->selectRaw('MONTH(start_date) as month_num, COUNT(*) as count')
            ->whereYear('start_date', $currentYear)
            ->whereMonth('start_date', '<=', $upToMonth)
            ->groupBy('month_num')
            ->orderBy('month_num', 'asc')
            ->get()
            ->keyBy('month_num');

        return collect(range(1, $upToMonth))->map(function ($month) use ($monthlyAbsencesRaw, $currentYear) {
            return [
                'month' => Carbon::create($currentYear, $month, 1)->locale('fr')->isoFormat('MMM'),
                'count' => (int) ($monthlyAbsencesRaw->get($month)?->count ?? 0),
            ];
        })->toArray();
    }

    private function getTodayBirthdays($tenantId)
    {
        $currentYear = now()->year;

        return Employee::with('user')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', now()->month)
            ->whereDay('birth_date', now()->day)
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
    }

    private function getUpcomingNews()
    {
        return News::active()
            ->whereDate('event_date', '>=', today())
            ->orderBy('event_date', 'asc')
            ->take(5)
            ->get();
    }

    private function getRecentNews()
    {
        return News::active()
            ->whereDate('event_date', today())
            ->orderBy('event_date', 'desc')
            ->take(3)
            ->get();
    }

    private function getAbsenceConflicts($tenantId): array
    {
        $approvedAbsences = Absence::with(['employee' => fn($q) => $q->withoutGlobalScopes()])
            ->where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->orderBy('employee_id')
            ->orderBy('start_date')
            ->get();

        $conflicts         = [];
        $currentEmployeeId = null;
        $employeeAbsences  = collect();

        foreach ($approvedAbsences as $absence) {
            if (!$absence->employee) continue;

            if ($absence->employee_id !== $currentEmployeeId) {
                if ($currentEmployeeId) {
                    $this->detectConflictsForEmployee($employeeAbsences, $currentEmployeeId, $conflicts);
                }
                $currentEmployeeId = $absence->employee_id;
                $employeeAbsences  = collect([$absence]);
            } else {
                $employeeAbsences->push($absence);
            }
        }

        if ($currentEmployeeId) {
            $this->detectConflictsForEmployee($employeeAbsences, $currentEmployeeId, $conflicts);
        }

        return $conflicts;
    }

    /**
     * Détecte les chevauchements d'absences pour un même employé.
     * Reprend exactement l'algorithme de la closure $processConflicts d'origine.
     */
    private function detectConflictsForEmployee($employeeAbsences, $currentEmployeeId, array &$conflicts): void
    {
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
                        'absence1'    => Absence::TYPES[$a->type] ?? $a->type,
                        'absence2'    => Absence::TYPES[$b->type] ?? $b->type,
                        'start'       => $overlapStart->format('d/m'),
                        'end'         => $overlapEnd->format('d/m/Y'),
                    ];
                }

                $nextIndex++;
            }
        }
    }

    private function resolveEmployee($user, $tenantId)
    {
        if (!$user) {
            return null;
        }

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

        return $employee;
    }

    private function getEmployeeWidgets($employee): array
    {
        if (!$employee || !$employee->id) {
            return [null, null];
        }

        $currentYear  = now()->year;
        $currentMonth = now()->month;

        $tempsWidget  = CompteurTemps::getOuCreeParMois($employee->id, $currentYear, $currentMonth);
        $droitsWidget = DroitAbsence::getOuCreeParAnnee($employee->id, $currentYear);

        return [$tempsWidget, $droitsWidget];
    }
}
