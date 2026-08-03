<?php

namespace App\Services;

use App\Models\Absence;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Planning;
use App\Models\Pointage;
use App\Models\Salary;
use App\Scopes\TenantScope;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportingService
{
    private const TZ      = 'Africa/Casablanca';
    private const PAUSE_H = 1.0;

    private const GARDE_SHIFT_TYPES = [
        'garde', 'Garde', 'GARDE',
        'night', 'Night', 'nuit', 'Nuit',
        'on_call', 'astreinte', 'Astreinte',
        'garde_nuit', 'night_shift', 'veille', 'permanence',
    ];

    private function getTenantId(): mixed
    {
        $tenantId = config('app.current_tenant_id');
        if (blank($tenantId) && auth()->check()) {
            $tenantId = auth()->user()->tenant_id;
        }
        return filled($tenantId) ? $tenantId : null;
    }

    private function getEmployeesQuery($tenantId): \Illuminate\Database\Eloquent\Builder
    {
        $q = Employee::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        try {
            $sample = Employee::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->whereNotNull('status')
                ->select('status')
                ->distinct()
                ->pluck('status')
                ->toArray();

            Log::debug('Reporting - statuts employés', ['statuts' => $sample]);

            $actifValues = array_values(array_filter($sample, function ($s) {
                return in_array(strtolower(trim((string)$s)), [
                    'actif', 'active', '1', 'true', 'en_poste', 'employe',
                    'enabled', 'working', 'present',
                ]);
            }));

            if (!empty($actifValues)) {
                $q->whereIn('status', $actifValues);
            }
        } catch (\Exception $e) {}

        return $q;
    }

    private function getValidationStatus($tenantId, Carbon $startDate, Carbon $endDate): array
    {
        $problems = [];

        // 1. Bulletins non generés ou non validés
        try {
            $empQuery  = $this->getEmployeesQuery($tenantId);
            $allEmpIds = $empQuery->pluck('id')->toArray();
            $nbrActifs = count($allEmpIds);

            if ($nbrActifs > 0) {
                $periodesMois = $this->getPeriodesMois($startDate, $endDate);

                $salariesQuery = Salary::whereIn('employee_id', $allEmpIds)
                    ->where(function ($q) use ($periodesMois) {
                        foreach ($periodesMois as $pm) {
                            $q->orWhere(fn($i) => $i->where('month', $pm['month'])->where('year', $pm['year']));
                        }
                    })
                    ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

                $empAvecBulletin    = (clone $salariesQuery)->distinct('employee_id')->count('employee_id');
                $bulletinsManquants = max(0, $nbrActifs - $empAvecBulletin);
                $bulletinsNonValides = (clone $salariesQuery)
                    ->whereNotIn('status', ['validated', 'paid', 'valide', 'paye'])
                    ->count();

                if ($bulletinsManquants > 0) {
                    $problems[] = [
                        'type'   => 'bulletins_manquants',
                        'label'  => 'Bulletins de paie non générés',
                        'detail' => $bulletinsManquants . ' employé(s) actif(s) sans bulletin sur cette période',
                        'count'  => $bulletinsManquants,
                        'url'    => route('salary.index'),
                    ];
                }

                if ($bulletinsNonValides > 0) {
                    $problems[] = [
                        'type'   => 'bulletins_invalides',
                        'label'  => 'Bulletins de paie non validés',
                        'detail' => $bulletinsNonValides . ' bulletin(s) en attente de validation',
                        'count'  => $bulletinsNonValides,
                        'url'    => route('salary.index'),
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::debug('Reporting - validation bulletins erreur', ['error' => $e->getMessage()]);
        }

        // 2. Pointages non validés
        try {
            $ptgBase = Pointage::withoutGlobalScope(TenantScope::class)
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->where(fn($q) => $q->where('statut', 'present')->orWhere('statut', 'présent'));

            $totalPointages   = (clone $ptgBase)->count();
            $pointagesValides = (clone $ptgBase)
                ->where(fn($q) => $q->where('valide', true)->orWhere('valide', 1))
                ->count();

            $pointagesNonValides = max(0, $totalPointages - $pointagesValides);

            if ($pointagesNonValides > 0) {
                $problems[] = [
                    'type'   => 'pointages',
                    'label'  => 'Pointages non validés',
                    'detail' => $pointagesNonValides . ' pointage(s) de présence non confirmé(s)',
                    'count'  => $pointagesNonValides,
                    'url'    => route('pointage.index'),
                ];
            }
        } catch (\Exception $e) {
            Log::debug('Reporting - validation pointages erreur', ['error' => $e->getMessage()]);
        }

        // 3. Employés actifs sans planning
        try {
            $empQuery  = $this->getEmployeesQuery($tenantId);
            $allEmpIds = $empQuery->pluck('id')->toArray();

            $empAvecPlanAvec = $tenantId
                ? Planning::whereIn('employee_id', $allEmpIds)
                    ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->where('tenant_id', $tenantId)
                    ->distinct('employee_id')->count('employee_id')
                : 0;

            $empAvecPlanSans = Planning::whereIn('employee_id', $allEmpIds)
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->distinct('employee_id')->count('employee_id');

            $empAvecPlan     = max($empAvecPlanAvec, $empAvecPlanSans);
            $empSansPlanning = max(0, count($allEmpIds) - $empAvecPlan);

            if ($empSansPlanning > 0) {
                $problems[] = [
                    'type'   => 'planning',
                    'label'  => 'Employés sans planning',
                    'detail' => $empSansPlanning . ' employé(s) actif(s) sans planning sur cette période',
                    'count'  => $empSansPlanning,
                    'url'    => route('planning.weekly'),
                ];
            }
        } catch (\Exception $e) {
            Log::debug('Reporting - validation planning erreur', ['error' => $e->getMessage()]);
        }

        return [
            'isReady'  => empty($problems),
            'problems' => $problems,
        ];
    }


    public function getIndexData(Request $request): array
    {
        $periode     = $request->get('periode', 'month');
        $departement = $request->get('departement', 'all');
        $dateDebut   = $request->get('date_debut');
        $dateFin     = $request->get('date_fin');

        [$startDate, $endDate] = $this->resolveDates($periode, $dateDebut, $dateFin);
        $tenantId     = $this->getTenantId();
        $periodesMois = $this->getPeriodesMois($startDate, $endDate);

        $empQuery = $this->getEmployeesQuery($tenantId);
        if ($departement !== 'all') {
            $empQuery->where(function ($q) use ($departement) {
                $q->where('department_id', $departement)->orWhere('department', $departement);
            });
        }

        $employeeIds    = $empQuery->pluck('id')->toArray();
        $nbrSalaries    = count($employeeIds);
        $joursOuvrables = $this->joursOuvrables($startDate, $endDate);

        if (empty($employeeIds)) {
            $nbrAbsences = $joursAbsence = $tauxAbsenteisme = 0;
            $heurePlanifiees = $empSansPlanning = $heuresPointees = $heuresSupp = $tauxPresence = 0;
            $heuresGarde = $nbGardes = 0;
            $absencesParType = collect();
        } else {
            $nbrAbsences  = $this->countAbsences($employeeIds, $startDate, $endDate);
            $joursAbsence = $this->calcJoursAbsence($employeeIds, $startDate, $endDate);
            $tauxAbsenteisme = $nbrSalaries > 0 && $joursOuvrables > 0
                ? round(($joursAbsence / ($nbrSalaries * $joursOuvrables)) * 100, 2) : 0;

            $heurePlanifiees = $this->calcHeuresPlanifiees($tenantId, $employeeIds, $startDate, $endDate);
            $empAvecPlanning = $this->countEmpAvecPlanning($tenantId, $employeeIds, $startDate, $endDate);
            $empSansPlanning = max(0, $nbrSalaries - $empAvecPlanning);
            $heuresPointees  = $this->calcHeuresPointees($tenantId, $employeeIds, $startDate, $endDate);
            $heuresSupp      = max(0, round($heuresPointees - $heurePlanifiees, 1));
            $tauxPresence    = $heurePlanifiees > 0
                ? round(($heuresPointees / $heurePlanifiees) * 100, 1) : 0;
            $absencesParType = $this->absencesParType($employeeIds, $startDate, $endDate);

            $gardeData   = $this->calcGardeData($tenantId, $employeeIds, $startDate, $endDate);
            $heuresGarde = $gardeData['heures'];
            $nbGardes    = $gardeData['count'];
        }

        $repartitionDept = $this->getRepartitionDept($tenantId);
        $fin             = $this->calcFinancialData($tenantId, $employeeIds, $periodesMois, $startDate, $endDate);
        $evolutionMasse  = $this->evolutionMasseSalariale($tenantId, $employeeIds, $startDate);
        $departments     = $this->getDepartments($tenantId);

        // ── Validation avant export ──
        $validation = $this->getValidationStatus($tenantId, $startDate, $endDate);

        Log::debug('Reporting - résultat', [
            'nbrSalaries'     => $nbrSalaries,
            'heurePlanifiees' => $heurePlanifiees,
            'heuresPointees'  => $heuresPointees,
            'heuresGarde'     => $heuresGarde,
            'nbGardes'        => $nbGardes,
            'masseBrute'      => $fin['masseSalarialeBrute'],
            'gardeTotal'      => $fin['gardeTotal'],
            'gardeHeures'     => $fin['gardeHeures'],
            'currency'        => $fin['currency'],
            'validation'      => $validation,
        ]);

        return [
            'periode'             => $periode,
            'departement'         => $departement,
            'dateDebut'           => $dateDebut,
            'dateFin'             => $dateFin,
            'startDate'           => $startDate,
            'endDate'             => $endDate,
            'departments'         => $departments,
            'nbrSalaries'         => $nbrSalaries,
            'nbrAbsences'         => $nbrAbsences,
            'joursAbsence'        => $joursAbsence,
            'tauxAbsenteisme'     => $tauxAbsenteisme,
            'heurePlanifiees'     => $heurePlanifiees,
            'empSansPlanning'     => $empSansPlanning,
            'heuresPointees'      => $heuresPointees,
            'heuresSupp'          => $heuresSupp,
            'tauxPresence'        => $tauxPresence,
            'absencesParType'     => $absencesParType,
            'repartitionDept'     => $repartitionDept,
            'joursOuvrables'      => $joursOuvrables,
            'heuresGarde'         => $heuresGarde,
            'nbGardes'            => $nbGardes,
            'masseSalarialeBrute' => $fin['masseSalarialeBrute'],
            'netTotal'            => $fin['netTotal'],
            'coutEmployeur'       => $fin['coutEmployeur'],
            'cnssEmployee'        => $fin['cnssEmployee'],
            'amoEmployee'         => $fin['amoEmployee'],
            'cnssPatron'          => $fin['cnssPatron'],
            'amoPatron'           => $fin['amoPatron'],
            'irRetenu'            => $fin['irRetenu'],
            'dgiMensuelle'        => $fin['dgiMensuelle'],
            'chargesSalariales'   => $fin['chargesSalariales'],
            'bulletinsTotal'      => $fin['bulletinsTotal'],
            'bulletinsValides'    => $fin['bulletinsValides'],
            'salaireMoyenBrut'    => $fin['salaireMoyenBrut'],
            'salaireMoyenNet'     => $fin['salaireMoyenNet'],
            'gardeTotal'          => $fin['gardeTotal'],
            'gardeHeures'         => $fin['gardeHeures'],
            'currency'            => $fin['currency'],
            'evolutionMasse'      => $evolutionMasse,
            'validation'          => $validation,
        ];
    }

    public function buildExportPdfData(Request $request): array
    {
        $data                = $this->buildAllData($request);
        $data['tenant']      = auth()->user()?->tenant;
        $data['deptName']    = $data['departement'] !== 'all'
            ? (Department::find($data['departement'])?->name ?? $data['departement'])
            : 'Tous départements';
        $data['generatedAt'] = now()->setTimezone(self::TZ)->format('d/m/Y H:i');

        // $data['currency'] est déjà défini par buildAllData() -> calcFinancialData(),
        // dérivé des bulletins de salaire réels de la période (pas du tenant).

        return $data;
    }


    private function calcGardeData($tenantId, array $ids, Carbon $debut, Carbon $fin): array
    {
        if (empty($ids)) {
            return ['count' => 0, 'heures' => 0.0];
        }

        try {
            $typesPresents = Pointage::withoutGlobalScope(TenantScope::class)
                ->whereIn('employee_id', $ids)
                ->whereBetween('date', [$debut->toDateString(), $fin->toDateString()])
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->whereNotNull('shift_type')
                ->select('shift_type')
                ->distinct()
                ->pluck('shift_type')
                ->toArray();

            Log::debug('Reporting - shift_types présents dans Pointage', [
                'periode'        => $debut->toDateString() . ' → ' . $fin->toDateString(),
                'shift_types'    => $typesPresents,
                'garde_cherches' => self::GARDE_SHIFT_TYPES,
            ]);
        } catch (\Exception $e) {
            Log::debug('Reporting - impossible de lister shift_types', ['error' => $e->getMessage()]);
        }

        $base = Pointage::withoutGlobalScope(TenantScope::class)
            ->whereIn('employee_id', $ids)
            ->whereIn('shift_type', self::GARDE_SHIFT_TYPES)
            ->whereBetween('date', [$debut->toDateString(), $fin->toDateString()])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        $count  = (clone $base)->count();
        $heures = (float) (clone $base)->whereNotNull('total_heures')->sum('total_heures');

        if ($heures === 0.0) {
            $heures = (float) (clone $base)->whereNotNull('heures_travaillees')->sum('heures_travaillees');
        }

        if ($heures === 0.0) {
            try {
                $h = (float) (clone $base)
                    ->whereNotNull('heure_entree')
                    ->whereNotNull('heure_sortie')
                    ->sum(DB::raw('TIME_TO_SEC(TIMEDIFF(heure_sortie, heure_entree)) / 3600'));
                if ($h > 0.0) $heures = $h;
            } catch (\Exception $e) {}
        }

        if ($heures === 0.0) {
            foreach (['hours_worked', 'duree', 'nb_heures', 'heures'] as $col) {
                try {
                    $h = (float) (clone $base)->whereNotNull($col)->sum($col);
                    if ($h > 0.0) { $heures = $h; break; }
                } catch (\Exception $e) {}
            }
        }

        if ($heures === 0.0 || $count === 0) {
            try {
                $gardeQueryBase = fn($withTenant) => Planning::whereIn('employee_id', $ids)
                    ->whereIn('shift_type', self::GARDE_SHIFT_TYPES)
                    ->whereBetween('date', [$debut->toDateString(), $fin->toDateString()])
                    ->when($withTenant && $tenantId, fn($q) => $q->where('tenant_id', $tenantId));

                $planGardes = null;
                $countAvec  = $tenantId ? (clone $gardeQueryBase(true))->count() : 0;
                $countSans  = (clone $gardeQueryBase(false))->count();

                if ($countAvec > 0)     $planGardes = $gardeQueryBase(true)->get();
                elseif ($countSans > 0) $planGardes = $gardeQueryBase(false)->get();

                if ($planGardes && $planGardes->isNotEmpty()) {
                    if ($count === 0) $count = $planGardes->count();

                    foreach ([['shift_start','shift_end'],['start_time','end_time'],['heure_debut','heure_fin']] as [$cs,$ce]) {
                        $total = 0.0;
                        foreach ($planGardes as $pg) {
                            $sv = $pg->{$cs} ?? null;
                            $ev = $pg->{$ce} ?? null;
                            if (blank($sv) || blank($ev)) continue;
                            try {
                                $d = Carbon::parse($pg->date);
                                $s = $d->copy()->setTimeFromTimeString($sv);
                                $e = $d->copy()->setTimeFromTimeString($ev);
                                if ($e->lte($s)) $e->addDay();
                                $dur = min($s->diffInMinutes($e) / 60, 24.0);
                                if ($dur > 0) $total += $dur;
                            } catch (\Exception $ex) {}
                        }
                        if ($total > 0.0) { $heures = $total; break; }
                    }

                    Log::debug('Reporting - gardes depuis Planning', [
                        'count'  => $count,
                        'heures' => round($heures, 1),
                    ]);
                }
            } catch (\Exception $e) {
                Log::debug('Reporting - fallback Planning garde échoué', ['error' => $e->getMessage()]);
            }
        }

        Log::debug('Reporting - garde data calculé', [
            'count'  => $count,
            'heures' => round($heures, 1),
        ]);

        return [
            'count'  => (int) $count,
            'heures' => round($heures, 1),
        ];
    }

    private function countAbsences(array $ids, Carbon $debut, Carbon $fin): int
    {
        try {
            return Absence::whereIn('employee_id', $ids)
                ->whereIn('status', ['approved','valide','accepte','accepted','validated'])
                ->whereBetween('start_date', [$debut->toDateString(), $fin->toDateString()])
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function calcJoursAbsence(array $ids, Carbon $debut, Carbon $fin): float
    {
        $base = Absence::whereIn('employee_id', $ids)
            ->whereIn('status', ['approved','valide','accepte','accepted','validated'])
            ->whereBetween('start_date', [$debut->toDateString(), $fin->toDateString()]);

        foreach (['total_days','nombre_jours','days_count','nb_jours','duration'] as $col) {
            try {
                $v = (clone $base)->sum($col);
                if ((float) $v > 0) return round((float) $v, 1);
            } catch (\Exception $e) {}
        }

        try {
            $v = (clone $base)->sum(DB::raw('DATEDIFF(end_date, start_date) + 1'));
            if ((float) $v > 0) return round((float) $v, 1);
        } catch (\Exception $e) {}

        return (float) (clone $base)->count();
    }

    private function absencesParType(array $ids, Carbon $debut, Carbon $fin)
    {
        try {
            return Absence::whereIn('employee_id', $ids)
                ->whereIn('status', ['approved','valide','accepte','accepted','validated'])
                ->whereBetween('start_date', [$debut->toDateString(), $fin->toDateString()])
                ->select(
                    DB::raw('COALESCE(type, type_absence, raison, "autre") as type'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(COALESCE(total_days,nombre_jours,days_count,nb_jours,DATEDIFF(end_date,start_date)+1,1)) as jours')
                )
                ->groupBy(DB::raw('COALESCE(type, type_absence, raison, "autre")'))
                ->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    private function calcHeuresPlanifiees($tenantId, array $ids, Carbon $debut, Carbon $fin): float
    {
        if (empty($ids)) return 0.0;

        $planningsQuery = fn() => Planning::whereIn('employee_id', $ids)
            ->whereBetween('date', [$debut->toDateString(), $fin->toDateString()]);

        $planBase = null;
        try {
            $countSansTenant = (clone $planningsQuery())->count();
            $countAvecTenant = $tenantId
                ? (clone $planningsQuery())->where('tenant_id', $tenantId)->count()
                : 0;

            Log::debug('Reporting - planning counts', [
                'sans_tenant' => $countSansTenant,
                'avec_tenant' => $countAvecTenant,
                'tenant_id'   => $tenantId,
            ]);

            if ($countAvecTenant > 0) {
                $planBase = fn() => Planning::whereIn('employee_id', $ids)
                    ->whereBetween('date', [$debut->toDateString(), $fin->toDateString()])
                    ->where('tenant_id', $tenantId);
            } elseif ($countSansTenant > 0) {
                $planBase = fn() => Planning::whereIn('employee_id', $ids)
                    ->whereBetween('date', [$debut->toDateString(), $fin->toDateString()]);
            }
        } catch (\Exception $e) {
            Log::debug('Reporting - erreur comptage planning', ['err' => $e->getMessage()]);
        }

        if ($planBase !== null) {
            $timePairs = [
                ['shift_start', 'shift_end'],
                ['start_time',  'end_time'],
                ['heure_debut', 'heure_fin'],
                ['debut',       'fin'],
                ['from',        'to'],
            ];

            foreach ($timePairs as [$cs, $ce]) {
                try {
                    $rows = $planBase()
                        ->whereNotNull($cs)
                        ->whereNotNull($ce)
                        ->whereNotIn('shift_type', self::GARDE_SHIFT_TYPES)
                        ->get(['date', $cs, $ce, 'shift_type']);

                    if ($rows->isEmpty()) continue;

                    $total = 0.0;
                    foreach ($rows as $p) {
                        $sv = $p->{$cs};
                        $ev = $p->{$ce};
                        if (blank($sv) || blank($ev)) continue;
                        try {
                            $d = Carbon::parse($p->date);
                            $s = $d->copy()->setTimeFromTimeString($sv);
                            $e = $d->copy()->setTimeFromTimeString($ev);
                            if ($e->lte($s)) $e->addDay();
                            $dur = min($s->diffInMinutes($e) / 60, 24.0);
                            if ($dur > 0) {
                                $total += $dur > 4.0 ? max(0.0, $dur - self::PAUSE_H) : $dur;
                            }
                        } catch (\Exception $ex) {}
                    }

                    if ($total > 0.0) {
                        Log::debug("Reporting - heures planifiées via Planning [{$cs}/{$ce}]", ['total' => round($total, 2)]);
                        return round($total, 2);
                    }
                } catch (\Exception $e) {}
            }

            foreach (['hours', 'heures', 'duration', 'duree', 'nb_heures', 'total_hours'] as $col) {
                try {
                    $h = (float) $planBase()
                        ->whereNotIn('shift_type', self::GARDE_SHIFT_TYPES)
                        ->sum($col);
                    if ($h > 0.0) {
                        Log::debug("Reporting - heures planifiées colonne directe [{$col}]", ['total' => round($h, 1)]);
                        return round($h, 1);
                    }
                } catch (\Exception $e) {}
            }
        }

        try {
            $mkPtg = fn() => Pointage::withoutGlobalScope(TenantScope::class)
                ->whereIn('employee_id', $ids)
                ->whereBetween('date', [$debut->toDateString(), $fin->toDateString()])
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->whereNotIn('shift_type', self::GARDE_SHIFT_TYPES)
                ->where(fn($q) => $q->where('statut', 'present')->orWhereNull('statut'));

            foreach (['total_heures', 'heures_travaillees'] as $col) {
                $h = (float) $mkPtg()->whereNotNull($col)->sum($col);
                if ($h > 0.0) {
                    Log::debug("Reporting - heures planifiées fallback pointage [{$col}]", ['h' => round($h, 1)]);
                    return round($h, 1);
                }
            }

            $h = (float) $mkPtg()
                ->whereNotNull('heure_entree')->whereNotNull('heure_sortie')
                ->sum(DB::raw('TIME_TO_SEC(TIMEDIFF(heure_sortie, heure_entree)) / 3600'));
            if ($h > 0.0) return round($h, 1);

        } catch (\Exception $e) {}

        return 0.0;
    }

    private function countEmpAvecPlanning($tenantId, array $ids, Carbon $debut, Carbon $fin): int
    {
        try {
            return Planning::whereIn('employee_id', $ids)
                ->whereBetween('date', [$debut->toDateString(), $fin->toDateString()])
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->distinct('employee_id')->count('employee_id');
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function calcHeuresPointees($tenantId, array $ids, Carbon $debut, Carbon $fin): float
    {
        if (empty($ids)) return 0.0;

        $mkBase = fn() => Pointage::withoutGlobalScope(TenantScope::class)
            ->whereIn('employee_id', $ids)
            ->whereBetween('date', [$debut->toDateString(), $fin->toDateString()])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereNotIn('shift_type', self::GARDE_SHIFT_TYPES);

        try {
            $h = (float) $mkBase()->whereNotNull('total_heures')->sum('total_heures');
            if ($h > 0.0) return round($h, 1);
        } catch (\Exception $e) {}

        try {
            $h = (float) $mkBase()->whereNotNull('heures_travaillees')->sum('heures_travaillees');
            if ($h > 0.0) return round($h, 1);
        } catch (\Exception $e) {}

        try {
            $h = (float) $mkBase()
                ->whereNotNull('heure_entree')
                ->whereNotNull('heure_sortie')
                ->sum(DB::raw('TIME_TO_SEC(TIMEDIFF(heure_sortie, heure_entree)) / 3600'));
            if ($h > 0.0) return round($h, 1);
        } catch (\Exception $e) {}

        foreach (['hours_worked', 'duree', 'nb_heures', 'heures'] as $col) {
            try {
                $h = (float) $mkBase()->whereNotNull($col)->sum($col);
                if ($h > 0.0) return round($h, 1);
            } catch (\Exception $e) {}
        }

        return 0.0;
    }

    private function calcFinancialData(
        $tenantId,
        array $employeeIds,
        array $periodesMois,
        Carbon $debut = null,
        Carbon $fin   = null
    ): array {
        $zero = [
            'masseSalarialeBrute' => 0, 'netTotal' => 0, 'coutEmployeur' => 0,
            'cnssEmployee' => 0, 'amoEmployee' => 0, 'cnssPatron' => 0, 'amoPatron' => 0,
            'irRetenu' => 0, 'dgiMensuelle' => 0, 'chargesSalariales' => 0,
            'bulletinsTotal' => 0, 'bulletinsValides' => 0,
            'salaireMoyenBrut' => 0, 'salaireMoyenNet' => 0,
            'gardeTotal' => 0, 'gardeHeures' => 0,
            'currency' => 'MAD',
        ];

        if (empty($employeeIds) || empty($periodesMois)) return $zero;

        try {
            $salaries = Salary::whereIn('employee_id', $employeeIds)
                ->where(function ($q) use ($periodesMois) {
                    foreach ($periodesMois as $pm) {
                        $q->orWhere(fn($i) => $i->where('month', $pm['month'])->where('year', $pm['year']));
                    }
                })
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->get();
        } catch (\Exception $e) {
            try {
                $d = Carbon::create($periodesMois[0]['year'], $periodesMois[0]['month'], 1)->startOfMonth();
                $f = Carbon::create(end($periodesMois)['year'], end($periodesMois)['month'], 1)->endOfMonth();
                $salaries = Salary::whereIn('employee_id', $employeeIds)
                    ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                    ->whereBetween('created_at', [$d, $f])->get();
            } catch (\Exception $e2) {
                return $zero;
            }
        }

        if ($salaries->isEmpty()) return $zero;

        Log::debug('Reporting - colonnes salary (premier bulletin)', [
            'keys' => array_keys($salaries->first()?->getAttributes() ?? []),
        ]);

        // ── Devise réelle des bulletins de la période ──
        // On ne se fie plus à $tenant->currency (souvent resté sur MAD par défaut) :
        // on lit la colonne 'currency' des bulletins effectivement utilisés dans ce rapport.
        $currenciesPresentes = $salaries->pluck('currency')->filter()->unique()->values();
        $currency = $currenciesPresentes->first() ?? 'MAD';

        if ($currenciesPresentes->count() > 1) {
            Log::debug('Reporting - devises mixtes détectées sur la période', [
                'currencies' => $currenciesPresentes->toArray(),
            ]);
        }

        $masseSalarialeBrute = round((float) $salaries->sum('gross_salary'), 2);
        $netTotal            = round((float) $salaries->sum('net_salary'), 2);
        $cnssEmployee        = round((float) $salaries->sum('cnss_deduction'), 2);
        $amoEmployee         = round((float) $salaries->sum('amo_deduction'), 2);
        $irRetenu            = round((float) $salaries->sum('ir_deduction'), 2);
        $cnssPatron          = round((float) ($salaries->sum('employer_cnss') ?: $salaries->sum('cnss_employer') ?: 0), 2);
        $amoPatron           = round((float) ($salaries->sum('employer_amo')  ?: $salaries->sum('amo_employer')  ?: 0), 2);
        $coutEmployeur       = round((float) ($salaries->sum('employer_total_cost') ?: 0), 2);

        $gardeHeures = 0.0;
        foreach (['garde_hours','heures_garde','night_hours','overtime_night_hours','garde_h','hours_garde'] as $col) {
            $v = (float) $salaries->sum($col);
            if ($v > 0.0) { $gardeHeures = round($v, 1); break; }
        }

        $gardeTotal = 0.0;
        foreach (['overtime_night_amount','garde_amount','garde_indemnite','indemnite_garde','night_pay','montant_garde','garde_pay','astreinte_amount'] as $col) {
            $v = (float) $salaries->sum($col);
            if ($v > 0.0) { $gardeTotal = round($v, 2); break; }
        }

        if ($gardeHeures === 0.0 && !empty($employeeIds) && $debut && $fin) {
            try {
                $gardeQueryFn = fn($withTenant) => Planning::whereIn('employee_id', $employeeIds)
                    ->whereIn('shift_type', self::GARDE_SHIFT_TYPES)
                    ->whereBetween('date', [$debut->toDateString(), $fin->toDateString()])
                    ->when($withTenant && $tenantId, fn($q) => $q->where('tenant_id', $tenantId));

                $countAvec  = $tenantId ? (clone $gardeQueryFn(true))->count() : 0;
                $countSans  = (clone $gardeQueryFn(false))->count();
                $planGardes = null;

                if ($countAvec > 0)     $planGardes = $gardeQueryFn(true)->get();
                elseif ($countSans > 0) $planGardes = $gardeQueryFn(false)->get();

                if ($planGardes && $planGardes->isNotEmpty()) {
                    $gardeH = 0.0;
                    foreach ([['shift_start','shift_end'],['start_time','end_time'],['heure_debut','heure_fin']] as [$cs,$ce]) {
                        $total = 0.0;
                        foreach ($planGardes as $pg) {
                            $sv = $pg->{$cs} ?? null;
                            $ev = $pg->{$ce} ?? null;
                            if (blank($sv) || blank($ev)) continue;
                            try {
                                $d = Carbon::parse($pg->date);
                                $s = $d->copy()->setTimeFromTimeString($sv);
                                $e = $d->copy()->setTimeFromTimeString($ev);
                                if ($e->lte($s)) $e->addDay();
                                $dur = min($s->diffInMinutes($e) / 60, 24.0);
                                if ($dur > 0) $total += $dur;
                            } catch (\Exception $ex) {}
                        }
                        if ($total > 0.0) { $gardeH = $total; break; }
                    }
                    if ($gardeH > 0.0) {
                        $gardeHeures = round($gardeH, 1);
                        Log::debug('Reporting - gardeHeures depuis Planning', ['heures' => $gardeHeures, 'count' => $planGardes->count()]);
                    }
                }
            } catch (\Exception $e) {
                Log::debug('Reporting - fallback Planning garde (financial) échoué', ['error' => $e->getMessage()]);
            }
        }

        if ($gardeHeures === 0.0 && !empty($employeeIds) && $debut && $fin) {
            try {
                $mkPtgGarde = fn() => Pointage::withoutGlobalScope(TenantScope::class)
                    ->whereIn('employee_id', $employeeIds)
                    ->whereIn('shift_type', self::GARDE_SHIFT_TYPES)
                    ->whereBetween('date', [$debut->toDateString(), $fin->toDateString()])
                    ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

                foreach (['total_heures','heures_travaillees','hours_worked','duree'] as $col) {
                    $h = (float) $mkPtgGarde()->whereNotNull($col)->sum($col);
                    if ($h > 0.0) { $gardeHeures = round($h, 1); break; }
                }

                if ($gardeHeures === 0.0) {
                    $h = (float) $mkPtgGarde()
                        ->whereNotNull('heure_entree')->whereNotNull('heure_sortie')
                        ->sum(DB::raw('TIME_TO_SEC(TIMEDIFF(heure_sortie, heure_entree)) / 3600'));
                    if ($h > 0.0) $gardeHeures = round($h, 1);
                }
            } catch (\Exception $e) {
                Log::debug('Reporting - fallback gardeHeures depuis Pointage échoué', ['error' => $e->getMessage()]);
            }
        }

        if ($gardeTotal === 0.0 && $gardeHeures > 0.0 && $masseSalarialeBrute > 0 && $salaries->count() > 0) {
            $tauxHMoyen = $masseSalarialeBrute / ($salaries->count() * 191.25);
            $gardeTotal = round($tauxHMoyen * $gardeHeures, 2);
            Log::debug('Reporting - gardeTotal calculé', [
                'gardeHeures' => $gardeHeures,
                'tauxHMoyen'  => round($tauxHMoyen, 4),
                'gardeTotal'  => $gardeTotal,
            ]);
        }

        if ($cnssPatron    === 0.0 && $masseSalarialeBrute > 0) $cnssPatron    = round($masseSalarialeBrute * 0.0898, 2);
        if ($amoPatron     === 0.0 && $masseSalarialeBrute > 0) $amoPatron     = round($masseSalarialeBrute * 0.0226, 2);
        if ($coutEmployeur === 0.0)                              $coutEmployeur = round($masseSalarialeBrute + $cnssPatron + $amoPatron, 2);

        $chargesSalariales = $cnssEmployee + $amoEmployee + $irRetenu;
        $dgiMensuelle      = round($irRetenu + ($masseSalarialeBrute * 0.016), 2);
        $bulletinsTotal    = $salaries->count();
        $bulletinsValides  = $salaries->whereIn('status', ['validated','paid','valide','paye'])->count();
        $salaireMoyenBrut  = $bulletinsTotal > 0 ? round($masseSalarialeBrute / $bulletinsTotal, 2) : 0;
        $salaireMoyenNet   = $bulletinsTotal > 0 ? round($netTotal / $bulletinsTotal, 2) : 0;

        Log::debug('Reporting - données financières calculées', [
            'gardeHeures' => $gardeHeures,
            'gardeTotal'  => $gardeTotal,
            'bulletins'   => $bulletinsTotal,
            'masseBrute'  => $masseSalarialeBrute,
            'currency'    => $currency,
        ]);

        return compact(
            'masseSalarialeBrute','netTotal','coutEmployeur',
            'cnssEmployee','amoEmployee','cnssPatron','amoPatron',
            'irRetenu','dgiMensuelle','chargesSalariales',
            'bulletinsTotal','bulletinsValides',
            'salaireMoyenBrut','salaireMoyenNet',
            'gardeTotal','gardeHeures','currency'
        );
    }

    private function getRepartitionDept($tenantId)
    {
        try {
            $r = Employee::where('employees.status', 'actif')
                ->when($tenantId, fn($q) => $q->where('employees.tenant_id', $tenantId))
                ->join('departments', 'employees.department_id', '=', 'departments.id')
                ->select('departments.name as dept', DB::raw('COUNT(employees.id) as total'))
                ->groupBy('departments.id', 'departments.name')->get();
            if ($r->isNotEmpty()) return $r;
        } catch (\Exception $e) {}

        try {
            $r = Employee::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->whereNotNull('department')->where('department','!=','')
                ->select(DB::raw('department as dept'), DB::raw('COUNT(*) as total'))
                ->groupBy('department')->get();
            if ($r->isNotEmpty()) return $r;
        } catch (\Exception $e) {}

        return collect();
    }

    private function getDepartments($tenantId)
    {
        try {
            $d = Department::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->orderBy('name')->get();
            if ($d->isNotEmpty()) return $d;
        } catch (\Exception $e) {}

        try {
            return Employee::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->whereNotNull('department')->where('department','!=','')
                ->select(DB::raw('department as id'), DB::raw('department as name'))
                ->distinct()->orderBy('department')->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    private function evolutionMasseSalariale($tenantId, array $ids, Carbon $refDate): array
    {
        $result = [];
        for ($i = 2; $i >= 0; $i--) {
            $month = $refDate->copy()->subMonths($i);
            $masse = 0;
            if (!empty($ids)) {
                try {
                    $masse = Salary::whereIn('employee_id', $ids)
                        ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                        ->where('month', $month->month)->where('year', $month->year)
                        ->sum('gross_salary');
                } catch (\Exception $e) {}
            }
            $result[] = ['label' => ucfirst($month->locale('fr')->translatedFormat('M Y')), 'montant' => round((float) $masse, 2)];
        }
        return $result;
    }

    private function getPeriodesMois(Carbon $debut, Carbon $fin): array
    {
        $periodes = [];
        $courant  = $debut->copy()->startOfMonth();
        $dernier  = $fin->copy()->startOfMonth();
        while ($courant->lte($dernier)) {
            $periodes[] = ['month' => $courant->month, 'year' => $courant->year];
            $courant->addMonth();
        }
        return $periodes;
    }

    private function resolveDates(string $periode, ?string $debut, ?string $fin): array
    {
        return match ($periode) {
            'quarter' => [Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()],
            'year'    => [Carbon::now()->startOfYear(),    Carbon::now()->endOfYear()],
            'custom'  => [
                $debut ? Carbon::parse($debut)->startOfDay() : Carbon::now()->startOfMonth(),
                $fin   ? Carbon::parse($fin)->endOfDay()     : Carbon::now()->endOfMonth(),
            ],
            default   => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }

    private function joursOuvrables(Carbon $start, Carbon $end): int
    {
        $count = 0;
        $cur   = $start->copy()->startOfDay();
        while ($cur->lte($end)) {
            if (!$cur->isWeekend()) $count++;
            $cur->addDay();
        }
        return $count;
    }

    private function buildAllData(Request $request): array
    {
        $periode     = $request->get('periode', 'month');
        $departement = $request->get('departement', 'all');
        $dateDebut   = $request->get('date_debut');
        $dateFin     = $request->get('date_fin');

        [$startDate, $endDate] = $this->resolveDates($periode, $dateDebut, $dateFin);
        $tenantId     = $this->getTenantId();
        $periodesMois = $this->getPeriodesMois($startDate, $endDate);

        $empQuery = $this->getEmployeesQuery($tenantId);
        if ($departement !== 'all') {
            $empQuery->where(fn($q) => $q->where('department_id', $departement)->orWhere('department', $departement));
        }

        $employeeIds    = $empQuery->pluck('id')->toArray();
        $nbrSalaries    = count($employeeIds);
        $joursOuvrables = $this->joursOuvrables($startDate, $endDate);

        if (empty($employeeIds)) {
            $nbrAbsences = $joursAbsence = $tauxAbsenteisme = 0;
            $heurePlanifiees = $empSansPlanning = $heuresPointees = $heuresSupp = $tauxPresence = 0;
            $heuresGarde = $nbGardes = 0;
            $absencesParType = collect();
        } else {
            $nbrAbsences     = $this->countAbsences($employeeIds, $startDate, $endDate);
            $joursAbsence    = $this->calcJoursAbsence($employeeIds, $startDate, $endDate);
            $tauxAbsenteisme = $nbrSalaries > 0 && $joursOuvrables > 0 ? round(($joursAbsence / ($nbrSalaries * $joursOuvrables)) * 100, 2) : 0;
            $heurePlanifiees = $this->calcHeuresPlanifiees($tenantId, $employeeIds, $startDate, $endDate);
            $empAvecPlanning = $this->countEmpAvecPlanning($tenantId, $employeeIds, $startDate, $endDate);
            $empSansPlanning = max(0, $nbrSalaries - $empAvecPlanning);
            $heuresPointees  = $this->calcHeuresPointees($tenantId, $employeeIds, $startDate, $endDate);
            $heuresSupp      = max(0, round($heuresPointees - $heurePlanifiees, 1));
            $tauxPresence    = $heurePlanifiees > 0 ? round(($heuresPointees / $heurePlanifiees) * 100, 1) : 0;
            $absencesParType = $this->absencesParType($employeeIds, $startDate, $endDate);
            $gardeData       = $this->calcGardeData($tenantId, $employeeIds, $startDate, $endDate);
            $heuresGarde     = $gardeData['heures'];
            $nbGardes        = $gardeData['count'];
        }

        $repartitionDept = $this->getRepartitionDept($tenantId);
        $fin             = $this->calcFinancialData($tenantId, $employeeIds, $periodesMois, $startDate, $endDate);
        $evolutionMasse  = $this->evolutionMasseSalariale($tenantId, $employeeIds, $startDate);
        $departments     = $this->getDepartments($tenantId);

        return array_merge(compact(
            'periode','departement','dateDebut','dateFin','startDate','endDate','departments',
            'nbrSalaries','nbrAbsences','joursAbsence','tauxAbsenteisme',
            'heurePlanifiees','empSansPlanning','heuresPointees','heuresSupp',
            'tauxPresence','absencesParType','repartitionDept','joursOuvrables',
            'heuresGarde','nbGardes','evolutionMasse'
        ), $fin);
    }

    public function getDebugData(): array
    {
        $tenantId  = $this->getTenantId();
        $startDate = Carbon::now()->startOfMonth();
        $endDate   = Carbon::now()->endOfMonth();

        $empQuery  = $this->getEmployeesQuery($tenantId);
        $employees = $empQuery->get(['id','first_name','last_name','status','department_id']);
        $ids       = $employees->pluck('id')->toArray();

        $allStatuts = Employee::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->select('status')->distinct()->pluck('status');

        $planSample = null; $planCols = []; $planCount = 0;
        try {
            $planCount  = Planning::whereIn('employee_id', $ids)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count();
            $planSample = Planning::whereIn('employee_id', $ids)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->first();
            $planCols   = $planSample ? array_keys($planSample->getAttributes()) : [];
        } catch (\Exception $e) {}

        $planShiftTypes = [];
        try {
            $planShiftTypes = Planning::whereIn('employee_id', $ids)
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->whereNotNull('shift_type')->select('shift_type')->distinct()->pluck('shift_type')->toArray();
        } catch (\Exception $e) {}

        $planSamples = [];
        try {
            $planSamples = Planning::whereIn('employee_id', $ids)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->limit(3)->get()->map(fn($p) => $p->getAttributes())->toArray();
        } catch (\Exception $e) {}

        $ptgSample = null; $ptgCols = []; $ptgCount = 0;
        try {
            $ptgCount  = Pointage::withoutGlobalScope(TenantScope::class)
                ->whereIn('employee_id', $ids)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count();
            $ptgSample = Pointage::withoutGlobalScope(TenantScope::class)
                ->whereIn('employee_id', $ids)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->first();
            $ptgCols   = $ptgSample ? array_keys($ptgSample->getAttributes()) : [];
        } catch (\Exception $e) {}

        $ptgShiftTypes = [];
        try {
            $ptgShiftTypes = Pointage::withoutGlobalScope(TenantScope::class)
                ->whereIn('employee_id', $ids)
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->whereNotNull('shift_type')->select('shift_type')->distinct()->pluck('shift_type')->toArray();
        } catch (\Exception $e) {}

        $ptgSamples = [];
        try {
            $ptgSamples = Pointage::withoutGlobalScope(TenantScope::class)
                ->whereIn('employee_id', $ids)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->limit(3)->get()->map(fn($p) => $p->getAttributes())->toArray();
        } catch (\Exception $e) {}

        // Validation status
        $validation = $this->getValidationStatus($tenantId, $startDate, $endDate);

        return [
            'periode'    => $startDate->toDateString() . ' → ' . $endDate->toDateString(),
            'tenant_id'  => $tenantId,
            'validation' => $validation,
            'employes'   => [
                'total'          => $employees->count(),
                'statuts_tous'   => $allStatuts,
                'statuts_actifs' => $employees->pluck('status')->unique()->values(),
                'ids'            => array_slice($ids, 0, 10),
            ],
            'planning'   => [
                'count'       => $planCount,
                'colonnes'    => $planCols,
                'shift_types' => $planShiftTypes,
                'exemples'    => $planSamples,
            ],
            'pointage'   => [
                'count'       => $ptgCount,
                'colonnes'    => $ptgCols,
                'shift_types' => $ptgShiftTypes,
                'exemples'    => $ptgSamples,
            ],
        ];
    }
}
