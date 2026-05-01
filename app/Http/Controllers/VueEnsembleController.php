<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Pointage;
use App\Models\Planning;
use App\Models\CompteurTemps;
use App\Services\GraphService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VueEnsembleController extends Controller
{
    // 1h de pause dejeuner deduite uniquement des heures planifiees (le pointage est deja net)
    const PAUSE_DEJEUNER = 1.0;

    protected GraphService $graphService;

    public function __construct(GraphService $graphService)
    {
        $this->graphService = $graphService;
    }

    // =========================================================================
    // POINT D'ENTREE
    // =========================================================================

    public function index(Request $request)
    {
        $user       = Auth::user();
        $annee      = $this->validerAnnee($request->get('annee'));
        $mois       = $this->validerMois($request->get('mois'));
        $employeeId = $request->get('employee_id');
        $department = $request->get('department');

        $departments         = Department::names();
        $listeEmployesSelect = Employee::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'matricule', 'department']);

        $moisPrecedent = Carbon::create($annee, $mois, 1)->subMonth();
        $moisSuivant   = Carbon::create($annee, $mois, 1)->addMonth();

        // ---------------------------------------------------------------
        // MODE DEPARTEMENT
        // ---------------------------------------------------------------
        if ($department && !$employeeId) {
            $donnees = $this->getDonneesDepartement($department, $annee, $mois);

            // Jours planifies pour le popup calendrier (departement = union des plannings)
            $joursPlanningSemaine = $this->getJoursPlanningPopup(null, null, $department, $annee);

            return view('vue-ensemble.index', array_merge($donnees, [
                'modeDepartement'      => true,
                'employee'             => null,
                'departments'          => $departments,
                'listeEmployesSelect'  => $listeEmployesSelect,
                'annee'                => $annee,
                'mois'                 => $mois,
                'employeeId'           => null,
                'department'           => $department,
                'moisPrecedent'        => $moisPrecedent,
                'moisSuivant'          => $moisSuivant,
                'compteurMois'         => null,
                'joursDetails'         => [],
                'semaines'             => [],
                'graphiqueMois'        => [],
                'joursPlanningSemaine' => $joursPlanningSemaine,
            ]));
        }

        // ---------------------------------------------------------------
        // MODE EMPLOYE
        // ---------------------------------------------------------------
        $employee      = $this->resoudreEmployee($employeeId, $user);
        $compteurMois  = null;
        $joursDetails  = [];
        $semaines      = [];
        $graphiqueMois = [];

        if ($employee && $employee->id > 0) {
            $compteurMois  = $this->calculerCompteurMois($employee, $annee, $mois);
            $joursDetails  = $this->getJoursDetails($employee, $annee, $mois);
            $semaines      = $this->getSemainesDuMois($employee, $annee, $mois);
            $graphiqueMois = $this->graphService->getGraphiqueMois($employee->id, $annee);
        }

        // Jours planifies pour le popup calendrier (employe individuel)
        $joursPlanningSemaine = $this->getJoursPlanningPopup($employee, $employeeId, null, $annee);

        return view('vue-ensemble.index', [
            'modeDepartement'      => false,
            'employee'             => $employee,
            'departments'          => $departments,
            'listeEmployesSelect'  => $listeEmployesSelect,
            'compteurMois'         => $compteurMois,
            'joursDetails'         => $joursDetails,
            'semaines'             => $semaines,
            'graphiqueMois'        => $graphiqueMois,
            'annee'                => $annee,
            'mois'                 => $mois,
            'employeeId'           => $employeeId,
            'department'           => $department,
            'moisPrecedent'        => $moisPrecedent,
            'moisSuivant'          => $moisSuivant,
            'nomDepartement'       => null,
            'statsGlobalesDept'    => null,
            'employesDept'         => [],
            'graphiqueMoisDept'    => [],
            'semainerDept'         => [],
            'joursPlanningSemaine' => $joursPlanningSemaine,
        ]);
    }

    // =========================================================================
    // POPUP CALENDRIER — JOURS PLANIFIES
    // =========================================================================

    /**
     * Retourne tous les jours planifies de l'annee pour le popup calendrier.
     *
     * Format retourne :
     * [
     *   '2026-04-07' => ['shift_start' => '08:00:00', 'shift_end' => '17:00:00'],
     *   '2026-04-08' => ['shift_start' => '08:00:00', 'shift_end' => '17:00:00'],
     *   ...
     * ]
     *
     * En mode departement : union des plannings de tous les employes du dept.
     * En mode employe     : planning de l'employe uniquement.
     */
    private function getJoursPlanningPopup(?Employee $employee, $employeeId, ?string $department, int $annee): array
    {
        $debut = Carbon::create($annee, 1, 1)->startOfYear()->format('Y-m-d');
        $fin   = Carbon::create($annee, 12, 31)->endOfYear()->format('Y-m-d');

        $query = Planning::whereBetween('date', [$debut, $fin])
            ->whereNotNull('shift_start')
            ->whereNotNull('shift_end');

        if ($employeeId && $employee && $employee->id > 0) {
            // Mode employe individuel
            $query->where('employee_id', $employee->id);
        } elseif ($department) {
            // Mode departement : tous les employes du dept
            $empIds = Employee::where('department', $department)->pluck('id')->toArray();
            if (empty($empIds)) return [];
            $query->whereIn('employee_id', $empIds);
        } else {
            return [];
        }

        $plannings = $query->get(['date', 'shift_start', 'shift_end']);

        $result = [];
        foreach ($plannings as $p) {
            $dateStr = Carbon::parse($p->date)->format('Y-m-d');
            // En mode departement, on conserve le premier shift rencontre pour ce jour
            if (!isset($result[$dateStr])) {
                $result[$dateStr] = [
                    'shift_start' => $p->shift_start,
                    'shift_end'   => $p->shift_end,
                ];
            }
        }

        return $result;
    }

    // =========================================================================
    // CALCUL HEURES PLANIFIEES (avec pause dejeuner)
    // =========================================================================

    /**
     * Calcule les heures planifiees depuis le planning pour une periode donnee.
     * Regle : duree shift - 1h pause pour chaque jour planifie.
     */
    private function calculerHeuresPlanifiees(int $employeeId, string $debut, string $fin): float
    {
        $plannings = Planning::where('employee_id', $employeeId)
            ->whereBetween('date', [$debut, $fin])
            ->whereNotNull('shift_start')
            ->whereNotNull('shift_end')
            ->get();

        $total = 0.0;
        foreach ($plannings as $p) {
            $duree = $this->dureeShiftHeures(
                Carbon::parse($p->date)->format('Y-m-d'),
                $p->shift_start,
                $p->shift_end
            );
            if ($duree > 0) {
                $total += max(0.0, $duree - self::PAUSE_DEJEUNER);
            }
        }

        return round($total, 2);
    }

    /**
     * Calcule la duree d'un shift en heures (gestion passage minuit, cap 24h).
     */
    private function dureeShiftHeures(string $date, string $start, string $end): float
    {
        $d     = Carbon::parse($date);
        $debut = $d->copy()->setTimeFromTimeString($start);
        $fin   = $d->copy()->setTimeFromTimeString($end);

        if ($fin->lte($debut)) {
            $fin->addDay(); // passage minuit
        }

        return min($debut->diffInMinutes($fin) / 60, 24.0);
    }

    // =========================================================================
    // MODE EMPLOYE
    // =========================================================================

    private function resoudreEmployee($employeeId, $user): Employee
    {
        if ($employeeId) {
            $emp = Employee::find($employeeId);
            if ($emp) return $emp;
        }

        if ($user) {
            $emp = Employee::where('user_id', $user->id)->first()
                ?? Employee::where('email', $user->email)->first();
            if ($emp) return $emp;
        }

        $ghost                = new Employee();
        $ghost->id            = 0;
        $ghost->first_name    = $user ? $user->name : 'Utilisateur';
        $ghost->last_name     = '';
        $ghost->position      = 'Employe';
        $ghost->department    = '';
        $ghost->contract_type = 'CDI';
        $ghost->work_hours    = 35;
        $ghost->work_days     = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];
        return $ghost;
    }

    /**
     * Calcule et persiste le compteur mensuel d'un employe.
     * Heures realisees = somme pointage (deja nettes)
     * Heures planifiees = planning - pause dejeuner
     */
    private function calculerCompteurMois(Employee $employee, int $annee, int $mois): object
    {
        $debut = Carbon::create($annee, $mois, 1)->startOfMonth()->format('Y-m-d');
        $fin   = Carbon::create($annee, $mois, 1)->endOfMonth()->format('Y-m-d');

        $pointages = Pointage::where('employee_id', $employee->id)
            ->whereBetween('date', [$debut, $fin])
            ->get();

        $heuresRealisees       = round((float) $pointages->sum('heures_travaillees'), 2);
        $heuresSupplementaires = round((float) $pointages->sum('heures_supplementaires'), 2);
        $heuresPlanifiees      = $this->calculerHeuresPlanifiees($employee->id, $debut, $fin);
        $ecart                 = ($heuresRealisees + $heuresSupplementaires) - $heuresPlanifiees;
        $joursTravailles       = $pointages->filter(fn($p) => (float) $p->heures_travaillees > 0)->count();
        $taux                  = $heuresPlanifiees > 0
            ? round(($heuresRealisees / $heuresPlanifiees) * 100)
            : 0;

        CompteurTemps::updateOrCreate(
            ['employee_id' => $employee->id, 'annee' => $annee, 'mois' => $mois],
            [
                'heures_planifiees'      => $heuresPlanifiees,
                'heures_realisees'       => $heuresRealisees,
                'heures_supplementaires' => $heuresSupplementaires,
                'solde_compteur'         => $ecart,
            ]
        );

        return (object) [
            'heures_planifiees'      => $heuresPlanifiees,
            'heures_realisees'       => $heuresRealisees,
            'heures_supplementaires' => $heuresSupplementaires,
            'ecart'                  => round($ecart, 2),
            'taux_realisation'       => $taux,
            'jours_travailles'       => $joursTravailles,
        ];
    }

    /**
     * Retourne le detail jour par jour pour l'employe sur le mois.
     */
    private function getJoursDetails(Employee $employee, int $annee, int $mois): array
    {
        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin   = Carbon::create($annee, $mois, 1)->endOfMonth();

        $pointages = Pointage::where('employee_id', $employee->id)
            ->whereBetween('date', [$debut->format('Y-m-d'), $fin->format('Y-m-d')])
            ->get()
            ->keyBy(fn($p) => Carbon::parse($p->date)->format('Y-m-d'));

        $plannings = Planning::where('employee_id', $employee->id)
            ->whereBetween('date', [$debut->format('Y-m-d'), $fin->format('Y-m-d')])
            ->get()
            ->keyBy(fn($p) => Carbon::parse($p->date)->format('Y-m-d'));

        $jours = [];

        foreach (CarbonPeriod::create($debut, $fin) as $current) {
            $dateStr  = $current->format('Y-m-d');
            $pointage = $pointages->get($dateStr);
            $planning = $plannings->get($dateStr);

            $planJour = 0.0;
            if ($planning && $planning->shift_start && $planning->shift_end) {
                $duree    = $this->dureeShiftHeures($dateStr, $planning->shift_start, $planning->shift_end);
                $planJour = max(0.0, $duree - self::PAUSE_DEJEUNER);
            }

            $realJour = $pointage ? round((float) $pointage->heures_travaillees, 2) : 0.0;
            $suppJour = $pointage ? round((float) $pointage->heures_supplementaires, 2) : 0.0;

            if ($current->isWeekend()) {
                $statut = 'weekend';
            } elseif ($pointage && $realJour > 0) {
                $statut = 'present';
            } elseif ($pointage && $realJour == 0) {
                $statut = 'absent';
            } elseif ($planning && $planning->shift_start) {
                $statut = $current->isFuture() ? 'planifie' : 'absent';
            } else {
                $statut = 'non_planifie';
            }

            $jours[] = [
                'date'                   => $dateStr,
                'jour'                   => $current->format('d'),
                'nom_jour'               => $current->locale('fr')->shortDayName,
                'nom_jour_complet'       => $current->locale('fr')->dayName,
                'is_weekend'             => $current->isWeekend(),
                'is_today'               => $current->isToday(),
                'heures_planifiees'      => round($planJour, 2),
                'heures_realisees'       => $realJour,
                'heures_supplementaires' => $suppJour,
                'total'                  => round($realJour + $suppJour, 2),
                'ecart'                  => round($realJour - $planJour, 2),
                'statut'                 => $statut,
                'heure_entree'           => $pointage->heure_entree ?? null,
                'heure_sortie'           => $pointage->heure_sortie ?? null,
                'shift_start'            => $planning->shift_start ?? null,
                'shift_end'              => $planning->shift_end ?? null,
            ];
        }

        return $jours;
    }

    /**
     * Retourne les semaines du mois avec les totaux planifies/realises.
     */
    private function getSemainesDuMois(Employee $employee, int $annee, int $mois): array
    {
        $debutMois = Carbon::create($annee, $mois, 1)->startOfMonth();
        $finMois   = Carbon::create($annee, $mois, 1)->endOfMonth();

        $pointagesMois = Pointage::where('employee_id', $employee->id)
            ->whereBetween('date', [$debutMois->format('Y-m-d'), $finMois->format('Y-m-d')])
            ->get();

        $planningsMois = Planning::where('employee_id', $employee->id)
            ->whereBetween('date', [$debutMois->format('Y-m-d'), $finMois->format('Y-m-d')])
            ->whereNotNull('shift_start')
            ->whereNotNull('shift_end')
            ->get();

        $semaines = [];
        $current  = $debutMois->copy()->startOfWeek(Carbon::MONDAY);
        $numSem   = 1;

        while ($current->lte($finMois)) {
            $debutSem = $current->copy();
            $finSem   = $current->copy()->endOfWeek(Carbon::SUNDAY);

            $ptsSem  = $pointagesMois->filter(fn($p) => Carbon::parse($p->date)->between($debutSem, $finSem));
            $planSem = $planningsMois->filter(fn($p) => Carbon::parse($p->date)->between($debutSem, $finSem));

            $planifieesSem = 0.0;
            foreach ($planSem as $p) {
                $duree = $this->dureeShiftHeures(
                    Carbon::parse($p->date)->format('Y-m-d'),
                    $p->shift_start,
                    $p->shift_end
                );
                $planifieesSem += max(0.0, $duree - self::PAUSE_DEJEUNER);
            }

            $realiseesSem = round((float) $ptsSem->sum('heures_travaillees'), 2);
            $suppSem      = round((float) $ptsSem->sum('heures_supplementaires'), 2);
            $totalSem     = $realiseesSem + $suppSem;
            $soldeSem     = $totalSem - $planifieesSem;
            $joursTrav    = $ptsSem->filter(fn($p) => (float) $p->heures_travaillees > 0)->count();
            $taux         = $planifieesSem > 0 ? round(($realiseesSem / $planifieesSem) * 100) : 0;

            $semaines[] = [
                'numero'                 => $numSem,
                'debut'                  => $debutSem->format('d/m'),
                'fin'                    => $finSem->format('d/m'),
                'heures_planifiees'      => round($planifieesSem, 2),
                'heures_realisees'       => $realiseesSem,
                'heures_supplementaires' => $suppSem,
                'total'                  => round($totalSem, 2),
                'solde'                  => round($soldeSem, 2),
                'jours_travailles'       => $joursTrav,
                'taux'                   => $taux,
            ];

            $current->addWeek();
            $numSem++;
        }

        return $semaines;
    }

    // =========================================================================
    // MODE DEPARTEMENT
    // =========================================================================

    private function getDonneesDepartement(string $department, int $annee, int $mois): array
    {
        $debut = Carbon::create($annee, $mois, 1)->startOfMonth()->format('Y-m-d');
        $fin   = Carbon::create($annee, $mois, 1)->endOfMonth()->format('Y-m-d');

        $employes = Employee::where('department', $department)->get();
        $empIds   = $employes->pluck('id')->toArray();

        $tousPointages = Pointage::whereIn('employee_id', $empIds)
            ->whereBetween('date', [$debut, $fin])
            ->get()
            ->groupBy('employee_id');

        $tousPlannings = Planning::whereIn('employee_id', $empIds)
            ->whereBetween('date', [$debut, $fin])
            ->whereNotNull('shift_start')
            ->whereNotNull('shift_end')
            ->get()
            ->groupBy('employee_id');

        $totalPlanifiees      = 0.0;
        $totalRealisees       = 0.0;
        $totalSupplementaires = 0.0;
        $employesDept         = [];

        foreach ($employes as $emp) {
            $empPointages = $tousPointages->get($emp->id, collect());
            $empPlannings = $tousPlannings->get($emp->id, collect());

            $planifiees = 0.0;
            foreach ($empPlannings as $p) {
                $duree = $this->dureeShiftHeures(
                    Carbon::parse($p->date)->format('Y-m-d'),
                    $p->shift_start,
                    $p->shift_end
                );
                $planifiees += max(0.0, $duree - self::PAUSE_DEJEUNER);
            }
            $planifiees = round($planifiees, 2);

            $realisees = round((float) $empPointages->sum('heures_travaillees'), 2);
            $supp      = round((float) $empPointages->sum('heures_supplementaires'), 2);
            $ecart     = ($realisees + $supp) - $planifiees;
            $taux      = $planifiees > 0 ? round(($realisees / $planifiees) * 100) : 0;

            $totalPlanifiees      += $planifiees;
            $totalRealisees       += $realisees;
            $totalSupplementaires += $supp;

            $employesDept[] = [
                'id'         => $emp->id,
                'nom'        => $emp->first_name . ' ' . $emp->last_name,
                'initiales'  => strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)),
                'poste'      => $emp->position ?? 'Employe',
                'contrat'    => $emp->contract_type ?? 'CDI',
                'planifiees' => $planifiees,
                'realisees'  => $realisees,
                'supp'       => $supp,
                'ecart'      => round($ecart, 2),
                'taux'       => $taux,
            ];
        }

        $ecartGlobal = $totalRealisees - $totalPlanifiees;

        $statsGlobalesDept = (object) [
            'nb_employes'            => $employes->count(),
            'heures_planifiees'      => round($totalPlanifiees, 2),
            'heures_realisees'       => round($totalRealisees, 2),
            'heures_supplementaires' => round($totalSupplementaires, 2),
            'ecart'                  => round($ecartGlobal, 2),
            'taux_realisation'       => $totalPlanifiees > 0
                ? round(($totalRealisees / $totalPlanifiees) * 100)
                : 0,
        ];

        $graphiqueMoisDept = $this->graphService->getGraphiqueMoisDepartement($department, $annee);
        $semainerDept      = $this->getSemainesDepartement($empIds, $annee, $mois);

        return [
            'nomDepartement'    => $department,
            'statsGlobalesDept' => $statsGlobalesDept,
            'employesDept'      => $employesDept,
            'graphiqueMoisDept' => $graphiqueMoisDept,
            'semainerDept'      => $semainerDept,
        ];
    }

    private function getSemainesDepartement(array $empIds, int $annee, int $mois): array
    {
        $debutMois = Carbon::create($annee, $mois, 1)->startOfMonth();
        $finMois   = Carbon::create($annee, $mois, 1)->endOfMonth();

        $tousPointages = Pointage::whereIn('employee_id', $empIds)
            ->whereBetween('date', [$debutMois->format('Y-m-d'), $finMois->format('Y-m-d')])
            ->get();

        $tousPlannings = Planning::whereIn('employee_id', $empIds)
            ->whereBetween('date', [$debutMois->format('Y-m-d'), $finMois->format('Y-m-d')])
            ->whereNotNull('shift_start')
            ->whereNotNull('shift_end')
            ->get();

        $semaines = [];
        $current  = $debutMois->copy()->startOfWeek(Carbon::MONDAY);
        $numSem   = 1;

        while ($current->lte($finMois)) {
            $debutSem = $current->copy();
            $finSem   = $current->copy()->endOfWeek(Carbon::SUNDAY);

            $ptsSem  = $tousPointages->filter(fn($p) => Carbon::parse($p->date)->between($debutSem, $finSem));
            $planSem = $tousPlannings->filter(fn($p) => Carbon::parse($p->date)->between($debutSem, $finSem));

            $planifieesSem = 0.0;
            foreach ($planSem as $p) {
                $duree = $this->dureeShiftHeures(
                    Carbon::parse($p->date)->format('Y-m-d'),
                    $p->shift_start,
                    $p->shift_end
                );
                $planifieesSem += max(0.0, $duree - self::PAUSE_DEJEUNER);
            }

            $realiseesSem = round((float) $ptsSem->sum('heures_travaillees'), 2);
            $suppSem      = round((float) $ptsSem->sum('heures_supplementaires'), 2);
            $totalSem     = $realiseesSem + $suppSem;
            $soldeSem     = $totalSem - $planifieesSem;

            $semaines[] = [
                'numero'                 => $numSem,
                'debut'                  => $debutSem->format('d/m'),
                'fin'                    => $finSem->format('d/m'),
                'heures_planifiees'      => round($planifieesSem, 2),
                'heures_realisees'       => $realiseesSem,
                'heures_supplementaires' => $suppSem,
                'total'                  => round($totalSem, 2),
                'solde'                  => round($soldeSem, 2),
            ];

            $current->addWeek();
            $numSem++;
        }

        return $semaines;
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function validerAnnee($val): int
    {
        $v = (int) $val;
        return ($v >= 2000 && $v <= 2100) ? $v : now()->year;
    }

    private function validerMois($val): int
    {
        $v = (int) $val;
        return ($v >= 1 && $v <= 12) ? $v : now()->month;
    }
}