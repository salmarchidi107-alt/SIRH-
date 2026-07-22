<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tenants', 'currency')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->string('currency', 3)->default('MAD')->after('name');
            });
        }

        // Backfill de sécurité : toute valeur nulle ou vide devient 'MAD'
        DB::table('tenants')
            ->where(function ($q) {
                $q->whereNull('currency')->orWhere('currency', '');
            })
            ->update(['currency' => 'MAD']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('tenants', 'currency')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
    }
};
