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

        $base           = (float) ($data['base_salary'] ?? $employee->base_salary);
        $overtimeHours  = (float) ($data['overtime_hours'] ?? 0);
        $bonuses        = (float) ($data['bonuses'] ?? 0);
        $transportAllow = (float) ($data['transport_allowance'] ?? 0);

        // 1. Éléments variables du mois (ajoutés via l'interface)
$variables = $employee->variableElements()
            ->where('month', $month)
            ->where('year', $year)
            ->get();

$variableGains    = $variables->where('type', \App\Enums\VariableElementType::GAIN)->sum('amount');
        $variableRetenues = $variables->where('type', \App\Enums\VariableElementType::RETENUE)->sum('amount');

        // 2. Prime d'ancienneté
        $seniorityBonus = round($base * $employee->seniority_rate, 2);

        // 3. Heures supplémentaires (taux jour par défaut)
        $hourlyRate   = $base / self::LEGAL_HOURS;
        $otAmount     = round($hourlyRate * $overtimeHours * (self::OT_DAY_RATE - 1), 2);

        // 4. Salaire brut total
        $grossSalary = $base
            + $seniorityBonus
            + $otAmount
            + $bonuses
            + $transportAllow
            + $variableGains
            - $variableRetenues;

        $grossSalary = max(0, round($grossSalary, 2));

        // 5. CNSS salariale (plafonné à 6 000 MAD)
        $cnssBase   = min($grossSalary, self::CNSS_CEILING);
        $cnss       = round($cnssBase * self::CNSS_RATE_SAL, 2);

        // 6. AMO salariale
        $amo = round($grossSalary * self::AMO_RATE, 2);

        // 7. Frais professionnels (20%, max 2 500/mois)
        $fp = min(round($grossSalary * self::FP_RATE, 2), self::FP_MAX_MONTHLY);

        // 8. Net imposable mensuel
        $taxableIncome = max(0, round($grossSalary - $cnss - $amo - $fp, 2));

        // 9. IR (calcul annuel ÷ 12)
        $ir = round($this->calculateIR(
            $taxableIncome * 12,
            $employee->family_status ?? 'celibataire',
            (int) ($employee->children_count ?? 0)
        ) / 12, 2);

        // 10. Net à payer
        $netSalary = round($grossSalary - $cnss - $amo - $ir, 2);

        // ─── Persist ──────────────────────────────────────────────

        return Salary::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'month'       => $month,
                'year'        => $year,
            ],
            [
                'base_salary'         => $base,
                'overtime_hours'      => $overtimeHours,
                'overtime_amount'     => $otAmount,
                'seniority_bonus'     => $seniorityBonus,
                'bonuses'             => $bonuses + $variableGains,
                'transport_allowance' => $transportAllow,
                'gross_salary'        => $grossSalary,
                'cnss_deduction'      => $cnss,
                'amo_deduction'       => $amo,
                'fp_deduction'        => $fp,
                'taxable_income'      => $taxableIncome,
                'ir_deduction'        => $ir,
                'net_salary'          => $netSalary,
                'status'              => 'draft',
            ]
        );
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
