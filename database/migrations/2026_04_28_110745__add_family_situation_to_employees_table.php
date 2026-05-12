<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('employees', 'family_situation')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('family_situation')->nullable()->after('address');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('employees', 'family_situation')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('family_situation');
            });
        }
    }
};
