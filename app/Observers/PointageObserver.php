<?php

namespace App\Observers;

use App\Models\Pointage;
use App\Models\CompteurTemps;
use Carbon\Carbon;

class PointageObserver
{
    /**
     * Handle the Pointage "created" event.
     */
    public function created(Pointage $pointage): void
    {
        $this->updateCompteur($pointage);
    }

    /**
     * Handle the Pointage "updated" event.
     */
    public function updated(Pointage $pointage): void
    {
        $this->updateCompteur($pointage);
    }

    /**
     * Handle the Pointage "deleted" event.
     */
    public function deleted(Pointage $pointage): void
    {
        $this->updateCompteur($pointage);
    }

    /**
     * Update/create CompteurTemps for the month affected by this pointage.
     */
    private function updateCompteur(Pointage $pointage): void
    {
        $date = Carbon::parse($pointage->date);
        $annee = $date->year;
        $mois = $date->month;

        // Recalculate full month sum (simple, performant with index)
        CompteurTemps::getOuCreeParMois($pointage->employee_id, $annee, $mois);
    }
}

