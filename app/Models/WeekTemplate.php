<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WeekTemplate extends Model
{
    use HasFactory, \App\Traits\HasTenantScope;

    protected $fillable = [
        'tenant_id',
        'name',
        'monday_shift_type',
        'monday_start',
        'monday_end',
        'tuesday_shift_type',
        'tuesday_start',
        'tuesday_end',
        'wednesday_shift_type',
        'wednesday_start',
        'wednesday_end',
        'thursday_shift_type',
        'thursday_start',
        'thursday_end',
        'friday_shift_type',
        'friday_start',
        'friday_end',
        'saturday_shift_type',
        'saturday_start',
        'saturday_end',
        'sunday_shift_type',
        'sunday_start',
        'sunday_end',
    ];

    public function plannings()
    {
        return $this->hasMany(Planning::class);
    }
}



    public function applyToEmployee($employeeId, $startDate)
    {
        $days = [
            'monday' => ['shift_type' => $this->monday_shift_type, 'start' => $this->monday_start, 'end' => $this->monday_end],
            'tuesday' => ['shift_type' => $this->tuesday_shift_type, 'start' => $this->tuesday_start, 'end' => $this->tuesday_end],
            'wednesday' => ['shift_type' => $this->wednesday_shift_type, 'start' => $this->wednesday_start, 'end' => $this->wednesday_end],
            'thursday' => ['shift_type' => $this->thursday_shift_type, 'start' => $this->thursday_start, 'end' => $this->thursday_end],
            'friday' => ['shift_type' => $this->friday_shift_type, 'start' => $this->friday_start, 'end' => $this->friday_end],
            'saturday' => ['shift_type' => $this->saturday_shift_type, 'start' => $this->saturday_start, 'end' => $this->saturday_end],
            'sunday' => ['shift_type' => $this->sunday_shift_type, 'start' => $this->sunday_start, 'end' => $this->sunday_end],
        ];

        $date = $startDate->copy()->startOfWeek(Carbon::MONDAY);

        foreach ($days as $dayName => $shift) {
            if ($shift['shift_type']) {
                Planning::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
                        'shift_type' => $shift['shift_type'],
                        'shift_start' => $shift['start'],
                        'shift_end' => $shift['end'],
                    ]
                );
            }
            $date->addDay();
        }
    }
}
