<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('salaries', function (Blueprint $table) {
            if (!Schema::hasColumn('salaries', 'seniority_bonus')) {
                $table->decimal('seniority_bonus', 10, 2)->default(0)->after('overtime_hours');
            }
        });
    }

    public function down()
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn('seniority_bonus');
        });
    }
};