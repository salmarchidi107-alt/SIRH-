<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'diploma_type')) {
                $table->string('diploma_type')->nullable()->after('position');
            }
            if (!Schema::hasColumn('employees', 'skills')) {
                $table->text('skills')->nullable()->after('diploma_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'diploma_type')) $table->dropColumn('diploma_type');
            if (Schema::hasColumn('employees', 'skills')) $table->dropColumn('skills');
        });
    }
};
