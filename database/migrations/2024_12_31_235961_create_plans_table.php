<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('slug')->unique();
            $table->decimal('prix_mensuel', 10, 2)->default(0);
            $table->decimal('prix_annuel', 10, 2)->default(0);
            $table->integer('max_employes')->default(0);
            $table->integer('max_admins')->default(1);
            $table->json('fonctionnalites')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
