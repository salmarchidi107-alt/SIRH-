<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            if (!Schema::hasColumn('absences', 'tenant_id')) {
                $table->uuid('tenant_id')->nullable()->after('id');
            }
        });

        // La contrainte de clé étrangère est ajoutée séparément : si la colonne
        // existait déjà mais sans contrainte, celle-ci sera bien créée quand même.
        $foreignKeyExists = collect(
            \DB::select("SHOW KEYS FROM absences WHERE Key_name = 'absences_tenant_id_foreign'")
        )->isNotEmpty();

        if (!$foreignKeyExists) {
            Schema::table('absences', function (Blueprint $table) {
                $table->foreign('tenant_id')
                      ->references('id')
                      ->on('tenants')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
