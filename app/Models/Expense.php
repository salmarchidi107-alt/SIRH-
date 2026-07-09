<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasTenantScope;

class Expense extends Model
{
    use HasFactory, HasTenantScope;

    /**
     * Valeurs possibles pour le statut d'une note de frais.
     * Workflow simplifié : une note est directement "Validée" à sa
     * création, un admin/rh peut la "Rejeter" si besoin.
     */
    public const STATUS_VALIDE = 'valide';
    public const STATUS_REJETE = 'rejete';

    public const STATUSES = [
        self::STATUS_VALIDE => 'Validé',
        self::STATUS_REJETE => 'Rejeté',
    ];

    /**
     * Valeurs possibles pour la catégorie de dépense.
     */
    public const CATEGORIES = [
        'deplacement' => 'Déplacement',
        'repas'       => 'Repas / Restauration',
        'hebergement' => 'Hébergement',
        'medical'     => 'Frais médicaux',
        'fournitures' => 'Fournitures',
        'autre'       => 'Autre',
    ];

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'title',
        'category',
        'expense_date',
        'amount',
        'currency',
        'description',
        'receipt_path',
        'status',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    protected $attributes = [
        'status'   => self::STATUS_VALIDE,
        'currency' => 'MAD',
    ];

    /**
     * Employé ayant engagé la dépense.
     */
    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class);
    }

    /* ── Accessors ── */

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /* ── Scopes ── */

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeForMonth(Builder $query, int $month, int $year): Builder
    {
        return $query->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year);
    }

    public function scopeForEmployee(Builder $query, ?int $employeeId): Builder
    {
        return $employeeId ? $query->where('employee_id', $employeeId) : $query;
    }

    public function scopeForTenant(Builder $query, ?string $tenantId): Builder
    {
        return $tenantId ? $query->where('tenant_id', $tenantId) : $query;
    }
}
