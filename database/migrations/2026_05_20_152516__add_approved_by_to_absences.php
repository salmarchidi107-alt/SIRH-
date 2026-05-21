<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            // Ajoute approved_by après approved_at si la colonne n'existe pas déjà
            if (! Schema::hasColumn('absences', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
                $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn('approved_by');
        });
    }
};
