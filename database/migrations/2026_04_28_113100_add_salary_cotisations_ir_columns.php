<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalaryCotisationsIrColumns extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            if (!Schema::hasColumn('salaries', 'taxable_income')) {
                $table->decimal('taxable_income', 10, 2)->nullable()->after('amo_deduction');
            }
            if (!Schema::hasColumn('salaries', 'ir_annual')) {
                $table->decimal('ir_annual', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('salaries', 'ir_family_deduction')) {
                $table->decimal('ir_family_deduction', 10, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('salaries', 'ir_deduction')) {
                $table->decimal('ir_deduction', 10, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        $columnsToDrop = [];
        foreach (['taxable_income', 'ir_annual', 'ir_family_deduction', 'ir_deduction'] as $col) {
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
