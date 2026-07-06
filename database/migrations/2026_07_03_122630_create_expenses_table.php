<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            // Tenant (UUID)
            $table->uuid('tenant_id');

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            // Employee (BigInt)
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('category', 50);
            $table->date('expense_date');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('MAD');
            $table->text('description')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('status', 20)->default('brouillon');

            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
