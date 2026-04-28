<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BadgeRecord extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
    ];

    // ou si vous ne voulez pas gérer $fillable :
    // protected $guarded = [];
}