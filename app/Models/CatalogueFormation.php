<?php
// app/Models/CatalogueFormation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasTenantScope;


class CatalogueFormation extends Model
{
    use HasFactory, SoftDeletes, HasTenantScope;

    protected $table = 'catalogue_formations';

    protected $fillable = [
         'tenant_id', 'titre', 'description', 'categorie',
        'duree_heures', 'type', 'actif', 'date_creation',
    ];

    protected $casts = [
        'actif'          => 'boolean',
        'date_creation'  => 'date',
        'duree_heures'   => 'integer',
    ];

    public function getDureeLibelleAttribute(): string
    {
        $h = $this->duree_heures;
        if ($h < 8)  return "{$h}h";
        $j = intdiv($h, 8);
        $r = $h % 8;
        return $r ? "{$j}j {$r}h" : "{$j} jour" . ($j > 1 ? 's' : '');
    }

    public function getTypeBadgeAttribute(): array
    {
        return match ($this->type) {
            'presentiel'  => ['Présentiel',  '#E1F5EE', '#085041'],
            'distanciel'  => ['Distanciel',  '#E6F1FB', '#185FA5'],
            'mixte'       => ['Mixte',       '#FAEEDA', '#BA7517'],
            default       => [$this->type,   '#F1EFE8', '#888780'],
        };
    }
}
