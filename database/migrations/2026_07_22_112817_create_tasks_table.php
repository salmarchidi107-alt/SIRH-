<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            // Multi-tenant, comme le reste de l'application (users, employees, absences...)
            $table->string('tenant_id')->nullable()->index();

            // Toute tâche appartient à un projet, créé au préalable par l'employé
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();

            // L'employé propriétaire / créateur de la tâche (redondant avec
            // projects.user_id mais évite un join pour les contrôles d'accès)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('priority', ['faible', 'normale', 'haute', 'urgente'])
                  ->default('normale');

            $table->enum('status', ['a_faire', 'en_cours', 'en_pause', 'terminee', 'annulee'])
                  ->default('a_faire');

            $table->date('due_date')->nullable();

            // Estimation optionnelle saisie par l'employé, stockée en minutes
            $table->unsignedInteger('estimated_minutes')->nullable();

            // Horodatage de démarrage du chronomètre en cours (null = pas de session active)
            $table->timestamp('timer_started_at')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'status']);
            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
