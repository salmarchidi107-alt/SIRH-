<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tenantId = null;
        try {
            $tenantId = config('app.current_tenant_id');
        } catch (\Throwable $e) {
            $tenantId = null;
        }

        DB::beginTransaction();
        try {
            // plannings: key (employee_id, date, tenant_id)
            if (Schema::hasTable('plannings') && Schema::hasColumn('plannings', 'tenant_id')) {
                $deleted = DB::delete(
                    "DELETE t1 FROM plannings t1
                     INNER JOIN plannings t2
                       WHERE t1.id > t2.id
                         AND t1.employee_id = t2.employee_id
                         AND t1.date = t2.date
                         AND (t1.tenant_id <=> t2.tenant_id)"
                );
                echo "[cleanup] plannings duplicates deleted: {$deleted}\n";

                if (!empty($tenantId)) {
                    $updated = DB::table('plannings')->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
                    echo "[cleanup] plannings tenant_id NULL normalized: {$updated}\n";
                }
            }

            // pointages: key (tenant_id, employee_id, date)
            if (Schema::hasTable('pointages') && Schema::hasColumn('pointages', 'tenant_id')) {
                $deleted = DB::delete(
                    "DELETE t1 FROM pointages t1
                     INNER JOIN pointages t2
                       WHERE t1.id > t2.id
                         AND t1.tenant_id = t2.tenant_id
                         AND t1.employee_id = t2.employee_id
                         AND t1.date = t2.date"
                );
                echo "[cleanup] pointages duplicates deleted: {$deleted}\n";
            }

            // salaries: key (employee_id, month, year, tenant_id)
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

                if (!empty($tenantId)) {
                    $updated = DB::table('salaries')->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
                    echo "[cleanup] salaries tenant_id NULL normalized: {$updated}\n";
                }
            }

            // absences
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
            }

            // compteurs_temps
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
            }

            // droits_absences
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
            }

            // badge_records: normaliser champs vides → NULL
            if (Schema::hasTable('badge_records')) {
                $cols = ['signature_arrivee', 'signature_depart', 'note', 'localisation', 'ip_arrivee', 'ip_depart', 'token', 'source'];
                foreach ($cols as $c) {
                    if (Schema::hasColumn('badge_records', $c)) {
                        DB::table('badge_records')
                            ->whereRaw("TRIM(CAST({$c} AS CHAR(255))) = ''")
                            ->update([$c => null]);
                    }
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
        echo "Data cleanup migration - no down action\n";
    }
};
