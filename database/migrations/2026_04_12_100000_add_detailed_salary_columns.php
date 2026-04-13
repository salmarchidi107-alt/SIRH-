<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            if (!Schema::hasColumn('salaries', 'performance_bonus')) {
                $table->decimal('performance_bonus', 10, 2)->default(0)->after('base_salary');
            }
            if (!Schema::hasColumn('salaries', 'transport_allowance')) {
                $table->decimal('transport_allowance', 10, 2)->default(0)->after('performance_bonus');
            }
            if (!Schema::hasColumn('salaries', 'meal_allowance')) {
                $table->decimal('meal_allowance', 10, 2)->default(0)->after('transport_allowance');
            }
            if (!Schema::hasColumn('salaries', 'housing_allowance')) {
                $table->decimal('housing_allowance', 10, 2)->default(0)->after('meal_allowance');
            }
            if (!Schema::hasColumn('salaries', 'responsibility_allowance')) {
                $table->decimal('responsibility_allowance', 10, 2)->default(0)->after('housing_allowance');
            }
            if (!Schema::hasColumn('salaries', 'overtime_day_hours')) {
                $table->decimal('overtime_day_hours', 8, 2)->default(0)->after('responsibility_allowance');
            }
            if (!Schema::hasColumn('salaries', 'overtime_night_hours')) {
                $table->decimal('overtime_night_hours', 8, 2)->default(0)->after('overtime_day_hours');
            }
            if (!Schema::hasColumn('salaries', 'overtime_weekend_hours')) {
                $table->decimal('overtime_weekend_hours', 8, 2)->default(0)->after('overtime_night_hours');
            }
            if (!Schema::hasColumn('salaries', 'absence_days')) {
                $table->decimal('absence_days', 4, 1)->default(0)->after('overtime_weekend_hours');
            }
            if (!Schema::hasColumn('salaries', 'advance_deduction')) {
                $table->decimal('advance_deduction', 10, 2)->default(0)->after('absence_days');
            }
            if (!Schema::hasColumn('salaries', 'loan_deduction')) {
                $table->decimal('loan_deduction', 10, 2)->default(0)->after('advance_deduction');
            }
            if (!Schema::hasColumn('salaries', 'garnishment_deduction')) {
                $table->decimal('garnishment_deduction', 10, 2)->default(0)->after('loan_deduction');
            }
            if (!Schema::hasColumn('salaries', 'gross_salary')) {
                $table->decimal('gross_salary', 10, 2)->after('garnishment_deduction');
            }
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn([
                'performance_bonus',
                'transport_allowance',
                'meal_allowance',
                'housing_allowance',
                'responsibility_allowance',
                'overtime_day_hours',
                'overtime_night_hours',
                'overtime_weekend_hours',
                'absence_days',
                'advance_deduction',
                'loan_deduction',
                'garnishment_deduction',
                'gross_salary',
            ]);
        });
    }
};

