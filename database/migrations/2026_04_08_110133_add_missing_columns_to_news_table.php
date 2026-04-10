<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {

            if (!Schema::hasColumn('news', 'type')) {
                $table->string('type')->default('annual_event')->after('image');
            }

            if (!Schema::hasColumn('news', 'event_date')) {
                $table->date('event_date')->nullable()->after('type');
            }

            if (!Schema::hasColumn('news', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('event_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {

            if (Schema::hasColumn('news', 'type')) {
                $table->dropColumn('type');
            }

            if (Schema::hasColumn('news', 'event_date')) {
                $table->dropColumn('event_date');
            }

            if (Schema::hasColumn('news', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
