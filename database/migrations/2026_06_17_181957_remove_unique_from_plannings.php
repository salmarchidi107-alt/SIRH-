<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Supprimer les FK qui s'appuient sur l'index unique
        Schema::table('plannings', function (Blueprint $table) {
            $table->dropForeign('plannings_employee_id_foreign');
            $table->dropForeign('plannings_tenant_id_foreign');
        });

        // 2. Supprimer l'index unique
        Schema::table('plannings', function (Blueprint $table) {
            $table->dropIndex('plannings_employee_id_date_tenant_unique');
        });

        // 3. Recréer les FK sans l'index unique
        Schema::table('plannings', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('plannings', function (Blueprint $table) {
            $table->dropForeign('plannings_employee_id_foreign');
            $table->dropForeign('plannings_tenant_id_foreign');
        });

        Schema::table('plannings', function (Blueprint $table) {
            $table->unique(['employee_id', 'date', 'tenant_id'], 'plannings_employee_id_date_tenant_unique');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }
};
