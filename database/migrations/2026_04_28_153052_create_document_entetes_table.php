<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('document_entetes', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->default('Entête principale');
            $table->string('logo_path')->nullable();
            $table->string('nom_societe')->nullable();
            $table->string('adresse')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('site_web')->nullable();
            $table->string('rc')->nullable();       // Registre de commerce
            $table->string('ice')->nullable();      // ICE
            $table->text('contenu_libre')->nullable(); // TinyMCE pour infos supplémentaires
            $table->boolean('actif')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('document_entetes');
    }
};