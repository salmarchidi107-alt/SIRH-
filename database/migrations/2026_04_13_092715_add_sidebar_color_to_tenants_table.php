<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tenants', 'sidebar_color')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->string('sidebar_color', 7)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tenants', 'sidebar_color')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('sidebar_color');
            });
        }
    }
};
