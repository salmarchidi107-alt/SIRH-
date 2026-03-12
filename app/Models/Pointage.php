<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Pointage extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'heure_entree',
        'heure_sortie',
        'heures_travaillees',
        'heures_supplementaires',
        'statut',
        'commentaire',
    ];

    protected $casts = [
        'date' => 'date',
        'heures_travaillees' => 'decimal:2',
        'heures_supplementaires' => 'decimal:2',
    ];

    // Relations
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Scopes
    public function scopeParMois($query, $annee, $mois)
    {
        return $query->whereYear('date', $annee)->whereMonth('date', $mois);
    }

    public function scopeParSemaine($query, $debut, $fin)
    {
        return $query->whereBetween('date', [$debut, $fin]);
    }

    public function scopeParEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    // Accessors
    public function getDureeAttribute()
    {
        if ($this->heure_entree && $this->heure_sortie) {
            $debut = Carbon::parse($this->heure_entree);
            $fin = Carbon::parse($this->heure_sortie);
            return $fin->diffInMinutes($debut) / 60;
        }
        return 0;
    }

    // Méthodes statiques utilitaires
    public static function getHeuresMois($employeeId, $annee, $mois)
    {
        return self::parEmployee($employeeId)
            ->parMois($annee, $mois)
            ->sum('heures_travaillees');
    }

    public static function getHeuresSemaine($employeeId, $debut, $fin)
    {
        return self::parEmployee($employeeId)
            ->parSemaine($debut, $fin)
            ->sum('heures_travaillees');
    }
}