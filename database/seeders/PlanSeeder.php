<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('plans')->insert([
            [
                'nom'           => 'Starter',
                'slug'          => 'starter',
                'prix_mensuel'  => 99.00,
                'prix_annuel'   => 990.00,
                'max_employes'  => 25,
                'max_admins'    => 1,
                'fonctionnalites' => json_encode(['planning', 'absences']),
                'actif'         => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'nom'           => 'Pro',
                'slug'          => 'pro',
                'prix_mensuel'  => 199.00,
                'prix_annuel'   => 1990.00,
                'max_employes'  => 100,
                'max_admins'    => 3,
                'fonctionnalites' => json_encode(['planning', 'absences', 'paie', 'pointage']),
                'actif'         => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'nom'           => 'Enterprise',
                'slug'          => 'enterprise',
                'prix_mensuel'  => 499.00,
                'prix_annuel'   => 4990.00,
                'max_employes'  => 0, // illimité
                'max_admins'    => 10,
                'fonctionnalites' => json_encode(['planning', 'absences', 'paie', 'pointage', 'rapports', 'api']),
                'actif'         => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);
    }
}
