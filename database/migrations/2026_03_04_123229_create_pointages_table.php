<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pointages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('date');
            $table->time('heure_entree')->nullable();
            $table->time('heure_sortie')->nullable();
            $table->decimal('heures_travaillees', 5, 2)->default(0);
            $table->decimal('heures_supplementaires', 5, 2)->default(0);
            $table->enum('statut', ['present', 'absent', 'conge', 'maladie'])->default('present');
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pointages');
    }
};