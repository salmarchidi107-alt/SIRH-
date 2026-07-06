<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Salary;
use App\Models\VariableElement;
use App\Models\Planning;
use Illuminate\Support\Facades\Auth;

class PayrollService
{
    // ─── Taux légaux marocains ─────────────────────────────────────

    const CNSS_RATE_SAL   = 0.0448;
    const CNSS_RATE_PAT   = 0.1029;
    const CNSS_CEILING    = 6000;
    const AMO_RATE        = 0.0226;
    const TFP_RATE        = 0.016;
    const FP_RATE         = 0.20;
    const FP_MAX_MONTHLY  = 2500;
    const OT_DAY_RATE     = 1.25;
    const OT_NIGHT_RATE   = 1.50;
    const LEGAL_HOURS     = 191;

    const IR_BRACKETS = [
        [0,      30000,  0.00, 0],
        [30001,  50000,  0.10, 3000],
        [50001,  60000,  0.20, 8000],
        [60001,  80000,  0.30, 14000],
        [80001,  180000, 0.34, 17200],
        [180001, PHP_INT_MAX, 0.38, 24400],
    ];

    // ─── Helper tenant_id ──────────────────────────────────────────

    private function getTenantId(): mixed
    {
        $tenantId = config('app.current_tenant_id');
        if (blank($tenantId) && Auth::check()) {
            $tenantId = Auth::user()->tenant_id;
        }
        return filled($tenantId) ? $tenantId : null;
    }

    // ─── Normaliser family_status ──────────────────────────────────

    private function normalizeFamilyStatus(string $status): string
    {
        $s = strtolower(trim($status));
        $s = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
        $s = preg_replace('/[^a-z]/', '', $s);
        return $s;
    }

    // ─── Calcul principal ──────────────────────────────────────────

