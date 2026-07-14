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
        Schema::table('absences', function (Blueprint $table) {
            if (Schema::hasColumn('absences', 'approved_by')) {
                // On ne drop la FK que si elle existe réellement (évite un crash
                // si cette migration s'exécute avant celle qui crée la colonne/FK)
                $foreignKeys = collect(\DB::select("
                    SELECT CONSTRAINT_NAME
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'absences'
                    AND COLUMN_NAME = 'approved_by'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                "))->pluck('CONSTRAINT_NAME');

                foreach ($foreignKeys as $fk) {
                    $table->dropForeign($fk);
                }

                $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            if (Schema::hasColumn('absences', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->foreign('approved_by')->references('id')->on('employees')->nullOnDelete();
            }
        });
    }
};
