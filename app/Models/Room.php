<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'name',
        'department_id',
        'capacity',
        'description',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}