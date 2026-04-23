<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = Tenant::where('slug', 'superadmin')->first()?->id ?? Tenant::create([
            'id' => 'superadmin-tenant-id',
            'name' => 'SuperAdmin',
            'slug' => 'superadmin',
            'status' => 'active',
            'plan_status' => 'pro',
        ])->id;

        $user = User::updateOrCreate(
            ['email' => 'superadmin@hospitalrh.com'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@hospitalrh.com',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
            ]
        );

        $this->command->info("SuperAdmin created: {$user->email} / password");
    }
}
?>

