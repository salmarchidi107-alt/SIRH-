<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DroitAbsence extends Model
{
    use HasFactory;

    protected $table = 'droits_absences';

    protected $fillable = [
        'employee_id',
        'annee',
        'jours_acquis',
        'jours_pris',
        'jours_en_attente',
        'jours_solde',
        'rtt_acquis',
        'rtt_pris',
    ];

    protected $casts = [
        'jours_acquis'      => 'decimal:2',
        'jours_pris'        => 'decimal:2',
        'jours_en_attente'  => 'decimal:2',
        'jours_solde'       => 'decimal:2',
        'rtt_acquis'        => 'decimal:2',
        'rtt_pris'          => 'decimal:2',
    ];

    
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

  
    public function scopeParAnnee($query, $annee)
    {
        return $query->where('annee', $annee);
    }

    
    public function getRttSoldeAttribute()
    {
        return $this->rtt_acquis - $this->rtt_pris;
    }

    public function getRttAcquisAttribute()
    {
        return $this->rtt_acquis ?? 0;
    }

    public function getPourcentagePrisAttribute()
    {
        if ($this->jours_acquis > 0) {
            return round(($this->jours_pris / $this->jours_acquis) * 100, 1);
        }
        return 0;
    }

   
    public static function getOuCreeParAnnee($employeeId, $annee)
    {
        if (!$employeeId) {
            
            $result = new \stdClass();
            $result->jours_acquis = 25;
            $result->jours_pris = 0;
            $result->jours_en_attente = 0;
            $result->jours_solde = 25;
            $result->rtt_acquis = 0;
            $result->rtt_pris = 0;
            $result->rtt_solde = 0;
            return $result;
        }
        
        return self::firstOrCreate(
            ['employee_id' => $employeeId, 'annee' => $annee],
            [
                'jours_acquis'     => 25,
                'jours_pris'       => 0,
                'jours_en_attente' => 0,
                'jours_solde'      => 25,
                'rtt_acquis'       => 0,
                'rtt_pris'         => 0,
            ]
        );
    }
}
