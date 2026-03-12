<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->enum('family_situation', ['célibataire', 'marié(e)', 'divorcé(e)', 'veuf(ve)', 'en instance de divorce'])
                  ->nullable()
                  ->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('family_situation');
        });
    }
};
