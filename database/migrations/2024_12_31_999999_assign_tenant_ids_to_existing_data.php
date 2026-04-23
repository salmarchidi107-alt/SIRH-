<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Tenant;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if already migrated (check multiple tables)
        $alreadyMigrated = false;
foreach (['employees', 'absences', 'plannings', 'salaries'] as $table) {
            if (Schema::hasTable($table) && DB::table($table)->whereNotNull('tenant_id')->count() > 0) {
                $alreadyMigrated = true;
                break;
            }
        }
        if ($alreadyMigrated) {
            echo "Data already migrated (checked multiple tables)\n";
            return;
        }

        // Get or create superadmin tenant
        $superadminTenant = Tenant::whereHas('users', fn($q) => $q->where('role', 'superadmin'))->first();
        if (! $superadminTenant) {
            $superadminTenant = Tenant::create([
                'id' => 'default-superadmin-tenant',
                'name' => 'Superadmin Central',
                'slug' => 'superadmin',
                'status' => 'active',
                'plan_status' => 'pro'
            ]);
            echo "Created superadmin tenant: default-superadmin-tenant\n";
        }
        $superadminTenantId = $superadminTenant->id;

        echo "Using tenant_id: {$superadminTenantId}\n";

        // Update all business tables with tenant_id (safe checks)
        $tables = [
            'employees', 'absences', 'plannings', 'salaries', 'pointages',
            'departments', 'compteurs_temps', 'droits_absences',
            'news', 'week_templates', 'tablettes'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)
                    ->whereNull('tenant_id')
                    ->update(['tenant_id' => $superadminTenantId]);
                echo "Updated {$count} records in {$table}\n";
            } else {
                echo "Skipped {$table} (table does not exist)\n";
            }
        }

        // Update users safely
        if (Schema::hasTable('users')) {
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
                echo "Reverted {$table}\n";
            }
        }

        // Revert users
        if (Schema::hasTable('users')) {
            DB::table('users')
                ->where('tenant_id', 'default-superadmin-tenant')
                ->update(['tenant_id' => null]);
        }

        echo "Data migration reverted\n";
    }
};
