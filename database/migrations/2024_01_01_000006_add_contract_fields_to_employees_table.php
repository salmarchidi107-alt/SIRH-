<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('work_hours')->nullable()->after('contract_type'); // Temps de travail contractuel
            $table->date('contract_start_date')->nullable()->after('work_hours'); // Début du contrat
            $table->date('contract_end_date')->nullable()->after('contract_start_date'); // Date de fin
            $table->json('work_days')->nullable()->after('contract_end_date'); // Jours de travail habituels
            $table->integer('cp_days')->default(0)->after('work_days'); // Compteur congés payés
            $table->decimal('work_hours_counter', 8, 2)->default(0)->after('cp_days'); // Compteur de temps
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['work_hours', 'contract_start_date', 'contract_end_date', 'work_days', 'cp_days', 'work_hours_counter']);
        });
    }
};
