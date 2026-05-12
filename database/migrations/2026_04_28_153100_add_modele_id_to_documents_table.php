<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('documents', 'modele_id')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->foreignId('modele_id')
                      ->nullable()
                      ->constrained('document_modeles')
                      ->nullOnDelete()
                      ->after('employe_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('documents', 'modele_id')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropForeign(['modele_id']);
                $table->dropColumn('modele_id');
            });
        }
    }
};
