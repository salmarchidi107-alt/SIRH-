<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['plannings', 'pointages', 'absences', 'salaries', 'compteur_temps'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->string('tenant_id', 36)->nullable()->after('id')->index();
                });
            }
        }

        // employees séparément (migration dédiée)
        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'tenant_id')) {
            Schema::table('employees', function (Blueprint $blueprint) {
                $blueprint->string('tenant_id', 36)->nullable()->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        $tables = ['plannings', 'pointages', 'absences', 'salaries', 'compteur_temps', 'employees'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropColumn('tenant_id');
                });
            }
        }
    }
};