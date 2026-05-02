<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'code',
        'color',
        'chef',
        'description',
    ];

    // =========================================================================
    // RELATIONS
    // =========================================================================

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department', 'name');
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getRoomsCountAttribute(): int
    {
        return $this->rooms()->count();
    }

    // =========================================================================
    // MÉTHODES STATIQUES
    // =========================================================================

    /**
     * Retourne la liste des noms de départements triés alphabétiquement.
     * Utilisé dans tous les contrôleurs via Department::names().
     * Fallback sur le champ department de la table employees si vide.
     */
    public static function names(): \Illuminate\Support\Collection
    {
        try {
            $names = static::orderBy('name')->pluck('name');
            if ($names->isNotEmpty()) {
                return $names;
            }
        } catch (\Exception $e) {
            // Table vide ou inexistante → fallback
        }

        return Employee::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');
    }
}