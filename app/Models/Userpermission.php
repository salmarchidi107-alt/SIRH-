<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \App\Traits\HasTenantScope;


class UserPermission extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'user_id',
        'module',
        'can_view',
        'can_create',
        'can_edit',
        'can_delete',
        'tenant_id',
    ];

    protected $casts = [
        'can_view'   => 'boolean',
        'can_create' => 'boolean',
        'can_edit'   => 'boolean',
        'can_delete' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
