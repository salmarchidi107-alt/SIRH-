<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pointages', function (Blueprint $table) {
            if (!Schema::hasColumn('pointages', 'heures_travaillees')) {
                $table->decimal('heures_travaillees', 5, 2)->nullable()->after('total_heures');
            }
            if (!Schema::hasColumn('pointages', 'heures_supplementaires')) {
                $table->decimal('heures_supplementaires', 5, 2)->nullable()->after('heures_travaillees');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pointages', function (Blueprint $table) {
            if (Schema::hasColumn('pointages', 'heures_travaillees')) $table->dropColumn('heures_travaillees');
            if (Schema::hasColumn('pointages', 'heures_supplementaires')) $table->dropColumn('heures_supplementaires');
        });
    }
};
