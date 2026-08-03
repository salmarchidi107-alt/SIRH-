<?php

namespace App\Services\Absence;

use App\Models\Absence;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;


class AbsenceCounterService
{
    // Durée d'un cycle de droits à congés, en mois
    private const CYCLE_MONTHS = 24;

    private const ABSENCE_TYPES_COUNTED = [
        'conge_annuel', 'conge_sans_solde', 'conge_maladie', 'absence_justifiee',
    ];

    public function __construct(
        private EmployeeFilterService $employeeFilterService,
    ) {}

    public function buildCountersData(Request $request): array
    {
        $employees = $this->getFilteredActiveEmployees($request);

        $requestedCycle = $request->filled('cycle') ? (int) $request->get('cycle') : null;
        $now            = Carbon::now();

        $countersData = [];

        foreach ($employees as $emp) {
            if (! $emp->hire_date) {
                continue;
            }

            $countersData[] = $this->buildCounterForEmployee($emp, $now, $requestedCycle);
        }

        return $countersData;
    }

    public function getMaxCycleNumber(Request $request): int
    {
        $query = Employee::active();
        $this->employeeFilterService->applyFilters($query, $request);
        $employees = $query->get(['hire_date']);

        $now = Carbon::now();
        $max = 1;

        foreach ($employees as $emp) {
            if (! $emp->hire_date) continue;
            $months = Carbon::parse($emp->hire_date)->diffInMonths($now);
            $max    = max($max, intdiv($months, self::CYCLE_MONTHS) + 1);
        }

        return $max;
    }

    private function getFilteredActiveEmployees(Request $request)
    {
        $query = Employee::active()->orderBy('department')->orderBy('last_name');
        $this->employeeFilterService->applyFilters($query, $request);

        return $query->get();
    }

    private function buildCounterForEmployee(Employee $emp, Carbon $now, ?int $requestedCycle): array
    {
        $hireDate = Carbon::parse($emp->hire_date);

        // Nombre total de mois travaillés depuis l'embauche jusqu'à aujourd'hui
        $totalMonths = $hireDate->diffInMonths($now);

        // Numéro du cycle EN COURS (1-indexé)
        $currentCycleNumber = intdiv($totalMonths, self::CYCLE_MONTHS) + 1;

        // Cycle demandé, sinon cycle en cours
        $cycleNumber = $requestedCycle ?? $currentCycleNumber;
        if ($cycleNumber < 1) {
            $cycleNumber = 1;
        }

        $cycleStart = $hireDate->copy()->addMonths(($cycleNumber - 1) * self::CYCLE_MONTHS);
        $cycleEnd   = $hireDate->copy()->addMonths($cycleNumber * self::CYCLE_MONTHS); // exclusive

        $isCompleted = $cycleNumber < $currentCycleNumber;
        $isCurrent   = $cycleNumber === $currentCycleNumber;
        $isFuture    = $cycleNumber > $currentCycleNumber;

        $monthsWorked = $this->computeMonthsWorked($isCompleted, $isCurrent, $totalMonths, $cycleNumber);
        $acquis       = round($monthsWorked * 1.5, 1);

        $taken = $this->getTakenDays($emp->id, $cycleStart, $cycleEnd, $cycleNumber, $emp->conges_anterieurs ?? 0);
        $pending = $this->getPendingDays($emp->id, $cycleStart, $cycleEnd);

        $solde = $acquis - $taken;

        return [
            'employee'          => $emp,
            'cycle_number'      => $cycleNumber,
            'cycle_start'       => $cycleStart,
            'cycle_end'         => $cycleEnd->copy()->subDay(),
            'is_completed'      => $isCompleted,
            'is_current'        => $isCurrent,
            'is_future'         => $isFuture,
            'months_worked'     => $monthsWorked,
            'acquis'            => $acquis,
            'taken'             => $taken,
            'conges_anterieurs' => (float) ($emp->conges_anterieurs ?? 0),
            'pending'           => $pending,
            'solde'             => $solde,
            'solde_if_pending'  => $solde - $pending,
        ];
    }

    private function computeMonthsWorked(bool $isCompleted, bool $isCurrent, int $totalMonths, int $cycleNumber): int
    {
        if ($isCompleted) {
            return self::CYCLE_MONTHS;
        }

        if ($isCurrent) {
            return $totalMonths - ($cycleNumber - 1) * self::CYCLE_MONTHS;
        }

        return 0;
    }

    private function getTakenDays(int $employeeId, Carbon $cycleStart, Carbon $cycleEnd, int $cycleNumber, float $congesAnterieurs): float
    {
        $taken = Absence::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereIn('type', self::ABSENCE_TYPES_COUNTED)
            ->whereDate('start_date', '>=', $cycleStart)
            ->whereDate('start_date', '<', $cycleEnd)
            ->sum('days');


        if ($cycleNumber === 1) {
            $taken += $congesAnterieurs;
        }

        return $taken;
    }

    private function getPendingDays(int $employeeId, Carbon $cycleStart, Carbon $cycleEnd): float
    {
        return Absence::where('employee_id', $employeeId)
            ->where('status', 'pending')
            ->whereDate('start_date', '>=', $cycleStart)
            ->whereDate('start_date', '<', $cycleEnd)
            ->sum('days');
    }
}
