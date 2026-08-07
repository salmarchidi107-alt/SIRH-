<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        Schema::table('absences', function (Blueprint $table) {
            if (!Schema::hasColumn('absences', 'tenant_id')) {
                $table->uuid('tenant_id')->nullable()->after('id');
            }
        });

        // Vérification compatible avec SQLite et MySQL
        $foreignKeyExists = false;

        if ($driver === 'sqlite') {
            // SQLite: use PRAGMA foreign_key_list
            $foreignKeys = DB::select("PRAGMA foreign_key_list(absences)");
            foreach ($foreignKeys as $fk) {
                if ($fk->from === 'tenant_id' && $fk->table === 'tenants') {
                    $foreignKeyExists = true;
                    break;
                }
            }
        } else {
            // MySQL/PostgreSQL: use information_schema
            $foreignKeyExists = DB::table('information_schema.TABLE_CONSTRAINTS')
                ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', 'absences')
                ->where('CONSTRAINT_NAME', 'absences_tenant_id_foreign')
                ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
                ->exists();
        }

        if (!$foreignKeyExists) {
            try {
                Schema::table('absences', function (Blueprint $table) {
                    $table->foreign('tenant_id')
                          ->references('id')
                          ->on('tenants')
                          ->onDelete('cascade');
                });
            } catch (\Illuminate\Database\QueryException $e) {
                // Si la contrainte existe déjà sous un nom différent, ignorer l'erreur
                if (!str_contains($e->getMessage(), 'errno: 121') &&
                    !str_contains($e->getMessage(), 'Duplicate') &&
                    !str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                    throw $e;
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            // Vérifier avant de supprimer pour éviter les erreurs
            if (Schema::hasColumn('absences', 'tenant_id')) {
                try {
                    $table->dropForeign(['tenant_id']);
                } catch (\Exception $e) {
                    // Ignorer si la FK n'existe pas
                }
                $table->dropColumn('tenant_id');
            }
        });
    }
};
