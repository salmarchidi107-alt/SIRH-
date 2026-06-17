<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use App\Traits\HasTenantScope;

class EquipmentCategory extends Model
{
    use HasTenantScope;

    protected $fillable = ['tenant_id', 'name', 'color'];

    public function equipments(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }
}

class Equipment extends Model
{
    use HasTenantScope;

    protected $table = 'equipments';
    protected $fillable = ['tenant_id', 'reference', 'designation', 'equipment_category_id', 'brand', 'serial_number', 'condition', 'status', 'value', 'purchase_date', 'warranty_end_date'];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_end_date' => 'date',
        'value' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'equipment_category_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(EquipmentAssignment::class);
    }

    public function currentAssignment()
    {
        return $this->assignments()->where('status', 'active')->latest()->first();
    }
}

class EquipmentAssignment extends Model
{
    use HasTenantScope;

    protected $fillable = ['tenant_id', 'equipment_id', 'employee_id', 'assigned_at', 'returned_at', 'condition_at_assignment', 'condition_at_return', 'return_notes', 'status', 'assigned_by'];

    protected $casts = [
        'assigned_at' => 'date',
        'returned_at' => 'date',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function discharges(): HasMany
    {
        return $this->hasMany(EquipmentDischarge::class, 'equipment_assignment_id');
    }
}

class EquipmentDischarge extends Model
{
    use HasTenantScope;

    protected $fillable = ['tenant_id', 'reference', 'equipment_assignment_id', 'type', 'status', 'pdf_path', 'signed_at'];

    protected $casts = ['signed_at' => 'datetime'];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(EquipmentAssignment::class, 'equipment_assignment_id');
    }
}
