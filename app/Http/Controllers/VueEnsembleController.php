<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Pointage;
use App\Models\CompteurTemps;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VueEnsembleController extends Controller
{
    public function index(Request $request)
    {
        $user       = Auth::user();
        $annee      = $this->validerAnnee($request->get('annee'));
        $mois       = $this->validerMois($request->get('mois'));
        $employeeId = $request->get('employee_id');
        $department = $request->get('department');

        $departments        = Employee::distinct()->pluck('department')->filter()->values();
        $listeEmployesSelect = Employee::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'matricule', 'department']);

        // ── Mode département ────────────────────────────────────────────────
        // Si un département est sélectionné ET qu'aucun employé n'est explicitement choisi
        if ($department && !$employeeId) {
            $donnesDept = $this->getDonneesDepartement($department, $annee, $mois);

            $moisPrecedent = Carbon::create($annee, $mois, 1)->subMonth();
            $moisSuivant   = Carbon::create($annee, $mois, 1)->addMonth();

            return view('vue-ensemble.index', array_merge($donnesDept, [
                'modeDepartement'    => true,
                'employee'           => null,
                'listeEmployesSelect' => $listeEmployesSelect,
                'departments'        => $departments,
                'annee'              => $annee,
                'mois'               => $mois,
                'employeeId'         => null,
                'department'         => $department,
                'moisPrecedent'      => $moisPrecedent,
                'moisSuivant'        => $moisSuivant,
                'joursDetails'       => [],
                'semaines'           => [],
            ]));
        }

        // ── Mode employé individuel ─────────────────────────────────────────
        $employee = $this->resoudreEmployee($employeeId, $user);

        $compteurMois = null;
        $joursDetails = [];
        $semaines     = [];

        if ($employee && $employee->id > 0) {
            $compteurMois = $this->recalculerCompteurMois($employee, $annee, $mois);
            $joursDetails = $this->getJoursDetails($employee->id, $annee, $mois);
            $semaines     = $this->getSemainesDuMois($employee, $annee, $mois);
        } else {
            $compteurMois = $this->getDefaultCompteur();
        }

        $graphiqueMois = $this->getGraphiqueMois($employee->id ?? 0, $annee);
        $moisPrecedent = Carbon::create($annee, $mois, 1)->subMonth();
        $moisSuivant   = Carbon::create($annee, $mois, 1)->addMonth();

        return view('vue-ensemble.index', [
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
            // variables département vides pour éviter les erreurs dans la vue
            'nomDepartement'     => null,
            'statsGlobalesDept'  => null,
            'employesDept'       => [],
            'graphiqueMoisDept'  => [],
            'semainerDept'       => [],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // VUE DÉPARTEMENT
    // ════════════════════════════════════════════════════════════════════════

    private function getDonneesDepartement(string $department, int $annee, int $mois): array
    {
        $employes = Employee::where('department', $department)->get();

        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin   = Carbon::create($annee, $mois, 1)->endOfMonth();

        $totalPlanifiees      = 0;
        $totalRealisees       = 0;
        $totalSupplementaires = 0;
        $employesDept         = [];

        foreach ($employes as $emp) {
            // Recalcul depuis les pointages pour chaque employé
            $compteur = $this->recalculerCompteurMois($emp, $annee, $mois);

            $totalPlanifiees      += $compteur->heures_planifiees;
            $totalRealisees       += $compteur->heures_realisees;
            $totalSupplementaires += $compteur->heures_supplementaires;

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
        $graphiqueMoisDept = $this->getGraphiqueMoisDepartement($department, $annee);

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

    private function getGraphiqueMoisDepartement(string $department, int $annee): array
    {
        $nomsMois  = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        $employeIds = Employee::where('department', $department)->pluck('id');

        $compteurs = CompteurTemps::whereIn('employee_id', $employeIds)
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

        return CompteurTemps::updateOrCreate(
            ['employee_id' => $employee->id, 'annee' => $annee, 'mois' => $mois],
            [
                'heures_planifiees'      => $heuresPlanifiees,
                'heures_realisees'       => $heuresRealisees,
                'heures_supplementaires' => $heuresSupplementaires,
                'ecart'                  => $ecart,
                'solde_compteur'         => $ecart,
            ]
        );
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

    private function getJoursDetails(int $employeeId, int $annee, int $mois): array
    {
        $debut     = Carbon::create($annee, $mois, 1);
        $fin       = Carbon::create($annee, $mois, 1)->endOfMonth();
        $pointages = Pointage::where('employee_id', $employeeId)
            ->whereBetween('date', [$debut->format('Y-m-d'), $fin->format('Y-m-d')])
            ->get()->keyBy('date');

        $jours   = [];
        $current = $debut->copy();

        while ($current <= $fin) {
            $dateStr  = $current->format('Y-m-d');
            $pointage = $pointages->get($dateStr);
            $jours[]  = [
                'date'                   => $dateStr,
                'jour'                   => $current->format('d'),
                'nom_jour'               => $current->locale('fr')->shortDayName,
                'is_weekend'             => $current->isWeekend(),
                'heures_travaillees'     => $pointage ? (float) $pointage->heures_travaillees : 0,
                'heures_supplementaires' => $pointage ? (float) $pointage->heures_supplementaires : 0,
                'total'                  => $pointage
                    ? (float) $pointage->heures_travaillees + (float) $pointage->heures_supplementaires
                    : 0,
                'statut' => $pointage
                    ? ($pointage->heure_entree ? 'present' : 'absent')
                    : ($current->isWeekend() ? 'weekend' : 'non_defini'),
            ];
            $current->addDay();
        }

        return $jours;
    }

    private function getSemainesDuMois(Employee $employee, int $annee, int $mois): array
    {
        $heuresPlanifieesSemaine = (float) ($employee->work_hours ?? 35);
        $debutMois  = Carbon::create($annee, $mois, 1);
        $finMois    = Carbon::create($annee, $mois, 1)->endOfMonth();

        $pointagesMois = Pointage::where('employee_id', $employee->id)
            ->whereBetween('date', [$debutMois->format('Y-m-d'), $finMois->format('Y-m-d')])
            ->get();

        $semaines = [];
        $current  = $debutMois->copy()->startOfWeek(Carbon::MONDAY);
        $numSem   = 1;

        while ($current->lte($finMois)) {
            $debutSem = $current->copy();
            $finSem   = $current->copy()->endOfWeek(Carbon::SUNDAY);

            $pts = $pointagesMois->filter(fn($p) => Carbon::parse($p->date)->between($debutSem, $finSem));

            $heuresTravaillees = (float) $pts->sum('heures_travaillees');
            $heuresSupp        = (float) $pts->sum('heures_supplementaires');
            $total             = $heuresTravaillees + $heuresSupp;

            $semaines[] = [
                'numero'            => $numSem,
                'debut'             => $debutSem->format('d/m'),
                'fin'               => $finSem->format('d/m'),
                'heures_planifiees' => $heuresPlanifieesSemaine,
                'heures_realisees'  => round($heuresTravaillees, 2),
                'heures_supplementaires' => round($heuresSupp, 2),
                'total'             => round($total, 2),
                'solde'             => round($total - $heuresPlanifieesSemaine, 2),
            ];

            $current->addWeek();
            $numSem++;
        }

        return $semaines;
    }

    private function getGraphiqueMois(int $employeeId, int $annee): array
    {
        $nomsMois = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        $compteurs = $employeeId
            ? CompteurTemps::where('employee_id', $employeeId)->where('annee', $annee)->get()->keyBy('mois')
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

    private function getDefaultCompteur(): object
    {
        return (object) [
            'heures_planifiees'      => 0,
            'heures_realisees'       => 0,
            'heures_supplementaires' => 0,
            'ecart'                  => 0,
            'solde_compteur'         => 0,
        ];
    }

    private function validerAnnee($val): int
    {
        $v = (int) $val;
        return ($v >= 1900 && $v <= 2100) ? $v : Carbon::now()->year;
    }

    private function validerMois($val): int
    {
        $v = (int) $val;
        return ($v >= 1 && $v <= 12) ? $v : Carbon::now()->month;
    }
}