<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // shift_start et shift_end sont déjà nullable dans la migration de création — rien à faire
    }

    public function down(): void {}
};
