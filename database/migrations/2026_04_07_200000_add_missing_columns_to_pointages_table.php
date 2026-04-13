<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pointages', function (Blueprint $table) {
            if (!Schema::hasColumn('pointages', 'valide')) {
                $table->boolean('valide')->default(false)->after('statut');
            }
            if (!Schema::hasColumn('pointages', 'ignore_badge')) {
                $table->boolean('ignore_badge')->default(false)->after('valide');
            }
            if (!Schema::hasColumn('pointages', 'source')) {
                $table->string('source')->default('tablette')->after('ignore_badge');
            }
            if (!Schema::hasColumn('pointages', 'tablette_id')) {
                $table->string('tablette_id')->nullable()->after('source');
            }
            if (!Schema::hasColumn('pointages', 'geolat')) {
                $table->decimal('geolat', 10, 7)->nullable()->after('tablette_id');
            }
            if (!Schema::hasColumn('pointages', 'geolng')) {
                $table->decimal('geolng', 10, 7)->nullable()->after('geolat');
            }
            if (!Schema::hasColumn('pointages', 'derniere_sync')) {
                $table->timestamp('derniere_sync')->nullable()->after('geolng');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pointages', function (Blueprint $table) {
            $table->dropColumn(['valide', 'ignore_badge', 'source', 'tablette_id', 'geolat', 'geolng', 'derniere_sync']);
        });
    }
};

