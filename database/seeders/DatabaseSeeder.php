<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Absence;
use App\Models\Planning;
use App\Models\Salary;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
$this->call([
            UserSeeder::class,
            PlanSeeder::class,
        ]);

        // Create required departments for seed
        $seedDepartments = ['Médecine Générale', 'Chirurgie', 'Urgences', 'Pédiatrie', 'Radiologie', 'Laboratoire', 'Administration', 'Pharmacie', 'Ressources Humaines'];
        foreach ($seedDepartments as $name) {
            Department::firstOrCreate(['name' => $name]);
        }

        $employeesData = [
            ['first_name' => 'Karim', 'last_name' => 'Benali', 'position' => 'Directeur RH', 'department' => 'Ressources Humaines', 'salary' => 18000],
            ['first_name' => 'Fatima', 'last_name' => 'Alaoui', 'position' => 'Médecin Chef', 'department' => 'Médecine Générale', 'salary' => 22000],
            ['first_name' => 'Mohammed', 'last_name' => 'Tazi', 'position' => 'Chirurgien', 'department' => 'Chirurgie', 'salary' => 25000],
            ['first_name' => 'Nadia', 'last_name' => 'Cherkaoui', 'position' => 'Infirmière Chef', 'department' => 'Urgences', 'salary' => 9500],
            ['first_name' => 'Youssef', 'last_name' => 'Mansouri', 'position' => 'Pédiatre', 'department' => 'Pédiatrie', 'salary' => 20000],
            ['first_name' => 'Houda', 'last_name' => 'Benkirane', 'position' => 'Radiologue', 'department' => 'Radiologie', 'salary' => 19000],
            ['first_name' => 'Rachid', 'last_name' => 'Idrissi', 'position' => 'Biologiste', 'department' => 'Laboratoire', 'salary' => 16000],
            ['first_name' => 'Samira', 'last_name' => 'Lahlou', 'position' => 'Pharmacienne', 'department' => 'Pharmacie', 'salary' => 14000],
            ['first_name' => 'Omar', 'last_name' => 'Squalli', 'position' => 'Infirmier', 'department' => 'Chirurgie', 'salary' => 7500],
            ['first_name' => 'Leila', 'last_name' => 'Filali', 'position' => 'Secrétaire Médicale', 'department' => 'Administration', 'salary' => 5500],
            ['first_name' => 'Hassan', 'last_name' => 'Berrada', 'position' => 'Médecin Urgentiste', 'department' => 'Urgences', 'salary' => 21000],
            ['first_name' => 'Zineb', 'last_name' => 'Ouazzani', 'position' => 'Infirmière', 'department' => 'Pédiatrie', 'salary' => 7000],
            ['first_name' => 'Mehdi', 'last_name' => 'Essaidi', 'position' => 'Technicien Labo', 'department' => 'Laboratoire', 'salary' => 8000],
            ['first_name' => 'Sara', 'last_name' => 'Bennani', 'position' => 'Comptable', 'department' => 'Administration', 'salary' => 8500],
            ['first_name' => 'Amine', 'last_name' => 'Bouazza', 'position' => 'Aide-soignant', 'department' => 'Médecine Générale', 'salary' => 5000],
        ];

        $employees = [];
        foreach ($employeesData as $i => $data) {
            $dept = Department::firstWhere('name', $data['department']);
            $emp = Employee::create([
                'matricule' => 'EMP' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => strtolower($data['first_name'] . '.' . $data['last_name'] . '@hospitalrh.ma'),
                'phone' => '06' . rand(10000000, 99999999),
                'department_id' => $dept ? $dept->id : null,
                'position' => $data['position'],
                'contract_type' => ['CDI', 'CDD', 'CDI', 'CDI', 'CDI'][$i % 5],
                'hire_date' => now()->subYears(rand(1, 15))->subMonths(rand(0, 11)),
                'birth_date' => now()->subYears(rand(25, 55)),
                'status' => $i < 13 ? 'active' : ($i === 13 ? 'leave' : 'active'),
                'base_salary' => $data['salary'],
                'cin' => 'A' . rand(100000, 999999),
                'cnss' => rand(1000000, 9999999),
            ]);
            $employees[] = $emp;
        }

        // Create absences
        $absenceTypes = array_keys(Absence::TYPES);
        foreach ($employees as $i => $emp) {
            $numAbsences = rand(1, 4);
            for ($j = 0; $j < $numAbsences; $j++) {
                $start = now()->subMonths(rand(0, 5))->addDays(rand(0, 20));
                $end = (clone $start)->addDays(rand(1, 10));
                Absence::create([
                    'employee_id' => $emp->id,
                    'type' => $absenceTypes[array_rand($absenceTypes)],
                    'start_date' => $start,
                    'end_date' => $end,
                    'days' => $start->diffInWeekdays($end) + 1,
                    'reason' => 'Motif personnel',
                    'status' => ['pending', 'approved', 'approved', 'rejected'][rand(0, 3)],
                    'replacement_id' => count($employees) > 1 ? $employees[array_rand($employees)]->id : null,
                ]);
            }
        }

        // Create planning for this month
        $shiftTypes = array_keys(Planning::SHIFT_TYPES);
        foreach ($employees as $emp) {
            for ($day = 1; $day <= 28; $day++) {
                if ($day % 7 === 0 || $day % 7 === 6) continue; // skip weekends
                $shiftType = $shiftTypes[array_rand($shiftTypes)];
                [$start, $end] = match($shiftType) {
                    'matin' => ['07:00', '14:00'],
                    'apres_midi' => ['14:00', '21:00'],
                    'nuit' => ['21:00', '07:00'],
                    'garde' => ['08:00', '20:00'],
                    default => ['08:00', '17:00'],
                };
                Planning::create([
                    'employee_id' => $emp->id,
                    'date' => now()->startOfMonth()->addDays($day - 1),
                    'shift_start' => $start,
                    'shift_end' => $end,
                    'shift_type' => $shiftType,
                ]);
            }
        }

        // Create salaries for last 3 months
        foreach ($employees as $emp) {
            for ($m = 2; $m >= 0; $m--) {
                $month = now()->subMonths($m);
                $base = $emp->base_salary;
                $bonuses = rand(0, 1) ? rand(500, 2000) : 0;
                $gross = $base + $bonuses;
                $cnss = min($gross * 0.0448, 419.96);
                $amo = $gross * 0.0226;
                $taxable = $gross - $cnss - $amo;
                $ir = $this->calculateIR($taxable * 12) / 12;
                $net = $gross - $cnss - $amo - $ir;

                Salary::create([
                    'employee_id' => $emp->id,
                    'month' => $month->month,
                    'year' => $month->year,
                    'base_salary' => $base,
                    'bonuses' => $bonuses,
                    'deductions' => 0,
                    'overtime_hours' => rand(0, 20),
                    'overtime_pay' => rand(0, 500),
                    'cnss_deduction' => $cnss,
                    'amo_deduction' => $amo,
                    'ir_deduction' => $ir,
                    'net_salary' => $net,
                    'paid_at' => $m > 0 ? $month->endOfMonth() : null,
                ]);
            }
        }
    }

    private function calculateIR(float $annual): float
    {
        if ($annual <= 30000) return 0;
        if ($annual <= 50000) return ($annual - 30000) * 0.10;
        if ($annual <= 60000) return 2000 + ($annual - 50000) * 0.20;
        if ($annual <= 80000) return 4000 + ($annual - 60000) * 0.30;
        if ($annual <= 180000) return 10000 + ($annual - 80000) * 0.34;
        return 44000 + ($annual - 180000) * 0.38;
    }
}

