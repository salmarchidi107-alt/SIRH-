<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'amount_excluding_tax')) {
                $table->decimal('amount_excluding_tax', 12, 2)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('expenses', 'vat_amount')) {
                $table->decimal('vat_amount', 12, 2)->nullable()->after('amount_excluding_tax');
            }
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'vat_amount')) {
                $table->dropColumn('vat_amount');
            }
            if (Schema::hasColumn('expenses', 'amount_excluding_tax')) {
                $table->dropColumn('amount_excluding_tax');
            }
        });
    }
};
