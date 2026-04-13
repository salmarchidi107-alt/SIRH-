<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\DroitAbsence;
use App\Models\CompteurTemps;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class EmployeeObserver
{
    public function creating(Employee $employee)
    {
        // Générer PIN si vide
        if (empty($employee->plain_pin)) {
            $plainPin = sprintf('%04d%s', rand(1000, 9999), chr(rand(65, 90)).chr(rand(65, 90)));
            $employee->plain_pin = $plainPin;
            $employee->pin = Hash::make($plainPin);
        }
    }

    public function created(Employee $employee)
    {
        $this->updateAbsenceRights($employee);
        $this->updateCurrentMonthCounter($employee);
    }

    public function updated(Employee $employee)
    {
        if ($employee->wasChanged('hire_date') || $employee->wasChanged('status')) {
            $this->updateAbsenceRights($employee);
        }
    }

    protected function updateAbsenceRights(Employee $employee)
    {
        $annee = now()->year;
        
        $droits = DroitAbsence::firstOrCreate(
            ['employee_id' => $employee->id, 'annee' => $annee],
            ['jours_acquis' => 0, 'jours_pris' => 0, 'jours_en_attente' => 0, 'jours_solde' => 0]
        );

        // Calcul droits basés embauche
        $hireDate = Carbon::parse($employee->hire_date);
        $startYear = now()->startOfYear();
$endMonth = now();
$moisTravaillesAnnee = $hireDate->gt($endMonth) ? 0 : max(1, $hireDate->diffInMonths($endMonth) + 1);
        $anciennete = $hireDate->diffInYears(now());

        // 1.5j par mois dans l'année (max 25)
        $baseDroits = min(1.5 * $moisTravaillesAnnee, 25);
        
        // Bonus ancienneté +1j/an après 10 ans
        $bonus = $anciennete >= 10 ? $anciennete - 10 : 0;
        $totalDroits = $baseDroits + $bonus;

        $droits->jours_acquis = round($totalDroits, 1);
        $droits->jours_solde = $droits->jours_acquis - $droits->jours_pris - $droits->jours_en_attente;
        $droits->save();
    }

    protected function updateCurrentMonthCounter(Employee $employee)
    {
$annee = now()->year;
        $mois = now()->month;

        $compteur = CompteurTemps::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'annee' => $annee,
                'mois' => $mois,
            ],
            [
                'heures_planifiees' => 140, // 35h/sem * 4
                'heures_realisees' => 0,
                'heures_supplementaires' => 0,
                'solde_compteur' => 0,
            ]
        );

        // TODO : Update depuis pointages
    }
}

