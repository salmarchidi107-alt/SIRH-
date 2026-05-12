<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('employees', 'plain_pin')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('plain_pin')->nullable()->after('pin');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('employees', 'plain_pin')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('plain_pin');
            });
        }
    }
};
