<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class BadgeTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete existing test data
        Employee::where('matricule', 'TEST001')->delete();
        User::where('email', 'test.badge@hospitalrh.local')->delete();

        // Create User for badge
        $user = User::create([
            'name' => 'Test Badge User',
            'email' => 'test.badge@hospitalrh.local',
            'password' => Hash::make('password123'), 
            'role' => 'employee',
        ]);

        // Create Employee linked
        $employee = Employee::create([
            'matricule' => 'TEST001',
            'hire_date' => now()->subYears(1),
            'first_name' => 'Test',
            'last_name' => 'Badge',
            'email' => 'test.badge@hospitalrh.local',
            'status' => 'active',
            'department' => 'Test Dept',
            'position' => 'Test Position',
            'pin' => Hash::make('1234AB'),
            'user_id' => $user->id,
            'base_salary' => 5000.00,
        ]);

        echo "✅ Test badge data created:\n";
        echo "- Matricule: TEST001\n";
        echo "- PIN: 1234AB\n";
        echo "- Use at http://127.0.0.1:8000/badge/login\n";
    }
}
