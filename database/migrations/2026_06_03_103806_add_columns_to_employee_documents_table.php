<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Créer la table si elle n'existe pas
        if (!Schema::hasTable('employee_documents')) {
            Schema::create('employee_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->string('name');
                $table->string('path');
                $table->string('original_name')->nullable();
                $table->integer('tenant_id')->nullable();
                $table->timestamps();

                $table->foreign('employee_id')
                      ->references('id')
                      ->on('employees')
                      ->onDelete('cascade');
            });
        } else {
            // Table existe, ajouter les colonnes manquantes
            Schema::table('employee_documents', function (Blueprint $table) {
                if (!Schema::hasColumn('employee_documents', 'employee_id')) {
                    $table->unsignedBigInteger('employee_id')->after('id');
                }
                if (!Schema::hasColumn('employee_documents', 'name')) {
                    $table->string('name')->after('employee_id');
                }
                if (!Schema::hasColumn('employee_documents', 'path')) {
                    $table->string('path')->after('name');
                }
                if (!Schema::hasColumn('employee_documents', 'original_name')) {
                    $table->string('original_name')->nullable()->after('path');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
