<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WeekTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'monday_shift_type',
        'monday_start',
        'monday_end',
        'monday_room',
        'tuesday_shift_type',
        'tuesday_start',
        'tuesday_end',
        'tuesday_room',
        'wednesday_shift_type',
        'wednesday_start',
        'wednesday_end',
        'wednesday_room',
        'thursday_shift_type',
        'thursday_start',
        'thursday_end',
        'thursday_room',
        'friday_shift_type',
        'friday_start',
        'friday_end',
        'friday_room',
        'saturday_shift_type',
        'saturday_start',
        'saturday_end',
        'saturday_room',
        'sunday_shift_type',
        'sunday_start',
        'sunday_end',
        'sunday_room',
    ];

    public function plannings()
    {
        return $this->hasMany(Planning::class);
    }

    public function applyToEmployee($employeeId, $startDate)
    {
        $days = [
            'monday' => ['shift_type' => $this->monday_shift_type, 'start' => $this->monday_start, 'end' => $this->monday_end, 'room' => $this->monday_room],
            'tuesday' => ['shift_type' => $this->tuesday_shift_type, 'start' => $this->tuesday_start, 'end' => $this->tuesday_end, 'room' => $this->tuesday_room],
            'wednesday' => ['shift_type' => $this->wednesday_shift_type, 'start' => $this->wednesday_start, 'end' => $this->wednesday_end, 'room' => $this->wednesday_room],
            'thursday' => ['shift_type' => $this->thursday_shift_type, 'start' => $this->thursday_start, 'end' => $this->thursday_end, 'room' => $this->thursday_room],
            'friday' => ['shift_type' => $this->friday_shift_type, 'start' => $this->friday_start, 'end' => $this->friday_end, 'room' => $this->friday_room],
            'saturday' => ['shift_type' => $this->saturday_shift_type, 'start' => $this->saturday_start, 'end' => $this->saturday_end, 'room' => $this->saturday_room],
            'sunday' => ['shift_type' => $this->sunday_shift_type, 'start' => $this->sunday_start, 'end' => $this->sunday_end, 'room' => $this->sunday_room],
        ];

        $date = $startDate->copy()->startOfWeek(Carbon::MONDAY);
        
        foreach ($days as $dayName => $shift) {
            if ($shift['shift_type'] && $shift['start'] && $shift['end']) {
                Planning::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
                        'shift_type' => $shift['shift_type'],
                        'shift_start' => $shift['start'],
                        'shift_end' => $shift['end'],
                        'room' => $shift['room'],
                    ]
                );
            }
            $date->addDay();
        }
    }
}
