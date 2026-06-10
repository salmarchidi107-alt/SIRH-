<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Jours consommés AVANT la création du compte (saisie unique à la création)
            $table->decimal('conges_anterieurs', 8, 2)
                  ->default(0)
                  ->after('cp_days')
                  ->comment('Jours de congés consommés avant la création du compte dans l\'application');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('conges_anterieurs');
        });
    }
};
