<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $weekRoomCols = [
            'monday_room'    => 'monday_end',
            'tuesday_room'   => 'tuesday_end',
            'wednesday_room' => 'wednesday_end',
            'thursday_room'  => 'thursday_end',
            'friday_room'    => 'friday_end',
            'saturday_room'  => 'saturday_end',
            'sunday_room'    => 'sunday_end',
        ];

        foreach ($weekRoomCols as $col => $after) {
            if (!Schema::hasColumn('week_templates', $col)) {
                Schema::table('week_templates', function (Blueprint $table) use ($col, $after) {
                    $table->string($col)->nullable()->after($after);
                });
            }
        }

        if (!Schema::hasColumn('plannings', 'room')) {
            Schema::table('plannings', function (Blueprint $table) {
                $table->string('room')->nullable()->after('notes');
            });
        }
    }

    public function down(): void
    {
        $cols = ['monday_room', 'tuesday_room', 'wednesday_room', 'thursday_room',
                 'friday_room', 'saturday_room', 'sunday_room'];
        foreach ($cols as $col) {
            if (Schema::hasColumn('week_templates', $col)) {
                Schema::table('week_templates', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }
        if (Schema::hasColumn('plannings', 'room')) {
            Schema::table('plannings', function (Blueprint $table) {
                $table->dropColumn('room');
            });
        }
    }
};
