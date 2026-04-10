<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition()
    {
        $departments = ['Administration', 'Médecine', 'Soins Infirmiers', 'Technique', 'Maintenance'];
        $positions = ['Médecin', 'Infirmier', 'Administrateur', 'Technicien', 'Comptable'];
        $contract_types = ['CDI', 'CDD', 'Intérim'];

        $deptName = $this->faker->randomElement($departments);
        $dept = Department::firstOrCreate(['name' => $deptName]);

        return [
            'matricule' => 'EMP' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'department_id' => $dept->id,
            'position' => $this->faker->randomElement($positions),
            'contract_type' => $this->faker->randomElement($contract_types),
            'hire_date' => $this->faker->dateTimeBetween('-10 years', 'now'),
            'birth_date' => $this->faker->dateTimeBetween('-60 years', '-20 years'),
            'status' => $this->faker->randomElement(['active', 'inactive', 'leave']),
            'base_salary' => $this->faker->numberBetween(5000, 15000),
            'cin' => strtoupper($this->faker->bothify('??#######')),
            'cnss' => $this->faker->numerify('########'),
        ];
    }
}

