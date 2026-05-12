<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id')->nullable()->index();
                $table->string('nom');
                $table->string('fichier_path')->nullable();
                $table->string('fichier_nom_original')->nullable();
                $table->unsignedBigInteger('taille')->nullable();
                $table->text('description')->nullable();
                $table->foreignId('employe_id')->nullable()->constrained('employees')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->date('date_document')->default(now());
                $table->timestamps();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
