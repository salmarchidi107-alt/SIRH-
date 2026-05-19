<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badge_records', function (Blueprint $table) {
            // Coordonnées GPS
            $table->decimal('latitude', 10, 7)->nullable()->after('type')
                  ->comment('Latitude GPS au moment du pointage');

            $table->decimal('longitude', 10, 7)->nullable()->after('latitude')
                  ->comment('Longitude GPS au moment du pointage');

            $table->decimal('accuracy', 8, 2)->nullable()->after('longitude')
                  ->comment('Précision GPS en mètres');

            // Adresse lisible (reverse geocoding)
            $table->string('location_address', 255)->nullable()->after('accuracy')
                  ->comment('Adresse humaine obtenue par reverse geocoding Nominatim');

            // Flag refus
            $table->boolean('geolocation_denied')->default(false)->after('location_address')
                  ->comment('true si l\'employé a refusé ou si la géoloc était indisponible');
        });
    }

    public function down(): void
    {
        Schema::table('badge_records', function (Blueprint $table) {
            $table->dropColumn([
                'latitude',
                'longitude',
                'accuracy',
                'location_address',
                'geolocation_denied',
            ]);
        });
    }
};
