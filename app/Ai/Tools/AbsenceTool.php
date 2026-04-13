<?php

namespace App\Ai\Tools;

use App\Models\Absence;
use Carbon\Carbon;

class AbsenceTool
{
    public function name(): string
    {
        return 'absence_today';
    }

    public function description(): string
    {
        return 'Liste les absences en cours aujourd\'hui. Arguments: none';
    }

    public function execute(array $arguments): string
    {
        $today = Carbon::today();

        $absences = Absence::with('employee')
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->get();

        if ($absences->isEmpty()) {
            return "Aucune absence approuvée n'est enregistrée aujourd'hui ({$today->format('d/m/Y')}).";
        }

        $lines = ["Absences du jour ({$today->format('d/m/Y')}):"];

        foreach ($absences as $absence) {
            $employee = $absence->employee;
            $employeeName = $employee ? ($employee->first_name . ' ' . $employee->last_name) : 'Employé inconnu';
            $matricule = $employee->matricule ?? 'N/A';
            $type = $absence->type;
            $start = Carbon::parse($absence->start_date)->format('d/m');
            $end = Carbon::parse($absence->end_date)->format('d/m');

            $lines[] = "- $employeeName (matricule: $matricule) : $type du $start au $end";
        }

        return implode("\n", $lines);
    }
}
