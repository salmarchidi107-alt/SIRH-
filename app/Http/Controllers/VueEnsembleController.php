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

    public function index(Request $request)
    {
        $user = Auth::user();

        // Validate and sanitize year/month params
        $inputAnnee = $request->get('annee');
        $inputMois = $request->get('mois');
        $annee = Carbon::now()->year;
        $mois = Carbon::now()->month;

        if ($inputAnnee !== null && is_numeric($inputAnnee)) {
            $inputAnnee = (int) $inputAnnee;
            if ($inputAnnee >= 1900 && $inputAnnee <= 2100) {
                $annee = $inputAnnee;
            }
        }

        if ($inputMois !== null && is_numeric($inputMois)) {
            $inputMois = (int) $inputMois;
            if ($inputMois >= 1 && $inputMois <= 12) {
                $mois = $inputMois;
            }
        }


        $employeeId = $request->get('employee_id');
        $department = $request->get('department');

        $departments = Employee::distinct()->pluck('department')->filter()->values();


        $selectedEmployee = null;

        if ($employeeId) {
            $selectedEmployee = Employee::with(['user'])->find($employeeId);
        } elseif ($department) {
            $selectedEmployee = Employee::with(['user'])->where('department', $department)->first();
        }


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


        $compteurMois = null;
        $joursDetails = [];

        if ($employee && $employee->id > 0) {
            $compteurMois = CompteurTemps::getOuCreeParMois($employee->id, $annee, $mois);


            $joursDetails = $this->getJoursDetails($employee->id, $annee, $mois);
        } else {
            $compteurMois = $this->getDefaultCompteur();
        }


        $semaines = [];
        if ($employee && $employee->id > 0) {
            $semaines = $this->getSemainesDuMois($employee->id, $annee, $mois);
        }


        $graphiqueMois = $this->getGraphiqueMois($employee->id ?? 0, $annee);


        $moisDisponibles = $this->getMoisDisponibles($annee);


        $moisPrecedent = Carbon::createSafe($annee, $mois, 1)->subMonth();
        $moisSuivant = Carbon::createSafe($annee, $mois, 1)->addMonth();

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


    private function getJoursDetails($employeeId, $annee, $mois)
    {
        $jours = [];
        $debut = Carbon::createSafe($annee, $mois, 1);
        $fin = Carbon::createSafe($annee, $mois, 1)->endOfMonth();

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


    private function getSemainesDuMois($employeeId, $annee, $mois)
    {
        if (!$employeeId) {
            return [];
        }

        $debut = Carbon::createSafe($annee, $mois, 1)->startOfWeek();
        $fin = Carbon::createSafe($annee, $mois, 1)->endOfMonth()->endOfWeek();

        $semaines = [];
        $current = $debut->copy();
        $numSem = 1;

        $heuresPlanifiees = 35;

        while ($current <= $fin) {
            $debutSem = $current->copy();
            $finSem = $current->copy()->endOfWeek();

            $pointages = Pointage::parEmployee($employeeId)
                ->parSemaine($debutSem->format('Y-m-d'), $finSem->format('Y-m-d'))
                ->get();

            $heuresTravaillees = $pointages->sum('heures_travaillees');
            $heuresSupp = $pointages->sum('heures_supplementaires');
            $total = $heuresTravaillees + $heuresSupp;


            $nbJoursSemaine = 5;
            $heuresNonTravaillees = 0;


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
