<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->uuid('tenant_id')->nullable()->after('id')->index();
        });

        Schema::table('document_entetes', function (Blueprint $table) {
            $table->uuid('tenant_id')->nullable()->after('id')->index();
        });

        Schema::table('document_modeles', function (Blueprint $table) {
            $table->uuid('tenant_id')->nullable()->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('document_entetes', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('document_modeles', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
    }
};