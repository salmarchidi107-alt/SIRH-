<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badgeuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('date_pointage');
            $table->time('heure_arrivee')->nullable();
            $table->time('heure_depart')->nullable();
            $table->string('signature_arrivee')->nullable(); // base64 ou path
            $table->string('signature_depart')->nullable();
            $table->integer('retard_minutes')->default(0);   // en minutes
            $table->integer('heures_travaillees')->nullable(); // en minutes
            $table->enum('statut', ['present', 'absent', 'retard', 'demi_journee'])->default('present');
            $table->text('note')->nullable();
            $table->string('ip_arrivee')->nullable();
            $table->string('ip_depart')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'date_pointage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badgeuses');
    }
};
