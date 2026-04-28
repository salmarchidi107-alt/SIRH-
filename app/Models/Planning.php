<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planning extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'shift_start',
        'shift_end',
        'shift_type',
        'notes',
        'room',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    const SHIFT_TYPES = [
        'matin' => 'Matin',
        'apres_midi' => 'Après-midi',
        'nuit' => 'Nuit',
        'journee' => 'Journée',
        'garde' => 'Garde',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room', 'id');
    }

    public function getRoomAttribute($value)
    {
        if ($this->relationLoaded('room') && $this->getRelation('room')) {
            return $this->getRelation('room')->name;
        }

        return $value;
    }
}
