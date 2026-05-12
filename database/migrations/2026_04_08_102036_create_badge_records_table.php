<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('badge_records')) {
            Schema::create('badge_records', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('employee_id');
                $table->string('type'); // entree, sortie, pause
                $table->timestamps();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_records');
    }
};
