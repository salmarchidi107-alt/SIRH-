<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\DroitAbsence;
use App\Models\CompteurTemps;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateAbsencesCommand extends Command
{
    protected $signature = 'absences:calculate-all';
    protected $description = 'Calcule automatiquement droits absence et compteurs temps pour tous employés';

    public function handle()
    {
        $annee = now()->year;
        $this->info("Calcul droits absence & compteurs - Année {$annee}");

        $employees = Employee::active()->cursor();

        foreach ($employees as $employee) {
            // Droits absence
            $droits = DroitAbsence::updateOrCreate(
                ['employee_id' => $employee->id, 'annee' => $annee],
                ['jours_pris' => 0, 'jours_en_attente' => 0]
            );

            $hireDate = Carbon::parse($employee->hire_date);
$startYear = now()->startOfYear();
$endMonth = now();
            $moisTravaillesAnnee = $hireDate->gt($endMonth) ? 0 : max(1, $hireDate->diffInMonths($endMonth) + 1);
$baseDroits = min(1.5 * $moisTravaillesAnnee, 25); // 1.5j/mois année courante
            $anciennete = $hireDate->diffInYears(now());
            $bonus = $anciennete >= 10 ? $anciennete - 10 : 0;
            $droits->jours_acquis = round($baseDroits + $bonus, 1);

            // Sync from actual absences
            $pris = \App\Models\Absence::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->whereYear('start_date', $annee)
                ->whereIn('type', ['conge_annuel', 'conge_sans_solde', 'conge_maladie', 'absence_justifiee'])
                ->sum('days');
            $droits->jours_pris = $pris;

            $attente = \App\Models\Absence::where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->whereYear('start_date', $annee)
                ->sum('days');
            $droits->jours_en_attente = $attente;

            $droits->jours_solde = $droits->jours_acquis - $droits->jours_pris - $droits->jours_en_attente;
            $droits->save();

            // Compteur temps courant
            $mois = now()->month;
            $compteur = CompteurTemps::updateOrCreate(
                [
                    'tenant_id' => $employee->tenant_id,
                    'employee_id' => $employee->id,
                    'annee' => $annee,
                    'mois' => $mois,
                ],
                ['heures_planifiees' => 140]
            );

            $this->line("✅ {$employee->full_name}: {$droits->jours_acquis}j acquis, solde {$droits->jours_solde}j");
        }

        $this->info('🎉 Calculs terminés !');
    }
}

