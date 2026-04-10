<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'absences', 'plannings', 'salaries', 'pointages',
            'departments', 'compteurs_temps', 'droits_absences',
            'news', 'week_templates', 'tablettes'
        ];

        foreach ($tables as $tableName) {

            // 1. Add column only
            if (!Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->uuid('tenant_id')->nullable()->index();
                });
            }

            // 2. Add FK only if NOT exists (safe try/catch)
            try {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreign('tenant_id')
                        ->references('id')
                        ->on('tenants')
                        ->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // FK already exists → ignore
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'absences', 'plannings', 'salaries', 'pointages',
            'departments', 'compteurs_temps', 'droits_absences',
            'news', 'week_templates', 'tablettes'
        ];

        foreach ($tables as $tableName) {

            // drop FK safely
            try {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['tenant_id']);
                });
            } catch (\Exception $e) {}

            // drop column safely
            if (Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('tenant_id');
                });
            }
        }
    }
};
