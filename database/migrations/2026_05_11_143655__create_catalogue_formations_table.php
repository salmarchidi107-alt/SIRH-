<?php
// database/migrations/2026_01_01_000011_create_catalogue_formations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogue_formations', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('categorie')->nullable();
            $table->integer('duree_heures')->default(8); // durée en heures
            $table->enum('type', ['presentiel', 'distanciel', 'mixte'])->default('presentiel');
            $table->boolean('actif')->default(true);
            $table->date('date_creation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void { Schema::dropIfExists('catalogue_formations'); }
};
