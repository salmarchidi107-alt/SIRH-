<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Salary;
use App\Models\VariableElement;

class PayrollService
{
    // ─── Taux légaux marocains ─────────────────────────────────────

    const CNSS_RATE_SAL   = 0.0448;  // Salariale
    const CNSS_RATE_PAT   = 0.1029;  // Patronale
    const CNSS_CEILING    = 6000;    // Plafond mensuel MAD
    const AMO_RATE        = 0.0226;  // Salariale & patronale
    const TFP_RATE        = 0.016;   // Taxe formation professionnelle (patronale)
    const FP_RATE         = 0.20;    // Frais professionnels
    const FP_MAX_MONTHLY  = 2500;    // Plafond mensuel frais pro
    const OT_DAY_RATE     = 1.25;    // Heures supp. jour (25%)
    const OT_NIGHT_RATE   = 1.50;    // Heures supp. nuit (50%)
    const LEGAL_HOURS     = 191;     // Heures légales mensuelles

    // ─── Barème IR (annuel) ────────────────────────────────────────

    const IR_BRACKETS = [
        [0,      30000,  0.00, 0],
        [30001,  50000,  0.10, 3000],
        [50001,  60000,  0.20, 8000],
        [60001,  80000,  0.30, 14000],
        [80001,  180000, 0.34, 17200],
        [180001, PHP_INT_MAX, 0.38, 24400],
    ];

    // ─── Calcul principal ──────────────────────────────────────────

