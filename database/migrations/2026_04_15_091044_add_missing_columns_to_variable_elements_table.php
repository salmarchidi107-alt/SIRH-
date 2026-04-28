<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('variable_elements', function (Blueprint $table) {
            if (!Schema::hasColumn('variable_elements', 'employee_id')) {
                $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('cascade');
            }
            if (!Schema::hasColumn('variable_elements', 'month')) {
                $table->tinyInteger('month')->unsigned()->nullable();
            }
            if (!Schema::hasColumn('variable_elements', 'year')) {
                $table->smallInteger('year')->unsigned()->nullable();
            }
            if (!Schema::hasColumn('variable_elements', 'label')) {
                $table->string('label')->nullable();
            }
            if (!Schema::hasColumn('variable_elements', 'amount')) {
                $table->decimal('amount', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('variable_elements', 'type')) {
                $table->enum('type', ['gain', 'deduction'])->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('variable_elements', function (Blueprint $table) {
            if (Schema::hasColumn('variable_elements', 'employee_id')) {
                $table->dropForeign(['employee_id']);
            }
            $columnsToDrop = [];
            foreach (['employee_id', 'month', 'year', 'label', 'amount', 'type'] as $col) {
                if (Schema::hasColumn('variable_elements', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
