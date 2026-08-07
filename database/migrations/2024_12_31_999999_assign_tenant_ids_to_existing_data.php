<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if already migrated
        $alreadyMigrated = false;
        foreach (['employees', 'absences', 'plannings', 'salaries'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id') && DB::table($table)->whereNotNull('tenant_id')->count() > 0) {
                $alreadyMigrated = true;
                break;
            }
        }
        if ($alreadyMigrated) {
            echo "Data already migrated (checked multiple tables)\n";
            return;
        }

        // Vérifier si un tenant par défaut existe déjà
        $existingTenant = DB::table('tenants')->where('id', 'default-superadmin-tenant')->first();

        if (! $existingTenant) {
            // La table tenants peut avoir plan/status ou non selon les migrations déjà jouées
            $tenantData = [
                'id'         => 'default-superadmin-tenant',
                'name'       => 'Superadmin Central',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Ajouter status uniquement si la colonne existe
            if (Schema::hasColumn('tenants', 'status')) {
                $tenantData['status'] = 'active';
            }
            if (Schema::hasColumn('tenants', 'plan')) {
                $tenantData['plan'] = 'starter';
            }

            DB::table('tenants')->insertOrIgnore($tenantData);
            echo "Created superadmin tenant: default-superadmin-tenant\n";
        }

        $superadminTenantId = 'default-superadmin-tenant';
        echo "Using tenant_id: {$superadminTenantId}\n";

        $tables = [
            'employees', 'absences', 'plannings', 'salaries', 'pointages',
            'departments', 'compteurs_temps', 'droits_absences',
            'news', 'week_templates', 'tablettes'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                $count = DB::table($table)
                    ->whereNull('tenant_id')
                    ->update(['tenant_id' => $superadminTenantId]);
                echo "Updated {$count} records in {$table}\n";
            } else {
                echo "Skipped {$table} (table or tenant_id column does not exist)\n";
            }
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'tenant_id')) {
            $userCount = DB::table('users')
                ->whereNull('tenant_id')
                ->update(['tenant_id' => $superadminTenantId]);
            echo "Updated {$userCount} users with tenant_id\n";
        }

        echo "✅ Data migration complete\n";
    }

    public function down(): void
    {
        $tables = [
            'employees', 'absences', 'plannings', 'salaries', 'pointages',
            'departments', 'compteurs_temps', 'droits_absences',
            'news', 'week_templates', 'tablettes'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)
                    ->where('tenant_id', 'default-superadmin-tenant')
                    ->update(['tenant_id' => null]);
            }
        }

        if (Schema::hasTable('users')) {
            DB::table('users')
                ->where('tenant_id', 'default-superadmin-tenant')
                ->update(['tenant_id' => null]);
        }

        echo "Data migration reverted\n";
    }
};
