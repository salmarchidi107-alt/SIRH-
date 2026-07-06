<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropForeignKeyOnColumn('plannings', 'employee_id');

        $this->dropForeignKeyOnColumn('plannings', 'tenant_id');

        try {
            Schema::table('plannings', function (Blueprint $table) {
                $table->dropUnique('plannings_employee_id_date_tenant_unique');
            });
        } catch (\Throwable $e) {
        }

        Schema::table('plannings', function (Blueprint $table) {
            if (!$this->hasForeignKeyOnColumn('plannings', 'employee_id')) {
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            }
            if (!$this->hasForeignKeyOnColumn('plannings', 'tenant_id')) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        $this->dropForeignKeyOnColumn('plannings', 'employee_id');
        $this->dropForeignKeyOnColumn('plannings', 'tenant_id');

        Schema::table('plannings', function (Blueprint $table) {
            $table->unique(['employee_id', 'date', 'tenant_id'], 'plannings_employee_id_date_tenant_unique');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }


    private function dropForeignKeyOnColumn(string $table, string $column): void
    {
        $dbName = DB::getDatabaseName();

        $constraints = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$dbName, $table, $column]);

        foreach ($constraints as $constraint) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
        }
    }


    private function hasForeignKeyOnColumn(string $table, string $column): bool
    {
        $dbName = DB::getDatabaseName();

        $result = DB::select("
            SELECT COUNT(*) as cnt
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$dbName, $table, $column]);

        return ($result[0]->cnt ?? 0) > 0;
    }
};
