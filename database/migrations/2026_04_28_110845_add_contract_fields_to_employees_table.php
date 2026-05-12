<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'work_hours')) {
                $table->string('work_hours')->nullable()->after('contract_type');
            }
            if (!Schema::hasColumn('employees', 'contract_start_date')) {
                $table->date('contract_start_date')->nullable()->after('work_hours');
            }
            if (!Schema::hasColumn('employees', 'contract_end_date')) {
                $table->date('contract_end_date')->nullable()->after('contract_start_date');
            }
            if (!Schema::hasColumn('employees', 'work_days')) {
                $table->json('work_days')->nullable()->after('contract_end_date');
            }
            if (!Schema::hasColumn('employees', 'cp_days')) {
                $table->integer('cp_days')->default(0)->after('work_days');
            }
            if (!Schema::hasColumn('employees', 'work_hours_counter')) {
                $table->decimal('work_hours_counter', 8, 2)->default(0)->after('cp_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            foreach (['work_hours', 'contract_start_date', 'contract_end_date', 'work_days', 'cp_days', 'work_hours_counter'] as $col) {
                if (Schema::hasColumn('employees', $col)) $table->dropColumn($col);
            }
        });
    }
};
