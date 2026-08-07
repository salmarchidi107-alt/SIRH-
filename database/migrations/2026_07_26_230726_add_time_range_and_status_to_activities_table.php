<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ne rien faire si la table n'existe pas
        if (!Schema::hasTable('activities')) {
            return;
        }

        Schema::table('activities', function (Blueprint $table) {

            if (!Schema::hasColumn('activities', 'start_time')) {
                $table->time('start_time')->nullable();
            }

            if (!Schema::hasColumn('activities', 'end_time')) {
                $table->time('end_time')->nullable();
            }

            if (!Schema::hasColumn('activities', 'status')) {
                $table->enum('status', ['soumise', 'validee', 'rejetee'])
                      ->default('soumise');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('activities')) {
            return;
        }

        Schema::table('activities', function (Blueprint $table) {

            if (Schema::hasColumn('activities', 'start_time')) {
                $table->dropColumn('start_time');
            }

            if (Schema::hasColumn('activities', 'end_time')) {
                $table->dropColumn('end_time');
            }

            if (Schema::hasColumn('activities', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
