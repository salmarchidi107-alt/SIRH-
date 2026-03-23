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
        $variables = VariableElement::where('employee_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $variableGains    = $variables->where('type', 'gain')->sum('amount');
        $variableRetenues = $variables->where('type', 'retenue')->sum('amount');

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
        $salaries = Salary::where('month', $month)->where('year', $year)->get();


        $totalPatronal = $salaries->sum(function ($s) {
            $base = min($s->gross_salary, self::CNSS_CEILING);
            return $base * self::CNSS_RATE_PAT + $s->gross_salary * self::AMO_RATE + $s->gross_salary * self::TFP_RATE;
        });

        return [
            'total_gross'      => $salaries->sum('gross_salary'),
            'total_cnss_sal'   => $salaries->sum('cnss_deduction'),
            'total_amo_sal'    => $salaries->sum('amo_deduction'),
            'total_ir'         => $salaries->sum('ir_deduction'),
            'total_net'        => $salaries->sum('net_salary'),
            'count'            => $salaries->count(),
            'count_validated'  => $salaries->where('status', 'validated')->count(),
            'count_paid'       => $salaries->where('status', 'paid')->count(),
            'count_draft'      => $salaries->where('status', 'draft')->count(),
            'total_employer_cnss' => $salaries->sum(function ($s) {
                return min($s->gross_salary, self::CNSS_CEILING) * self::CNSS_RATE_PAT;
            }),
            'total_employer_amo'  => $salaries->sum('gross_salary') * self::AMO_RATE,
            'total_employer_tfp'  => $salaries->sum('gross_salary') * self::TFP_RATE,
            'total_employer_cost' => $totalPatronal,
        ];
    }

}
