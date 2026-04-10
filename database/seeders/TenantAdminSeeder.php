<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Facades\Tenancy;

class TenantAdminSeeder extends Seeder
{
    public function run(): void
    {
tenancy()->raw(function () {
            $tenant = tenancy()->tenant();
            \App\Models\User::updateOrCreate(
                ['email' => 'admin@' . $tenant->slug . '.local'],
                [
                    'name' => 'Tenant Admin',
                    'password' => Hash::make('password123'),
                    'role' => 'admin',
                    'tenant_id' => $tenant->getKey(),
                    'is_super_admin' => false,
                ]
            );

            $this->command->info('✅ Tenant Admin created: admin@' . $tenant->slug . '.local / password123');
        });
    }
}
