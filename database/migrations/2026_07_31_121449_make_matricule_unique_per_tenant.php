<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Supprime l'ancienne contrainte unique globale sur matricule
            $table->dropUnique('employees_matricule_unique');

            // Le matricule doit être unique par tenant, pas sur toute la table
            $table->unique(['tenant_id', 'matricule'], 'employees_tenant_id_matricule_unique');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique('employees_tenant_id_matricule_unique');
            $table->unique('matricule', 'employees_matricule_unique');
        });
    }
};
