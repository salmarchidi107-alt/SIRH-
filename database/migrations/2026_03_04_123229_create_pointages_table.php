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
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('date');
            $table->time('heure_entree')->nullable();
            $table->time('heure_sortie')->nullable();
            $table->integer('pause_minutes')->default(0);
            $table->decimal('total_heures', 5, 2)->nullable();
            $table->enum('statut', ['present', 'absent', 'absence_injustifiee', 'pas_de_badge'])->default('pas_de_badge');
            $table->boolean('valide')->default(false);
            $table->boolean('ignore_badge')->default(false);
            $table->string('source')->default('tablette'); // 'tablette' | 'manuel'
            $table->string('tablette_id')->nullable();
            $table->decimal('geolat', 10, 7)->nullable();
            $table->decimal('geolng', 10, 7)->nullable();
            $table->timestamp('derniere_sync')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'employee_id', 'date']);
            $table->index(['date', 'statut']);
        });

        Schema::create('pointage_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('pointage_id')->nullable()->constrained('pointages')->onDelete('set null');
            $table->enum('type', ['entree', 'sortie']);
            $table->timestamp('scanne_le');
            $table->string('tablette_id')->nullable();
            $table->string('token_tablette')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'scanne_le']);
        });

        Schema::create('tablettes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('tablette_id')->unique();
            $table->string('token', 64)->unique();
            $table->string('localisation')->nullable();
            $table->timestamp('derniere_connexion')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pointage_events');
        Schema::dropIfExists('pointages');
        Schema::dropIfExists('tablettes');
    }
};
