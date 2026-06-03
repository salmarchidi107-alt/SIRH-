<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            if (!Schema::hasColumn('salaries', 'validated_by')) {
                $table->unsignedBigInteger('validated_by')->nullable()->after('status');
            }
            if (!Schema::hasColumn('salaries', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('validated_by');
            }
            if (!Schema::hasColumn('salaries', 'paid_by')) {
                $table->unsignedBigInteger('paid_by')->nullable()->after('paid_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $cols = ['validated_by', 'validated_at', 'paid_by'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('salaries', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
