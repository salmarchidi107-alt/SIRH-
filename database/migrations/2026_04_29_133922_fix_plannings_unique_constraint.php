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
        Schema::table('plannings', function (Blueprint $table) {
            $table->dropUnique('plannings_employee_id_date_unique');
            $table->unique(['employee_id', 'date', 'tenant_id'], 'plannings_employee_id_date_tenant_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plannings', function (Blueprint $table) {
            $table->dropUnique('plannings_employee_id_date_tenant_unique');
            $table->unique(['employee_id', 'date'], 'plannings_employee_id_date_unique');
        });
    }
};
