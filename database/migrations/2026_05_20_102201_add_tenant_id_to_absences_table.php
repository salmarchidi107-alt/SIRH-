<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            if (!Schema::hasColumn('absences', 'tenant_id')) {
                $table->uuid('tenant_id')->nullable()->after('id');
            }
        });

        // Vérifier si la contrainte FOREIGN KEY existe
        $foreignKeyExists = DB::select("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'absences'
            AND COLUMN_NAME = 'tenant_id'
            AND REFERENCED_TABLE_NAME = 'tenants'
            AND TABLE_SCHEMA = DATABASE()
        ");

        if (empty($foreignKeyExists)) {
            Schema::table('absences', function (Blueprint $table) {
                $table->foreign('tenant_id')
                      ->references('id')
                      ->on('tenants')
                      ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            // Vérifier avant de supprimer
            try {
                $table->dropForeign(['tenant_id']);
            } catch (\Exception $e) {
                // Ignorer si elle n'existe pas
            }

            if (Schema::hasColumn('absences', 'tenant_id')) {
                $table->dropColumn('tenant_id');
            }
        });
    }
};
