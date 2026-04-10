<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SuperAdminLogin extends Command
{
    protected $signature = 'superadmin:login {email} {--password=admin123}';
    protected $description = 'Login as super admin for development';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->option('password');

        // Super admin - no tenant
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'is_super_admin' => true,
                'role' => 'superadmin',
                'tenant_id' => null,
            ]
        );

        Auth::login($user);

        $this->info("Logged in as {$user->email} (Super Admin)");
        $this->info('Session cookie set. Open browser to localhost:8000');
        $this->line('User data: ' . json_encode($user->only('id', 'email', 'role', 'is_super_admin', 'tenant_id')));
    }
}

