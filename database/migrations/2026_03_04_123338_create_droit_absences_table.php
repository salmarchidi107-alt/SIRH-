<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('droits_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->integer('annee');
            $table->decimal('jours_acquis', 5, 2)->default(0);
            $table->decimal('jours_pris', 5, 2)->default(0);
            $table->decimal('jours_en_attente', 5, 2)->default(0);
            $table->decimal('jours_solde', 5, 2)->default(0);
            $table->decimal('rtt_acquis', 5, 2)->default(0);
            $table->decimal('rtt_pris', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['employee_id', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('droits_absences');
    }
};