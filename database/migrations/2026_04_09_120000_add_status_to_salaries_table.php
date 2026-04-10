<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            if (!Schema::hasColumn('salaries', 'status')) {
                $table->enum('status', ['draft', 'validated', 'paid'])
                      ->default('draft')
                      ->after('net_salary');
            }
        });

        // Set default for existing records
        DB::table('salaries')
            ->whereNull('status')
            ->update(['status' => 'draft']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            if (Schema::hasColumn('salaries', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};

