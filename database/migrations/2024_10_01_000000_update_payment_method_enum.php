<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update existing data to valid enum values before changing enum (safe check)
        if (Schema::hasColumn('employees', 'payment_method')) {
            DB::statement("UPDATE employees SET payment_method = 'cash' WHERE payment_method = 'bank'");
        }

        // Modify enum to match form values (French terms) - only if column exists
        if (Schema::hasColumn('employees', 'payment_method')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->enum('payment_method', ['virement', 'cash', 'chèque'])->default('virement')->change();
            });
        }
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->enum('payment_method', ['bank', 'cash'])->default('bank')->change();
        });
    }
};

