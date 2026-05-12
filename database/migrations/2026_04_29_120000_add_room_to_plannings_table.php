<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Colonne 'room' déjà ajoutée dans 2026_04_28_112448 — on vérifie avant d'insérer
        if (!Schema::hasColumn('plannings', 'room')) {
            Schema::table('plannings', function (Blueprint $table) {
                $table->string('room')->nullable()->after('notes');
            });
        }
    }

    public function down(): void
    {
        // Ne pas supprimer : géré par la migration principale
    }
};
