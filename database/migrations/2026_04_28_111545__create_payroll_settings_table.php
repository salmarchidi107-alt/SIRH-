<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payroll_settings')) {
            Schema::create('payroll_settings', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id')->nullable()->index();
                $table->string('key');
                $table->decimal('value', 10, 4);
                $table->string('label');
                $table->string('category');
                $table->string('type')->default('rate');
                $table->text('description')->nullable();
                $table->timestamps();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                // Unicité par (key, tenant_id) pour le multi-tenant
                $table->unique(['key', 'tenant_id'], 'payroll_settings_key_tenant_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
    }
};
