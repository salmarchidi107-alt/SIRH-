<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // ✅ Seulement les colonnes MANQUANTES
            // name, slug, plan, status existent déjà
            $table->string('sector')->nullable()->after('slug');
            $table->string('logo_path')->nullable()->after('sector');
            $table->string('brand_color', 7)->default('#1a8fa5')->after('logo_path');
            $table->string('region')->default('EU-West')->after('brand_color');
            $table->string('database_name')->nullable()->after('region');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['sector', 'logo_path', 'brand_color', 'region', 'database_name']);
        });
    }
};
