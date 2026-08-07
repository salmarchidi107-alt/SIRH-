<?php

namespace App\Services\SuperAdmin;

use App\Models\SecurityAlert;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * TenantIntegrityService
 * ───────────────────────────────────────────────────────────────────────
 * Service UNIQUE regroupant tout le diagnostic d'intégrité multi-tenant :
 * détecte toute anomalie pouvant provoquer un partage de données entre tenants.
 *
 * Deux familles de contrôles sont fusionnées dans le même rapport :
 *  - contrôles STRUCTURELS (base de données) : incohérences déjà présentes
 *    en DB (tenant_id invalide, manquant, ou incohérent entre relations) ;
 *  - contrôle RUNTIME (frontend) : fuites détectées en temps réel par le
 *    détecteur JS côté client (public/js/data-leak-detector.js), qui a
 *    bloqué l'affichage d'une donnée d'un autre tenant et journalisé
 *    l'incident dans la table `security_alerts`.
 *
 * Les deux sont traitées comme des "checks" au même titre, ce qui fait que
 * summary() (et donc le bloc du dashboard SuperAdmin) les agrège déjà
 * automatiquement, sans aucune modification requise côté vue.
 *
 * Principes respectés :
 *  - Lecture seule : ne modifie/corrige jamais de données.
 *  - Chaque contrôle est isolé dans sa propre méthode privée (SRP), ce qui
 *    facilite la maintenance et l'ajout de nouveaux contrôles.
 *  - Résilient : si une table/colonne attendue n'existe pas, le contrôle
 *    concerné se déclare "skipped" au lieu de planter tout le diagnostic.
 *  - Performant : le rapport est mis en cache 5 minutes.
 *
 * Pour ajouter un nouveau contrôle demain :
 *  1. Écrire une nouvelle méthode privée `checkXxx(): array` qui retourne
 *     $this->ok(...) / $this->anomalies(...) / $this->skipped(...).
 *  2. L'ajouter au tableau retourné par checks() ci-dessous.
 *  C'est tout — aucune autre classe à toucher.
 */
class TenantIntegrityService
{
    /** Nombre max d'enregistrements détaillés renvoyés par contrôle (perf). */
    public const RECORD_LIMIT = 25;

    public const CACHE_KEY = 'superadmin:tenant-integrity:report';

    /** Durée de mise en cache du rapport, en minutes. */
    public const CACHE_TTL_MINUTES = 5;

    /** Fenêtre glissante prise en compte pour les alertes runtime récentes. */
    public const RUNTIME_LEAK_WINDOW_DAYS = 7;

