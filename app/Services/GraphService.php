<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Pointage;
use App\Models\Planning;
use Carbon\Carbon;

class GraphService
{
    const PAUSE_DEJEUNER = 1.0;

    // =========================================================================
    // EMPLOYE — 12 mois pour une année donnée
    // =========================================================================
    public function getGraphiqueMois(int $employeeId, int $annee): array
    {
        $moisLabels = ['Jan','Fév','Mar','Avr','Mai','Jun',
                       'Jul','Aoû','Sep','Oct','Nov','Déc'];

        // Charger toute l'année en une seule requête
        $debutAnnee = Carbon::create($annee, 1, 1)->startOfYear()->format('Y-m-d');
        $finAnnee   = Carbon::create($annee, 12, 31)->endOfYear()->format('Y-m-d');

        // Pointages de toute l'année
        $tousPointages = Pointage::where('employee_id', $employeeId)
            ->whereBetween('date', [$debutAnnee, $finAnnee])
            ->get();

        // Plannings de toute l'année — même filtre que PlanningService::getPlanningsBetween()
        $tousPlannings = Planning::where('employee_id', $employeeId)
            ->whereDate('date', '>=', $debutAnnee)
            ->whereDate('date', '<=', $finAnnee)
            ->whereNotNull('shift_start')
            ->whereNotNull('shift_end')
            ->get();

        $result = [];

        for ($m = 1; $m <= 12; $m++) {
            $debutMois = Carbon::create($annee, $m, 1)->startOfMonth();
            $finMois   = Carbon::create($annee, $m, 1)->endOfMonth();

            // Filtrer les pointages du mois
            $pointagesMois = $tousPointages->filter(function ($p) use ($debutMois, $finMois) {
                $date = Carbon::parse($p->date);
                return $date->between($debutMois, $finMois);
            });

            // Filtrer les plannings du mois
            $planningsMois = $tousPlannings->filter(function ($p) use ($debutMois, $finMois) {
                $date = Carbon::parse($p->date);
                return $date->between($debutMois, $finMois);
            });

            // Heures réalisées (directement du pointage, déjà nettes)
            $heuresRealisees = round((float) $pointagesMois->sum('heures_travaillees'), 1);
            $heuresSupp      = round((float) $pointagesMois->sum('heures_supplementaires'), 1);

            // Heures planifiées (shift - 1h pause)
            $heuresPlanifiees = 0.0;
            foreach ($planningsMois as $p) {
                $duree = $this->dureeShiftHeures(
                    Carbon::parse($p->date)->format('Y-m-d'),
                    $p->shift_start,
                    $p->shift_end
                );
                $heuresPlanifiees += max(0.0, $duree - self::PAUSE_DEJEUNER);
            }

            $result[] = [
                'mois'              => $moisLabels[$m - 1],
                'heures_planifiees' => round($heuresPlanifiees, 1),
                'heures_realisees'  => $heuresRealisees,
                'heures_supp'       => $heuresSupp,
            ];
        }

        return $result;
    }

    // =========================================================================
    // DEPARTEMENT — 12 mois pour une année donnée
    // =========================================================================
    public function getGraphiqueMoisDepartement(string $department, int $annee): array
    {
        $moisLabels = ['Jan','Fév','Mar','Avr','Mai','Jun',
                       'Jul','Aoû','Sep','Oct','Nov','Déc'];

        $empIds = Employee::where('department', $department)
            ->pluck('id')
            ->toArray();

        if (empty($empIds)) {
            return [];
        }

        $debutAnnee = Carbon::create($annee, 1, 1)->startOfYear()->format('Y-m-d');
        $finAnnee   = Carbon::create($annee, 12, 31)->endOfYear()->format('Y-m-d');

        // Une seule requête pour toute l'année, tous les employés du département
        $tousPointages = Pointage::whereIn('employee_id', $empIds)
            ->whereBetween('date', [$debutAnnee, $finAnnee])
            ->get();

        $tousPlannings = Planning::whereIn('employee_id', $empIds)
            ->whereDate('date', '>=', $debutAnnee)
            ->whereDate('date', '<=', $finAnnee)
            ->whereNotNull('shift_start')
            ->whereNotNull('shift_end')
            ->get();

        $result = [];

        for ($m = 1; $m <= 12; $m++) {
            $debutMois = Carbon::create($annee, $m, 1)->startOfMonth();
            $finMois   = Carbon::create($annee, $m, 1)->endOfMonth();

            $pointagesMois = $tousPointages->filter(function ($p) use ($debutMois, $finMois) {
                return Carbon::parse($p->date)->between($debutMois, $finMois);
            });

            $planningsMois = $tousPlannings->filter(function ($p) use ($debutMois, $finMois) {
                return Carbon::parse($p->date)->between($debutMois, $finMois);
            });
            // if($planningsMois->count()>0 && false)
            // dd($planningsMois[0]);
            $heuresRealisees = round((float) $pointagesMois->sum('heures_travaillees'), 1);
            $heuresSupp      = round((float) $pointagesMois->sum('heures_supplementaires'), 1);

            $heuresPlanifiees = 0.0;
            foreach ($planningsMois as $p) {
                $duree = $this->dureeShiftHeures(
                    Carbon::parse($p->date)->format('Y-m-d'),
                    $p->shift_start,
                    $p->shift_end
                );
                // if($planningsMois->count()>0 )
            // dd($duree, Carbon::parse($p->date)->format('Y-m-d'),
                    // $p->shift_start,
                    // $p->shift_end);
                $heuresPlanifiees += max(0.0, $duree - self::PAUSE_DEJEUNER);
            }

            //   if($planningsMois->count()>0 )
            // dd($heuresPlanifiees);
            $result[] = [
                'mois'              => $moisLabels[$m - 1],
                'heures_planifiees' => round($heuresPlanifiees, 1),
                'heures_realisees'  => $heuresRealisees,
                'heures_supp'       => $heuresSupp,
            ];
        }

        return $result;
    }

    // =========================================================================
    // HELPER — même logique que VueEnsembleController::dureeShiftHeures()
    // =========================================================================
    private function dureeShiftHeures(string $date, string $start, string $end): float
    {
        $d     = Carbon::parse($date);
        $debut = $d->copy()->setTimeFromTimeString($start);
        $fin   = $d->copy()->setTimeFromTimeString($end);
   
        // Passage minuit
        if ($fin->lte($debut)) {
            $fin->addDay();
        }

        // Cap à 24h max
        return min($debut->diffInMinutes($fin) / 60, 24.0);
    }
}