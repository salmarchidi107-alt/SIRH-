<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('document_modeles', 'contenu')) {
            Schema::table('document_modeles', function (Blueprint $table) {
                $table->longText('contenu')->nullable()->after('categorie');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('document_modeles', 'contenu')) {
            Schema::table('document_modeles', function (Blueprint $table) {
                $table->dropColumn('contenu');
            });
        }
    }
};
