<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = ['brand_color', 'sidebar_color', 'region', 'database_name'];

        foreach ($columns as $column) {
            if (!Schema::hasColumn('tenants', $column)) {
                Schema::table('tenants', function (Blueprint $table) use ($column) {
                    if ($column === 'brand_color') {
                        $table->string($column, 7)->default('#1a8fa5')->after('logo_path');
                    } elseif ($column === 'sidebar_color') {
                        $table->string($column, 7)->nullable()->after('brand_color');
                    } elseif ($column === 'region') {
                        $table->string($column)->default('EU-West')->after('brand_color');
                    } elseif ($column === 'database_name') {
                        $table->string($column)->nullable()->after('region');
                    }
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['brand_color', 'sidebar_color', 'region', 'database_name']);
        });
    }
};

