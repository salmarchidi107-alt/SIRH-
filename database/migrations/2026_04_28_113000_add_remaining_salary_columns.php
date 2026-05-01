<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            if (!Schema::hasColumn('salaries', 'base_salary')) {
                $table->decimal('base_salary', 10, 2)->default(0)->after('working_hours');
            }
            if (!Schema::hasColumn('salaries', 'other_deductions')) {
                $table->decimal('other_deductions', 10, 2)->default(0)->after('garnishment_deduction');
            }
            if (!Schema::hasColumn('salaries', 'absence_deduction')) {
                $table->decimal('absence_deduction', 10, 2)->default(0)->after('absence_days');
            }
            if (!Schema::hasColumn('salaries', 'delay_hours')) {
                $table->decimal('delay_hours', 8, 2)->default(0)->after('absence_hours');
            }
            if (!Schema::hasColumn('salaries', 'cnss_base')) {
                $table->decimal('cnss_base', 10, 2)->default(0)->after('delay_hours');
            }
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['base_salary', 'other_deductions', 'absence_deduction', 'delay_hours', 'cnss_base'] as $col) {
                if (Schema::hasColumn('salaries', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
