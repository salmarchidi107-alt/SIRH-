<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('salaries');
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('month');
            $table->smallInteger('year');
            $table->decimal('base_salary', 10, 2);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->decimal('overtime_amount', 10, 2)->default(0);
            $table->decimal('seniority_bonus', 10, 2)->default(0);
            $table->decimal('bonuses', 10, 2)->default(0);
            $table->decimal('transport_allowance', 10, 2)->default(0);
            $table->decimal('gross_salary', 10, 2)->default(0);
            $table->decimal('cnss_deduction', 10, 2)->default(0);
            $table->decimal('amo_deduction', 10, 2)->default(0);
            $table->decimal('fp_deduction', 10, 2)->default(0);
            $table->decimal('taxable_income', 10, 2)->default(0);
            $table->decimal('ir_deduction', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2)->default(0);
            $table->string('status')->default('draft'); // draft | validated | paid
            $table->timestamps();
            $table->unique(['employee_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
