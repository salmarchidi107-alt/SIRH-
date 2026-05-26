<?php
// app/Models/Formateur.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Formateur extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom', 'prenom', 'email', 'telephone',
        'specialite', 'type', 'actif','tenant_id',
    ];

    protected $casts = ['actif' => 'boolean'];

    public function getNomCompletAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    // Formations planifiées liées (via la table formations)
    public function formations()
    {
        return $this->hasMany(Formation::class, 'formateur', 'nom_complet');
    }
}
