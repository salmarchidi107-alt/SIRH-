<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('position');
            }
            if (!Schema::hasColumn('employees', 'pin')) {
                $table->string('pin')->nullable()->after('matricule');
            }
            if (!Schema::hasColumn('employees', 'plain_pin')) {
                $table->string('plain_pin')->nullable()->after('pin');
            }
            if (!Schema::hasColumn('employees', 'signature')) {
                $table->longText('signature')->nullable()->after('plain_pin');
            }
            if (!Schema::hasColumn('employees', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('department')
                      ->constrained('departments')->nullOnDelete();
            }
        });

        // Changer payment_method en string nullable si c'est un enum
        try {
            if (Schema::hasColumn('employees', 'payment_method')) {
                Schema::table('employees', function (Blueprint $table) {
                    $table->string('payment_method')->nullable()->change();
                });
            }
        } catch (\Exception $e) {
            // Déjà correct ou non applicable
        }
    }

    public function down(): void
    {
        // Pas de rollback — colonnes optionnelles
    }
};
