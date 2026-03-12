<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Pointage;
use App\Models\CompteurTemps;
use App\Models\DroitAbsence;
use App\Models\Salary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VueEnsembleController extends Controller
{
    /**
     * Affiche la vue d'ensemble du temps de travail
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $annee = $request->get('annee', Carbon::now()->year);
        $mois = $request->get('mois', Carbon::now()->month);
        
        // Recherche par employé
        $employeeId = $request->get('employee_id');
        $department = $request->get('department');
        
        $departments = Employee::distinct()->pluck('department')->filter()->values();

        // Sélection d'un employé
        $selectedEmployee = null;
        
        if ($employeeId) {
            $selectedEmployee = Employee::with(['user'])->find($employeeId);
        } elseif ($department) {
            $selectedEmployee = Employee::with(['user'])->where('department', $department)->first();
        }
        
        // Auto-liaison si pas d'employé sélectionné
        if (!$selectedEmployee && $user) {
            $selectedEmployee = Employee::with(['user'])->where('user_id', $user->id)->first();
            
            if (!$selectedEmployee && $user->email) {
                $selectedEmployee = Employee::with(['user'])->where('email', $user->email)->first();
                if ($selectedEmployee && !$selectedEmployee->user_id) {
                    $selectedEmployee->user_id = $user->id;
                    $selectedEmployee->save();
                }
            }
            
            if (!$selectedEmployee && $user->name) {
                $parts = explode(' ', $user->name, 2);
                if (count($parts) == 2) {
                    $selectedEmployee = Employee::with(['user'])
                        ->where('first_name', 'like', '%' . $parts[0] . '%')
                        ->where('last_name', 'like', '%' . $parts[1] . '%')
                        ->first();
                    if ($selectedEmployee && !$selectedEmployee->user_id) {
                        $selectedEmployee->user_id = $user->id;
                        $selectedEmployee->save();
                    }
                }
            }
        }

        // Employé par défaut si none
        if (!$selectedEmployee) {
            $selectedEmployee = new Employee();
            $selectedEmployee->id = 0;
            $selectedEmployee->first_name = $user ? $user->name : 'Utilisateur';
            $selectedEmployee->last_name = '';
            $selectedEmployee->email = $user ? $user->email : '';
            $selectedEmployee->position = 'Employé';
            $selectedEmployee->matricule = '';
            $selectedEmployee->department = '';
            $selectedEmployee->contract_type = 'CDI';
            $selectedEmployee->work_hours = 35;
            $selectedEmployee->work_days = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];
            $selectedEmployee->base_salary = 0;
            $selectedEmployee->setRelation('user', $user);
        }

        $employee = $selectedEmployee;
        $listeEmployesSelect = Employee::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'matricule', 'department']);

        // Données du mois en cours
        $compteurMois = null;
        $joursDetails = [];
        
        if ($employee && $employee->id > 0) {
            $compteurMois = CompteurTemps::getOuCreeParMois($employee->id, $annee, $mois);
            
            // Détails par jour
            $joursDetails = $this->getJoursDetails($employee->id, $annee, $mois);
        } else {
            $compteurMois = $this->getDefaultCompteur();
        }

        // Semaines du mois
        $semaines = [];
        if ($employee && $employee->id > 0) {
            $semaines = $this->getSemainesDuMois($employee->id, $annee, $mois);
        }

        // Graphique 12 derniers mois
        $graphiqueMois = $this->getGraphiqueMois($employee->id ?? 0, $annee);

        // Navigation mois
        $moisDisponibles = $this->getMoisDisponibles($annee);

        // Mois précédent et suivant pour la période glissante
        $moisPrecedent = Carbon::create($annee, $mois)->subMonth();
        $moisSuivant = Carbon::create($annee, $mois)->addMonth();

        return view('vue-ensemble.index', compact(
            'employee',
            'listeEmployesSelect',
            'departments',
            'compteurMois',
            'joursDetails',
            'semaines',
            'graphiqueMois',
            'moisDisponibles',
            'annee',
            'mois',
            'employeeId',
            'department',
            'moisPrecedent',
            'moisSuivant'
        ));
    }
    
    /**
     * Obtenir les détails par jour pour le mois
     */
    private function getJoursDetails($employeeId, $annee, $mois)
    {
        $jours = [];
        $debut = Carbon::create($annee, $mois, 1);
        $fin = Carbon::create($annee, $mois, 1)->endOfMonth();
        
        $current = $debut->copy();
        
        while ($current <= $fin) {
            $jourSemaine = strtolower($current->locale('fr')->dayName);
            
            $pointages = Pointage::parEmployee($employeeId)
                ->whereDate('date', $current->format('Y-m-d'))
                ->get();
            
            $heuresTravaillees = $pointages->sum('heures_travaillees');
            $heuresSupp = $pointages->sum('heures_supplementaires');
            
            $jours[] = [
                'date' => $current->format('Y-m-d'),
                'jour' => $current->format('d'),
                'nom_jour' => $current->locale('fr')->shortDayName,
                'is_weekend' => in_array($jourSemaine, ['samedi', 'dimanche']),
                'heures_travaillees' => $heuresTravaillees,
                'heures_supplementaires' => $heuresSupp,
                'total' => $heuresTravaillees + $heuresSupp,
            ];
            
            $current->addDay();
        }
        
        return $jours;
    }
    
    /**
     * Obtenir les données par semaine pour un mois
     */
    private function getSemainesDuMois($employeeId, $annee, $mois)
    {
        if (!$employeeId) {
            return [];
        }

        $debut = Carbon::create($annee, $mois, 1)->startOfWeek();
        $fin = Carbon::create($annee, $mois, 1)->endOfMonth()->endOfWeek();

        $semaines = [];
        $current = $debut->copy();
        $numSem = 1;
        
        $heuresPlanifiees = 35; // Par défaut

        while ($current <= $fin) {
            $debutSem = $current->copy();
            $finSem = $current->copy()->endOfWeek();

            $pointages = Pointage::parEmployee($employeeId)
                ->parSemaine($debutSem->format('Y-m-d'), $finSem->format('Y-m-d'))
                ->get();

            $heuresTravaillees = $pointages->sum('heures_travaillees');
            $heuresSupp = $pointages->sum('heures_supplementaires');
            $total = $heuresTravaillees + $heuresSupp;
            
            // Calculer les heures non travaillées (weekends)
            $nbJoursSemaine = 5; // Du lundi au vendredi
            $heuresNonTravaillees = 0;
            
            // Jours détails pour la semaine
            $joursSemaine = [];
            $jourCourant = $debutSem->copy();
            while ($jourCourant <= $finSem) {
                $jourSemaine = strtolower($jourCourant->locale('fr')->dayName);
                $isWeekend = in_array($jourSemaine, ['samedi', 'dimanche']);
                
                $pointageJour = $pointages->filter(function($p) use ($jourCourant) {
                    return $p->date == $jourCourant->format('Y-m-d');
                })->first();
                
                $joursSemaine[] = [
                    'date' => $jourCourant->format('Y-m-d'),
                    'jour' => $jourCourant->format('d'),
                    'nom_jour' => $jourCourant->locale('fr')->shortDayName,
                    'is_weekend' => $isWeekend,
                    'heures_travaillees' => $pointageJour ? $pointageJour->heures_travaillees : 0,
                    'heures_supplementaires' => $pointageJour ? $pointageJour->heures_supplementaires : 0,
                    'status' => $pointageJour ? ($pointageJour->heure_entree ? 'present' : 'absent') : 'non defini',
                ];
                
                $jourCourant->addDay();
            }

            $semaines[] = [
                'numero' => $numSem,
                'debut' => $debutSem->format('d/m'),
                'fin' => $finSem->format('d/m'),
                'debut_raw' => $debutSem->format('Y-m-d'),
                'fin_raw' => $finSem->format('Y-m-d'),
                'heures_planifiees' => $heuresPlanifiees,
                'heures_realisees' => round($heuresTravaillees, 2),
                'heures_supplementaires' => round($heuresSupp, 2),
                'heures_non_travaillees' => $heuresNonTravaillees,
                'total' => round($total, 2),
                'solde' => round($total - $heuresPlanifiees, 2),
                'jours' => $joursSemaine,
            ];

            $current->addWeek();
            $numSem++;
        }

        return $semaines;
    }

    /**
     * Données graphique pour les 12 mois
     */
    private function getGraphiqueMois($employeeId, $annee)
    {
        $nomsMois = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        $donnees = [];

        for ($m = 1; $m <= 12; $m++) {
            $compteur = null;
            if ($employeeId) {
                $compteur = CompteurTemps::where('employee_id', $employeeId)
                    ->where('annee', $annee)
                    ->where('mois', $m)
                    ->first();
            }

            $donnees[] = [
                'mois' => $nomsMois[$m - 1],
                'numero' => $m,
                'heures_planifiees' => isset($compteur) ? (float) $compteur->heures_planifiees : 140,
                'heures_realisees' => isset($compteur) ? (float) $compteur->heures_realisees : 0,
                'heures_supp' => isset($compteur) ? (float) $compteur->heures_supplementaires : 0,
            ];
        }

        return $donnees;
    }

    /**
     * Liste des mois pour la navigation
     */
    private function getMoisDisponibles($annee)
    {
        $noms = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];

        return collect($noms)->map(fn($nom, $num) => [
            'numero' => $num,
            'nom' => $nom,
            'court' => substr($nom, 0, 3),
        ]);
    }
    
    /**
     * Compteur par défaut
     */
    private function getDefaultCompteur()
    {
        $compteur = new \stdClass();
        $compteur->heures_planifiees = 140;
        $compteur->heures_realisees = 0;
        $compteur->heures_supplementaires = 0;
        $compteur->solde_compteur = -140;
        $compteur->ecart = -140;
        $compteur->taux_realisation = 0;
        return $compteur;
    }
}
