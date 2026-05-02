<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (!Schema::hasColumn('departments', 'code')) {
                $table->string('code', 10)->nullable()->after('name');
            }
            if (!Schema::hasColumn('departments', 'color')) {
                $table->string('color', 7)->default('#0ea5e9')->after('code');
            }
            if (!Schema::hasColumn('departments', 'chef')) {
                $table->string('chef')->nullable()->after('color');
            }
            if (!Schema::hasColumn('departments', 'description')) {
                $table->text('description')->nullable()->after('chef');
            }
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn(['code', 'color', 'chef', 'description']);
        });
    }
};