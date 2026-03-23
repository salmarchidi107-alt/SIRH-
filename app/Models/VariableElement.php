<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariableElement extends Model
{
    protected $fillable = [
        'employee_id', 'month', 'year',
'category', 'rubrique', 'label', 'amount', 'unit', 'type',
    ];

    protected $casts = ['amount' => 'decimal:2'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getCategoryColorAttribute(): string
    {
        return $this->category === 'gain' ? 'success' : 'danger';
    }

    public function getCategoryLabelAttribute(): string
    {
        return $this->category === 'gain' ? 'Gain' : 'Retenue';
    }
}
