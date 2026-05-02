<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'capacity')) {
                $table->unsignedInteger('capacity')->nullable()->after('department_id');
            }
            if (!Schema::hasColumn('rooms', 'description')) {
                $table->text('description')->nullable()->after('capacity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['capacity', 'description']);
        });
    }
};