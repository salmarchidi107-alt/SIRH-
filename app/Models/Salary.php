<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salary extends Model
{
    protected $fillable = [
        'employee_id', 'month', 'year',
        // Gains
        'base_salary', 'seniority_bonus',
        'overtime_hours', 'overtime_day_amount', 'overtime_night_amount', 'overtime_weekend_amount',
        'performance_bonus', 'transport_allowance', 'meal_allowance',
        'housing_allowance', 'responsibility_allowance', 'other_gains',
        'gross_salary',
        // Retenues
        'absence_days', 'absence_deduction',
        'advance_deduction', 'loan_deduction', 'garnishment_deduction', 'other_deductions',
        // Cotisations
        'cnss_base', 'cnss_deduction', 'amo_deduction', 'fp_deduction',
        // IR
        'taxable_income', 'ir_annual', 'ir_family_deduction', 'ir_deduction',
        // Patronal
        'employer_cnss', 'employer_amo', 'employer_tfp', 'employer_total_cost',
        // Net
        'net_salary', 'status', 'notes',
        // ─── NEW: Mode cotisations et type salaire ─────────
        'mode_cotisation', 'cnss_deduction_manual', 'amo_deduction_manual', 'fp_deduction_manual',
        'salary_type', 'hourly_rate', 'working_hours',
        'overtime_hours_day', 'overtime_hours_night', 'overtime_hours_weekend',
        'absence_hours', 'delay_hours',
    ];

    protected $casts = [
        'base_salary'              => 'decimal:2',
        'seniority_bonus'          => 'decimal:2',
        'overtime_hours'           => 'decimal:2',
        'overtime_day_amount'      => 'decimal:2',
        'overtime_night_amount'    => 'decimal:2',
        'overtime_weekend_amount'  => 'decimal:2',
        'performance_bonus'        => 'decimal:2',
        'transport_allowance'      => 'decimal:2',
        'meal_allowance'           => 'decimal:2',
        'housing_allowance'        => 'decimal:2',
        'responsibility_allowance' => 'decimal:2',
        'other_gains'              => 'decimal:2',
        'gross_salary'             => 'decimal:2',
        'absence_days'             => 'decimal:2',
        'absence_deduction'        => 'decimal:2',
        'advance_deduction'        => 'decimal:2',
        'loan_deduction'           => 'decimal:2',
        'garnishment_deduction'    => 'decimal:2',
        'other_deductions'         => 'decimal:2',
        'cnss_base'                => 'decimal:2',
        'cnss_deduction'           => 'decimal:2',
        'amo_deduction'            => 'decimal:2',
        'fp_deduction'             => 'decimal:2',
        'taxable_income'           => 'decimal:2',
        'ir_annual'                => 'decimal:2',
        'ir_family_deduction'      => 'decimal:2',
        'ir_deduction'             => 'decimal:2',
        'employer_cnss'            => 'decimal:2',
        'employer_amo'             => 'decimal:2',
        'employer_tfp'             => 'decimal:2',
        'employer_total_cost'      => 'decimal:2',
        'net_salary'               => 'decimal:2',
        // ─── NEW: New fields casting ────────────────────────────
        'cnss_deduction_manual'    => 'decimal:2',
        'amo_deduction_manual'     => 'decimal:2',
        'fp_deduction_manual'      => 'decimal:2',
        'hourly_rate'              => 'decimal:2',
        'working_hours'            => 'decimal:2',
        'overtime_hours_day'       => 'decimal:2',
        'overtime_hours_night'     => 'decimal:2',
        'overtime_hours_weekend'   => 'decimal:2',
        'absence_hours'            => 'decimal:2',
        'delay_hours'              => 'decimal:2',
    ];

    // ── Relations ──────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // ── Accesseurs ─────────────────────────────────────────────────

    public function getMonthNameAttribute(): string
    {
        return match ($this->month) {
            1 => 'Janvier',
            2 => 'Février',
            3 => 'Mars',
            4 => 'Avril',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juillet',
            8 => 'Août',
            9 => 'Septembre',
            10 => 'Octobre',
            11 => 'Novembre',
            12 => 'Décembre',
            default => '',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'validated' => 'Validé',
            'paid'      => 'Payé',
            default     => 'Brouillon',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'validated' => 'success',
            'paid'      => 'info',
            default     => 'warning',
        };
    }

    // ── Totaux calculés ────────────────────────────────────────────

    public function getTotalOvertimeAttribute(): float
    {
        return round(
            $this->overtime_day_amount +
            $this->overtime_night_amount +
            $this->overtime_weekend_amount, 2
        );
    }

    public function getTotalGainsAttribute(): float
    {
        return round(
            $this->base_salary +
            $this->seniority_bonus +
            $this->total_overtime +
            $this->performance_bonus +
            $this->transport_allowance +
            $this->meal_allowance +
            $this->housing_allowance +
            $this->responsibility_allowance +
            $this->other_gains, 2
        );
    }

    public function getTotalSalarialDeductionsAttribute(): float
    {
        return round(
            $this->absence_deduction +
            $this->advance_deduction +
            $this->loan_deduction +
            $this->garnishment_deduction +
            $this->other_deductions, 2
        );
    }

    public function getTotalCotisationsAttribute(): float
    {
        return round($this->cnss_deduction + $this->amo_deduction, 2);
    }

    public function getTotalRetentionsAttribute(): float
    {
        return round(
            $this->total_salarial_deductions +
            $this->total_cotisations +
            $this->ir_deduction, 2
        );
    }

    // ── Helpers cotisations ────────────────────────────────────────

    public function isCotisationAuto(): bool
    {
        return $this->mode_cotisation === 'auto';
    }

    public function isCotisationManual(): bool
    {
        return $this->mode_cotisation === 'manual';
    }

    public function isHourlyPayroll(): bool
    {
        return $this->salary_type === 'hourly';
    }

    public function isMonthlyPayroll(): bool
    {
        return $this->salary_type === 'monthly';
    }

    /**
     * Récupère les cotisations effectives (auto ou manuelles)
     */
    public function getEffectiveCnss(): float
    {
        return $this->isCotisationManual() && $this->cnss_deduction_manual !== null
            ? (float) $this->cnss_deduction_manual
            : (float) $this->cnss_deduction;
    }

    public function getEffectiveAmo(): float
    {
        return $this->isCotisationManual() && $this->amo_deduction_manual !== null
            ? (float) $this->amo_deduction_manual
            : (float) $this->amo_deduction;
    }

    public function getEffectiveFp(): float
    {
        return $this->isCotisationManual() && $this->fp_deduction_manual !== null
            ? (float) $this->fp_deduction_manual
            : (float) $this->fp_deduction;
    }

    /**
     * Total heures supplémentaires
     */
    public function getTotalOvertimeHours(): float
    {
        return round(
            ($this->overtime_hours_day ?? 0) +
            ($this->overtime_hours_night ?? 0) +
            ($this->overtime_hours_weekend ?? 0), 2
        );
    }
}

