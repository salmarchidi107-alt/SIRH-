<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('plannings')) {
            Schema::create('plannings', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id')->nullable()->index();
                $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
                $table->date('date');
                $table->time('shift_start')->nullable();
                $table->time('shift_end')->nullable();
                $table->enum('shift_type', ['matin', 'apres_midi', 'nuit', 'journee', 'garde'])->default('journee');
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                // Unicité composite avec tenant_id (multi-tenant)
                $table->unique(['employee_id', 'date', 'tenant_id'], 'plannings_employee_id_date_tenant_unique');
            });
        }

        if (!Schema::hasTable('salaries')) {
            Schema::create('salaries', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id')->nullable()->index();
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
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                // Unicité composite avec tenant_id (multi-tenant)
                $table->unique(['employee_id', 'month', 'year', 'tenant_id'], 'salaries_employee_month_year_tenant_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('salaries');
        Schema::dropIfExists('plannings');
    }
};
