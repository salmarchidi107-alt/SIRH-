<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('titre');
            $table->string('formateur');
            $table->string('organisme');
            $table->date('date');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->enum('statut', ['Planifiée', 'En cours', 'Terminée', 'Annulée'])->default('Planifiée');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};
