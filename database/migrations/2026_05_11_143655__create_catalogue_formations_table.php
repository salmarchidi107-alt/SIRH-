<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('catalogue_formations')) {
            Schema::create('catalogue_formations', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id')->nullable()->index();
                $table->string('titre');
                $table->text('description')->nullable();
                $table->string('categorie')->nullable();
                $table->integer('duree_heures')->default(8);
                $table->enum('type', ['presentiel', 'distanciel', 'mixte'])->default('presentiel');
                $table->boolean('actif')->default(true);
                $table->date('date_creation')->nullable();
                $table->timestamps();
                $table->softDeletes();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }
    }

    public function down(): void { Schema::dropIfExists('catalogue_formations'); }
};
