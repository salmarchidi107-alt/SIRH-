<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GeneratePinsSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::whereNull('pin')->where('status', 'active')->get();
        
        foreach ($employees as $employee) {
            $pin = sprintf('%04d%s', rand(1000, 9999), chr(rand(65, 90)).chr(rand(65, 90)));
            $employee->pin = Hash::make($pin);
            $employee->save();
            
            echo "✅ {$employee->full_name} (ID:{$employee->id}): PIN = {$pin}\n";
        }
        
        echo "\n🎉 Tous PINs générés !\n";
    }
}

