<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('variable_elements')) {
            Schema::create('variable_elements', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id')->nullable()->index();
                $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
                $table->tinyInteger('month');
                $table->smallInteger('year');
                $table->string('type'); // gain | retenue
                $table->string('label');
                $table->decimal('amount', 10, 2);
                $table->timestamps();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('variable_elements');
    }
};
