<?php
// database/migrations/2026_01_01_000010_create_formateurs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formateurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('specialite')->nullable();
            $table->enum('type', ['interne', 'externe'])->default('externe');
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void { Schema::dropIfExists('formateurs'); }
};