    public function calculate(Employee $employee, array $data): Salary
    {
        $month = (int) $data['month'];
        $year  = (int) $data['year'];

        // Récupérer ou créer le salary record
        $salary = Salary::firstOrNew([
            'employee_id' => $employee->id,
            'month'       => $month,
            'year'        => $year,
        ]);

        // ─── Type de salaire (mensuel / horaire) ──────────────────
        $salaryType = $data['salary_type'] ?? $employee->default_salary_type ?? 'monthly';
        
        // ─── Déterminer le salaire de base ─────────────────────────
        if ($salaryType === 'hourly') {
            $workingHours = (float) ($data['working_hours'] ?? $salary->working_hours ?? 0);
            $hourlyRate   = (float) ($data['hourly_rate'] ?? $employee->hourly_rate ?? 0);
            $base         = round($workingHours * $hourlyRate, 2);
        } else {
            $base = (float) ($data['base_salary'] ?? $employee->base_salary);
        }

        // ─── Heures travaillées détaillées ──────────────────────
        $workingHours       = (float) ($data['working_hours'] ?? $salary->working_hours ?? 0);
        $overtimeHoursDay   = (float) ($data['overtime_hours_day'] ?? $salary->overtime_hours_day ?? 0);
        $overtimeHoursNight = (float) ($data['overtime_hours_night'] ?? $salary->overtime_hours_night ?? 0);
        $overtimeHoursWe    = (float) ($data['overtime_hours_weekend'] ?? $salary->overtime_hours_weekend ?? 0);
        $absenceHours       = (float) ($data['absence_hours'] ?? $salary->absence_hours ?? 0);
        $delayHours         = (float) ($data['delay_hours'] ?? $salary->delay_hours ?? 0);

        // 1. Éléments variables du mois
        $variables = $employee->variableElements()
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $variableGains    = $variables->where('type', \App\Enums\VariableElementType::GAIN)->sum('amount');
        $variableRetenues = $variables->where('type', \App\Enums\VariableElementType::RETENUE)->sum('amount');

        // 2. Prime d'ancienneté
        $seniorityBonus = round($base * $employee->seniority_rate, 2);

        // 3. Heures supplémentaires
        $hourlyRate   = $base / self::LEGAL_HOURS;
        $otDayAmount     = round($hourlyRate * $overtimeHoursDay * (self::OT_DAY_RATE - 1), 2);
        $otNightAmount   = round($hourlyRate * $overtimeHoursNight * (self::OT_NIGHT_RATE - 1), 2);
        $otWeekendAmount = round($hourlyRate * $overtimeHoursWe * (self::OT_DAY_RATE - 1), 2);
        $totalOtAmount   = $otDayAmount + $otNightAmount + $otWeekendAmount;

        // 4. Primes et indemnités
        $performanceBonus      = (float) ($data['performance_bonus'] ?? 0);
        $transportAllowance    = (float) ($data['transport_allowance'] ?? 0);
        $mealAllowance         = (float) ($data['meal_allowance'] ?? 0);
        $housingAllowance      = (float) ($data['housing_allowance'] ?? 0);
        $responsibilityAllow   = (float) ($data['responsibility_allowance'] ?? 0);
        $otherGains            = (float) ($data['other_gains'] ?? 0);

        // 5. Salaire brut total
        $grossSalary = $base
            + $seniorityBonus
            + $totalOtAmount
            + $performanceBonus
            + $transportAllowance
            + $mealAllowance
            + $housingAllowance
            + $responsibilityAllow
            + $otherGains
            + $variableGains
            - $variableRetenues;

        $grossSalary = max(0, round($grossSalary, 2));

        // 6-8. Cotisations (mode automatique ou manuel)
        $modeCotisation = $data['mode_cotisation'] ?? 'auto';

        if ($modeCotisation === 'manual') {
            // Mode manuel
            $cnss = (float) ($data['cnss_deduction_manual'] ?? 0);
            $amo  = (float) ($data['amo_deduction_manual'] ?? 0);
            $fp   = (float) ($data['fp_deduction_manual'] ?? 0);
        } else {
            // Mode automatique
            $cnssBase = min($grossSalary, self::CNSS_CEILING);
            $cnss     = round($cnssBase * self::CNSS_RATE_SAL, 2);
            $amo      = round($grossSalary * self::AMO_RATE, 2);
            $fp       = min(round($grossSalary * self::FP_RATE, 2), self::FP_MAX_MONTHLY);
        }

        // 7. Retenues salariales
        $absenceDeduction     = (float) ($data['absence_deduction'] ?? 0);
        $advanceDeduction     = (float) ($data['advance_deduction'] ?? 0);
        $loanDeduction        = (float) ($data['loan_deduction'] ?? 0);
        $garnishmentDeduction = (float) ($data['garnishment_deduction'] ?? 0);
        $otherDeductions      = (float) ($data['other_deductions'] ?? 0);

        // 8. Net imposable mensuel
        $taxableIncome = max(0, round($grossSalary - $cnss - $amo - $fp, 2));

        // 9. IR (calcul annuel ÷ 12)
        $ir = round($this->calculateIR(
            $taxableIncome * 12,
            $employee->family_status ?? 'celibataire',
            (int) ($employee->children_count ?? 0)
        ) / 12, 2);

        // 10. Net à payer
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

        // ─── Mise à jour du record ────────────────────────────────

        $salary->fill([
            'salary_type'              => $salaryType,
            'hourly_rate'              => $salaryType === 'hourly' ? $hourlyRate : null,
            'working_hours'            => $workingHours,
            'overtime_hours_day'       => $overtimeHoursDay,
            'overtime_hours_night'     => $overtimeHoursNight,
            'overtime_hours_weekend'   => $overtimeHoursWe,
            'absence_hours'            => $absenceHours,
            'delay_hours'              => $delayHours,
            'base_salary'              => $base,
            'overtime_hours'           => (float)($overtimeHoursDay + $overtimeHoursNight + $overtimeHoursWe),
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
            'cnss_deduction'           => $modeCotisation === 'auto' ? $cnss : (float)($data['cnss_deduction_manual'] ?? 0),
            'cnss_deduction_manual'    => $modeCotisation === 'manual' ? $cnss : null,
            'amo_deduction'            => $modeCotisation === 'auto' ? $amo : (float)($data['amo_deduction_manual'] ?? 0),
            'amo_deduction_manual'     => $modeCotisation === 'manual' ? $amo : null,
            'fp_deduction'             => $modeCotisation === 'auto' ? $fp : (float)($data['fp_deduction_manual'] ?? 0),
            'fp_deduction_manual'      => $modeCotisation === 'manual' ? $fp : null,
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
        return $salary;
    }

    // ─── Calcul IR barème progressif marocain ──────────────────────

    public function calculateIR(float $annualIncome, string $familyStatus, int $children): float
    {
        if ($annualIncome <= 0) return 0;

        $ir = 0;
        foreach (self::IR_BRACKETS as [$min, $max, $rate, $deduction]) {
            if ($annualIncome > $min) {
                $ir = ($annualIncome * $rate) - $deduction;
            }
        }

        // Déductions familiales
        $familyDeduction = 0;
        if ($familyStatus === 'marie') {
            $familyDeduction += 360;
        }
        $familyDeduction += min($children, 6) * 360; // Plafonné à 6 enfants

        return max(0, $ir - $familyDeduction);
    }

    // ─── Récupérer heures travaillées du mois ──────────────────────

    /**
     * Récupère les heures travaillées d'un employé pour un mois donné
     * depuis la table pointages
     */
    public function getMonthlyWorkingHours(int $employeeId, int $month, int $year): array
    {
        $pointages = \App\Models\Pointage::where('employee_id', $employeeId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        $workingHours = 0;
        $overtimeHoursDay = 0;
        $overtimeHoursNight = 0;
        $overtimeHoursWeekend = 0;
        $absenceHours = 0;
        $delayHours = 0;

        foreach ($pointages as $p) {
            $workingHours += (float) ($p->heures_realisees ?? 0);
            $overtimeHoursDay += (float) ($p->heures_supp_jour ?? 0);
            $overtimeHoursNight += (float) ($p->heures_supp_nuit ?? 0);
            $overtimeHoursWeekend += (float) ($p->heures_supp_weekend ?? 0);
            $absenceHours += (float) ($p->heures_absence ?? 0);
            $delayHours += (float) ($p->heures_retard ?? 0);
        }

        return [
            'working_hours'     => round($workingHours, 2),
            'overtime_day'      => round($overtimeHoursDay, 2),
            'overtime_night'    => round($overtimeHoursNight, 2),
            'overtime_weekend'  => round($overtimeHoursWeekend, 2),
            'absence_hours'     => round($absenceHours, 2),
            'delay_hours'       => round($delayHours, 2),
        ];
    }

    // ─── Simulation (sans persist) ─────────────────────────────────

    public function simulate(Employee $employee, array $data): array
    {
        $salary = $this->calculate($employee, $data);

        // Retour d'une simulation sans sauvegarder
        // On récupère le record puis on le supprime si c'était draft
        $result = $salary->toArray();
        return $result;
    }

    // ─── Résumé masse salariale mensuelle ─────────────────────────

    public function getMonthlySummary(int $month, int $year): array
    {
        $cacheKey = "payroll.summary.{$month}.{$year}";
        
        return cache()->remember($cacheKey, now()->addHour(), function () use ($month, $year) {
            $stats = Salary::where('month', $month)
                ->where('year', $year)
                ->selectRaw('
                    COUNT(*) as count,
                    SUM(gross_salary) as total_gross,
                    SUM(cnss_deduction) as total_cnss_sal,
                    SUM(amo_deduction) as total_amo_sal,
                    SUM(ir_deduction) as total_ir,
                    SUM(net_salary) as total_net,
                    SUM(CASE WHEN status = "validated" THEN 1 ELSE 0 END) as count_validated,
                    SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as count_paid,
                    SUM(CASE WHEN status = "draft" THEN 1 ELSE 0 END) as count_draft,
                    SUM(LEAST(gross_salary, ' . self::CNSS_CEILING . ')) as cnss_bases,
                    SUM(gross_salary) as total_gross_for_rates
                ')
                ->first();

            $cnssCeilSum = $stats->cnss_bases ?? 0;
            $grossSum = $stats->total_gross_for_rates ?? 0;

            return [
                'total_gross'           => (float) ($stats->total_gross ?? 0),
                'total_cnss_sal'        => (float) ($stats->total_cnss_sal ?? 0),
                'total_amo_sal'         => (float) ($stats->total_amo_sal ?? 0),
                'total_ir'              => (float) ($stats->total_ir ?? 0),
                'total_net'             => (float) ($stats->total_net ?? 0),
                'count'                 => (int) $stats->count,
                'count_validated'       => (int) $stats->count_validated,
                'count_paid'            => (int) $stats->count_paid,
                'count_draft'           => (int) $stats->count_draft,
                'total_employer_cnss'   => round($cnssCeilSum * self::CNSS_RATE_PAT, 2),
                'total_employer_amo'    => round($grossSum * self::AMO_RATE, 2),
                'total_employer_tfp'    => round($grossSum * self::TFP_RATE, 2),
                'total_employer_cost'   => round(
                    $cnssCeilSum * self::CNSS_RATE_PAT + 
                    $grossSum * self::AMO_RATE + 
                    $grossSum * self::TFP_RATE, 2
                ),
            ];
        });
    }

}
