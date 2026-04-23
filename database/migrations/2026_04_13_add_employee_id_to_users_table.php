<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('employee_id')
                  ->nullable()
                  ->after('tenant_id')
                  ->constrained('employees')
                  ->nullOnDelete();
        });

        // Backfill existing relationships
        DB::statement('
            UPDATE users u
            INNER JOIN employees e ON u.id = e.user_id
            SET u.employee_id = e.id
        ');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
        });
    }
};

