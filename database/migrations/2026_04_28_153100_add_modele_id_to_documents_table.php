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
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'modele_id')) {
                $table->foreignId('modele_id')
                      ->nullable()
                      ->constrained('document_modeles')
                      ->nullOnDelete()
                      ->after('employe_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'modele_id')) {
                $table->dropForeign(['modele_id']);
                $table->dropColumn('modele_id');
            }
        });
    }
};
