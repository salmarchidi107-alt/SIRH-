<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('amount_ht', 10, 2)->nullable()->after('amount');
            $table->decimal('vat_amount', 10, 2)->nullable()->after('amount_ht');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['amount_ht', 'vat_amount']);
        });
    }
};
