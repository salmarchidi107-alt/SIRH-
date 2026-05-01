<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;

class DocumentModele extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'nom',
        'categorie',
        'contenu',
        'description',
        'created_by',
        'tenant_id',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class, 'modele_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}