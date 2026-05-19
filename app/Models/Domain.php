<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Domain extends Model
{
    protected $fillable = [
        'id',
        'tenant_id',
        'domain',
    ];

    protected $casts = [
        'id'        => 'string',
        'tenant_id' => 'string',
    ];

    /**
     * Relation vers le tenant propriétaire de ce domaine
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
