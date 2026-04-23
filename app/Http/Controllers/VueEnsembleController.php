<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Pointage;
use App\Models\CompteurTemps;
use App\DTOs\CompteurMoisDTO;
use App\Services\GraphService;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class VueEnsembleController extends Controller
{
    protected GraphService $graphService;

    public function __construct(GraphService $graphService)
    {
        $this->graphService = $graphService;
    }
    public function index(Request $request)
    {
        $user       = Auth::user();
        $annee      = $this->validerAnnee($request->get('annee'));
        $mois       = $this->validerMois($request->get('mois'));
        $employeeId = $request->get('employee_id');
        $department = $request->get('department');

        $departments        = Department::names();
        $listeEmployesSelect = Employee::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'matricule', 'department']);

        // ── Mode employé par défaut, département conditionnel avec when() ──
        $employee = $this->resoudreEmployee($employeeId, $user);

        $compteurMois = null;
        $joursDetails = [];
        $semaines     = [];

        $graphiqueMois = $this->graphService->getGraphiqueMois($employee->id ?? 0, $annee);
        $moisPrecedent = Carbon::create($annee, $mois, 1)->subMonth();
        $moisSuivant   = Carbon::create($annee, $mois, 1)->addMonth();

        if ($employee && $employee->id > 0) {
            $compteurMois = $this->recalculerCompteurMois($employee, $annee, $mois);
            $joursDetails = $this->getJoursDetails($employee->id, $annee, $mois);
            $semaines     = $this->getSemainesDuMois($employee, $annee, $mois);
        } else {
            $compteurMois = $this->getDefaultCompteur();
        }

        $viewData = [
            'modeDepartement'    => false,
            'employee'           => $employee,
            'listeEmployesSelect' => $listeEmployesSelect,
            'departments'        => $departments,
            'compteurMois'       => $compteurMois,
            'joursDetails'       => $joursDetails,
            'semaines'           => $semaines,
            'graphiqueMois'      => $graphiqueMois,
            'annee'              => $annee,
            'mois'               => $mois,
            'employeeId'         => $employeeId,
            'department'         => $department,
            'moisPrecedent'      => $moisPrecedent,
            'moisSuivant'        => $moisSuivant,
            'nomDepartement'     => null,
            'statsGlobalesDept'  => null,
            'employesDept'       => [],
            'graphiqueMoisDept'  => [],
            'semainerDept'       => [],
        ];

        if ($department && !$employeeId) {
            $donneesDept = $this->getDonneesDepartement($department, $annee, $mois);
            $viewData = array_merge($viewData, $donneesDept, [
                'modeDepartement'    => true,
                'employee'           => null,
                'joursDetails'       => [],
                'semaines'           => [],
                'employeeId'         => null,
            ]);
        }

        return view('vue-ensemble.index', $viewData);
    }

    // ════════════════════════════════════════════════════════════════════════
    // VUE DÉPARTEMENT
    // ════════════════════════════════════════════════════════════════════════

    private function getDonneesDepartement(string $department, int $annee, int $mois): array
    {
        $employes = Employee::where('department', $department)->get();

        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin   = Carbon::create($annee, $mois, 1)->endOfMonth();

        // Batch load all pointages for department to fix N+1
        $pointagesDept = Pointage::whereIn('employee_id', $employes->pluck('id'))
            ->whereBetween('date', [$debut->format('Y-m-d'), $fin->format('Y-m-d')])
            ->get()
            ->groupBy('employee_id');

        $totalPlanifiees      = 0;
        $totalRealisees       = 0;
        $totalSupplementaires = 0;
        $employesDept         = [];

        foreach ($employes as $emp) {
            // Compute from batched pointages (no DB query)
            $empPointages = $pointagesDept->get($emp->id, collect());
            $heuresRealisees = $empPointages->sum('heures_travaillees');
            $heuresSupplementaires = $empPointages->sum('heures_supplementaires');
            $heuresPlanifiees = $this->calculerHeuresPlanifiees($emp, $annee, $mois);
            $ecart = ($heuresRealisees + $heuresSupplementaires) - $heuresPlanifiees;

            $totalPlanifiees      += $heuresPlanifiees;
            $totalRealisees       += $heuresRealisees;
            $totalSupplementaires += $heuresSupplementaires;

            $compteur = (object) [
                'heures_planifiees' => $heuresPlanifiees,
                'heures_realisees' => $heuresRealisees,
                'heures_supplementaires' => $heuresSupplementaires,
                'ecart' => $ecart,
            ];

            $employesDept[] = [
                'id'          => $emp->id,
                'nom'         => $emp->first_name . ' ' . $emp->last_name,
                'initiales'   => strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)),
'poste'       => $emp->position ?? 'Employé',
                'contrat'     => $emp->contract_type ?? 'CDI',
                'work_hours'  => $emp->work_hours ?? 35,
                'planifiees'  => round($compteur->heures_planifiees, 1),
                'realisees'   => round($compteur->heures_realisees, 1),
                'supp'        => round($compteur->heures_supplementaires, 1),
                'ecart'       => round($compteur->ecart, 1),
                'taux'        => $compteur->heures_planifiees > 0
                    ? round(($compteur->heures_realisees / $compteur->heures_planifiees) * 100)
                    : 0,
            ];
        }

        $ecartGlobal = $totalRealisees - $totalPlanifiees;

        $statsGlobalesDept = (object) [
            'nb_employes'            => $employes->count(),
            'heures_planifiees'      => round($totalPlanifiees, 1),
            'heures_realisees'       => round($totalRealisees, 1),
            'heures_supplementaires' => round($totalSupplementaires, 1),
            'ecart'                  => round($ecartGlobal, 1),
            'taux_realisation'       => $totalPlanifiees > 0
                ? round(($totalRealisees / $totalPlanifiees) * 100)
                : 0,
        ];

        // Graphique annuel agrégé du département
        $graphiqueMoisDept = $this->graphService->getGraphiqueMoisDepartement($department, $annee);

        // Semaines agrégées du département
        $semainerDept = $this->getSemainesDepartement($employes, $annee, $mois);

        return [
            'nomDepartement'    => $department,
            'statsGlobalesDept' => $statsGlobalesDept,
            'employesDept'      => $employesDept,
            'graphiqueMoisDept' => $graphiqueMoisDept,
            'semainerDept'      => $semainerDept,
            'graphiqueMois'     => $graphiqueMoisDept, // pour le script JS commun
            'compteurMois'      => $statsGlobalesDept,
        ];
    }



    private function getSemainesDepartement($employes, int $annee, int $mois): array
    {
        $debutMois = Carbon::create($annee, $mois, 1);
        $finMois   = Carbon::create($annee, $mois, 1)->endOfMonth();
        $employeIds = $employes->pluck('id')->toArray();

        // Une seule requête pour tous les employés du département
        $tousPointages = Pointage::whereIn('employee_id', $employeIds)
            ->whereBetween('date', [$debutMois->format('Y-m-d'), $finMois->format('Y-m-d')])
            ->get();

        $semaines = [];
        $current  = $debutMois->copy()->startOfWeek(Carbon::MONDAY);
        $numSem   = 1;

        // Heures planifiées agrégées : somme des work_hours de tous les employés
        $totalWorkHours = $employes->sum('work_hours') ?: ($employes->count() * 35);

        while ($current->lte($finMois)) {
            $debutSem = $current->copy();
            $finSem   = $current->copy()->endOfWeek(Carbon::SUNDAY);

            $pointagesSemaine = $tousPointages->filter(function ($p) use ($debutSem, $finSem) {
                $d = Carbon::parse($p->date);
                return $d->between($debutSem, $finSem);
            });

            $heuresTravaillees = (float) $pointagesSemaine->sum('heures_travaillees');
            $heuresSupp        = (float) $pointagesSemaine->sum('heures_supplementaires');
            $total             = $heuresTravaillees + $heuresSupp;

            $semaines[] = [
                'numero'            => $numSem,
                'debut'             => $debutSem->format('d/m'),
                'fin'               => $finSem->format('d/m'),
                'heures_planifiees' => $totalWorkHours,
                'heures_realisees'  => round($heuresTravaillees, 2),
                'heures_supplementaires' => round($heuresSupp, 2),
                'total'             => round($total, 2),
                'solde'             => round($total - $totalWorkHours, 2),
            ];

            $current->addWeek();
            $numSem++;
        }

        return $semaines;
    }

    // ════════════════════════════════════════════════════════════════════════
    // VUE EMPLOYÉ INDIVIDUEL
    // ════════════════════════════════════════════════════════════════════════

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

        // Employé fantôme (pas de données en BDD)
        $ghost = new Employee();
        $ghost->id            = 0;
        $ghost->first_name    = $user ? $user->name : 'Utilisateur';
        $ghost->last_name     = '';
        $ghost->position      = 'Employé';
        $ghost->department    = '';
        $ghost->contract_type = 'CDI';
        $ghost->work_hours    = 35;
        $ghost->work_days     = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];
        return $ghost;
    }

    private function recalculerCompteurMois(Employee $employee, int $annee, int $mois): object
    {
        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin   = Carbon::create($annee, $mois, 1)->endOfMonth();

        $pointages = Pointage::where('employee_id', $employee->id)
            ->whereBetween('date', [$debut->format('Y-m-d'), $fin->format('Y-m-d')])
            ->get();

        $heuresRealisees       = (float) $pointages->sum('heures_travaillees');
        $heuresSupplementaires = (float) $pointages->sum('heures_supplementaires');
        $heuresPlanifiees      = $this->calculerHeuresPlanifiees($employee, $annee, $mois);
        $ecart                 = ($heuresRealisees + $heuresSupplementaires) - $heuresPlanifiees;

        $compteur = CompteurTemps::updateOrCreate(
            ['tenant_id' => $employee->tenant_id, 'employee_id' => $employee->id, 'annee' => $annee, 'mois' => $mois],
            [
                'heures_planifiees'      => $heuresPlanifiees,
                'heures_realisees'       => $heuresRealisees,
                'heures_supplementaires' => $heuresSupplementaires,
                'solde_compteur'         => $ecart,
            ]
        );
        return CompteurMoisDTO::fromModel($compteur);
    }

    private function calculerHeuresPlanifiees(Employee $employee, int $annee, int $mois): float
    {
        $workHours = (float) ($employee->work_hours ?? 35);
        $workDays  = $employee->work_days ?? ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];

        if (is_string($workDays)) {
            $workDays = json_decode($workDays, true) ?? ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];
        }

        $nbJoursSemaine = count($workDays) ?: 5;
        $heuresParJour  = $workHours / $nbJoursSemaine;

        $debut   = Carbon::create($annee, $mois, 1);
        $fin     = Carbon::create($annee, $mois, 1)->endOfMonth();
        $current = $debut->copy();
        $nbJours = 0;

        while ($current <= $fin) {
            if (in_array(strtolower($current->locale('fr')->dayName), $workDays)) {
                $nbJours++;
            }
            $current->addDay();
        }

        return round($heuresParJour * $nbJours, 2);
    }

    /**
     * Get optimized daily details with caching & CarbonPeriod
     */
    private function getJoursDetails(int $employeeId, int $annee, int $mois): array
    {
        $cacheKey = "vue_ensemble_jours_{$employeeId}_{$annee}_{$mois}";

        return Cache::remember($cacheKey, 3600, function () use ($employeeId, $annee, $mois) {
            $dateRange = $this->getDateRange($annee, $mois);
            $pointages = Pointage::where('employee_id', $employeeId)
                ->whereBetween('date', [$dateRange['debut'], $dateRange['fin']])
                ->get()
                ->keyBy('date');

            $jours = [];

            $current = $dateRange['debut_obj']->copy();
            while ($current <= $dateRange['fin_obj']) {
                $dateStr = $current->format('Y-m-d');
                $pointage = $pointages->get($dateStr);

                $jours[] = [
                    'date' => $dateStr,
                    'jour' => $current->format('d'),
                    'nom_jour' => $current->locale('fr')->shortDayName,
                    'is_weekend' => $current->isWeekend(),
                    'heures_travaillees' => $pointage ? (float) $pointage->heures_travaillees : 0,
                    'heures_supplementaires' => $pointage ? (float) $pointage->heures_supplementaires : 0,
                    'total' => $pointage
                        ? (float) $pointage->heures_travaillees + (float) $pointage->heures_supplementaires
                        : 0,
                    'statut' => $pointage
                        ? ($pointage->heure_entree ? 'present' : 'absent')
                        : ($current->isWeekend() ? 'weekend' : 'non_defini'),
                ];

                $current->addDay();
            }

            return $jours;
        });
    }


    private function getSemainesDuMois(Employee $employee, int $annee, int $mois): array
    {
        $cacheKey = "vue_ensemble_semaines_{$employee->id}_{$annee}_{$mois}";
        $heuresPlanifieesSemaine = (float) ($employee->work_hours ?? 35);

        return Cache::remember($cacheKey, 3600, function () use ($employee, $annee, $mois, $heuresPlanifieesSemaine) {
            $dateRange = $this->getDateRange($annee, $mois);

            $pointagesMois = Pointage::where('employee_id', $employee->id)
                ->whereBetween('date', [$dateRange['debut'], $dateRange['fin']])
                ->get()
                ->map(function ($p) {
                    $p->date_obj = Carbon::parse($p->date);
                    $p->week_start = $p->date_obj->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
                    return $p;
                });

            // Group by week start for O(1) lookups
            $pointagesByWeek = $pointagesMois->groupBy('week_start');

            $semaines = [];
            $debutMoisObj = $dateRange['debut_obj']->copy()->startOfWeek(Carbon::MONDAY);
            $finMoisObj = $dateRange['fin_obj'];
            $numSem = 1;

            while ($debutMoisObj->lte($finMoisObj)) {
                $weekStart = $debutMoisObj->format('Y-m-d');
                $weekEnd = $debutMoisObj->copy()->endOfWeek(Carbon::SUNDAY);

                $ptsSemaine = $pointagesByWeek->get($weekStart, collect());
                $heuresTravaillees = (float) $ptsSemaine->sum('heures_travaillees');
                $heuresSupp = (float) $ptsSemaine->sum('heures_supplementaires');
                $total = $heuresTravaillees + $heuresSupp;

                $semaines[] = [
                    'numero' => $numSem,
                    'debut' => $debutMoisObj->format('d/m'),
                    'fin' => $weekEnd->format('d/m'),
                    'heures_planifiees' => $heuresPlanifieesSemaine,
                    'heures_realisees' => round($heuresTravaillees, 2),
                    'heures_supplementaires' => round($heuresSupp, 2),
                    'total' => round($total, 2),
                    'solde' => round($total - $heuresPlanifieesSemaine, 2),
                ];

                $debutMoisObj->addWeek();
                $numSem++;
            }

            return $semaines;
        });
    }



    private function getDefaultCompteur(): CompteurMoisDTO
    {
        return CompteurMoisDTO::defaults();
    }

    private function validerAnnee($val): int
    {
        $v = (int) $val;
        return ($v >= 1900 && $v <= 2100) ? $v : now()->year;
    }

    private function validerMois($val): int
    {
        $v = (int) $val;
        return ($v >= 1 && $v <= 12) ? $v : now()->month;
    }

    /**
     * Get standardized month date range
     */
    private function getDateRange(int $annee, int $mois): array
    {
        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin = Carbon::create($annee, $mois, 1)->endOfMonth();

        return [
            'debut' => $debut->format('Y-m-d'),
            'fin' => $fin->format('Y-m-d'),
            'debut_obj' => $debut,
            'fin_obj' => $fin
        ];
    }
}
