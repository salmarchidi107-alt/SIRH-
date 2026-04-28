<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plannings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('shift_start');
            $table->time('shift_end');
            $table->enum('shift_type', ['matin', 'apres_midi', 'nuit', 'journee', 'garde'])->default('journee');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'date']);
        });

        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('month');
            $table->year('year');
            $table->decimal('base_salary', 10, 2);
            $table->decimal('bonuses', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('overtime_pay', 10, 2)->default(0);
            $table->decimal('cnss_deduction', 10, 2)->default(0);
            $table->decimal('amo_deduction', 10, 2)->default(0);
            $table->decimal('ir_deduction', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2);
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salaries');
        Schema::dropIfExists('plannings');
    }
};
