<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // "Date de début" demandée dans le formulaire de tâche admin.
            $table->date('start_date')->nullable()->after('due_date');

            // Pourcentage d'avancement saisi par l'employé (distinct du statut,
            // qui reste piloté par l'admin ou par l'employé lui-même).
            $table->unsignedTinyInteger('percent_complete')->default(0)->after('estimated_minutes');

            // Commentaire libre de l'employé sur l'avancement de SA tâche —
            // distinct des commentaires des saisies de temps (table activities).
            $table->text('employee_comment')->nullable()->after('percent_complete');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'percent_complete', 'employee_comment']);
        });
    }
};
