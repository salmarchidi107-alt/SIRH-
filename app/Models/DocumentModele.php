<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentModele extends Model
{
    protected $fillable = [
        'nom',
        'categorie',
        'contenu',
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
    public function edit(Document $document)
{
    $employes = \App\Models\Employee::orderBy('last_name')->orderBy('first_name')->get();
    $modeles  = \App\Models\DocumentModele::orderBy('nom')->get();

    // Charger l'employé lié au document
    $document->load(['employe', 'modele']);

    return view('ged.edit', compact('document', 'employes', 'modeles'));
}
}