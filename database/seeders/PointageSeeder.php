<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pointage;
use App\Models\Employee;
use Carbon\Carbon;

class PointageSeeder extends Seeder
{
    public function run()
    {
        $employees = Employee::where('status', 'active')->get();
        
        if ($employees->isEmpty()) {
            $this->command->info('Aucun employee trouve. Creez des employes d\'abord.');
            return;
        }

        $annee = Carbon::now()->year;
        $mois = Carbon::now()->month;
        
        foreach ($employees as $employee) {
            // Créer des pointages pour le mois en cours
            $debut = Carbon::create($annee, $mois, 1);
            $fin = $debut->copy()->endOfMonth();
            
            $jour = $debut->copy();
            while ($jour <= $fin) {
                // Ne pas travailler le weekend
                if (!in_array($jour->dayOfWeek, [0, 6])) {
                    // Simuler des heures de travail (8h par jour en moyenne)
                    $heuresBase = 8;
                    $heuresSupp = rand(0, 2); // 0-2h supplémentaires aléatoire
                    
                    Pointage::create([
                        'employee_id' => $employee->id,
                        'date' => $jour->format('Y-m-d'),
                        'heure_entree' => '08:00',
                        'heure_sortie' => '17:00',
                        'heures_travaillees' => $heuresBase,
                        'heures_supplementaires' => $heuresSupp,
                        'statut' => 'present',
                        'commentaire' => 'Pointage automatique',
                    ]);
                }
                $jour->addDay();
            }
            
            // Créer aussi des données pour les mois précédents (3 derniers mois)
            for ($m = 1; $m <= 3; $m++) {
                $moisPrecedent = Carbon::now()->subMonths($m);
                $debut = Carbon::create($moisPrecedent->year, $moisPrecedent->month, 1);
                $fin = $debut->copy()->endOfMonth();
                
                $jour = $debut->copy();
                while ($jour <= $fin) {
                    if (!in_array($jour->dayOfWeek, [0, 6])) {
                        $heuresBase = 8;
                        $heuresSupp = rand(0, 2);
                        
                        Pointage::create([
                            'employee_id' => $employee->id,
                            'date' => $jour->format('Y-m-d'),
                            'heure_entree' => '08:00',
                            'heure_sortie' => '17:00',
                            'heures_travaillees' => $heuresBase,
                            'heures_supplementaires' => $heuresSupp,
                            'statut' => 'present',
                            'commentaire' => 'Pointage automatique',
                        ]);
                    }
                    $jour->addDay();
                }
            }
        }
        
        $this->command->info('Donnees de pointage creees pour ' . $employees->count() . ' employe(s)');
    }
}

