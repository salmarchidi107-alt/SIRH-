<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // tenant_id déjà dans la migration de création de rooms — rien à faire
    }

    public function down(): void {}
};
