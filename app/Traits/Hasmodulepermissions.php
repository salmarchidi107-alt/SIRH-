<?php

namespace App\Traits;

use App\Models\UserPermission;
use Illuminate\Support\Collection;

/**
 * Trait HasModulePermissions
 *
 * Règles :
 *  - superadmin → tout autorisé, jamais de vérification en base
 *  - admin      → tout autorisé, jamais de vérification en base
 *  - rh         → vérifie la table user_permissions (permissions granulaires)
 *  - employee   → vérifie la table user_permissions (permissions granulaires)
 *
 * Si un utilisateur rh/employee n'a AUCUNE ligne en base pour un module,
 * l'accès est refusé par défaut.
 */
trait HasModulePermissions
{
    /**
     * Relation Eloquent vers les permissions de cet utilisateur.
     */
    public function modulePermissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    /**
     * Cache local pour éviter les requêtes répétées dans la même requête HTTP.
     */
    protected ?Collection $_permCache = null;

    /**
     * Charge et met en cache les permissions (indexées par module).
     */
    protected function permCache(): Collection
    {
        if ($this->_permCache === null) {
            $this->_permCache = $this->modulePermissions()
                ->get()
                ->keyBy('module');
        }

        return $this->_permCache;
    }

    /**
     * Invalide le cache (utile après une mise à jour des permissions).
     */
    public function clearPermCache(): void
    {
        $this->_permCache = null;
    }

    /**
     * Seuls superadmin et admin ont tous les droits automatiquement.
     * Le rôle rh passe par la table user_permissions comme un employee.
     */
    public function isFullAccessRole(): bool
    {
        return in_array($this->role ?? '', ['admin', 'superadmin']);
    }

    /**
     * Rôles qui utilisent la table user_permissions (rh + employee).
     */
    public function usesPermissionTable(): bool
    {
        return in_array($this->role ?? '', ['rh', 'employee']);
    }

    /* ─── Vérifications individuelles ───────────────────────────────────── */

    public function canView(string $module): bool
    {
        if ($this->isFullAccessRole()) return true;

        return (bool) ($this->permCache()->get($module)?->can_view ?? false);
    }

    public function canCreate(string $module): bool
    {
        if ($this->isFullAccessRole()) return true;

        return (bool) ($this->permCache()->get($module)?->can_create ?? false);
    }

    public function canEdit(string $module): bool
    {
        if ($this->isFullAccessRole()) return true;

        return (bool) ($this->permCache()->get($module)?->can_edit ?? false);
    }

    public function canDelete(string $module): bool
    {
        if ($this->isFullAccessRole()) return true;

        return (bool) ($this->permCache()->get($module)?->can_delete ?? false);
    }

    /**
     * Vérification générique : hasPermission('employees', 'create')
     */
    public function hasPermission(string $module, string $action): bool
    {
        return match ($action) {
            'view'   => $this->canView($module),
            'create' => $this->canCreate($module),
            'edit'   => $this->canEdit($module),
            'delete' => $this->canDelete($module),
            default  => false,
        };
    }

    /**
     * Retourne toutes les permissions sous forme de tableau PHP.
     */
    public function allPermissionsArray(): array
    {
        $allModules = [
            'dashboard','employees','trombinoscope','news',
            'planning','temps_vue','pointage',
            'absences','absences_calendar','absences_counters',
            'lms','referentiel','lms_planning',
            'salary','ged','ged_modeles','ged_entete',
            'parametrage','reporting',
        ];

        if ($this->isFullAccessRole()) {
            return collect($allModules)->mapWithKeys(fn($m) => [
                $m => ['view' => true, 'create' => true, 'edit' => true, 'delete' => true],
            ])->toArray();
        }

        // rh et employee : lire la base
        $result = [];
        foreach ($allModules as $module) {
            $perm = $this->permCache()->get($module);
            $result[$module] = [
                'view'   => (bool) ($perm?->can_view   ?? false),
                'create' => (bool) ($perm?->can_create ?? false),
                'edit'   => (bool) ($perm?->can_edit   ?? false),
                'delete' => (bool) ($perm?->can_delete ?? false),
            ];
        }

        return $result;
    }
}
