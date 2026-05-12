<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('compteurs_temps')) {
            Schema::create('compteurs_temps', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id')->nullable()->index();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->integer('annee');
                $table->integer('mois');
                $table->decimal('heures_planifiees', 6, 2)->default(0);
                $table->decimal('heures_realisees', 6, 2)->default(0);
                $table->decimal('heures_supplementaires', 6, 2)->default(0);
                $table->decimal('solde_compteur', 6, 2)->default(0);
                $table->timestamps();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->unique(['employee_id', 'annee', 'mois', 'tenant_id'], 'compteurs_temps_employee_annee_mois_tenant_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('compteurs_temps');
    }
};
