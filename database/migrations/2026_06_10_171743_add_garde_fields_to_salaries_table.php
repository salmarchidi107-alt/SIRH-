<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            // Ajouter seulement si les colonnes n'existent pas encore
            if (! Schema::hasColumn('salaries', 'garde_indemnite')) {
                $table->decimal('garde_indemnite', 10, 2)
                      ->default(0)
                      ->after('other_gains')
                      ->comment('Indemnité de garde — valeur persistée (manuelle ou auto)');
            }

            if (! Schema::hasColumn('salaries', 'garde_override')) {
                $table->boolean('garde_override')
                      ->default(false)
                      ->after('garde_indemnite')
                      ->comment('true = valeur saisie manuellement, false = calculée auto');
            }
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn(['garde_indemnite', 'garde_override']);
        });
    }
};
