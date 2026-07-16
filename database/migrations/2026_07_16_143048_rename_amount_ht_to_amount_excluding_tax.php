<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'amount_ht') && !Schema::hasColumn('expenses', 'amount_excluding_tax')) {
                $table->renameColumn('amount_ht', 'amount_excluding_tax');
            } elseif (!Schema::hasColumn('expenses', 'amount_excluding_tax')) {
                $table->decimal('amount_excluding_tax', 12, 2)->nullable()->after('amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'amount_excluding_tax')) {
                $table->renameColumn('amount_excluding_tax', 'amount_ht');
            }
        });
    }
};
