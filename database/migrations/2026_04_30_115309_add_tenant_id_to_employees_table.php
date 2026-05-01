<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'tenant_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('tenant_id', 36)->nullable()->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'tenant_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }
    }
};