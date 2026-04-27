<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentModele extends Model
{
    protected $fillable = [
        'nom',
        'categorie',
        'contenu',        // Contenu HTML généré par TinyMCE
        'description',
        'created_by',
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
