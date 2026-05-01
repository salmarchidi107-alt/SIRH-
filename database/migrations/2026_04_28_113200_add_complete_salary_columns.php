<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompleteSalaryColumns extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            if (!Schema::hasColumn('salaries', 'net_salary')) {
                $table->decimal('net_salary', 10, 2)->nullable()->after('ir_deduction');
            }
            if (!Schema::hasColumn('salaries', 'employer_cnss')) {
                $table->decimal('employer_cnss', 10, 2)->nullable()->after('net_salary');
            }
            if (!Schema::hasColumn('salaries', 'employer_amo')) {
                $table->decimal('employer_amo', 10, 2)->nullable()->after('employer_cnss');
            }
            if (!Schema::hasColumn('salaries', 'employer_tfp')) {
                $table->decimal('employer_tfp', 10, 2)->nullable()->after('employer_amo');
            }
            if (!Schema::hasColumn('salaries', 'employer_total_cost')) {
                $table->decimal('employer_total_cost', 10, 2)->nullable()->after('employer_tfp');
            }
        });
    }

    public function down(): void
    {
        $columnsToDrop = [];
        foreach (['net_salary', 'employer_cnss', 'employer_amo', 'employer_tfp', 'employer_total_cost'] as $col) {
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
}
