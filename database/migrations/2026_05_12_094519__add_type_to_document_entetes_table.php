<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('document_entetes', 'type')) {
            Schema::table('document_entetes', function (Blueprint $table) {
                $table->string('type')->default('entete')->after('nom');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('document_entetes', 'type')) {
            Schema::table('document_entetes', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