    public function calculate(Employee $employee, array $data): Salary
    {
        $month = (int) $data['month'];
        $year  = (int) $data['year'];

        $salary = Salary::firstOrNew([
            'employee_id' => $employee->id,
            'month'       => $month,
            'year'        => $year,
        ]);

        $salaryType = $data['salary_type'] ?? $employee->default_salary_type ?? 'monthly';

        if ($salaryType === 'hourly') {
            $workingHours = (float) ($data['working_hours'] ?? $salary->working_hours ?? 0);
            $hourlyRate   = (float) ($data['hourly_rate'] ?? $employee->hourly_rate ?? 0);
            $base         = round($workingHours * $hourlyRate, 2);
        } else {
            $base = (float) ($data['base_salary'] ?? $employee->base_salary);
        }

        $workingHours       = (float) ($data['working_hours']          ?? $salary->working_hours        ?? 0);
        $overtimeHoursDay   = (float) ($data['overtime_hours_day']     ?? $salary->overtime_hours_day   ?? 0);
        $overtimeHoursNight = (float) ($data['overtime_hours_night']   ?? $salary->overtime_hours_night ?? 0);
        $overtimeHoursWe    = (float) ($data['overtime_hours_weekend'] ?? $salary->overtime_hours_weekend ?? 0);
        $absenceHours       = (float) ($data['absence_hours']          ?? $salary->absence_hours        ?? 0);
        $delayHours         = (float) ($data['delay_hours']            ?? $salary->delay_hours          ?? 0);
        $gardeHours         = (float) ($data['garde_hours']            ?? $salary->garde_hours          ?? 0);

        $gardeOverride  = (bool)  ($data['garde_override']   ?? $salary->garde_override  ?? false);
        $gardeIndemnite = (float) ($data['garde_indemnite']  ?? $salary->garde_indemnite ?? 0);

        $variables = $employee->variableElements()
            ->where('month', $month)
            ->where('year',  $year)
            ->get();

        $variableGains    = $variables->where('type', \App\Enums\VariableElementType::GAIN)->sum('amount');
        $variableRetenues = $variables->where('type', \App\Enums\VariableElementType::RETENUE)->sum('amount');

        $seniorityBonus = round($base * $employee->seniority_rate, 2);

        $hourlyRate      = $base / self::LEGAL_HOURS;
        $otDayAmount     = round($hourlyRate * $overtimeHoursDay   * (self::OT_DAY_RATE   - 1), 2);
        $otNightAmount   = round($hourlyRate * $overtimeHoursNight * (self::OT_NIGHT_RATE - 1), 2);
        $otWeekendAmount = round($hourlyRate * $overtimeHoursWe    * (self::OT_DAY_RATE   - 1), 2);
        $totalOtAmount   = $otDayAmount + $otNightAmount + $otWeekendAmount;

        $performanceBonus    = (float) ($data['performance_bonus']        ?? 0);
        $transportAllowance  = (float) ($data['transport_allowance']      ?? 0);
        $mealAllowance       = (float) ($data['meal_allowance']           ?? 0);
        $housingAllowance    = (float) ($data['housing_allowance']        ?? 0);
        $responsibilityAllow = (float) ($data['responsibility_allowance'] ?? 0);
        $otherGains          = (float) ($data['other_gains']              ?? 0);

        $gardeAmount = $gardeOverride
            ? $gardeIndemnite
            : round($hourlyRate * $gardeHours, 2);

        $grossSalary = $base
            + $seniorityBonus
            + $totalOtAmount
            + $performanceBonus
            + $transportAllowance
            + $mealAllowance
            + $housingAllowance
            + $responsibilityAllow
            + $otherGains
            + $gardeAmount
            + $variableGains
            - $variableRetenues;

        $grossSalary = max(0, round($grossSalary, 2));

        $modeCotisation = $data['mode_cotisation'] ?? 'auto';

        if ($modeCotisation === 'manual') {
            $cnss = (float) ($data['cnss_deduction_manual'] ?? 0);
            $amo  = (float) ($data['amo_deduction_manual']  ?? 0);
            $fp   = (float) ($data['fp_deduction_manual']   ?? 0);
        } else {
            $cnssBase = min($grossSalary, self::CNSS_CEILING);
            $cnss     = round($cnssBase    * self::CNSS_RATE_SAL, 2);
            $amo      = round($grossSalary * self::AMO_RATE,      2);
            $fp       = min(round($grossSalary * self::FP_RATE, 2), self::FP_MAX_MONTHLY);
        }

        $absenceDeduction     = (float) ($data['absence_deduction']     ?? 0);
        $advanceDeduction     = (float) ($data['advance_deduction']     ?? 0);
        $loanDeduction        = (float) ($data['loan_deduction']        ?? 0);
        $garnishmentDeduction = (float) ($data['garnishment_deduction'] ?? 0);
        $otherDeductions      = (float) ($data['other_deductions']      ?? 0);

        $taxableIncome = max(0, round($grossSalary - $cnss - $amo - $fp, 2));

        $rawFamilyStatus = $employee->family_situation ?? 'celibataire';
        $childrenCount   = (int) ($employee->children_count ?? 0);

        $ir = round($this->calculateIR(
            $taxableIncome * 12,
            $rawFamilyStatus,
            $childrenCount
        ) / 12, 2);

        $netSalary = round(
            $grossSalary
            - $cnss
            - $amo
            - $ir
            - $absenceDeduction
            - $advanceDeduction
            - $loanDeduction
            - $garnishmentDeduction
            - $otherDeductions, 2
        );

        $salary->fill([
            'salary_type'              => $salaryType,
            'hourly_rate'              => $salaryType === 'hourly' ? $hourlyRate : null,
            'working_hours'            => $workingHours,
            'overtime_hours_day'       => $overtimeHoursDay,
            'overtime_hours_night'     => $overtimeHoursNight,
            'overtime_hours_weekend'   => $overtimeHoursWe,
            'absence_hours'            => $absenceHours,
            'delay_hours'              => $delayHours,
            'garde_hours'              => $gardeHours,
            'garde_indemnite'          => $gardeAmount,
            'garde_override'           => $gardeOverride,
            'base_salary'              => $base,
            'overtime_hours'           => (float) ($overtimeHoursDay + $overtimeHoursNight + $overtimeHoursWe),
            'overtime_day_amount'      => $otDayAmount,
            'overtime_night_amount'    => $otNightAmount,
            'overtime_weekend_amount'  => $otWeekendAmount,
            'seniority_bonus'          => $seniorityBonus,
            'performance_bonus'        => $performanceBonus,
            'transport_allowance'      => $transportAllowance,
            'meal_allowance'           => $mealAllowance,
            'housing_allowance'        => $housingAllowance,
            'responsibility_allowance' => $responsibilityAllow,
            'other_gains'              => $otherGains,
            'gross_salary'             => $grossSalary,
            'mode_cotisation'          => $modeCotisation,
            'cnss_deduction'           => $modeCotisation === 'auto'   ? $cnss : (float) ($data['cnss_deduction_manual'] ?? 0),
            'cnss_deduction_manual'    => $modeCotisation === 'manual' ? $cnss : null,
            'amo_deduction'            => $modeCotisation === 'auto'   ? $amo  : (float) ($data['amo_deduction_manual']  ?? 0),
            'amo_deduction_manual'     => $modeCotisation === 'manual' ? $amo  : null,
            'fp_deduction'             => $modeCotisation === 'auto'   ? $fp   : (float) ($data['fp_deduction_manual']   ?? 0),
            'fp_deduction_manual'      => $modeCotisation === 'manual' ? $fp   : null,
            'taxable_income'           => $taxableIncome,
            'ir_deduction'             => $ir,
            'absence_deduction'        => $absenceDeduction,
            'advance_deduction'        => $advanceDeduction,
            'loan_deduction'           => $loanDeduction,
            'garnishment_deduction'    => $garnishmentDeduction,
            'other_deductions'         => $otherDeductions,
            'net_salary'               => $netSalary,
            'status'                   => 'draft',
        ]);

        $salary->save();

        $this->clearSummaryCache($month, $year);

        return $salary;
    }

