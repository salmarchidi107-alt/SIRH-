<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // L'employé responsable de la tâche (peut différer du créateur :
            // un admin/RH crée la tâche et l'assigne à cet employé).
            $table->foreignId('assigned_to')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['assigned_to', 'status']);
        });

        // Rétro-compatibilité : les tâches déjà créées sont assignées à leur créateur.
        DB::table('tasks')->whereNull('assigned_to')->update([
            'assigned_to' => DB::raw('user_id'),
        ]);
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to');
        });
    }
};