    // ═══════════════════════════════════════════════════════════════════
    // API PUBLIQUE
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Exécute le diagnostic complet (ou renvoie le rapport en cache).
     *
     * @return array{
     *     generated_at: \Illuminate\Support\Carbon,
     *     total_anomalies: int,
     *     status: string,
     *     checks: array<int, array{
     *         key: string, label: string, status: string, count: int,
     *         records: array, description: string, recommendation: string
     *     }>
     * }
     */
    public function run(bool $fresh = false): array
    {
        if ($fresh) {
            $report = $this->execute();
            Cache::put(self::CACHE_KEY, $report, now()->addMinutes(self::CACHE_TTL_MINUTES));

            return $report;
        }

        return Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn () => $this->execute()
        );
    }

    /**
     * Résumé léger pour la carte du dashboard et le badge de la sidebar.
     * Inclut déjà les anomalies structurelles ET les fuites runtime, puisque
     * les deux sont fusionnées au niveau de execute()/checks().
     *
     * @return array{total_anomalies: int, status: string, generated_at: \Illuminate\Support\Carbon}
     */
    public function summary(): array
    {
        $report = $this->run();

        return [
            'total_anomalies' => $report['total_anomalies'],
            'status' => $report['status'],
            'generated_at' => $report['generated_at'],
        ];
    }

    // ═══════════════════════════════════════════════════════════════════
    // ORCHESTRATION
    // ═══════════════════════════════════════════════════════════════════

    protected function execute(): array
    {
        $results = [];

        foreach ($this->checks() as $key => $check) {
            try {
                $results[] = $check();
            } catch (Throwable $e) {
                Log::error("[TenantIntegrityService] Échec de la vérification \"{$key}\": ".$e->getMessage(), [
                    'exception' => $e,
                ]);
                $results[] = $this->skipped($key, $key, 'Cette vérification a échoué de façon inattendue et a été ignorée. Voir les logs applicatifs.');
            }
        }

        $total = array_sum(array_column($results, 'count'));

        $status = 'ok';
        foreach ($results as $r) {
            if ($r['status'] === 'error') {
                $status = 'error';
                break;
            }
        }
        if ($status === 'ok') {
            foreach ($results as $r) {
                if ($r['status'] === 'warning') {
                    $status = 'warning';
                    break;
                }
            }
        }

        return [
            'generated_at' => now(),
            'total_anomalies' => $total,
            'status' => $status,
            'checks' => $results,
        ];
    }

    /**
     * Registre des contrôles actifs. Ajouter une ligne ici pour étendre le diagnostic.
     *
     * @return array<string, callable(): array>
     */
    protected function checks(): array
    {
        return [
            // ── Contrôles structurels (base de données) ──────────────────
            'users_invalid_tenant_reference' => fn () => $this->checkUsersInvalidTenantReference(),
            'employees_user_tenant_mismatch' => fn () => $this->checkEmployeesUserTenantMismatch(),
            'tasks_project_tenant_mismatch' => fn () => $this->checkTasksProjectTenantMismatch(),
            'tasks_assignee_tenant_mismatch' => fn () => $this->checkTasksAssigneeTenantMismatch(),
            'projects_invalid_tenant_reference' => fn () => $this->checkProjectsInvalidTenantReference(),
            'users_missing_tenant_id' => fn () => $this->checkUsersMissingTenantId(),
            'projects_missing_tenant_id' => fn () => $this->checkProjectsMissingTenantId(),
            'tasks_missing_tenant_id' => fn () => $this->checkTasksMissingTenantId(),
            'employees_missing_tenant_id' => fn () => $this->checkEmployeesMissingTenantId(),

            // ── Contrôle runtime (frontend) ───────────────────────────────
            'runtime_data_leaks' => fn () => $this->checkRuntimeDataLeaks(),

            // ── Ajoutez vos futurs contrôles ici, ex :
            // 'invoices_client_tenant_mismatch' => fn () => $this->checkInvoicesClientTenantMismatch(),
        ];
    }

    // ═══════════════════════════════════════════════════════════════════
    // CONTRÔLES — chacun isolé dans sa propre méthode (SRP)
    // ═══════════════════════════════════════════════════════════════════

    /** Utilisateurs dont le tenant_id ne correspond à aucun tenant existant. */
    private function checkUsersInvalidTenantReference(): array
    {
        return $this->checkInvalidTenantReference(
            key: 'users_invalid_tenant_reference',
            label: 'Utilisateurs — référence tenant invalide',
            table: 'users',
            description: "Des utilisateurs possèdent un tenant_id qui ne correspond à aucune entreprise (tenant) existante.",
            recommendation: "Vérifier l'origine de ces enregistrements (tenant supprimé, import de données...) et réassigner ou désactiver ces utilisateurs."
        );
    }

    /** Employés liés à un utilisateur appartenant à un autre tenant. */
    private function checkEmployeesUserTenantMismatch(): array
    {
        return $this->checkRelationMismatch(
            key: 'employees_user_tenant_mismatch',
            label: "Employés — cohérence avec l'utilisateur lié",
            baseTable: 'employees',
            baseForeignKey: 'user_id',
            relatedTable: 'users',
            description: "Des fiches employé sont rattachées à un utilisateur d'un autre tenant, ce qui peut exposer des données RH entre entreprises.",
            recommendation: "Corriger le tenant_id de l'employé ou de l'utilisateur lié afin qu'ils appartiennent au même tenant."
        );
    }

    /** Tâches liées à un projet d'un autre tenant. */
    private function checkTasksProjectTenantMismatch(): array
    {
        return $this->checkRelationMismatch(
            key: 'tasks_project_tenant_mismatch',
            label: "Tâches — cohérence avec le projet lié",
            baseTable: 'tasks',
            baseForeignKey: 'project_id',
            relatedTable: 'projects',
            description: "Des tâches appartiennent à un tenant différent de celui de leur projet parent.",
            recommendation: "Aligner le tenant_id de la tâche sur celui de son projet, ou vérifier qu'elle n'a pas été associée au mauvais projet."
        );
    }

    /** Tâches assignées à un utilisateur d'un autre tenant. */
    private function checkTasksAssigneeTenantMismatch(): array
    {
        return $this->checkRelationMismatch(
            key: 'tasks_assignee_tenant_mismatch',
            label: "Tâches — cohérence avec l'utilisateur assigné",
            baseTable: 'tasks',
            baseForeignKey: 'assigned_to',
            relatedTable: 'users',
            description: "Des tâches sont assignées à un utilisateur qui n'appartient pas au même tenant que la tâche.",
            recommendation: "Réassigner la tâche à un utilisateur du bon tenant, ou corriger le tenant_id de la tâche."
        );
    }

    /** Projets dont le tenant_id ne correspond à aucun tenant existant. */
    private function checkProjectsInvalidTenantReference(): array
    {
        return $this->checkInvalidTenantReference(
            key: 'projects_invalid_tenant_reference',
            label: 'Projets — référence tenant invalide',
            table: 'projects',
            description: "Des projets possèdent un tenant_id qui ne correspond à aucune entreprise (tenant) existante.",
            recommendation: "Réassigner ces projets à un tenant valide ou les archiver s'ils sont orphelins."
        );
    }

    /** Utilisateurs sans tenant_id alors qu'il est obligatoire. */
    private function checkUsersMissingTenantId(): array
    {
        return $this->checkMissingTenantId(
            key: 'users_missing_tenant_id',
            label: 'Utilisateurs — tenant_id manquant',
            table: 'users',
            description: "Des utilisateurs n'ont aucun tenant_id renseigné, ce qui peut les rendre visibles hors de tout périmètre tenant.",
            recommendation: "Renseigner le tenant_id manquant ou confirmer qu'il s'agit d'un compte SuperAdmin/global légitime."
        );
    }

    private function checkProjectsMissingTenantId(): array
    {
        return $this->checkMissingTenantId(
            key: 'projects_missing_tenant_id',
            label: 'Projets — tenant_id manquant',
            table: 'projects',
            description: "Des projets n'ont aucun tenant_id renseigné.",
            recommendation: "Renseigner le tenant_id du projet concerné."
        );
    }

    private function checkTasksMissingTenantId(): array
    {
        return $this->checkMissingTenantId(
            key: 'tasks_missing_tenant_id',
            label: 'Tâches — tenant_id manquant',
            table: 'tasks',
            description: "Des tâches n'ont aucun tenant_id renseigné.",
            recommendation: "Renseigner le tenant_id de la tâche, généralement à partir de son projet parent."
        );
    }

    private function checkEmployeesMissingTenantId(): array
    {
        return $this->checkMissingTenantId(
            key: 'employees_missing_tenant_id',
            label: 'Employés — tenant_id manquant',
            table: 'employees',
            description: "Des fiches employé n'ont aucun tenant_id renseigné.",
            recommendation: "Renseigner le tenant_id de la fiche employé, généralement à partir de l'utilisateur lié."
        );
    }

    /**
     * Fuites de données détectées côté frontend (runtime), journalisées dans
     * `security_alerts` par le détecteur JS. Contrairement aux autres
     * contrôles, celui-ci ne relit pas la cohérence de la DB : il rapporte
     * des incidents déjà survenus et déjà bloqués côté client.
     */
    private function checkRuntimeDataLeaks(): array
    {
        $key = 'runtime_data_leaks';
        $label = 'Fuites de données — détection runtime (frontend)';

        if (! Schema::hasTable('security_alerts')) {
            return $this->skipped($key, $label, 'Vérification ignorée : table security_alerts introuvable (migration non exécutée).');
        }

        $since = now()->subDays(self::RUNTIME_LEAK_WINDOW_DAYS);

        $buildQuery = fn () => SecurityAlert::query()->where('created_at', '>=', $since);

        $total = (clone $buildQuery())->count();

        $records = $total > 0
            ? (clone $buildQuery())
                ->latest()
                ->limit(self::RECORD_LIMIT)
                ->get(['id', 'user_name', 'module', 'model_name', 'expected_tenant_id', 'received_tenant_id', 'created_at'])
                ->map(fn (SecurityAlert $alert) => [
                    'id' => $alert->id,
                    'detail' => sprintf(
                        '%s — %s (%s) : tenant %s → %s, le %s',
                        $alert->user_name,
                        $alert->module,
                        $alert->model_name,
                        $alert->expected_tenant_id ?? '—',
                        $alert->received_tenant_id ?? '—',
                        $alert->created_at->format('d/m/Y H:i')
                    ),
                ])
                ->toArray()
            : [];

        return $this->anomalies(
            key: $key,
            label: $label,
            count: $total,
            records: $records,
            description: sprintf(
                "Le détecteur frontend a bloqué l'affichage de données appartenant à un autre tenant à %d reprise(s) au cours des %d derniers jours.",
                $total,
                self::RUNTIME_LEAK_WINDOW_DAYS
            ),
            recommendation: "Consulter le détail de chaque alerte dans l'interface dédiée (Alertes de sécurité) pour identifier la requête ou le contrôleur mal filtré à l'origine de la fuite.",
            severity: 'warning'
        );
    }

    // ═══════════════════════════════════════════════════════════════════
    // HELPERS GÉNÉRIQUES RÉUTILISABLES (privés)
    // ═══════════════════════════════════════════════════════════════════

    /**
     * $table.tenant_id ne correspond à aucune ligne de `tenants`.
     */
    private function checkInvalidTenantReference(
        string $key,
        string $label,
        string $table,
        string $description,
        string $recommendation,
        string $tenantColumn = 'tenant_id'
    ): array {
        if (! $this->schemaHas([$table => [$tenantColumn, 'id'], 'tenants' => ['id']])) {
            return $this->skipped($key, $label, "Vérification ignorée : structure attendue introuvable pour la table {$table}.");
        }

        $buildQuery = fn () => DB::table($table)
            ->whereNotNull($tenantColumn)
            ->whereNotExists(function ($query) use ($table, $tenantColumn) {
                $query->select(DB::raw(1))
                    ->from('tenants')
                    ->whereColumn('tenants.id', "{$table}.{$tenantColumn}");
            });

        $total = (clone $buildQuery())->count();

        $records = $total > 0
            ? (clone $buildQuery())
                ->select(['id', "{$tenantColumn} as invalid_tenant_id"])
                ->limit(self::RECORD_LIMIT)
                ->get()
                ->map(fn ($row) => ['id' => $row->id, 'detail' => "tenant_id={$row->invalid_tenant_id} inexistant"])
                ->toArray()
            : [];

        return $this->anomalies($key, $label, $total, $records, $description, $recommendation, 'error');
    }

    /**
     * $baseTable.$baseTenantColumn != $relatedTable.$relatedTenantColumn,
     * jointure via $baseTable.$baseForeignKey = $relatedTable.$relatedPrimaryKey.
     */
    private function checkRelationMismatch(
        string $key,
        string $label,
        string $baseTable,
        string $baseForeignKey,
        string $relatedTable,
        string $description,
        string $recommendation,
        string $baseTenantColumn = 'tenant_id',
        string $relatedTenantColumn = 'tenant_id',
        string $relatedPrimaryKey = 'id'
    ): array {
        $requiredColumns = [
            $baseTable => [$baseForeignKey, $baseTenantColumn, 'id'],
            $relatedTable => [$relatedPrimaryKey, $relatedTenantColumn],
        ];

        if (! $this->schemaHas($requiredColumns)) {
            return $this->skipped($key, $label, "Vérification ignorée : structure attendue introuvable ({$baseTable}/{$relatedTable}).");
        }

        $buildQuery = fn () => DB::table($baseTable)
            ->join($relatedTable, "{$relatedTable}.{$relatedPrimaryKey}", '=', "{$baseTable}.{$baseForeignKey}")
            ->whereNotNull("{$baseTable}.{$baseTenantColumn}")
            ->whereNotNull("{$relatedTable}.{$relatedTenantColumn}")
            ->whereColumn("{$baseTable}.{$baseTenantColumn}", '!=', "{$relatedTable}.{$relatedTenantColumn}");

        $total = (clone $buildQuery())->count();

        $records = $total > 0
            ? (clone $buildQuery())
                ->select([
                    "{$baseTable}.id as id",
                    "{$baseTable}.{$baseTenantColumn} as base_tenant_id",
                    "{$relatedTable}.{$relatedTenantColumn} as related_tenant_id",
                ])
                ->limit(self::RECORD_LIMIT)
                ->get()
                ->map(fn ($row) => [
                    'id' => $row->id,
                    'detail' => "tenant_id={$row->base_tenant_id} vs {$relatedTable}.tenant_id={$row->related_tenant_id}",
                ])
                ->toArray()
            : [];

        return $this->anomalies($key, $label, $total, $records, $description, $recommendation, 'error');
    }

    /**
     * $table.$tenantColumn est NULL alors qu'il est censé être obligatoire.
     */
    private function checkMissingTenantId(
        string $key,
        string $label,
        string $table,
        string $description,
        string $recommendation,
        string $tenantColumn = 'tenant_id'
    ): array {
        if (! $this->schemaHas([$table => [$tenantColumn, 'id']])) {
            return $this->skipped($key, $label, "Vérification ignorée : colonne {$tenantColumn} introuvable sur {$table}.");
        }

        $buildQuery = fn () => DB::table($table)->whereNull($tenantColumn);

        $total = (clone $buildQuery())->count();

        $records = $total > 0
            ? (clone $buildQuery())
                ->select(['id'])
                ->limit(self::RECORD_LIMIT)
                ->get()
                ->map(fn ($row) => ['id' => $row->id, 'detail' => 'tenant_id manquant'])
                ->toArray()
            : [];

        return $this->anomalies($key, $label, $total, $records, $description, $recommendation, 'warning');
    }

    /** Vérifie qu'une table et ses colonnes existent avant d'interroger (évite tout crash). */
    private function schemaHas(array $tableColumns): bool
    {
        foreach ($tableColumns as $table => $columns) {
            if (! Schema::hasTable($table)) {
                return false;
            }
            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    return false;
                }
            }
        }

        return true;
    }

    // ── Constructeurs de résultat ────────────────────────────────────────

    private function anomalies(string $key, string $label, int $count, array $records, string $description, string $recommendation, string $severity = 'error'): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => $count > 0 ? $severity : 'ok',
            'count' => $count,
            'records' => $records,
            'description' => $description,
            'recommendation' => $recommendation,
        ];
    }

    private function skipped(string $key, string $label, string $reason): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => 'skipped',
            'count' => 0,
            'records' => [],
            'description' => $reason,
            'recommendation' => '',
        ];
    }
}
