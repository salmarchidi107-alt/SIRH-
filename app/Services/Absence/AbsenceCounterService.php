<?php

namespace App\Services\Absence;

use App\Models\Absence;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AbsenceCounterService
{
    // Durée d'une période de droits à congés, en mois — une fois écoulée,
    // les compteurs repartent automatiquement à zéro pour la période suivante.
    private const PERIOD_MONTHS = 24;

    private const ABSENCE_TYPES_COUNTED = [
        'conge_annuel', 'conge_sans_solde', 'conge_maladie', 'absence_justifiee',
    ];

    public function __construct(
        private EmployeeFilterService $employeeFilterService,
    ) {}

    public function buildCountersData(Request $request): array
    {
        $employees = $this->getFilteredActiveEmployees($request);
        $now       = Carbon::now();

        $countersData = [];

        foreach ($employees as $emp) {
            if (! $emp->hire_date) {
                continue;
            }

            $countersData[] = $this->buildCounterForEmployee($emp, $now);
        }

        return $countersData;
    }

    private function getFilteredActiveEmployees(Request $request)
    {
        $query = Employee::active()->orderBy('department')->orderBy('last_name');
        $this->employeeFilterService->applyFilters($query, $request);

        return $query->get();
    }

    private function buildCounterForEmployee(Employee $emp, Carbon $now): array
    {
        $hireDate = Carbon::parse($emp->hire_date);

        // Nombre total de mois travaillés depuis l'embauche jusqu'à aujourd'hui
        $totalMonths = $hireDate->diffInMonths($now);

        // Index de la période EN COURS (0-indexé) : bascule automatiquement
        // tous les 24 mois depuis la date d'embauche, sans intervention utilisateur
        $periodIndex = intdiv($totalMonths, self::PERIOD_MONTHS);

        $periodStart = $hireDate->copy()->addMonths($periodIndex * self::PERIOD_MONTHS);
        $periodEnd   = $periodStart->copy()->addMonths(self::PERIOD_MONTHS); // exclusive

        // Mois écoulés depuis le début de la période en cours
        $monthsWorked = $totalMonths - $periodIndex * self::PERIOD_MONTHS;
        $acquis       = round($monthsWorked * 1.5, 1);

        $isFirstPeriod = $periodIndex === 0;

        $taken   = $this->getTakenDays($emp->id, $periodStart, $periodEnd, $isFirstPeriod, $emp->conges_anterieurs ?? 0);
        $pending = $this->getPendingDays($emp->id, $periodStart, $periodEnd);

        $solde = $acquis - $taken;

        return [
            'employee'          => $emp,
            'period_start'      => $periodStart,
            'period_end'        => $periodEnd->copy()->subDay(),
            'months_worked'     => $monthsWorked,
            'acquis'            => $acquis,
            'taken'             => $taken,
            'conges_anterieurs' => (float) ($emp->conges_anterieurs ?? 0),
            'pending'           => $pending,
            'solde'             => $solde,
            'solde_if_pending'  => $solde - $pending,
        ];
    }

    private function getTakenDays(int $employeeId, Carbon $periodStart, Carbon $periodEnd, bool $isFirstPeriod, float $congesAnterieurs): float
    {
        $taken = Absence::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereIn('type', self::ABSENCE_TYPES_COUNTED)
            ->whereDate('start_date', '>=', $periodStart)
            ->whereDate('start_date', '<', $periodEnd)
            ->sum('days');

        if ($isFirstPeriod) {
            $taken += $congesAnterieurs;
        }

        return $taken;
    }

    private function getPendingDays(int $employeeId, Carbon $periodStart, Carbon $periodEnd): float
    {
        return Absence::where('employee_id', $employeeId)
            ->where('status', 'pending')
            ->whereDate('start_date', '>=', $periodStart)
            ->whereDate('start_date', '<', $periodEnd)
            ->sum('days');
    }
}
