<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipements', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);          // UUID string — pas unsignedBigInteger
            $table->string('reference')->unique();     // PC-00125, TEL-00045 …
            $table->string('designation');
            $table->string('categorie');               // Ordinateur portable, Téléphone, Badge …
            $table->string('marque')->nullable();
            $table->string('modele')->nullable();
            $table->string('numero_serie')->nullable();
            $table->date('date_acquisition')->nullable();
            $table->string('fournisseur')->nullable();
            $table->decimal('valeur_acquisition', 12, 2)->default(0);
            $table->string('etat')->default('Neuf');   // Neuf | Bon état | À réparer | Hors service
            $table->string('statut')->default('Disponible'); // Disponible | Affecté | Maintenance | Perdu
            $table->string('localisation')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'statut']);
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipements');
    }
};
