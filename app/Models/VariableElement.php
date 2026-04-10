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

    protected $casts = [
        'amount' => 'decimal:2',
        'type' => \App\Enums\VariableElementType::class,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getTypeColorAttribute(): string
    {
        return $this->type === \App\Enums\VariableElementType::GAIN ? 'success' : 'danger';
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type->label();
    }
}
