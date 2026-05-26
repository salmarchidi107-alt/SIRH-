<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'name',
        'department_id',
        'capacity',
        'description',
        'tenant_id',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
