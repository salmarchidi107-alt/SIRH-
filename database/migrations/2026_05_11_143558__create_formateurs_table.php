<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('formateurs')) {
            Schema::create('formateurs', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id')->nullable()->index();
                $table->string('nom');
                $table->string('prenom');
                $table->string('email')->nullable();
                $table->string('telephone')->nullable();
                $table->string('specialite')->nullable();
                $table->enum('type', ['interne', 'externe'])->default('externe');
                $table->boolean('actif')->default(true);
                $table->timestamps();
                $table->softDeletes();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }
    }

    public function down(): void { Schema::dropIfExists('formateurs'); }
};
