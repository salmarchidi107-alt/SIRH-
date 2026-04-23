<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $columns = Schema::getColumnListing('tenants');

        if (!in_array('sector', $columns)) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->string('sector')->nullable()->after('slug');
            });
        }

        if (!in_array('logo_path', $columns)) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->string('logo_path')->nullable()->after('sector');
            });
        }

        if (!in_array('brand_color', $columns)) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->string('brand_color', 7)->default('#1a8fa5')->after('logo_path');
            });
        }

        // Add other custom columns if needed...
    }

    public function down(): void
    {
        $columns = Schema::getColumnListing('tenants');

        if (in_array('sector', $columns)) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('sector');
            });
        }

        // Similar for others...
    }
};

