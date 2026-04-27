<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('document_modeles', function (Blueprint $table) {
            $table->string('fichier_path')->nullable()->change();
            $table->string('fichier_nom_original')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_modeles', function (Blueprint $table) {
            $table->string('fichier_path')->nullable(false)->change();
            $table->string('fichier_nom_original')->nullable(false)->change();
        });
    }
};

