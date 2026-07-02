<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_locations', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 100);      // UUID ou int — limité à 100 pour l'index
            $table->string('department', 100)->nullable(); // limité à 100 pour l'index
            $table->string('site_name');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('radius_meters')->default(300);
            $table->timestamps();
        });

        // Index ajouté manuellement avec longueur explicite pour éviter l'erreur MySQL 1071
        DB::statement('ALTER TABLE site_locations ADD INDEX idx_tenant_dept (tenant_id(100), department(100))');
    }

    public function down(): void
    {
        Schema::dropIfExists('site_locations');
    }
};
