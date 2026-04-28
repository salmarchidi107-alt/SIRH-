<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            if (!Schema::hasColumn('salaries', 'salary_type')) {
                $table->enum('salary_type', ['monthly', 'hourly'])->default('monthly');
            }
            if (!Schema::hasColumn('salaries', 'hourly_rate')) {
                $table->decimal('hourly_rate', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('salaries', 'working_hours')) {
                $table->decimal('working_hours', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('salaries', 'overtime_hours_day')) {
                $table->decimal('overtime_hours_day', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('salaries', 'overtime_hours_night')) {
                $table->decimal('overtime_hours_night', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('salaries', 'overtime_hours_weekend')) {
                $table->decimal('overtime_hours_weekend', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('salaries', 'absence_hours')) {
                $table->decimal('absence_hours', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('salaries', 'delay_hours')) {
                $table->decimal('delay_hours', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('salaries', 'overtime_day_amount')) {
                $table->decimal('overtime_day_amount', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('salaries', 'overtime_night_amount')) {
                $table->decimal('overtime_night_amount', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('salaries', 'overtime_weekend_amount')) {
                $table->decimal('overtime_weekend_amount', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('salaries', 'mode_cotisation')) {
                $table->string('mode_cotisation', 20)->default('auto');
            }
            if (!Schema::hasColumn('salaries', 'cnss_deduction_manual')) {
                $table->decimal('cnss_deduction_manual', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('salaries', 'amo_deduction_manual')) {
                $table->decimal('amo_deduction_manual', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('salaries', 'fp_deduction_manual')) {
                $table->decimal('fp_deduction_manual', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('salaries', 'performance_bonus')) {
                $table->decimal('performance_bonus', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('salaries', 'transport_allowance')) {
                $table->decimal('transport_allowance', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('salaries', 'meal_allowance')) {
                $table->decimal('meal_allowance', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('salaries', 'housing_allowance')) {
                $table->decimal('housing_allowance', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('salaries', 'responsibility_allowance')) {
                $table->decimal('responsibility_allowance', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('salaries', 'other_gains')) {
                $table->decimal('other_gains', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('salaries', 'taxable_income')) {
                $table->decimal('taxable_income', 10, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        $columnsToDrop = [];
        foreach ([
            'salary_type', 'hourly_rate', 'working_hours',
            'overtime_hours_day', 'overtime_hours_night', 'overtime_hours_weekend',
            'absence_hours', 'delay_hours',
            'overtime_day_amount', 'overtime_night_amount', 'overtime_weekend_amount',
            'mode_cotisation', 'cnss_deduction_manual', 'amo_deduction_manual', 'fp_deduction_manual',
            'performance_bonus', 'transport_allowance', 'meal_allowance',
            'housing_allowance', 'responsibility_allowance', 'other_gains',
            'taxable_income',
        ] as $col) {
            if (Schema::hasColumn('salaries', $col)) {
                $columnsToDrop[] = $col;
            }
        }
        if (!empty($columnsToDrop)) {
            Schema::table('salaries', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }
};
