<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CentralTenant as Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ValidateTenantUsersSeeder extends Seeder
{
    public function run()
    {
        // On central DB
        DB::connection('central')->table('tenants')->chunk(100, function ($tenants) {
            foreach ($tenants as $tenant) {
                // Switch to tenant DB
                tenancy()->initialize($tenant);

                // Check users tenant_id
                User::where('tenant_id', '!=', $tenant->id)
                    ->orWhereNull('tenant_id')
                    ->update(['tenant_id' => $tenant->id]);

                tenancy()->end();
            }
        });

        echo "Tenant users validated.\n";
    }
}

