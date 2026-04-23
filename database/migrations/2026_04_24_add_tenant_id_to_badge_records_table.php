<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = 'badge_records';

        // Add column if not exists
        if (!Schema::hasColumn($tableName, 'tenant_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->uuid('tenant_id')->nullable()->after('employee_id')->index();
            });
        }

        // Add FK safely
        try {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign('tenant_id')
                    ->references('id')
                    ->on('tenants')
                    ->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // FK already exists or other error, ignore
        }
    }

    public function down(): void
    {
        $tableName = 'badge_records';

        // Drop FK safely
        try {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
            });
        } catch (\Exception $e) {}

        // Drop column if exists
        if (Schema::hasColumn($tableName, 'tenant_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }
    }
};

