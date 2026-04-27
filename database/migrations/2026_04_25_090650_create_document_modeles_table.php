<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_modeles', function (Blueprint $table) {
    $table->id();
    $table->string('nom');
    $table->enum('categorie', ['attestation','certificat','contrat','avertissement','autre'])->default('autre');
    $table->string('fichier_path');
    $table->string('fichier_nom_original');
    $table->text('description')->nullable();
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_modeles');
    }
};
