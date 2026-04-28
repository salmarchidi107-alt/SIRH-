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
        Schema::table('pointages', function (Blueprint $table) {
            if (!Schema::hasColumn('pointages', 'pause_start')) {
                $table->time('pause_start')->nullable();
            }
            if (!Schema::hasColumn('pointages', 'pause_end')) {
                $table->time('pause_end')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pointages', function (Blueprint $table) {
            if (Schema::hasColumn('pointages', 'pause_start')) {
                $table->dropColumn('pause_start');
            }
            if (Schema::hasColumn('pointages', 'pause_end')) {
                $table->dropColumn('pause_end');
            }
        });
    }
};
