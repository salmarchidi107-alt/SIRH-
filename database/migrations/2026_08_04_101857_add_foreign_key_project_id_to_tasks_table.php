<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Vérifier si la clé étrangère existe avant de la supprimer
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = 'tasks'
                AND COLUMN_NAME = 'project_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            if (!empty($foreignKeys)) {
                $table->dropForeign(['project_id']);
            }

            // Créer la nouvelle clé étrangère
            $table->foreign('project_id')
                ->references('id')->on('projects')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            try {
                $table->dropForeign(['project_id']);
            } catch (\Exception $e) {
                // Ignorer si la clé n'existe pas
            }

            $table->foreign('project_id')
                ->references('id')->on('projects')
                ->cascadeOnDelete();
        });
    }
};
