<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration restores the status and plan columns that were removed
     * by migration 2026_05_01_224033_update_tenants_table_structure.php
     * but are still needed by the TenantController for filtering tenants.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Add status column if it doesn't exist
            if (!Schema::hasColumn('tenants', 'status')) {
                $table->enum('status', ['trial', 'active', 'suspended', 'inactive'])
                    ->default('trial')
                    ->after('slug');
            }

            // Add plan column if it doesn't exist
            if (!Schema::hasColumn('tenants', 'plan')) {
                $table->enum('plan', ['starter', 'pro', 'enterprise'])
                    ->default('starter')
                    ->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('tenants', 'plan')) {
                $table->dropColumn('plan');
            }
        });
    }
};
