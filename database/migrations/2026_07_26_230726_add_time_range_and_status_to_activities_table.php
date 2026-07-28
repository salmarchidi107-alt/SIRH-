<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Heure de début / fin, saisies quand l'employé les renseigne plutôt
            // qu'une durée brute. Les deux restent nullable : soit l'employé
            // saisit une plage horaire, soit il saisit directement une durée
            // (cf. App\Support\Duration::toMinutes) — duration_minutes reste
            // la source de vérité pour tous les calculs de temps.
            $table->time('start_time')->nullable()->after('activity_date');
            $table->time('end_time')->nullable()->after('start_time');

            // Statut de la saisie : l'employé la soumet, l'admin/RH la valide
            // ou la rejette (pour un suivi précis, cf. besoin exprimé).
            $table->enum('status', ['soumise', 'validee', 'rejetee'])
                  ->default('soumise')
                  ->after('comment');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time', 'status']);
        });
    }
};
