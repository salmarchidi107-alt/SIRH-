<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('week_templates', function (Blueprint $table) {
            $table->string('monday_room')->nullable()->after('monday_end');
            $table->string('tuesday_room')->nullable()->after('tuesday_end');
            $table->string('wednesday_room')->nullable()->after('wednesday_end');
            $table->string('thursday_room')->nullable()->after('thursday_end');
            $table->string('friday_room')->nullable()->after('friday_end');
            $table->string('saturday_room')->nullable()->after('saturday_end');
            $table->string('sunday_room')->nullable()->after('sunday_end');
        });

        Schema::table('plannings', function (Blueprint $table) {
            $table->string('room')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('week_templates', function (Blueprint $table) {
            $table->dropColumn([
                'monday_room',
                'tuesday_room',
                'wednesday_room',
                'thursday_room',
                'friday_room',
                'saturday_room',
                'sunday_room',
            ]);
        });

        Schema::table('plannings', function (Blueprint $table) {
            $table->dropColumn('room');
        });
    }
};
