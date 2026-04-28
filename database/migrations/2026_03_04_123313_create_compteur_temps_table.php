<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compteurs_temps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->integer('annee');
            $table->integer('mois');
            $table->decimal('heures_planifiees', 6, 2)->default(0);
            $table->decimal('heures_realisees', 6, 2)->default(0);
            $table->decimal('heures_supplementaires', 6, 2)->default(0);
            $table->decimal('solde_compteur', 6, 2)->default(0);
            $table->timestamps();

            $table->unique(['employee_id', 'annee', 'mois']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compteurs_temps');
    }
};