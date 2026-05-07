<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('document_entetes', function (Blueprint $table) {
            $table->text('contenu_pied_de_page')->nullable()->after('contenu_libre');
        });
    }

    public function down(): void {
        Schema::table('document_entetes', function (Blueprint $table) {
            $table->dropColumn('contenu_pied_de_page');
        });
    }
};

