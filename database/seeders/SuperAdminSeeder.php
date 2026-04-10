<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@tenantes.io'],
            [
                'name'           => 'Super Admin',
                'password'       => Hash::make('SuperAdmin@2024!'),
                'is_super_admin' => true,
'role'           => User::ROLE_SUPER_ADMIN,
                'tenant_id'      => null,
            ]
        );

        $this->command->info('✅ Super Admin créé : superadmin@tenantes.io');
    }
}
