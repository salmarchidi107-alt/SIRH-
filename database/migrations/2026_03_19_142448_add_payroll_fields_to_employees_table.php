<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Ajouter seulement si les colonnes n'existent pas déjà
            if (!Schema::hasColumn('employees', 'cnss_number')) {
                $table->string('cnss_number')->nullable()->after('cin');
            }
            if (!Schema::hasColumn('employees', 'family_status')) {
                $table->string('family_status')->default('celibataire')->after('hire_date');
            }
            if (!Schema::hasColumn('employees', 'children_count')) {
                $table->integer('children_count')->default(0)->after('family_status');
            }
            if (!Schema::hasColumn('employees', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('children_count');
            }
            if (!Schema::hasColumn('employees', 'rib')) {
                $table->string('rib')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('employees', 'payment_mode')) {
                $table->string('payment_mode')->default('virement')->after('rib');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['cnss_number', 'family_status', 'children_count', 'bank_name', 'rib', 'payment_mode']);
        });
    }
};
