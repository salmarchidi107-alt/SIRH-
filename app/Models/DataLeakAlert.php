<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataLeakAlert extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'user_name', 'user_email',
        'expected_tenant_id', 'expected_tenant_name',
        'leaked_tenant_id', 'leaked_tenant_name',
        'module', 'route_name', 'controller_action', 'url',
        'rows_count', 'row_ids',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'row_ids' => 'array',
    ];
}
