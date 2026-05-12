<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id')->nullable()->index();
                $table->string('group', 100);
                $table->string('name', 100);
                $table->json('payload');
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent();

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->unique(['group', 'name', 'tenant_id'], 'settings_group_name_tenant_unique');
                $table->index(['group', 'name']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
