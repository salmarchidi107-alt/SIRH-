<?php

namespace App\Ai\Tools;

use App\Models\Planning;
use App\Models\Employee;
use Carbon\Carbon;

class PlanningTool
{
    public function name(): string
    {
        return 'planning_search';
    }

    public function description(): string
    {
        return 'Planning employé par matricule, date/semaine. Arguments: matricule (string), date (YYYY-MM-DD, optionnel)';
    }

    public function execute(array $arguments): string
    {
        $matricule = $arguments['matricule'] ?? '';
        $dateStr   = $arguments['date'] ?? now()->format('Y-m-d');

        // parse() en CarbonImmutable évite toute mutation accidentelle
        $date = Carbon::parse($dateStr)->copy();

        $employee = Employee::where('matricule', $matricule)->first();
        if (!$employee) {
            return "Matricule $matricule non trouvé.";
        }

        // copy() sur chaque borne pour ne pas muter $date
        $weekStart = $date->copy()->startOfWeek();   // lundi
        $weekEnd   = $date->copy()->endOfWeek();     // dimanche

        $plannings = Planning::where('employee_id', $employee->id)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->orderBy('date')
            ->get(['date', 'shift_start', 'shift_end', 'shift_type']);

        if ($plannings->isEmpty()) {
            return "Aucun planning trouvé pour {$employee->first_name} {$employee->last_name}"
                 . " (matricule $matricule)"
                 . " du {$weekStart->format('d/m/Y')} au {$weekEnd->format('d/m/Y')}.";
        }

        $result  = "📅 Planning **{$employee->first_name} {$employee->last_name}**";
        $result .= " (matricule $matricule)\n";
        $result .= "Semaine du {$weekStart->format('d/m')} → {$weekEnd->format('d/m')}\n\n";
        $result .= "| Date | Début | Fin | Type |\n";
        $result .= "|------|-------|-----|------|\n";

        foreach ($plannings as $plan) {
            $result .= sprintf(
                "| %s | %s | %s | %s |\n",
                Carbon::parse($plan->date)->format('d/m/Y'),
                $plan->shift_start ?? '-',
                $plan->shift_end   ?? '-',
                $plan->shift_type  ?? 'Libre'
            );
        }

        return $result;
    }
}