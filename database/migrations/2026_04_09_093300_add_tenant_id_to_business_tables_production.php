<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // tenant_id déjà présent dans toutes les migrations de création — rien à faire
    }

    public function down(): void {}
};
