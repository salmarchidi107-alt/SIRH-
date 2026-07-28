<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            // Supprime l'ancien index unique global sur `reference` seul
            // (bug : empêchait deux tenants différents d'avoir tous les deux
            // une référence "PC-00001", alors que genererReference() génère
            // ce numéro de façon indépendante par tenant)
            $table->dropUnique('equipements_reference_unique');

            // Nouvel index unique composite : la référence doit être unique
            // par tenant, pas globalement
            $table->unique(['tenant_id', 'reference'], 'equipements_tenant_reference_unique');
        });
    }

    public function down(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            $table->dropUnique('equipements_tenant_reference_unique');
            $table->unique('reference', 'equipements_reference_unique');
        });
    }
};
