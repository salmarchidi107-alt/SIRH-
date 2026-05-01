<?php

namespace App\Observers;

use App\Models\Absence;
use App\Models\Pointage;
use Carbon\Carbon;

class AbsenceObserver
{
    /**
     * Handle the Absence "updated" event.
     */
    public function updated(Absence $absence): void
    {
        // Auto-mark pointages as absent when approved
        if ($absence->wasChanged('status') && $absence->status === 'approved' && $absence->employee_id) {
            $startDate = $absence->start_date->copy();
            $endDate = $absence->end_date->copy();

            $tenantId = $absence->tenant_id ?? config('app.current_tenant_id');

            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                // Skip weekends
                if ($date->isWeekend()) {
                    continue;
                }

                Pointage::updateOrCreate(
                    [
                        'employee_id' => $absence->employee_id,
                        'date' => $date->toDateString(),
                    ],
                    [
                        'tenant_id' => $tenantId,
                        'statut' => 'absent',
                        'heure_entree' => null,
                        'heure_sortie' => null,
                        'pause_minutes' => 0,
                        'total_heures' => 0,
                        'valide' => false,
                        'ignore_badge' => true, // Prevent badge override
                    ]
                );

                // Delete overlapping plannings
                \App\Models\Planning::where('employee_id', $absence->employee_id)
                    ->whereDate('date', $date->toDateString())
                    ->delete();
            }
        }
    }
}

