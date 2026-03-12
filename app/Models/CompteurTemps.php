<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompteurTemps extends Model
{
    use HasFactory;

    protected $table = 'compteurs_temps';

    protected $fillable = [
        'employee_id',
        'annee',
        'mois',
        'heures_planifiees',
        'heures_realisees',
        'heures_supplementaires',
        'solde_compteur',
    ];

    protected $casts = [
        'annee' => 'integer',
        'mois' => 'integer',
        'heures_planifiees' => 'decimal:2',
        'heures_realisees' => 'decimal:2',
        'heures_supplementaires' => 'decimal:2',
        'solde_compteur' => 'decimal:2',
    ];

    // Relations
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Scopes
    public function scopeParAnnee($query, $annee)
    {
        return $query->where('annee', $annee);
    }

    public function scopeParMois($query, $annee, $mois)
    {
        return $query->where('annee', $annee)->where('mois', $mois);
    }

    public function scopeParEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    // Accesseurs calculés
    public function getEcartAttribute()
    {
        return $this->heures_realisees - $this->heures_planifiees;
    }

    public function getTauxRealisationAttribute()
    {
        if ($this->heures_planifiees > 0) {
            return round(($this->heures_realisees / $this->heures_planifiees) * 100, 1);
        }
        return 0;
    }

    // Méthode utilitaire pour obtenir ou créer le compteur du mois
    public static function getOuCreeParMois($employeeId, $annee, $mois)
    {
        if (!$employeeId) {
            // Retourner un objet stdClass avec des valeurs par défaut
            $result = new \stdClass();
            $result->heures_planifiees = 140;
            $result->heures_realisees = 0;
            $result->heures_supplementaires = 0;
            $result->solde_compteur = -140;
            $result->ecart = -140;
            $result->taux_realisation = 0;
            return $result;
        }
        
        // Calculer les heures planifiées selon le contrat (35h par défaut)
        $employee = Employee::find($employeeId);
        $heuresPlanifiees = 35 * 4; // 35h * 4 semaines par défaut
        
        // Essayer de trouver un compteur existant
        $compteur = self::where('employee_id', $employeeId)
            ->where('annee', $annee)
            ->where('mois', $mois)
            ->first();

        // Si aucun compteur, créer un nouveau avec des valeurs par défaut
        if (!$compteur) {
            $compteur = self::create([
                'employee_id' => $employeeId,
                'annee' => $annee,
                'mois' => $mois,
                'heures_planifiees' => $heuresPlanifiees,
                'heures_realisees' => 0,
                'heures_supplementaires' => 0,
                'solde_compteur' => 0,
            ]);
        }

        // Calculer automatiquement les heures réalisées depuis les pointages
        $pointages = Pointage::parEmployee($employeeId)
            ->parMois($annee, $mois)
            ->get();

        $compteur->heures_realisees = $pointages->sum('heures_travaillees');
        $compteur->heures_supplementaires = $pointages->sum('heures_supplementaires');
        $compteur->solde_compteur = $compteur->heures_realisees - $compteur->heures_planifiees;
        $compteur->save();

        return $compteur;
    }

    // Méthode pour obtenir les compteurs de l'année
    public static function getParAnnee($employeeId, $annee)
    {
        return self::parEmployee($employeeId)
            ->parAnnee($annee)
            ->orderBy('mois')
            ->get();
    }
}

