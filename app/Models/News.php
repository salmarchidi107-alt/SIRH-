<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image',
        'type',
        'event_date',
        'is_active',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_active' => 'boolean',
    ];

    const TYPES = [
        'annual_event' => 'Événement annuel',
        'meeting' => 'Réunion programmée',
        'holiday' => 'Jour férié',
        'new_recruit' => 'Nouvelle recrue',
        'promotion' => 'Promotion',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now()->startOfMonth())
                    ->where('event_date', '<=', now()->endOfMonth()->addWeeks(2))
                    ->orderBy('event_date');
    }
}
