<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (!Schema::hasColumn('departments', 'code')) {
                $table->string('code', 10)->nullable()->after('name');
            }
            if (!Schema::hasColumn('departments', 'color')) {
                $table->string('color', 7)->default('#0ea5e9')->after('code');
            }
            if (!Schema::hasColumn('departments', 'chef')) {
                $table->string('chef')->nullable()->after('color');
            }
            if (!Schema::hasColumn('departments', 'description')) {
                $table->text('description')->nullable()->after('chef');
            }
            if (!Schema::hasColumn('departments', 'tenant_id')) {
                $table->string('tenant_id', 36)->nullable()->after('id')->index();
            }
        });

        // Ajouter la contrainte unique composite (name, tenant_id) maintenant que les deux colonnes existent
        // On vérifie que l'index n'existe pas déjà
        try {
            Schema::table('departments', function (Blueprint $table) {
                $table->unique(['name', 'tenant_id'], 'departments_name_tenant_unique');
            });
        } catch (\Exception $e) {
            // Index déjà existant — ignorer
        }
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            try { $table->dropUnique('departments_name_tenant_unique'); } catch (\Exception $e) {}
            $table->dropColumn(array_filter([
                Schema::hasColumn('departments', 'code')        ? 'code'        : null,
                Schema::hasColumn('departments', 'color')       ? 'color'       : null,
                Schema::hasColumn('departments', 'chef')        ? 'chef'        : null,
                Schema::hasColumn('departments', 'description') ? 'description' : null,
                Schema::hasColumn('departments', 'tenant_id')   ? 'tenant_id'   : null,
            ]));
        });
    }
};
