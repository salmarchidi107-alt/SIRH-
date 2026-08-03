<?php

namespace App\Services;

use App\Models\BadgeRecord;
use App\Models\CompteurTemps;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Planning;
use App\Models\Pointage;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\Request;

class VueEnsembleService
{
    // 1h de pause dejeuner deduite uniquement des heures planifiees
    private const PAUSE_DEJEUNER = 1.0;

    public function __construct(private GraphService $graphService) {}

    public function getIndexData(Request $request): array
    {
        $user       = auth()->user();
        $annee      = $this->validerAnnee($request->get('annee'));
        $mois       = $this->validerMois($request->get('mois'));
        $employeeId = $request->get('employee_id');
        $department = $request->get('department');

        // ── Filtre par date de début / fin (custom range) ─────────────────
        $dateDebut = $request->get('date_debut');
        $dateFin   = $request->get('date_fin');

        // Si filtre custom fourni, on déduit annee/mois depuis date_debut
        if ($dateDebut && $dateFin) {
            $dateDebutCarbon = Carbon::parse($dateDebut);
            $dateFinCarbon   = Carbon::parse($dateFin);
            // On s'assure que debut <= fin
            if ($dateDebutCarbon->gt($dateFinCarbon)) {
                [$dateDebut, $dateFin] = [$dateFin, $dateDebut];
                [$dateDebutCarbon, $dateFinCarbon] = [$dateFinCarbon, $dateDebutCarbon];
            }
            $annee = $dateDebutCarbon->year;
            $mois  = $dateDebutCarbon->month;
        } else {
            $dateDebut       = null;
            $dateFin         = null;
            $dateDebutCarbon = Carbon::create($annee, $mois, 1)->startOfMonth();
            $dateFinCarbon   = Carbon::create($annee, $mois, 1)->endOfMonth();
        }

        $departments         = $this->getDepartmentsList();
        $listeEmployesSelect = Employee::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'matricule', 'department']);

        $moisPrecedent = Carbon::create($annee, $mois, 1)->subMonth();
        $moisSuivant   = Carbon::create($annee, $mois, 1)->addMonth();


        if ($department && !$employeeId) {
            $donnees = $this->getDonneesDepartement(
                $department, $annee, $mois,
                $dateDebutCarbon->format('Y-m-d'),
                $dateFinCarbon->format('Y-m-d')
            );

            $joursPlanningSemaine = $this->getJoursPlanningPopup(null, null, $department, $annee);

            return array_merge($donnees, [
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
                'dateDebut'            => $dateDebut,
                'dateFin'              => $dateFin,
            ]);
        }


        $employee      = $this->resoudreEmployee($employeeId, $user);
        $compteurMois  = null;
        $joursDetails  = [];
        $semaines      = [];
        $graphiqueMois = [];
        $gardeShifts   = [];

        if ($employee && $employee->id > 0) {
            $debut = $dateDebutCarbon->format('Y-m-d');
            $fin   = $dateFinCarbon->format('Y-m-d');

            $compteurMois  = $this->calculerCompteurMois($employee, $annee, $mois, $debut, $fin);
            $joursDetails  = $this->getJoursDetails($employee, $annee, $mois, $debut, $fin);
            $semaines      = $this->getSemainesDuMois($employee, $annee, $mois, $debut, $fin);
            $graphiqueMois = $this->graphService->getGraphiqueMois($employee->id, $annee);
            $gardeShifts   = $this->getGardeShifts($employee->id, $debut, $fin);
        }

        $joursPlanningSemaine = $this->getJoursPlanningPopup($employee, $employeeId, null, $annee);

        return [
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
            'gardeShifts'          => $gardeShifts,
            'dateDebut'            => $dateDebut,
            'dateFin'              => $dateFin,
            // workingData pour la vue (garde_shifts)
            'workingData'          => ['garde_shifts' => $gardeShifts],
        ];
    }



    private function getGardeShifts(int $employeeId, string $debut, string $fin): array
    {
        $records = BadgeRecord::where('employee_id', $employeeId)
            ->where('shift_type', 'garde')
            ->whereBetween(\DB::raw('DATE(created_at)'), [$debut, $fin])
            ->orderBy('created_at')
            ->get(['id', 'type', 'shift_type', 'created_at']);

        $result = [];
        foreach ($records as $r) {
            $date = Carbon::parse($r->created_at)->format('Y-m-d');
            if (!isset($result[$date])) {
                $result[$date] = ['date' => $date, 'type' => $r->type, 'shift_type' => 'garde'];
            }
        }

        return array_values($result);
    }


    private function calculerStatsShift(int $employeeId, string $debut, string $fin): array
    {
        // Heures réalisées en shift normal
        $pointagesNormal = Pointage::where('employee_id', $employeeId)
            ->whereBetween('date', [$debut, $fin])
            ->where('shift_type', 'normal')
            ->get();

        // Heures réalisées en garde
        $pointagesGarde = Pointage::where('employee_id', $employeeId)
            ->whereBetween('date', [$debut, $fin])
            ->where('shift_type', 'garde')
            ->get();

        $heuresNormal = round((float) $pointagesNormal->sum('heures_travaillees'), 2);
        $heuresGarde  = round((float) $pointagesGarde->sum('heures_travaillees'), 2);

        $joursNormal = $pointagesNormal->filter(fn($p) => (float) $p->heures_travaillees > 0)->count();
        $joursGarde  = $pointagesGarde->filter(fn($p) => (float) $p->heures_travaillees > 0)->count();

        // Heures planifiées pour les gardes (via BadgeRecord ou Planning)
        $heuresPlanGarde = $this->calculerHeuresPlanifieesParShift($employeeId, $debut, $fin, 'garde');
        $heuresPlanNorm  = $this->calculerHeuresPlanifieesParShift($employeeId, $debut, $fin, 'normal');

        return [
            'normal' => [
                'heures_realisees'  => $heuresNormal,
                'jours'             => $joursNormal,
                'heures_planifiees' => $heuresPlanNorm,
            ],
            'garde' => [
                'heures_realisees'  => $heuresGarde,
                'jours'             => $joursGarde,
                'heures_planifiees' => $heuresPlanGarde,
            ],
        ];
    }

    /**
     * Heures planifiées filtrées par shift_type.
     * On joint BadgeRecord pour connaître le type de shift d'une date.
     */
    private function calculerHeuresPlanifieesParShift(
        int $employeeId, string $debut, string $fin, string $shiftType
    ): float {
        // Dates où l'employé a pointé en 'garde' ou 'normal'
        $datesShift = BadgeRecord::where('employee_id', $employeeId)
            ->where('shift_type', $shiftType)
            ->whereBetween(\DB::raw('DATE(created_at)'), [$debut, $fin])
            ->selectRaw('DATE(created_at) as date_badge')
            ->distinct()
            ->pluck('date_badge')
            ->toArray();

        if (empty($datesShift)) return 0.0;

        $plannings = Planning::where('employee_id', $employeeId)
            ->whereIn('date', $datesShift)
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
            $total += max(0.0, $duree - self::PAUSE_DEJEUNER);
        }

        return round($total, 2);
    }

    private function getJoursPlanningPopup(?Employee $employee, $employeeId, ?string $department, int $annee): array
    {
        $debut = Carbon::create($annee, 1, 1)->startOfYear()->format('Y-m-d');
        $fin   = Carbon::create($annee, 12, 31)->endOfYear()->format('Y-m-d');

        $query = Planning::whereBetween('date', [$debut, $fin])
            ->whereNotNull('shift_start')
            ->whereNotNull('shift_end');

        if ($employeeId && $employee && $employee->id > 0) {
            $query->where('employee_id', $employee->id);
        } elseif ($department) {
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
            if (!isset($result[$dateStr])) {
                $result[$dateStr] = [
                    'shift_start' => $p->shift_start,
                    'shift_end'   => $p->shift_end,
                ];
            }
        }

        return $result;
    }


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

    private function dureeShiftHeures(string $date, string $start, string $end): float
    {
        $d     = Carbon::parse($date);
        $debut = $d->copy()->setTimeFromTimeString($start);
        $fin   = $d->copy()->setTimeFromTimeString($end);

        if ($fin->lte($debut)) {
            $fin->addDay();
        }

        return min($debut->diffInMinutes($fin) / 60, 24.0);
    }


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

    private function calculerCompteurMois(
        Employee $employee,
        int $annee,
        int $mois,
        string $debut,
        string $fin
    ): object {
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

        // ── Stats par type de shift ──────────────────────────────────────
        $statsShift = $this->calculerStatsShift($employee->id, $debut, $fin);

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
            // ── Par shift ──
            'heures_shift_normal'    => $statsShift['normal']['heures_realisees'],
            'jours_shift_normal'     => $statsShift['normal']['jours'],
            'plan_shift_normal'      => $statsShift['normal']['heures_planifiees'],
            'heures_shift_garde'     => $statsShift['garde']['heures_realisees'],
            'jours_shift_garde'      => $statsShift['garde']['jours'],
            'plan_shift_garde'       => $statsShift['garde']['heures_planifiees'],
        ];
    }

    private function getJoursDetails(
        Employee $employee,
        int $annee,
        int $mois,
        string $debut,
        string $fin
    ): array {
        $debutCarbon = Carbon::parse($debut);
        $finCarbon   = Carbon::parse($fin);

        $pointages = Pointage::where('employee_id', $employee->id)
            ->whereBetween('date', [$debut, $fin])
            ->get()
            ->keyBy(fn($p) => Carbon::parse($p->date)->format('Y-m-d'));

        $plannings = Planning::where('employee_id', $employee->id)
            ->whereBetween('date', [$debut, $fin])
            ->get()
            ->keyBy(fn($p) => Carbon::parse($p->date)->format('Y-m-d'));

        // Badge records pour shift_type par jour
        $badgeRecords = BadgeRecord::where('employee_id', $employee->id)
            ->where('type', 'entree')
            ->whereBetween(\DB::raw('DATE(created_at)'), [$debut, $fin])
            ->get()
            ->keyBy(fn($r) => Carbon::parse($r->created_at)->format('Y-m-d'));

        $jours = [];

        foreach (CarbonPeriod::create($debutCarbon, $finCarbon) as $current) {
            $dateStr  = $current->format('Y-m-d');
            $pointage = $pointages->get($dateStr);
            $planning = $plannings->get($dateStr);
            $badge    = $badgeRecords->get($dateStr);

            $planJour = 0.0;
            if ($planning && $planning->shift_start && $planning->shift_end) {
                $duree    = $this->dureeShiftHeures($dateStr, $planning->shift_start, $planning->shift_end);
                $planJour = max(0.0, $duree - self::PAUSE_DEJEUNER);
            }

            $realJour = $pointage ? round((float) $pointage->heures_travaillees, 2) : 0.0;
            $suppJour = $pointage ? round((float) $pointage->heures_supplementaires, 2) : 0.0;

            // shift_type : depuis badge record (priorité) ou pointage
            $shiftType = $badge?->shift_type ?? $pointage?->shift_type ?? 'normal';

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
                'shift_type'             => $shiftType,    // ← NOUVEAU
                'heure_entree'           => $pointage?->heure_entree ?? null,
                'heure_sortie'           => $pointage?->heure_sortie ?? null,
                'shift_start'            => $planning?->shift_start ?? null,
                'shift_end'              => $planning?->shift_end ?? null,
            ];
        }

        return $jours;
    }

    private function getSemainesDuMois(
        Employee $employee,
        int $annee,
        int $mois,
        string $debut,
        string $fin
    ): array {
        $debutMois = Carbon::parse($debut);
        $finMois   = Carbon::parse($fin);

        $pointagesMois = Pointage::where('employee_id', $employee->id)
            ->whereBetween('date', [$debut, $fin])
            ->get();

        $planningsMois = Planning::where('employee_id', $employee->id)
            ->whereBetween('date', [$debut, $fin])
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


    private function getDonneesDepartement(
        string $department,
        int $annee,
        int $mois,
        string $debut,
        string $fin
    ): array {
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
        $totalHeuresNormal    = 0.0;
        $totalHeuresGarde     = 0.0;
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

            // Heures par shift_type pour cet employé
            $hNormal = round((float) $empPointages->where('shift_type', 'normal')->sum('heures_travaillees'), 2);
            $hGarde  = round((float) $empPointages->where('shift_type', 'garde')->sum('heures_travaillees'), 2);

            $totalPlanifiees      += $planifiees;
            $totalRealisees       += $realisees;
            $totalSupplementaires += $supp;
            $totalHeuresNormal    += $hNormal;
            $totalHeuresGarde     += $hGarde;

            $employesDept[] = [
                'id'            => $emp->id,
                'nom'           => $emp->first_name . ' ' . $emp->last_name,
                'initiales'     => strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)),
                'poste'         => $emp->position      ?? 'Employe',
                'contrat'       => $emp->contract_type ?? 'CDI',
                'planifiees'    => $planifiees,
                'realisees'     => $realisees,
                'supp'          => $supp,
                'ecart'         => round($ecart, 2),
                'taux'          => $taux,
                'heures_normal' => $hNormal,
                'heures_garde'  => $hGarde,
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
            'heures_shift_normal'    => round($totalHeuresNormal, 2),
            'heures_shift_garde'     => round($totalHeuresGarde, 2),
        ];

        $graphiqueMoisDept = $this->graphService->getGraphiqueMoisDepartement($department, $annee);
        $semainerDept      = $this->getSemainesDepartement($empIds, $debut, $fin);

        return [
            'nomDepartement'    => $department,
            'statsGlobalesDept' => $statsGlobalesDept,
            'employesDept'      => $employesDept,
            'graphiqueMoisDept' => $graphiqueMoisDept,
            'semainerDept'      => $semainerDept,
        ];
    }

    private function getSemainesDepartement(array $empIds, string $debut, string $fin): array
    {
        $debutMois = Carbon::parse($debut);
        $finMois   = Carbon::parse($fin);

        $tousPointages = Pointage::whereIn('employee_id', $empIds)
            ->whereBetween('date', [$debut, $fin])
            ->get();

        $tousPlannings = Planning::whereIn('employee_id', $empIds)
            ->whereBetween('date', [$debut, $fin])
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

    private function getDepartmentsList()
    {
        try {
            $departments = Department::orderBy('name')->pluck('name');
            if ($departments->isNotEmpty()) {
                return $departments;
            }
        } catch (Exception $e) {
            // fallback
        }

        return Employee::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');
    }
}
