<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // fichier_path et fichier_nom_original sont déjà nullable dans la migration de création
    }

    public function down(): void {}
};