    // ─── Calcul IR ─────────────────────────────────────────────────

    public function calculateIR(float $annualIncome, string $familyStatus, int $children): float
    {
        if ($annualIncome <= 0) return 0;

        $status = $this->normalizeFamilyStatus($familyStatus);

        $ir = 0;
        foreach (self::IR_BRACKETS as [$min, $max, $rate, $deduction]) {
            if ($annualIncome > $min) {
                $ir = ($annualIncome * $rate) - $deduction;
            }
        }

        $familyDeduction = 0;

        if (str_starts_with($status, 'mari')) {
            $familyDeduction += 360;
        }

        $familyDeduction += min($children, 6) * 360;

        return max(0, $ir - $familyDeduction);
    }

    // ─── Heures travaillées + gardes + détail shifts ───────────────

    public function getMonthlyWorkingHours(int $employeeId, int $month, int $year): array
    {
        // ══════════════════════════════════════════════════════════
        // 0. PLANNING DU MOIS — utilisé comme référence pour les retards
        //    Indexé par date (Y-m-d) => shift_start (premier shift du jour)
        // ══════════════════════════════════════════════════════════
        $planningsDuMois = Planning::where('employee_id', $employeeId)
            ->whereYear('date',  $year)
            ->whereMonth('date', $month)
            ->orderBy('shift_start')
            ->get();

        $planningParDate = [];
        foreach ($planningsDuMois as $pl) {
            $dateKey = $pl->date instanceof \Carbon\Carbon
                ? $pl->date->format('Y-m-d')
                : \Carbon\Carbon::parse($pl->date)->format('Y-m-d');

            if (!isset($planningParDate[$dateKey]) && !empty($pl->shift_start)) {
                $planningParDate[$dateKey] = $pl->shift_start;
            }
        }

        // ══════════════════════════════════════════════════════════
        // 1. POINTAGES — heures travaillées, retards, absences
        // ══════════════════════════════════════════════════════════
        $pointages = \App\Models\Pointage::where('employee_id', $employeeId)
            ->whereYear('date',  $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        $workingHours  = 0;
        $overtimeHours = 0;
        $absenceHours  = 0;
        $delayHours    = 0;

        $pointageShifts = [];
        $overtimeShifts = [];
        $delayShifts    = [];

        // ── IMPORTANT : initialisé ICI (avant la boucle des pointages)
        //    car on y ajoute désormais aussi les absences détectées
        //    directement depuis les pointages (statut absent),
        //    en plus de celles de la table `absences` (section 2).
        //    Avant ce fix, $absenceShifts n'était alimenté QUE par la
        //    table `absences`, jamais par les pointages — d'où le
        //    total (16h, calculé depuis les pointages) qui ne
        //    correspondait à aucune ligne affichée dans le modal.
        $absenceShifts = [];

        foreach ($pointages as $p) {
            $wh = (float) ($p->heures_travaillees     ?? 0);
            $oh = (float) ($p->heures_supplementaires ?? 0);

            $workingHours  += $wh;
            $overtimeHours += $oh;

            // ── Date normalisée (nécessaire pour consulter le planning
            //    et pour reconstruire des Carbon fiables) ────────────
            $dateStr = $p->date instanceof \Carbon\Carbon
                ? $p->date->format('Y-m-d')
                : \Carbon\Carbon::parse($p->date)->format('Y-m-d');

            // ══════════════════════════════════════════════════════
            // CALCUL DU RETARD — priorité :
            //   1) heure_prevue du pointage (si renseignée)
            //   2) shift_start du Planning du jour (fallback fiable)
            //   3) retard_minutes stocké — UNIQUEMENT si positif
            // ══════════════════════════════════════════════════════
            $retardMin   = 0;
            $heurePrevue = $p->heure_prevue ?? null;

            if (empty($heurePrevue) && !empty($p->heure_entree)) {
                $heurePrevue = $planningParDate[$dateStr] ?? null;
            }

            if (!empty($p->heure_entree) && !empty($heurePrevue)) {
                try {
                    $entree = \Carbon\Carbon::parse($dateStr . ' ' . \Carbon\Carbon::parse($p->heure_entree)->format('H:i:s'));
                    $prevue = \Carbon\Carbon::parse($dateStr . ' ' . \Carbon\Carbon::parse($heurePrevue)->format('H:i:s'));

                    if ($entree->gt($prevue)) {
                        $retardMin = abs($entree->diffInMinutes($prevue, true));
                        $delayHours += round($retardMin / 60, 2);
                    }
                } catch (\Exception $e) {}
            } elseif (!empty($p->retard_minutes) && (int) $p->retard_minutes > 0) {
                $retardMin  = (int) $p->retard_minutes;
                $delayHours += round($retardMin / 60, 2);
            }

            // ══════════════════════════════════════════════════════
            // ABSENCE — détectée directement depuis le statut du pointage
            // ══════════════════════════════════════════════════════
            if (in_array($p->statut ?? '', ['absent', 'absence_injustifiee'])) {
                $absenceHours += 8.0;

                // ── Ajout : peupler la liste pour le modal ──────────
                // Avant ce fix, seule la table `absences` (demandes
                // approuvées) alimentait cette liste ; les pointages
                // marqués "absent" directement n'y apparaissaient
                // jamais, d'où la liste vide malgré un total > 0.
                $absenceShifts[] = [
                    'date'   => $dateStr,
                    'type'   => $p->statut === 'absence_injustifiee'
                                    ? 'Absence injustifiée'
                                    : 'Absence',
                    'heures' => 8,
                    'statut' => 'pointage',
                ];
            }

            // ── Shift pointage (modal heures travaillées) ────────
            if ($wh > 0 || !empty($p->heure_entree)) {
                $pointageShifts[] = [
                    'date'         => $dateStr,
                    'heure_entree' => $p->heure_entree ?? null,
                    'heure_sortie' => $p->heure_sortie ?? null,
                    'duree_heures' => $wh,
                ];
            }

            // ── Shift heures supp (modal overtime) ───────────────
            if ($oh > 0) {
                $otDay     = (float) ($p->overtime_day     ?? $p->heures_sup_jour     ?? 0);
                $otNight   = (float) ($p->overtime_night   ?? $p->heures_sup_nuit     ?? 0);
                $otWeekend = (float) ($p->overtime_weekend ?? $p->heures_sup_weekend  ?? 0);

                if ($otDay + $otNight + $otWeekend == 0) {
                    $otDay = $oh;
                }

                if ($otDay > 0) {
                    $overtimeShifts[] = [
                        'date'         => $dateStr,
                        'type'         => 'day',
                        'duree_heures' => $otDay,
                    ];
                }
                if ($otNight > 0) {
                    $overtimeShifts[] = [
                        'date'         => $dateStr,
                        'type'         => 'night',
                        'duree_heures' => $otNight,
                    ];
                }
                if ($otWeekend > 0) {
                    $overtimeShifts[] = [
                        'date'         => $dateStr,
                        'type'         => 'weekend',
                        'duree_heures' => $otWeekend,
                    ];
                }
            }

            // ── Shift retard (modal delay) ────────────────────────
            if ($retardMin > 0) {
                $delayShifts[] = [
                    'date'           => $dateStr,
                    'heure_entree'   => $p->heure_entree ?? null,
                    'heure_prevue'   => $heurePrevue,
                    'retard_minutes' => $retardMin,
                ];
            }
        }

        // ══════════════════════════════════════════════════════════
        // 2. ABSENCES depuis la table absences (demandes approuvées)
        //    — vient S'AJOUTER aux absences déjà détectées depuis
        //    les pointages ci-dessus, sans les dupliquer si possible.
        // ══════════════════════════════════════════════════════════
        try {
            $absences = \App\Models\Absence::where('employee_id', $employeeId)
                ->where(function ($q) {
                    $q->whereRaw('LOWER(status) LIKE ?', ['appro%'])
                      ->orWhereRaw('LOWER(status) LIKE ?', ['valid%']);
                })
                ->where(function ($q) use ($month, $year) {
                    $q->where(function ($q2) use ($month, $year) {
                        $q2->whereMonth('date_debut', $month)
                           ->whereYear('date_debut', $year);
                    })->orWhere(function ($q2) use ($month, $year) {
                        $q2->whereMonth('date_fin', $month)
                           ->whereYear('date_fin', $year);
                    });
                })
                ->orderBy('date_debut')
                ->get();

            // Dates déjà couvertes par les pointages, pour éviter
            // d'afficher un doublon si l'absence existe aussi comme
            // demande formelle ET comme pointage marqué absent.
            $datesDejaAjoutees = array_column($absenceShifts, 'date');

            foreach ($absences as $a) {
                $debut  = $a->date_debut instanceof \Carbon\Carbon
                    ? $a->date_debut->copy()->startOfDay()
                    : \Carbon\Carbon::parse($a->date_debut)->startOfDay();
                $fin    = $a->date_fin instanceof \Carbon\Carbon
                    ? $a->date_fin->copy()->startOfDay()
                    : \Carbon\Carbon::parse($a->date_fin ?? $a->date_debut)->startOfDay();

                $type   = $a->type ?? $a->motif ?? 'Absence';
                $statut = $a->status ?? 'approuvee';

                $cursor = $debut->copy();
                while ($cursor->lte($fin)) {
                    $curDateStr = $cursor->format('Y-m-d');

                    if ((int) $cursor->month === $month
                        && (int) $cursor->year  === $year
                        && $cursor->dayOfWeek !== 0
                        && !in_array($curDateStr, $datesDejaAjoutees)
                    ) {
                        $absenceShifts[] = [
                            'date'   => $curDateStr,
                            'type'   => $type,
                            'heures' => 8,
                            'statut' => $statut,
                        ];
                    }
                    $cursor->addDay();
                }
            }

            // Retrier par date pour un affichage cohérent dans le modal
            usort($absenceShifts, fn($a, $b) => strcmp($a['date'], $b['date']));

        } catch (\Exception $e) {
            // La table absences peut ne pas exister dans tous les tenants
        }

        // ══════════════════════════════════════════════════════════
        // 3. GARDES depuis le planning (avec détail par shift)
        // ══════════════════════════════════════════════════════════
        $gardeShifts = Planning::where('employee_id', $employeeId)
            ->where('shift_type', 'garde')
            ->whereYear('date',  $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        $gardeHours = 0;
        $gardeData  = [];

        foreach ($gardeShifts as $shift) {
            $duree = 0;

            if ($shift->shift_start && $shift->shift_end) {
                $start = \Carbon\Carbon::parse($shift->shift_start);
                $end   = \Carbon\Carbon::parse($shift->shift_end);
                if ($end->lte($start)) {
                    $end->addDay();
                }
                $duree = round($start->diffInMinutes($end) / 60, 2);
            } else {
                $duree = 8;
            }

            $gardeHours += $duree;

            $dateFormatted = $shift->date instanceof \Carbon\Carbon
                ? $shift->date->format('Y-m-d')
                : \Carbon\Carbon::parse($shift->date)->format('Y-m-d');

            $gardeData[] = [
                'date'         => $dateFormatted,
                'shift_start'  => $shift->shift_start,
                'shift_end'    => $shift->shift_end,
                'room'         => $shift->room,
                'duree_heures' => $duree,
            ];
        }

        // ══════════════════════════════════════════════════════════
        // Retour complet avec tous les shifts pour les modals
        // ══════════════════════════════════════════════════════════
        return [
            'working_hours'    => round($workingHours,  2),
            'overtime_day'     => round($overtimeHours, 2),
            'overtime_night'   => 0,
            'overtime_weekend' => 0,
            'absence_hours'    => round($absenceHours,  2),
            'delay_hours'      => round($delayHours,    2),
            'garde_hours'      => round($gardeHours,    2),
            'garde_days'       => count($gardeData),

            'garde_shifts'    => $gardeData,
            'pointage_shifts' => $pointageShifts,
            'overtime_shifts' => $overtimeShifts,
            'absence_shifts'  => $absenceShifts,
            'delay_shifts'    => $delayShifts,
        ];
    }

    // ─── Résumé masse salariale ────────────────────────────────────

    public function getMonthlySummary(int $month, int $year): array
    {
        $tenantId = $this->getTenantId();

        $cacheKey = "payroll.summary.{$tenantId}.{$month}.{$year}";

        return cache()->remember($cacheKey, now()->addMinutes(5), function () use ($month, $year, $tenantId) {

            $query = Salary::where('month', $month)
                ->where('year', $year);

            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            }

            $ceiling = self::CNSS_CEILING;
            $sql = "COUNT(*) as count,"
                . " COALESCE(SUM(gross_salary), 0) as total_gross,"
                . " COALESCE(SUM(cnss_deduction), 0) as total_cnss_sal,"
                . " COALESCE(SUM(amo_deduction), 0) as total_amo_sal,"
                . " COALESCE(SUM(ir_deduction), 0) as total_ir,"
                . " COALESCE(SUM(net_salary), 0) as total_net,"
                . " COALESCE(SUM(CASE WHEN status = 'validated' THEN 1 ELSE 0 END), 0) as count_validated,"
                . " COALESCE(SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END), 0) as count_paid,"
                . " COALESCE(SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END), 0) as count_draft,"
                . " COALESCE(SUM(LEAST(gross_salary, {$ceiling})), 0) as cnss_bases,"
                . " COALESCE(SUM(employer_total_cost), 0) as total_employer_cost_stored";

            $stats = $query->selectRaw($sql)->first();

            $cnssCeilSum  = (float) ($stats->cnss_bases                ?? 0);
            $grossSum     = (float) ($stats->total_gross                ?? 0);
            $employerCost = (float) ($stats->total_employer_cost_stored ?? 0);

            if ($employerCost <= 0 && $grossSum > 0) {
                $employerCost = round(
                    $cnssCeilSum * self::CNSS_RATE_PAT +
                    $grossSum    * self::AMO_RATE       +
                    $grossSum    * self::TFP_RATE, 2
                );
            }

            return [
                'total_gross'         => (float) ($stats->total_gross    ?? 0),
                'total_cnss_sal'      => (float) ($stats->total_cnss_sal ?? 0),
                'total_amo_sal'       => (float) ($stats->total_amo_sal  ?? 0),
                'total_ir'            => (float) ($stats->total_ir       ?? 0),
                'total_net'           => (float) ($stats->total_net      ?? 0),
                'count'               => (int)   ($stats->count          ?? 0),
                'count_validated'     => (int)   ($stats->count_validated ?? 0),
                'count_paid'          => (int)   ($stats->count_paid      ?? 0),
                'count_draft'         => (int)   ($stats->count_draft     ?? 0),
                'total_employer_cnss' => round($cnssCeilSum * self::CNSS_RATE_PAT, 2),
                'total_employer_amo'  => round($grossSum    * self::AMO_RATE,      2),
                'total_employer_tfp'  => round($grossSum    * self::TFP_RATE,      2),
                'total_employer_cost' => $employerCost,
            ];
        });
    }

    // ─── Vider le cache summary ────────────────────────────────────

    public function clearSummaryCache(int $month, int $year): void
    {
        $tenantId = $this->getTenantId();
        cache()->forget("payroll.summary.{$tenantId}.{$month}.{$year}");
    }

    // ─── Simulation sans persist ───────────────────────────────────

    public function simulate(Employee $employee, array $data): array
    {
        $salary = $this->calculate($employee, $data);
        return $salary->toArray();
    }
}
