<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Table badge_records ──────────────────────────────────────────
        Schema::table('badge_records', function (Blueprint $table) {
            $table->string('shift_type', 20)
                  ->default('normal')
                  ->after('type')
                  ->comment('Type de shift : normal | garde');
        });

        // ── Table pointages ──────────────────────────────────────────────
        Schema::table('pointages', function (Blueprint $table) {
            $table->string('shift_type', 20)
                  ->default('normal')
                  ->after('source')
                  ->comment('Type de shift : normal | garde');
        });
    }

    public function down(): void
    {
        Schema::table('badge_records', function (Blueprint $table) {
            $table->dropColumn('shift_type');
        });

        Schema::table('pointages', function (Blueprint $table) {
            $table->dropColumn('shift_type');
        });
    }
};
