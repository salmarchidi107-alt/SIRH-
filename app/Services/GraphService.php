<?php

namespace App\Services;

use App\DTOs\CompteurMoisDTO;
use App\Models\CompteurTemps;

class GraphService
{
    protected CompteurTemps $compteurTemps;

    public function __construct(CompteurTemps $compteurTemps)
    {
        $this->compteurTemps = $compteurTemps;
    }

    /**
     * Get monthly graph data for a specific employee and year.
     */
    public function getGraphiqueMois(int $employeeId, int $annee): array
    {
        $nomsMois = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        $compteurs = $employeeId
            ? $this->compteurTemps->where('employee_id', $employeeId)->where('annee', $annee)->get()->keyBy('mois')
            : collect();

        $donnees = [];
        for ($m = 1; $m <= 12; $m++) {
            $c = $compteurs->get($m);
            $donnees[] = [
                'mois'             => $nomsMois[$m - 1],
                'numero'           => $m,
                'heures_planifiees' => $c ? (float) $c->heures_planifiees : 0,
                'heures_realisees'  => $c ? (float) $c->heures_realisees  : 0,
                'heures_supp'       => $c ? (float) $c->heures_supplementaires : 0,
            ];
        }

        return $donnees;
    }

    /**
     * Get monthly graph data aggregated for a department and year.
     */
    public function getGraphiqueMoisDepartement(string $department, int $annee): array
    {
        $nomsMois  = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        $employeIds = \App\Models\Employee::where('department', $department)->pluck('id');

        $compteurs = $this->compteurTemps->whereIn('employee_id', $employeIds)
            ->where('annee', $annee)
            ->get()
            ->groupBy('mois');

        $donnees = [];
        for ($m = 1; $m <= 12; $m++) {
            $groupe = $compteurs->get($m, collect());
            $donnees[] = [
                'mois'             => $nomsMois[$m - 1],
                'numero'           => $m,
                'heures_planifiees' => round($groupe->sum('heures_planifiees'), 1),
                'heures_realisees'  => round($groupe->sum('heures_realisees'), 1),
                'heures_supp'       => round($groupe->sum('heures_supplementaires'), 1),
            ];
        }

        return $donnees;
    }
}
?>

