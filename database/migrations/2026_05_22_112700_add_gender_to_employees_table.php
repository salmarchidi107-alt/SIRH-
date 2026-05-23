<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Si gender existe déjà → on renomme
            if (Schema::hasColumn('employees', 'gender')) {
                $table->renameColumn('gender', 'genre');
            }
            // Si ni gender ni genre n'existent → on crée directement genre
            elseif (!Schema::hasColumn('employees', 'genre')) {
                $table->enum('genre', ['homme', 'femme'])->nullable()->after('birth_date');
            }
            // Si genre existe déjà → rien à faire
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'genre')) {
                $table->dropColumn('genre');
            }
        });
    }
};
