<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {

            // ── Supprimer les anciennes colonnes si elles existent ─────────
            if (Schema::hasColumn('tenants', 'plan')) {
                $table->dropColumn('plan');
            }
            if (Schema::hasColumn('tenants', 'status')) {
                $table->dropColumn('status');
            }

            // ── Ajouter les nouvelles colonnes si elles n'existent pas ─────
            if (!Schema::hasColumn('tenants', 'sector')) {
                $table->string('sector', 50)->nullable()->after('slug');
            }
            if (!Schema::hasColumn('tenants', 'region')) {
                $table->string('region')->nullable()->after('sector');
            }
            if (!Schema::hasColumn('tenants', 'address')) {
                $table->string('address')->nullable()->after('region');
            }
            if (!Schema::hasColumn('tenants', 'phone')) {
                $table->string('phone', 20)->nullable()->after('address');
            }
            if (!Schema::hasColumn('tenants', 'ice')) {
                $table->string('ice', 15)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('tenants', 'email_societe')) {
                $table->string('email_societe')->nullable()->after('ice');
            }
            if (!Schema::hasColumn('tenants', 'website')) {
                $table->string('website')->nullable()->after('email_societe');
            }
            if (!Schema::hasColumn('tenants', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('website');
            }
            if (!Schema::hasColumn('tenants', 'brand_color')) {
                $table->string('brand_color', 7)->default('#1abfa5')->after('logo_path');
            }
            if (!Schema::hasColumn('tenants', 'sidebar_color')) {
                $table->string('sidebar_color', 7)->default('#0d2137')->after('brand_color');
            }
            if (!Schema::hasColumn('tenants', 'database_name')) {
                $table->string('database_name')->nullable()->after('sidebar_color');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('tenants', 'address')       ? 'address'       : null,
                Schema::hasColumn('tenants', 'phone')         ? 'phone'         : null,
                Schema::hasColumn('tenants', 'ice')           ? 'ice'           : null,
                Schema::hasColumn('tenants', 'email_societe') ? 'email_societe' : null,
                Schema::hasColumn('tenants', 'website')       ? 'website'       : null,
                Schema::hasColumn('tenants', 'sidebar_color') ? 'sidebar_color' : null,
                Schema::hasColumn('tenants', 'database_name') ? 'database_name' : null,
            ]));

            $table->enum('plan',   ['starter', 'pro', 'enterprise'])->default('starter');
            $table->enum('status', ['trial', 'active', 'suspended'])->default('trial');
        });
    }
};