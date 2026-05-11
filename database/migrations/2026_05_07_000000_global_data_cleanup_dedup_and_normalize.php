<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function tenantIdSql(): string
    {
        // Try to use the configured tenant id if present; fallback to NULL.
        // This migration is designed to be safe even if tenant scoping is not available.
        return "(SELECT NULLIF(TRIM(CAST(? AS CHAR(36))), '') )";
    }

    public function up(): void
    {
        // NOTE: this migration performs best-effort deduplication + normalization.
        // It does NOT attempt to repair all business constraints.

        $tenantId = null;
        try {
            $tenantId = config('app.current_tenant_id');
        } catch (\Throwable $e) {
            $tenantId = null;
        }

        // We deliberately run without relying on intl-based helpers.
        DB::beginTransaction();
        try {
            // ----------------------------
            // plannings: key (employee_id, date, tenant_id)
            // ----------------------------
            if (Schema::hasTable('plannings') && Schema::hasColumn('plannings', 'tenant_id')) {
                // Delete duplicates keeping lowest ID
                $deleted = DB::delete(
                    "DELETE t1 FROM plannings t1
                     INNER JOIN plannings t2
                       WHERE t1.id > t2.id
                         AND t1.employee_id = t2.employee_id
                         AND t1.date = t2.date
                         AND (t1.tenant_id <=> t2.tenant_id)"
                );
                echo "[cleanup] plannings duplicates deleted: {$deleted}\n";

                // Normalize tenant_id NULL -> current tenant if available
                if (!empty($tenantId)) {
                    $updated = DB::table('plannings')
                        ->whereNull('tenant_id')
                        ->update(['tenant_id' => $tenantId]);
                    echo "[cleanup] plannings tenant_id NULL normalized: {$updated}\n";
                }
            }

            // ----------------------------
            // pointages: key (tenant_id, employee_id, date, statut)
            // ----------------------------
            if (Schema::hasTable('pointages') && Schema::hasColumn('pointages', 'tenant_id')) {
                $deleted = DB::delete(
                    "DELETE t1 FROM pointages t1
                     INNER JOIN pointages t2
                       WHERE t1.id > t2.id
                         AND t1.tenant_id = t2.tenant_id
                         AND t1.employee_id = t2.employee_id
                         AND t1.date = t2.date
                         AND t1.statut = t2.statut"
                );
                echo "[cleanup] pointages duplicates deleted: {$deleted}\n";
            }

            // ----------------------------
            // salaries: key (employee_id, month, year, tenant_id)
            // ----------------------------
            if (Schema::hasTable('salaries') && Schema::hasColumn('salaries', 'tenant_id')) {
                $deleted = DB::delete(
                    "DELETE t1 FROM salaries t1
                     INNER JOIN salaries t2
                       WHERE t1.id > t2.id
                         AND t1.employee_id = t2.employee_id
                         AND t1.month = t2.month
                         AND t1.year = t2.year
                         AND (t1.tenant_id <=> t2.tenant_id)"
                );
                echo "[cleanup] salaries duplicates deleted: {$deleted}\n";

                // Normalize tenant_id NULL -> current tenant if available
                if (!empty($tenantId)) {
                    $updated = DB::table('salaries')
                        ->whereNull('tenant_id')
                        ->update(['tenant_id' => $tenantId]);
                    echo "[cleanup] salaries tenant_id NULL normalized: {$updated}\n";
                }
            }

            // ----------------------------
            // absences: key (employee_id, start_date, end_date, tenant_id)
            // ----------------------------
            if (Schema::hasTable('absences') && Schema::hasColumn('absences', 'tenant_id')) {
                $deleted = DB::delete(
                    "DELETE t1 FROM absences t1
                     INNER JOIN absences t2
                       WHERE t1.id > t2.id
                         AND t1.employee_id = t2.employee_id
                         AND t1.start_date = t2.start_date
                         AND t1.end_date = t2.end_date
                         AND (t1.tenant_id <=> t2.tenant_id)"
                );
                echo "[cleanup] absences duplicates deleted: {$deleted}\n";

                if (!empty($tenantId)) {
                    $updated = DB::table('absences')
                        ->whereNull('tenant_id')
                        ->update(['tenant_id' => $tenantId]);
                    echo "[cleanup] absences tenant_id NULL normalized: {$updated}\n";
                }
            }

            // ----------------------------
            // compteurs_temps: key (employee_id, annee, mois, tenant_id)
            // ----------------------------
            if (Schema::hasTable('compteurs_temps') && Schema::hasColumn('compteurs_temps', 'tenant_id')) {
                $deleted = DB::delete(
                    "DELETE t1 FROM compteurs_temps t1
                     INNER JOIN compteurs_temps t2
                       WHERE t1.id > t2.id
                         AND t1.employee_id = t2.employee_id
                         AND t1.annee = t2.annee
                         AND t1.mois = t2.mois
                         AND (t1.tenant_id <=> t2.tenant_id)"
                );
                echo "[cleanup] compteurs_temps duplicates deleted: {$deleted}\n";

                if (!empty($tenantId)) {
                    $updated = DB::table('compteurs_temps')
                        ->whereNull('tenant_id')
                        ->update(['tenant_id' => $tenantId]);
                    echo "[cleanup] compteurs_temps tenant_id NULL normalized: {$updated}\n";
                }
            }

            // ----------------------------
            // droits_absences: key (employee_id, annee, tenant_id)
            // ----------------------------
            if (Schema::hasTable('droits_absences') && Schema::hasColumn('droits_absences', 'tenant_id')) {
                $deleted = DB::delete(
                    "DELETE t1 FROM droits_absences t1
                     INNER JOIN droits_absences t2
                       WHERE t1.id > t2.id
                         AND t1.employee_id = t2.employee_id
                         AND t1.annee = t2.annee
                         AND (t1.tenant_id <=> t2.tenant_id)"
                );
                echo "[cleanup] droits_absences duplicates deleted: {$deleted}\n";

                if (!empty($tenantId)) {
                    $updated = DB::table('droits_absences')
                        ->whereNull('tenant_id')
                        ->update(['tenant_id' => $tenantId]);
                    echo "[cleanup] droits_absences tenant_id NULL normalized: {$updated}\n";
                }
            }

            // ----------------------------
            // badgeuses: key (employee_id, date_pointage, tenant_id if exists)
            // ----------------------------
            if (Schema::hasTable('badgeuses')) {
                // badgeuses in this DB has NO tenant_id column
                // So we dedup by (employee_id, date_pointage)
                if (Schema::hasColumn('badgeuses', 'employee_id') && Schema::hasColumn('badgeuses', 'date_pointage')) {
                    $deleted = DB::delete(
                        "DELETE t1 FROM badgeuses t1
                         INNER JOIN badgeuses t2
                           WHERE t1.id > t2.id
                             AND t1.employee_id = t2.employee_id
                             AND t1.date_pointage = t2.date_pointage"
                    );
                    echo "[cleanup] badgeuses duplicates deleted: {$deleted}\n";
                }
            }

            // ----------------------------
            // badge_records: key (employee_id, created_at?) best-effort:
            // if tenant_id exists, use (tenant_id, employee_id, DATE(created_at), maybe signature)
            // We'll only do simple normalization: remove empty strings -> NULL.
            // ----------------------------
            if (Schema::hasTable('badge_records')) {
                // Normalize common bad fields if they exist
                $cols = ['signature_arrivee', 'signature_depart', 'note', 'localisation', 'ip_arrivee', 'ip_depart', 'token', 'source'];
                $existing = [];
                foreach ($cols as $c) {
                    if (Schema::hasColumn('badge_records', $c)) {
                        $existing[] = $c;
                    }
                }
                foreach ($existing as $c) {
                    $updated = DB::table('badge_records')
                        ->whereRaw("TRIM(CAST({$c} AS CHAR(255))) = ''")
                        ->update([$c => null]);
                    echo "[cleanup] badge_records normalize empty->NULL for {$c}: {$updated}\n";
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function down(): void
    {
        // Data cleanup is not reversible.
        echo "Data cleanup migration - no down action\n";
    }
};

