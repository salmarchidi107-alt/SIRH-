<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('organismes_formation')) {
            Schema::create('organismes_formation', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id')->nullable()->index();
                $table->string('nom');
                $table->string('adresse')->nullable();
                $table->string('telephone')->nullable();
                $table->string('email')->nullable();
                $table->string('site_web')->nullable();
                $table->boolean('agree')->default(false);
                $table->boolean('actif')->default(true);
                $table->date('date_creation')->nullable();
                $table->timestamps();
                $table->softDeletes();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }
    }

    public function down(): void { Schema::dropIfExists('organismes_formation'); }
};
