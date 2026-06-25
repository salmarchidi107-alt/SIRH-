<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affectations_equipement', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);           // UUID string
            $table->unsignedBigInteger('equipement_id');
            $table->unsignedBigInteger('employee_id');
            $table->date('date_affectation');
            $table->date('date_retour_prevue')->nullable();
            $table->date('date_retour_effectif')->nullable();
            $table->string('etat_remise')->default('Bon état');   // Neuf | Bon état | État moyen
            $table->string('etat_retour')->nullable();             // Bon état | Usure normale | Endommagé | Perdu
            $table->text('observations')->nullable();
            $table->text('observations_retour')->nullable();
            $table->string('statut')->default('Actif');            // Actif | Restitué | Perdu
            $table->string('numero_decharge')->nullable();         // DCH-2026-00089
            $table->boolean('decharge_signee')->default(false);
            $table->timestamps();

            $table->foreign('equipement_id')->references('id')->on('equipements')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->index(['tenant_id', 'statut']);
            $table->index(['employee_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affectations_equipement');
    }
};
