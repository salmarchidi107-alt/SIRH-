<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            // Ajouter la colonne tenant_id après user_id
            $table->uuid('tenant_id')->after('user_id')->nullable();

            // Ajouter une clé étrangère vers la table tenants
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            // Ajouter un index pour les performances
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
