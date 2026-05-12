<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'pin')) {
                $table->string('pin')->nullable()->after('matricule');
            }
            if (!Schema::hasColumn('employees', 'signature')) {
                $table->longText('signature')->nullable()->after('pin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'pin')) $table->dropColumn('pin');
            if (Schema::hasColumn('employees', 'signature')) $table->dropColumn('signature');
        });
    }
};
