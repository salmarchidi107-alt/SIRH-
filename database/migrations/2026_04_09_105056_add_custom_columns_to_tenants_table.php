<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {

            if (!Schema::hasColumn('tenants', 'sector')) {
                $table->string('sector')->nullable();
            }

            if (!Schema::hasColumn('tenants', 'logo_path')) {
                $table->string('logo_path')->nullable();
            }

            if (!Schema::hasColumn('tenants', 'brand_color')) {
                $table->string('brand_color', 7)->default('#1a8fa5');
            }

            if (!Schema::hasColumn('tenants', 'region')) {
                $table->string('region')->default('EU-West');
            }

            if (!Schema::hasColumn('tenants', 'database_name')) {
                $table->string('database_name')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            foreach (['sector', 'logo_path', 'brand_color', 'region', 'database_name'] as $col) {
                if (Schema::hasColumn('tenants', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
