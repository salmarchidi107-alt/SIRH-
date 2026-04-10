<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('department_id')
                ->nullable()
                ->after('photo')
                ->constrained()
                ->nullOnDelete();
        });

        $tenantId = 'default-superadmin-tenant';

        // Create departments from employees
        DB::statement("
            INSERT INTO departments (name, slug, tenant_id, created_at, updated_at)
            SELECT DISTINCT
                TRIM(department),
                LOWER(REPLACE(TRIM(department), ' ', '-')),
                '{$tenantId}',
                NOW(),
                NOW()
            FROM employees
            WHERE department IS NOT NULL
              AND TRIM(department) != ''
              AND TRIM(department) NOT IN (
                  SELECT name FROM departments WHERE tenant_id = '{$tenantId}'
              )
        ");

        // Link employees to departments (tenant-safe)
        DB::statement("
            UPDATE employees e
            JOIN departments d
              ON d.name = TRIM(e.department)
             AND d.tenant_id = '{$tenantId}'
            SET e.department_id = d.id
            WHERE e.department IS NOT NULL
              AND TRIM(e.department) != ''
        ");
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
