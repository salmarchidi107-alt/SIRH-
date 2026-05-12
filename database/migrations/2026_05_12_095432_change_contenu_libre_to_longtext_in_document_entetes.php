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
    Schema::table('document_entetes', function (Blueprint $table) {
        $table->longText('contenu_libre')->nullable()->change();
        $table->longText('contenu_pied_de_page')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('document_entetes', function (Blueprint $table) {
        $table->string('contenu_libre')->nullable()->change();
        $table->string('contenu_pied_de_page')->nullable()->change();
    });
}
};
